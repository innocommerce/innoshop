<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InnoShop\Common\Services\MediaUsageScanner;

return new class extends Migration
{
    /**
     * Add a unique index on media_files.storage_key.
     *
     * The table was created with a plain index, which left room for two
     * failure modes (#291):
     *
     * - VideoPosterService::resolveVideoMedia() / adoptThemeVideo() collapse
     *   concurrent adoptions with firstOrCreate(). Without a unique
     *   constraint, two parallel requests can each see "no row" and both
     *   insert — observed on enterprise.test as two `logo(1).png` rows.
     * - The earlier `ensurePoster()` path registered a second row on the
     *   same deterministic poster path under `--force` (fixed in this
     *   branch by updating the existing row in place; pre-fix duplicates
     *   linger).
     *
     * A unique index refuses to build while duplicates exist, so this
     * migration collapses each duplicate group first. The index covers
     * soft-deleted rows too, so a group is ALL rows sharing the key,
     * regardless of `deleted_at` (production hit this: two soft-deleted
     * uploads of the same poster frame blocked the index build). Per storage_key:
     *
     *  - Winner = a live row referenced via `poster_media_id` (the link is
     *    restorable, so its target row must survive).
     *  - Otherwise = the most recent live row (on-disk file matches the last
     *    write — older rows are stale snapshots under the same path).
     *  - If no live row exists, the most recent soft-deleted row survives so
     *    a future restore keeps the latest write.
     *
     * Losers have any `poster_media_id` value pointing at them re-pointed
     * at the winner, then are hard-deleted. Hard-deleting soft-deleted
     * losers is safe: MediaUrlResolver::resolve() goes through Eloquent's
     * SoftDeletes scope, so `media://{id}` refs to those rows already
     * resolve to a placeholder — nothing functional is lost.
     *
     * Business references (`media://{id}` in products.images, articles.image,
     * …) are re-pointed at the winner before the losers disappear: both rows
     * share one storage_key, so they serve the very same object and the
     * rendered image is unchanged. Leaving a reference on a deleted row
     * would break the cover — that, not the re-pointing, is the failure this
     * step exists to prevent.
     *
     * @return void
     */
    public function up(): void
    {
        $this->relinkBusinessReferences();

        $this->deduplicateStorageKeys();

        Schema::table('media_files', function (Blueprint $table) {
            // dropIndex/listIndex must be idempotent: a previous partial
            // run (the production hit this branch) already consumed the
            // plain index, so re-running must not blow up on its absence.
            if ($this->indexExists('media_files_storage_key_index')) {
                $table->dropIndex('media_files_storage_key_index');
            }
            if (! $this->indexExists('media_files_storage_key_unique')) {
                $table->unique('storage_key', 'media_files_storage_key_unique');
            }
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropUnique('media_files_storage_key_unique');
            $table->index('storage_key', 'media_files_storage_key_index');
        });
    }

    /**
     * Check whether an index with the given name exists on the table.
     * Schema::getIndexes() works on both MySQL/MariaDB and SQLite (the test
     * driver), so the same idempotency guard runs in tests and production.
     */
    private function indexExists(string $indexName): bool
    {
        foreach (Schema::getIndexes('media_files') as $index) {
            if (($index['name'] ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Collapse every storage_key with more than one row down to one —
     * soft-deleted rows included, since the unique index spans them too.
     * Driver-agnostic — operates through the query builder so the same
     * logic runs on MySQL, MariaDB and SQLite (the test driver).
     */
    private function deduplicateStorageKeys(): void
    {
        foreach ($this->duplicateStorageKeys() as $storageKey) {
            $rows = DB::table('media_files')
                ->where('storage_key', $storageKey)
                ->orderBy('id')
                ->get();

            if ($rows->count() < 2) {
                continue;
            }

            $winner = $this->pickWinner($rows);

            foreach ($rows->where('id', '!=', $winner->id) as $loser) {
                // Re-link any FK pointing at the loser onto the winner
                // before the loser disappears (poster_media_id is the only
                // self-referencing FK in the table today).
                DB::table('media_files')
                    ->where('poster_media_id', $loser->id)
                    ->update(['poster_media_id' => $winner->id]);

                DB::table('media_files')->where('id', $loser->id)->delete();
            }
        }
    }

    /**
     * Choose the surviving row for a duplicate storage_key group.
     * Prefer a live row referenced via poster_media_id (the FK is
     * restorable, so dropping the target row would orphan the link);
     * otherwise prefer the most recent live row, since on-disk bytes match
     * the last write. When the group holds only soft-deleted rows, keep the
     * most recent one so a future restore picks up the latest write.
     *
     * @param  Collection<int, object>  $rows  Rows for one storage_key (live and soft-deleted), ordered by id asc.
     * @return object
     */
    private function pickWinner(Collection $rows): object
    {
        $live = $rows->whereNull('deleted_at')->values();

        if ($live->isEmpty()) {
            return $rows->sortByDesc('id')->first();
        }

        $referenced = $live->first(function ($row) {
            return DB::table('media_files')
                ->where('poster_media_id', $row->id)
                ->exists();
        });

        if ($referenced !== null) {
            return $referenced;
        }

        return $live->sortByDesc('id')->first();
    }

    /**
     * Re-point business references at the surviving row of every duplicate
     * group before the losers are dropped.
     *
     * A `media://{id}` reference stored in products.images / articles.image /
     * … names a media row, and the row's storage_key names the stored object.
     * Both rows of a duplicate group carry the SAME storage_key, so they
     * serve the same object: re-pointing the reference leaves the rendered
     * cover byte-identical, while leaving it on the deleted row would break
     * it (MediaUrlResolver::resolve() falls back to the placeholder for a
     * missing row).
     *
     * Soft-deleted losers are deliberately left alone: a reference to one
     * already resolves to a placeholder, and re-pointing it would resurrect
     * a picture the operator deleted.
     *
     * The only case that cannot be fixed by re-pointing is a loser on a
     * different disk than the winner — the same key on two disks is two
     * different objects, so swapping the reference would silently change the
     * picture. That one fails loudly.
     *
     * @throws RuntimeException when a loser and the winner live on different disks.
     */
    private function relinkBusinessReferences(): void
    {
        $usageTables = [
            'products'             => ['images', 'hover_image'],
            'product_skus'         => ['images'],
            'brands'               => ['logo'],
            'categories'           => ['image'],
            'catalogs'             => ['image'],
            'articles'             => ['image'],
            'article_translations' => ['image'],
        ];

        foreach ($this->duplicateStorageKeys() as $storageKey) {
            $rows = DB::table('media_files')
                ->where('storage_key', $storageKey)
                ->orderBy('id')
                ->get();

            if ($rows->count() < 2) {
                continue;
            }

            // Same winner the dedup picks — both passes must agree, or the
            // reference would be re-pointed at a row the dedup then drops.
            $winner = $this->pickWinner($rows);

            foreach ($rows->where('id', '!=', $winner->id)->whereNull('deleted_at') as $loser) {
                if (($loser->disk ?? null) !== ($winner->disk ?? null)) {
                    throw new RuntimeException(sprintf(
                        'media_files rows %d and %d share storage_key %s but live on different disks (%s vs %s). '
                        .'Re-pointing the business references would swap the picture — resolve the rows by hand first.',
                        (int) $loser->id,
                        (int) $winner->id,
                        $storageKey,
                        (string) ($loser->disk ?? ''),
                        (string) ($winner->disk ?? ''),
                    ));
                }

                foreach ($usageTables as $table => $columns) {
                    foreach ($columns as $column) {
                        MediaUsageScanner::relinkReference($table, $column, (int) $loser->id, (int) $winner->id);
                    }
                }
            }
        }
    }

    /**
     * Storage keys owned by more than one row — soft-deleted rows included,
     * since the unique index spans them too.
     *
     * @return Collection<int, string>
     */
    private function duplicateStorageKeys()
    {
        return DB::table('media_files')
            ->select('storage_key', DB::raw('COUNT(*) as c'))
            ->groupBy('storage_key')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('storage_key');
    }
};
