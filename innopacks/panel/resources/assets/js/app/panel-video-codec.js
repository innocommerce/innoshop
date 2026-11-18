/**
 * Client-side video codec compatibility probe (exposed as `window.innoProbeVideoCodec`).
 *
 * Browsers play video with their built-in decoders, and H.264/AVC is the only
 * codec they universally support. Phones increasingly record H.265/HEVC, which
 * most browsers cannot decode, so such uploads play for nobody in the panel or
 * on the storefront. We cannot transcode client-side; the best layer-1 fix is
 * telling the manager immediately instead of after the video ships.
 *
 * The probe reads two small slices of the file (head + tail, where the moov
 * atom lives for both faststart and phone-recorded layouts) and scans them for
 * codec fourcc signatures. It never blocks the upload: incompatible files get
 * a warning, unknown files stay silent — a false warning is worse than none.
 */

const HEAD_WINDOW = 2 * 1024 * 1024;
const TAIL_WINDOW = 2 * 1024 * 1024;

// H.265/HEVC family sample entries and config boxes. dvh1/dvhC are Dolby
// Vision, which is carried over HEVC and equally undecodable.
const HEVC_SIGNATURES = ['hvc1', 'hev1', 'hvcC', 'dvh1', 'dvhC'];

// Codecs browsers decode natively when muxed into MP4: H.264/AVC, VP8/VP9,
// AV1. (webm/ogg uploads are web-safe by construction and never probed.)
const WEB_SAFE_SIGNATURES = ['avc1', 'avc3', 'avcC', 'vp08', 'vp09', 'av01'];

function toLatin1String(bytes) {
  let out = '';
  for (let i = 0; i < bytes.length; i += 0x8000) {
    out += String.fromCharCode.apply(null, bytes.subarray(i, i + 0x8000));
  }
  return out;
}

function containsAny(text, signatures) {
  for (const sig of signatures) {
    if (text.indexOf(sig) !== -1) {
      return true;
    }
  }
  return false;
}

const URL_HEAD_WINDOW = 256 * 1024;
const URL_TAIL_WINDOW = 256 * 1024;

const UNKNOWN = { incompatible: false, codec: null, webSafe: false, unknown: true };

/**
 * Extract the total size from a Content-Range response header.
 *
 * @param {string|null} header e.g. "bytes 0-262143/13624066"
 * @returns {number|null}
 */
export function parseContentRange(header) {
  if (!header) {
    return null;
  }
  const match = /\/(\d+)\s*$/.exec(header);
  return match ? parseInt(match[1], 10) : null;
}

/**
 * Probe an already-uploaded video over HTTP with small Range requests, so the
 * media library can badge stored HEVC files without backend support and
 * without loading whole videos. Range-unsupported servers only get probed
 * when the file is small enough to fetch in full; anything else — CORS
 * blocks, offline, non-MP4 servers — stays unknown and the UI stays silent.
 *
 * @param {string} url
 * @param {Function} [fetchImpl] injectable for tests
 * @returns {Promise<{incompatible: boolean, codec: 'hevc'|null, webSafe: boolean, unknown: boolean}>}
 */
export async function probeVideoUrl(url, fetchImpl) {
  const f = fetchImpl || (typeof fetch !== 'undefined' ? fetch : null);
  if (!f || !url) {
    return UNKNOWN;
  }
  try {
    const headRes = await f(url, { headers: { Range: 'bytes=0-' + (URL_HEAD_WINDOW - 1) } });
    if (!headRes.ok) {
      return UNKNOWN;
    }
    let headBytes;
    let total = null;
    if (headRes.status === 206) {
      headBytes = new Uint8Array(await headRes.arrayBuffer());
      total = parseContentRange(headRes.headers.get('content-range'));
    } else {
      const length = parseInt(headRes.headers.get('content-length') || '0', 10);
      if (!length || length > URL_HEAD_WINDOW * 2) {
        return UNKNOWN;
      }
      headBytes = new Uint8Array(await headRes.arrayBuffer());
    }
    let tailBytes = null;
    if (total && total > headBytes.length) {
      const start = Math.max(0, total - URL_TAIL_WINDOW);
      const tailRes = await f(url, { headers: { Range: 'bytes=' + start + '-' + (total - 1) } });
      if (tailRes.status === 206) {
        tailBytes = new Uint8Array(await tailRes.arrayBuffer());
      }
    }
    let bytes = headBytes;
    if (tailBytes) {
      const merged = new Uint8Array(headBytes.length + tailBytes.length);
      merged.set(headBytes, 0);
      merged.set(tailBytes, headBytes.length);
      bytes = merged;
    }
    return { ...analyzeVideoCodecBytes(bytes), unknown: false };
  } catch (e) {
    return UNKNOWN;
  }
}

/**
 * Classify scanned MP4 bytes. Pure function so it is unit-testable
 * (see tests/js/panel-video-codec.test.js).
 *
 * @param {Uint8Array} bytes - head and tail slices of the file, concatenated
 * @returns {{incompatible: boolean, codec: 'hevc'|null, webSafe: boolean}}
 */
export function analyzeVideoCodecBytes(bytes) {
  if (!bytes || bytes.length < 8) {
    return { incompatible: false, codec: null, webSafe: false };
  }
  const text = toLatin1String(bytes);
  if (containsAny(text, HEVC_SIGNATURES)) {
    return { incompatible: true, codec: 'hevc', webSafe: false };
  }
  if (containsAny(text, WEB_SAFE_SIGNATURES)) {
    return { incompatible: false, codec: null, webSafe: true };
  }
  return { incompatible: false, codec: null, webSafe: false };
}

/**
 * Probe a video File/Blob without loading it fully: read head and tail
 * windows only. Note a fourcc split exactly across the head/tail seam would
 * be missed; moov boxes never straddle file positions that way in practice.
 *
 * @param {File|Blob} file
 * @returns {Promise<{incompatible: boolean, codec: 'hevc'|null, webSafe: boolean}>}
 */
export async function probeVideoFile(file) {
  if (!file || typeof file.slice !== 'function' || file.size < 12) {
    return { incompatible: false, codec: null, webSafe: false };
  }
  const head = new Uint8Array(
    await file.slice(0, Math.min(HEAD_WINDOW, file.size)).arrayBuffer()
  );
  let bytes = head;
  if (file.size > HEAD_WINDOW) {
    const tail = new Uint8Array(
      await file.slice(Math.max(0, file.size - TAIL_WINDOW)).arrayBuffer()
    );
    const merged = new Uint8Array(head.length + tail.length);
    merged.set(head, 0);
    merged.set(tail, head.length);
    bytes = merged;
  }
  return analyzeVideoCodecBytes(bytes);
}

if (typeof window !== 'undefined') {
  window.innoProbeVideoCodec = probeVideoFile;
  window.innoProbeVideoCodecUrl = probeVideoUrl;
}
