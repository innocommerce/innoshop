import { test } from 'node:test';
import assert from 'node:assert/strict';
import { analyzeVideoCodecBytes, parseContentRange, probeVideoUrl } from '../../resources/assets/js/app/panel-video-codec.js';

/**
 * Synthetic MP4 fixtures. Only the fourcc signatures the scanner looks for
 * matter; everything else is filler bytes chosen not to collide with them.
 */

function bytes(...parts) {
  const chunks = parts.map((p) => {
    if (typeof p === 'string') return Uint8Array.from(p, (c) => c.charCodeAt(0));
    return p;
  });
  const total = chunks.reduce((n, c) => n + c.length, 0);
  const out = new Uint8Array(total);
  let offset = 0;
  for (const c of chunks) {
    out.set(c, offset);
    offset += c.length;
  }
  return out;
}

const FTYP = '\x00\x00\x00\x18ftypisom\x00\x00\x02\x00isomiso2mp41';

test('flags H.265/HEVC sample entry (hvc1 + hvcC, moov at end like phone recordings)', () => {
  const data = bytes(FTYP, 'mdat', '\x00'.repeat(64), 'hvc1', '\x00'.repeat(16), 'hvcC', '\x00'.repeat(16));
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, true);
  assert.equal(result.codec, 'hevc');
});

test('flags H.265/HEVC alternate sample entry (hev1)', () => {
  const data = bytes(FTYP, 'moov', 'hev1', '\x00'.repeat(8), 'hvcC');
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, true);
  assert.equal(result.codec, 'hevc');
});

test('flags Dolby Vision over HEVC (dvh1) as HEVC-family', () => {
  const data = bytes(FTYP, 'dvh1', '\x00'.repeat(8), 'hvcC');
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, true);
  assert.equal(result.codec, 'hevc');
});

test('accepts H.264/AVC (avc1 + avcC) as web-safe', () => {
  const data = bytes(FTYP, 'mdat', '\x00'.repeat(64), 'avc1', '\x00'.repeat(16), 'avcC');
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, false);
  assert.equal(result.webSafe, true);
});

test('accepts H.264/AVC alternate sample entry (avc3)', () => {
  const data = bytes(FTYP, 'avc3', '\x00'.repeat(8), 'avcC');
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, false);
  assert.equal(result.webSafe, true);
});

test('accepts VP9-in-MP4 (vp09) as web-safe', () => {
  const data = bytes(FTYP, 'vp09', '\x00'.repeat(8));
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, false);
  assert.equal(result.webSafe, true);
});

test('accepts AV1-in-MP4 (av01) as web-safe', () => {
  const data = bytes(FTYP, 'av01', '\x00'.repeat(8));
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, false);
  assert.equal(result.webSafe, true);
});

test('stays silent (no flags) on bytes with no known video codec signatures', () => {
  const data = bytes(FTYP, 'mdat', 'just some ordinary binary payload \x01\x02\x03');
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, false);
  assert.equal(result.webSafe, false);
});

test('stays silent on empty and tiny inputs', () => {
  for (const data of [new Uint8Array(0), bytes('ftyp')]) {
    const result = analyzeVideoCodecBytes(data);
    assert.equal(result.incompatible, false);
    assert.equal(result.webSafe, false);
  }
});

test('HEVC detection wins when both HEVC and H.264 signatures appear', () => {
  // Some muxers keep leftovers of both track types; playing HEVC is what matters.
  const data = bytes(FTYP, 'avc1', 'avcC', 'hvc1', 'hvcC');
  const result = analyzeVideoCodecBytes(data);
  assert.equal(result.incompatible, true);
  assert.equal(result.codec, 'hevc');
});

// ---- URL range probing (media library badge scan) ----

function rangeResponse(body, { status = 206, total = null, start = 0 } = {}) {
  const headers = {};
  if (status === 206) {
    headers['Content-Range'] = `bytes ${start}-${start + body.length - 1}/${total}`;
  }
  if (total !== null || status === 200) {
    headers['Content-Length'] = String(body.length);
  }
  return new Response(body, { status, headers });
}

test('parseContentRange extracts total size from a Content-Range header', () => {
  assert.equal(parseContentRange('bytes 0-262143/13624066'), 13624066);
  assert.equal(parseContentRange('bytes 13588479-13624065/13624066'), 13624066);
});

test('parseContentRange returns null for unparsable or missing headers', () => {
  assert.equal(parseContentRange('bytes */*'), null);
  assert.equal(parseContentRange(''), null);
  assert.equal(parseContentRange(null), null);
});

function makeRangeFetcher(serverFile) {
  return async (url, init = {}) => {
    const m = /bytes=(\d+)-(\d+)/.exec(init.headers.Range);
    if (!m) throw new Error('expected a Range request');
    const start = parseInt(m[1], 10);
    const end = parseInt(m[2], 10);
    const slice = serverFile.subarray(start, end + 1);
    return rangeResponse(slice, { status: 206, total: serverFile.length, start });
  };
}

test('probeVideoUrl detects HEVC sitting only in the tail via two range requests', async () => {
  const filler = new Uint8Array(1024 * 1024).fill(0x61);
  const serverFile = new Uint8Array(16 + filler.length + 24);
  serverFile.set(Uint8Array.from('ftypisommdat', (c) => c.charCodeAt(0)), 0);
  serverFile.set(filler, 16);
  serverFile.set(Uint8Array.from('moovxxhvc1hvcCEND', (c) => c.charCodeAt(0)), 16 + filler.length);

  let requestCount = 0;
  const fetchImpl = async (...args) => { requestCount++; return makeRangeFetcher(serverFile)(...args); };
  const result = await probeVideoUrl('http://example.test/v.mp4', fetchImpl);
  assert.equal(result.incompatible, true);
  assert.equal(result.codec, 'hevc');
  assert.equal(result.unknown, false);
  assert.equal(requestCount, 2);
});

test('probeVideoUrl accepts a small plain-200 response with H.264 signatures', async () => {
  const body = Uint8Array.from('ftypisomavc1avcC' + 'x'.repeat(256), (c) => c.charCodeAt(0));
  const fetchImpl = async () => rangeResponse(body, { status: 200 });
  const result = await probeVideoUrl('http://example.test/v.mp4', fetchImpl);
  assert.equal(result.incompatible, false);
  assert.equal(result.webSafe, true);
  assert.equal(result.unknown, false);
});

test('probeVideoUrl stays unknown when the server ignores Range on a big file', async () => {
  // Plain 200 with a huge content-length: the small-file fallback must be denied.
  const big = async () => new Response(new Uint8Array(64), {
    status: 200,
    headers: { 'Content-Length': String(500 * 1024 * 1024) },
  });
  const result = await probeVideoUrl('http://example.test/v.mp4', big);
  assert.equal(result.unknown, true);
});

test('probeVideoUrl stays unknown when fetch throws (CORS, network, offline)', async () => {
  const fetchImpl = async () => { throw new TypeError('Failed to fetch'); };
  const result = await probeVideoUrl('http://cdn.example.test/v.mp4', fetchImpl);
  assert.equal(result.unknown, true);
  assert.equal(result.incompatible, false);
});

test('probeVideoUrl stays unknown without a fetch implementation or url', async () => {
  const result = await probeVideoUrl('', async () => rangeResponse(new Uint8Array(16), { status: 200 }));
  assert.equal(result.unknown, true);
});
