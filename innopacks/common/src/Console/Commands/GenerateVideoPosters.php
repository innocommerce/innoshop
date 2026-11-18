<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Illuminate\Console\Command;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Services\VideoPosterService;
use Throwable;

class GenerateVideoPosters extends Command
{
    protected $signature = 'media:generate-posters
                            {--limit=0 : Max videos to process (0 = no limit)}
                            {--force : Re-extract posters even when one is already linked (the replaced poster row is pruned automatically when no other video shares it)}';

    /**
     * Optional backfill for videos that never went through the panel upload
     * form (REST/CLI ingestion, legacy rows). Regular panel uploads capture
     * their poster in the browser (canvas → media.set_poster), so this
     * command — and its ffmpeg dependency — is opt-in, not a deployment
     * requirement (#291).
     */
    protected $description = 'Backfill poster frames for videos that have none (API/CLI ingested or legacy rows). Requires the ffmpeg binary; panel uploads do not.';

    /**
     * Handle the command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $force = (bool) $this->option('force');

        // Mirror VideoPosterService::isVideoMedia(): finfo reports mp4 as
        // application/mp4 (not video/*) and API ingests may carry an empty
        // mime — the old video/*-only filter made exactly those rows
        // invisible to the backfill while isVideoMedia kept rendering them
        // as poster-less videos.
        $query = MediaFile::query()->where(function ($q) {
            $q->where('mime', 'like', 'video/%')
                ->orWhere('storage_key', 'like', '%.mp4')
                ->orWhere('storage_key', 'like', '%.webm')
                ->orWhere('storage_key', 'like', '%.ogg');
        });
        if (! $force) {
            $query->whereNull('poster_media_id');
        }
        if ($limit > 0) {
            $query->limit($limit);
        }

        // Container resolution lets tests bind a fake VideoPosterExtractor.
        $service = app(VideoPosterService::class);
        $done    = 0;
        $failed  = 0;

        // chunkById keeps memory flat on large media libraries; the --limit
        // cap (when set) stays applied to the builder across chunks.
        $query->chunkById(200, function ($videos) use ($service, $force, &$done, &$failed) {
            foreach ($videos as $video) {
                try {
                    if ($service->ensurePoster($video, $force)) {
                        $done++;
                        $this->line("  [OK] {$video->storage_key}");
                    } else {
                        $failed++;
                        $this->line("  [SKIP] {$video->storage_key} (see log for reason)");
                    }
                } catch (Throwable $e) {
                    $failed++;
                    $this->line("  [FAIL] {$video->storage_key}: {$e->getMessage()}");
                }
            }
        });

        if ($done + $failed === 0) {
            $this->info('No videos need poster extraction.');

            return self::SUCCESS;
        }

        $this->info("Done. Extracted: {$done}, failed/skipped: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
