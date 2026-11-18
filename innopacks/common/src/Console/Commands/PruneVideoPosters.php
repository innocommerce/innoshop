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
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Models\MediaFile;
use Throwable;

class PruneVideoPosters extends Command
{
    protected $signature = 'media:prune-posters
                            {--dry-run : List what would be deleted without deleting anything}';

    protected $description = 'Delete machine-generated poster frames (source=video_poster) that no video links and no business row references — accumulated by repeated cover re-picking before automatic pruning existed.';

    /**
     * Handle the command.
     */
    public function handle(): int
    {
        $pruned = 0;
        $kept   = 0;
        $dryRun = (bool) $this->option('dry-run');

        MediaFile::query()
            ->where('source', 'video_poster')
            ->orderBy('id')
            ->chunkById(200, function ($posters) use (&$pruned, &$kept, $dryRun) {
                foreach ($posters as $poster) {
                    // A video sitting in the trash still owns its poster
                    // (the link is restorable) — ignore SoftDeletes here.
                    $linked = MediaFile::withTrashed()->where('poster_media_id', $poster->id)->exists();
                    if ($linked) {
                        $kept++;

                        continue;
                    }

                    // Business references (media://{id} or the raw key in
                    // product/article/category covers) count as in-use —
                    // same guard the media manager enforces for deletes.
                    // isInUse() short-circuits on the first hit and reuses
                    // the per-request schema cache (#291); only posters that
                    // are actually referenced pay for the per-table breakdown
                    // used in the log line below.
                    if ($poster->isInUse()) {
                        $kept++;
                        $this->line('  [KEPT] #'.$poster->id.' referenced by: '.implode(', ', array_keys($poster->usageCount())));

                        continue;
                    }

                    if ($dryRun) {
                        $this->line("[DRY] would prune #{$poster->id} {$poster->storage_key}");
                        $pruned++;

                        continue;
                    }

                    try {
                        $rawKey = $poster->getRawStorageKey();
                        Storage::disk('media')->delete($rawKey);

                        $variantDir  = public_path('cache/static/media/'.pathinfo($rawKey, PATHINFO_DIRNAME));
                        $variantBase = pathinfo($rawKey, PATHINFO_FILENAME);
                        foreach (glob($variantDir.'/'.$variantBase.'-*') ?: [] as $variant) {
                            @unlink($variant);
                        }

                        $poster->delete();
                        $pruned++;
                        $this->line("  [PRUNED] {$poster->storage_key}");
                    } catch (Throwable $e) {
                        $this->line("  [SKIP] #{$poster->id}: {$e->getMessage()}");
                    }
                }
            });

        $suffix = $dryRun ? ' (dry-run, nothing deleted)' : '';
        $this->info("Done. Pruned: {$pruned}, still referenced: {$kept}.{$suffix}");

        return self::SUCCESS;
    }
}
