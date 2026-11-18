<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Extracts a single poster frame from a video using ffmpeg.
 *
 * Designed to fail gracefully: every failure mode (missing binary, timeout,
 * non-zero exit, unreadable source) returns false and logs a warning, so the
 * caller can keep the upload flow running without a poster. The whole point
 * is "nice to have", not "must have".
 */
class VideoPosterExtractor
{
    /**
     * Default poster canvas. Chosen to match the recommended cover size of
     * 800x600 documented in the article panel.
     */
    public const DEFAULT_WIDTH = 800;

    public const DEFAULT_HEIGHT = 600;

    /**
     * Skip the first second of footage to avoid black/title frames before
     * grabbing a real frame.
     */
    private const SKIP_PREFIX_SECONDS = 1;

    /**
     * Hard cap on ffmpeg runtime. A single frame grab is normally <1s on
     * local disk, but remote-backed storage (S3 etc.) can be slower.
     */
    private const TIMEOUT_SECONDS = 30;

    /**
     * Cached `which ffmpeg` lookup so we don't fork a process per upload.
     */
    private static ?string $resolvedBinary = null;

    private static ?bool $binaryAvailable = null;

    /**
     * @param  string  $videoAbsolutePath  Absolute path to the source video on disk.
     * @param  string  $posterAbsolutePath  Absolute path where the poster image should be written.
     * @param  int  $width  Output canvas width.
     * @param  int  $height  Output canvas height.
     * @return bool True when the poster was successfully written, false on any failure.
     */
    public function extract(
        string $videoAbsolutePath,
        string $posterAbsolutePath,
        int $width = self::DEFAULT_WIDTH,
        int $height = self::DEFAULT_HEIGHT,
    ): bool {
        if (! $this->isBinaryAvailable()) {
            Log::warning('VideoPosterExtractor: ffmpeg binary not found; skipping poster extraction.');

            return false;
        }

        if (! is_file($videoAbsolutePath) || ! is_readable($videoAbsolutePath)) {
            Log::warning('VideoPosterExtractor: video source not readable.', ['path' => $videoAbsolutePath]);

            return false;
        }

        // Ensure parent dir exists (caller usually pre-creates it, but be safe).
        $posterDir = dirname($posterAbsolutePath);
        if (! is_dir($posterDir) && ! @mkdir($posterDir, 0755, true) && ! is_dir($posterDir)) {
            Log::warning('VideoPosterExtractor: cannot create poster directory.', ['dir' => $posterDir]);

            return false;
        }

        // Filter strategy:
        //   1. scale down to fit within W:H preserving aspect ratio
        //   2. pad to exact W:H with white (matches panel cover conventions)
        // Skip the first second to avoid black leader frames common in mp4s.
        $vf = sprintf(
            'scale=%d:%d:force_original_aspect_ratio=decrease,pad=%d:%d:(ow-iw)/2:(oh-ih)/2:white',
            $width, $height, $width, $height
        );

        $cmd = [
            self::$resolvedBinary,
            '-y',
            '-loglevel', 'error',
            '-ss', (string) self::SKIP_PREFIX_SECONDS,
            '-i', $videoAbsolutePath,
            '-frames:v', '1',
            '-vf', $vf,
            '-q:v', '2',
            $posterAbsolutePath,
        ];

        try {
            $process = new Process($cmd);
            $process->setTimeout(self::TIMEOUT_SECONDS);
            $process->run();
        } catch (\Throwable $e) {
            Log::warning('VideoPosterExtractor: ffmpeg invocation failed.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if (! $process->isSuccessful()) {
            Log::warning('VideoPosterExtractor: ffmpeg returned non-zero exit.', [
                'exit_code' => $process->getExitCode(),
                'stderr'    => $process->getErrorOutput(),
                'video'     => basename($videoAbsolutePath),
            ]);

            return false;
        }

        if (! is_file($posterAbsolutePath) || filesize($posterAbsolutePath) === 0) {
            Log::warning('VideoPosterExtractor: poster file missing or empty after ffmpeg.');

            return false;
        }

        return true;
    }

    /**
     * Lazy probe for the ffmpeg binary on PATH.
     */
    public function isBinaryAvailable(): bool
    {
        if (self::$binaryAvailable !== null) {
            return self::$binaryAvailable;
        }

        $binary = $this->probeBinary();
        if ($binary === null) {
            self::$binaryAvailable = false;

            return false;
        }

        self::$resolvedBinary  = $binary;
        self::$binaryAvailable = true;

        return true;
    }

    /**
     * Resolve the ffmpeg binary, checking both PATH and common Windows
     * locations (which `which` may miss if /usr/bin/which is not available
     * under Git Bash).
     */
    private function probeBinary(): ?string
    {
        $candidates = ['ffmpeg'];

        // Git Bash on Windows: try common install paths.
        if (DIRECTORY_SEPARATOR === '\\') {
            $candidates[] = 'C:\\ProgramData\\chocolatey\\bin\\ffmpeg.exe';
            $candidates[] = 'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe';
        }

        foreach ($candidates as $candidate) {
            try {
                $process = new Process([$candidate, '-version']);
                $process->setTimeout(5);
                $process->run();
                if ($process->isSuccessful()) {
                    return $candidate;
                }
            } catch (\Throwable $e) {
                // try next
            }
        }

        return null;
    }
}
