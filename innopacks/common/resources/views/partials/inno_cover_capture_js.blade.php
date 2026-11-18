{{--
  Shared video-cover capture helpers (#291).

  The pick-cover modal in the form component and the media manager iframe
  each needed the same three primitives:
    - innoCaptureVideoFrame(src, maxSeekSeconds) -> Promise<dataUrl>
    - innoDrawContain(video, canvas)               -> draws the current frame
    - innoSetVideoPoster(videoRef, dataUrl, attempts) -> POST + retry

  Keeping them inline in both blade files led to drift: token reading
  diverged, the visibilityState guard had to be added in two places. This
  partial is the single source of truth — @include it inside any existing
  <script> block. All three are attached to window so they survive both
  @pushOnce (form component) and @push (media footer) contexts without
  scope gotchas. The partial emits raw JS statements, NOT a <script> tag,
  so callers control the wrapping.
--}}
window.innoCaptureVideoFrame = function (src, maxSeekSeconds) {
  return new Promise((resolve, reject) => {
    const video = document.createElement('video');
    video.muted = true;
    video.preload = 'auto';
    video.src = src;
    const fail = (err) => {
      video.removeAttribute('src');
      reject(err);
    };
    video.addEventListener('error', () => fail(new Error('video load failed')));
    video.addEventListener('loadeddata', () => {
      const duration = isFinite(video.duration) && video.duration > 0 ? video.duration : 1;
      video.currentTime = Math.min(maxSeekSeconds, Math.max(0.1, duration / 10));
    });
    video.addEventListener('seeked', () => {
      try {
        const canvas = document.createElement('canvas');
        canvas.width = 800;
        canvas.height = 600;
        window.innoDrawContain(video, canvas);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        video.removeAttribute('src');
        if (dataUrl.indexOf('data:image/jpeg;base64,') === 0) {
          resolve(dataUrl);
        } else {
          reject(new Error('canvas export failed'));
        }
      } catch (e) {
        fail(e);
      }
    });
  });
};

window.innoDrawContain = function (video, canvas) {
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  if (!video.videoWidth || !video.videoHeight) {
    return;
  }
  const scale = Math.min(canvas.width / video.videoWidth, canvas.height / video.videoHeight);
  const width = video.videoWidth * scale;
  const height = video.videoHeight * scale;
  ctx.drawImage(video, (canvas.width - width) / 2, (canvas.height - height) / 2, width, height);
};

window.innoSetVideoPoster = function (videoRef, dataUrl, attempts) {
  attempts = attempts || 6;
  // The retry chain runs up to 6 × 2.5s = 15s of background fetches after
  // a failure — when the user has closed or backgrounded the tab, every
  // retry is pure waste (#291). Bail on retries (attempts < 6) when the
  // page is hidden; the initial user-triggered call still goes through.
  if (attempts < 6 && document.visibilityState === 'hidden') {
    return Promise.resolve({ ok: false, json: { success: false } });
  }
  const base = ((window.urls && window.urls.panel_api) || '/api/panel').replace(/\/+$/, '');
  // Codebase convention (#291): read the API token from the server-
  // rendered meta tag (panel-config.js, _header.blade.php, etc.).
  const token = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const isId = /^\d+$/.test(String(videoRef));
  const videoPayload = isId ? String(parseInt(videoRef, 10)) : btoa(unescape(encodeURIComponent(videoRef)));
  return fetch(base + '/media/set-poster', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + token,
      'X-CSRF-TOKEN': (csrfMeta && csrfMeta.content) || '',
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ video: videoPayload, video_encoded: isId ? false : true, poster_base64: dataUrl })
  }).then((resp) => resp.json().then((json) => ({ ok: resp.ok, json: json }))).then((result) => {
    if ((!result.ok || !result.json.success) && attempts > 1) {
      return new Promise((resolve) => setTimeout(resolve, 2500))
        .then(() => window.innoSetVideoPoster(videoRef, dataUrl, attempts - 1));
    }
    return result;
  }).catch(() => {
    if (attempts > 1) {
      return new Promise((resolve) => setTimeout(resolve, 2500))
        .then(() => window.innoSetVideoPoster(videoRef, dataUrl, attempts - 1));
    }
    return { ok: false, json: { success: false } };
  });
};
