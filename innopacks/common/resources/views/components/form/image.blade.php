<x-panel::form.row :title="$title">
  <div class="single-image-upload-wrapper">
    <div class="is-up-file" data-type="{{ $type }}">
    <div class="img-upload-item bg-light wh-80 rounded border d-flex justify-content-center align-items-center me-2 mb-2 position-relative cursor-pointer overflow-hidden">
      <div class="position-absolute tool-wrap {{ !$value ? 'd-none' : '' }} d-flex top-0 start-0 w-100 bg-primary bg-opacity-75"><div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div><div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div></div>
      <div class="position-absolute bg-white d-none img-loading"><div class="spinner-border opacity-50"></div></div>

      <div class="img-info rounded h-100 w-100 d-flex justify-content-center align-items-center">
        @php
          $isVideoValue = media_is_video($value);
          $posterUrl = $isVideoValue ? media_poster_url($value) : '';
        @endphp
        @if ($value)
          @if ($isVideoValue && $posterUrl)
            {{-- Poster frame + play overlay (video). No position-relative on the
                 wrapper: it would paint above .tool-wrap and block the buttons. --}}
            <div class="w-100 h-100 d-flex align-items-center justify-content-center" data-is-video="1">
              <img src="{{ $posterUrl }}" data-origin-img="{{ image_origin($value) }}" class="img-fluid">
              <i class="bi bi-play-circle-fill position-absolute top-50 start-50 translate-middle fs-1 text-white"></i>
            </div>
          @elseif ($isVideoValue)
            <i class="bi bi-play-circle fs-1 text-primary" data-origin-img="{{ image_origin($value) }}"></i>
          @else
            <img src="{{ image_resize($value) }}" data-origin-img="{{ image_origin($value) }}" class="img-fluid">
          @endif
        @else
          <i class="bi bi-plus fs-1 text-secondary opacity-75"></i>
        @endif
      </div>
      <input type="hidden" value="{{ $value }}" name="{{ $name }}">
    </div>

    {{-- 视频封面入口（#291）：浏览器端 canvas 抓帧，服务端不再依赖 ffmpeg。
         必须位于 .img-upload-item 之外——item 的 click 会打开媒体库弹层。
         legacy raw path 视频也能选：服务端会按 storage_key 补建 media 行。 --}}
    @if ($isVideoValue)
      <div class="pick-cover-wrap mt-1">
        <button type="button" class="btn btn-sm btn-outline-secondary pick-cover-btn">
          <i class="bi bi-camera"></i> {{ __('panel/article.choose_cover') }}
        </button>
      </div>
    @endif
  </div>
  @if ($description)
    <div class="mt-2 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{!! $description !!}
    </div>
  @endif
  {{ $slot }}
  </div>
</x-panel::form.row>


@pushOnce('footer')
@php
  $innoCoverLang = [
    'upload_failed'        => __('panel/media.upload_failed'),
    'capture_load_failed'  => __('panel/media.capture_load_failed'),
    'capture_cross_origin' => __('panel/media.capture_cross_origin'),
  ];
@endphp
<div class="modal fade" id="modal-show-img">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body text-center p-4" style="min-height: 200px; display: flex; align-items: center; justify-content: center;"></div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>

{{-- 手动选封面弹窗（#291）：拖动进度条选帧，canvas 实时预览，确认后回传 --}}
<div class="modal fade" id="modal-pick-cover">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('panel/article.choose_cover') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <video class="pick-cover-video w-100" controls muted preload="auto"></video>
        <input type="range" class="form-range pick-cover-range" min="0" max="1000" value="0">
        <canvas class="pick-cover-canvas" width="800" height="600"></canvas>
        <div class="text-danger small mt-2 pick-cover-hint d-none"><i class="bi bi-exclamation-circle me-1"></i><span class="pick-cover-hint-msg"></span></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/media.cancel') }}</button>
        <button type="button" class="btn btn-primary pick-cover-confirm">{{ __('panel/media.ok') }}</button>
      </div>
    </div>
  </div>
</div>

<script>
  // ---- video cover capture (#291): browser-side frame grab, no ffmpeg ----

  @include('common::partials.inno_cover_capture_js')

  window.innoCoverPickLabel = '{{ __('panel/article.choose_cover') }}';
  window.innoCoverLang = @json($innoCoverLang);

  /** Escape a dynamic value before string-building HTML. Storage keys preserve
      original filenames — quotes/HTML characters ride along, so every URL or
      key interpolated below is attacker-controllable and must pass through. */
  function innoEscapeAttr(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  /** Adopt the server-side media reference: legacy raw paths are upgraded to media:// so the cover survives saves. */
  function innoAdoptMediaReference(item, val, reference) {
    if (reference && val !== reference) {
      item.find('input').val(reference).trigger('change');
      return String(reference);
    }
    return val;
  }

  /** Re-render the preview as poster + play overlay (same markup as the blade branch). */
  function innoRenderVideoCover(item, posterUrl, originUrl) {
    item.find('.img-info').html(
      '<div class="w-100 h-100 d-flex align-items-center justify-content-center" data-is-video="1">'
      + '<img src="' + innoEscapeAttr(posterUrl) + '" class="img-fluid" data-origin-img="' + innoEscapeAttr(originUrl) + '">'
      + '<i class="bi bi-play-circle-fill position-absolute top-50 start-50 translate-middle fs-1 text-white"></i>'
      + '</div>'
    );
  }

  /** Keep exactly one cover-pick button under the preview card, for any video value. */
  function innoSyncCoverButton(item, isVideo) {
    item.closest('.is-up-file').find('.pick-cover-wrap').remove();
    if (!isVideo) {
      return;
    }
    item.after(
      '<div class="pick-cover-wrap mt-1">'
      + '<button type="button" class="btn btn-sm btn-outline-secondary pick-cover-btn">'
      + '<i class="bi bi-camera"></i> ' + window.innoCoverPickLabel
      + '</button>'
      + '</div>'
    );
  }

  $(document).on('click', '.single-image-upload-wrapper .is-up-file .img-upload-item', function (e) {
    // .pick-cover-btn lives outside the item, but keep it excluded here so a
    // stray click can never open the media iframe over the picker modal.
    if ($(e.target).closest('.delete-img, .show-img, .pick-cover-btn').length) {
      return;
    }
    const _self = $(this);
    // Only type="file" enables the unfiltered (video-capable) picker. The class
    // component defaults data-type to "common" when type is omitted, and the
    // media iframe currently ignores the param entirely — whitelist it so the
    // default stays byte-identical to the historical hardcoded 'image'.
    const rawType = _self.closest('.is-up-file').data('type') || '';
    const pickerType = rawType === 'file' ? 'file' : 'image';

    window.inno.mediaIframe((file) => {
      let val = file.path;
      let url = file.url; // Thumbnail for display
      let originUrl = file.origin_url || file.url; // Original file for preview
      const mime = file.mime || '';
      const isVideo = /^video\//i.test(mime) || /\.(mp4|webm|ogg)(\?|$)/i.test(val);
      _self.find('input').val(val);
      _self.find('.tool-wrap').removeClass('d-none');
      if (isVideo && file.poster_url) {
        // Extracted poster frame + play overlay. No position-relative on the
        // wrapper: a positioned box after .tool-wrap would paint above it and
        // block the preview/delete buttons.
        innoRenderVideoCover(_self, file.poster_url, originUrl);
      } else if (isVideo) {
        _self.find('.img-info').html('<i class="bi bi-play-circle fs-1 text-primary" data-origin-img="' + innoEscapeAttr(originUrl) + '"></i>');
        // Auto-capture a default cover right after the upload (#291): the
        // browser grabs an early frame and posts it to media.set_poster. Any
        // failure keeps the play-icon placeholder — posters are nice-to-have.
        const videoRef = file.media_id ? String(parseInt(file.media_id, 10)) : String(val || '');
        if (videoRef) {
          innoCaptureVideoFrame(originUrl, 1)
            .then((blob) => innoSetVideoPoster(videoRef, blob))
            .then((result) => {
              // Ignore when the editor already swapped in another value meanwhile.
              if (result.ok && result.json.success && result.json.data.poster_url && _self.find('input').val() === val) {
                const reference = innoAdoptMediaReference(_self, val, result.json.data.media_reference);
                innoRenderVideoCover(_self, result.json.data.poster_url, originUrl);
              }
            })
            .catch(() => {});
        }
      } else {
        _self.find('.img-info').html('<img src="' + innoEscapeAttr(url) + '" class="img-fluid" data-origin-img="' + innoEscapeAttr(originUrl) + '">');
      }
      innoSyncCoverButton(_self, isVideo);

      _self.find('input').trigger('change');
    }, {
      multiple: false,
      type: pickerType
    });
  });

  $(document).on('click', '.single-image-upload-wrapper .is-up-file .delete-img', function (e) {
    e.stopPropagation();
    let _self = $(this).parent().parent();
    _self.find('input').val('');
    _self.find('.tool-wrap').addClass('d-none');
    _self.find('.img-info').html('<i class="bi bi-plus fs-1 text-secondary opacity-75"></i>');
    innoSyncCoverButton(_self, false);
  });

  // ---- manual cover picking: scrub the video, confirm a frame ----

  $(document).on('click', '.single-image-upload-wrapper .is-up-file .pick-cover-btn', function (e) {
    e.stopPropagation();
    const item = $(this).closest('.is-up-file').find('.img-upload-item');
    const videoRef = String(item.find('input').val() || '');
    if (!videoRef) {
      return;
    }
    const originHolder = item.find('.img-info [data-is-video] img, .img-info i.bi-play-circle').first();
    const videoSrc = String(originHolder.data('origin-img') || '');
    if (!videoSrc) {
      return;
    }

    const modal = $('#modal-pick-cover');
    const video = modal.find('video').get(0);
    const range = modal.find('.pick-cover-range');
    const canvas = modal.find('canvas').get(0);
    const confirmBtn = modal.find('.pick-cover-confirm');
    const lang   = window.innoCoverLang || {};
    const showHint = (msg) => modal.find('.pick-cover-hint').removeClass('d-none').find('.pick-cover-hint-msg').text(msg);
    const resetHint = () => modal.find('.pick-cover-hint').addClass('d-none').find('.pick-cover-hint-msg').text('');

    confirmBtn.prop('disabled', true);
    resetHint();
    video.muted = true;
    video.src = videoSrc;
    $(video).off('error.pick-cover loadeddata.pick-cover seeked.pick-cover')
      .on('error.pick-cover', () => {
        // Load failure (missing file, codec, network): nothing to scrub —
        // say so instead of leaving a dead modal with a forever-disabled
        // confirm button.
        confirmBtn.prop('disabled', true);
        showHint(lang.capture_load_failed);
      })
      .on('loadeddata.pick-cover', () => {
        const duration = isFinite(video.duration) && video.duration > 0 ? video.duration : 0;
        range.attr('max', Math.max(1, Math.round(duration * 1000)));
        const start = Math.min(1, Math.max(0.1, duration / 10));
        range.val(Math.round(start * 1000));
        video.currentTime = start;
        // Confirm stays locked: the initial seek has not painted yet and the
        // canvas is still blank white — unlocking here let a fast OK capture
        // a white frame and replace a good poster with it.
      })
      .on('seeked.pick-cover', () => {
        innoDrawContain(video, canvas);
        confirmBtn.prop('disabled', false);
      });
    range.off('input.pick-cover').on('input.pick-cover', () => {
      video.currentTime = parseInt(range.val(), 10) / 1000;
    });
    modal.off('hidden.bs.modal.pick-cover').on('hidden.bs.modal.pick-cover', () => {
      // Release the media element so a modal re-open starts from a clean state.
      video.removeAttribute('src');
      confirmBtn.prop('disabled', true);
      resetHint();
    });

    confirmBtn.off('click.pick-cover').on('click.pick-cover', () => {
      confirmBtn.prop('disabled', true);
      resetHint();
      let dataUrl;
      try {
        dataUrl = canvas.toDataURL('image/jpeg', 0.85);
      } catch (e) {
        // Tainted canvas: the video is cross-origin without CORS headers, so
        // browser security forbids pixel export. Retrying can never succeed —
        // surface the reason instead of bricking the button silently.
        showHint(lang.capture_cross_origin);
        return;
      }
      innoSetVideoPoster(videoRef, dataUrl).then((result) => {
        confirmBtn.prop('disabled', false);
        if (result.ok && result.json.success && result.json.data.poster_url) {
          modal.modal('hide');
          innoAdoptMediaReference(item, videoRef, result.json.data.media_reference);
          innoRenderVideoCover(item, result.json.data.poster_url, videoSrc);
        } else {
          showHint(lang.upload_failed);
        }
      }).catch(() => {
        confirmBtn.prop('disabled', false);
        showHint(lang.upload_failed);
      });
    });

    modal.modal('show');
  });

  $(document).on('click', '.single-image-upload-wrapper .is-up-file .show-img', function (e) {
    e.stopPropagation();
    const item = $(this).closest('.img-upload-item');
    const videoWrap = item.find('.img-info [data-is-video]');
    const playIcon = item.find('.img-info i.bi-play-circle');
    const imgElement = item.find('.img-info img');
    let tag = 'img';
    let src;
    if (videoWrap.length) {
      // Poster + overlay structure: the origin URL on the inner img is the video.
      tag = 'video';
      src = videoWrap.find('img').data('origin-img') || '';
    } else if (playIcon.length) {
      // Play-icon placeholder (video without an extracted poster yet).
      tag = 'video';
      src = playIcon.data('origin-img') || '';
    } else {
      src = imgElement.data('origin-img') || imgElement.attr('src');
    }
    if (!src) {
      console.error('Image source not found');
      return;
    }
    // .preview-media (defined in panel global.scss) constrains the media inside the modal.
    const html = tag === 'video'
      ? '<video src="' + innoEscapeAttr(src) + '" controls autoplay muted class="img-fluid preview-media"></video>'
      : '<img src="' + innoEscapeAttr(src) + '" class="img-fluid preview-media">';
    $('#modal-show-img .modal-body').html(html);
    $('#modal-show-img').modal('show');
  });
</script>
@endPushOnce
