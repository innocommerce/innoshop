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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Services\MediaUrlResolver;
use InnoShop\Common\Services\StorageService;

class MediaMigrateReferences extends Command
{
    protected $signature = 'media:migrate-references
                            {--force : Actually write changes. Without this flag, runs in dry-run mode.}
                            {--table= : Only migrate a specific table.}';

    protected $description = 'Rewrite business table image fields from raw storage_key paths to "media://{id}" references.';

    /**
     * Tables and their image fields to migrate.
     * - 'json' fields are treated as JSON arrays (each entry replaced).
     * - 'string' fields are treated as a single path.
     *
     * @var array<string, array<string, 'string'|'json'>>
     */
    protected array $tables = [
        'products'     => ['images' => 'json', 'hover_image' => 'string'],
        'product_skus' => ['images' => 'json'],
        'brands'       => ['logo' => 'string'],
        'categories'   => ['image' => 'string'],
        'catalogs'     => ['image' => 'string'],
        'articles'     => ['image' => 'string'],
    ];

    public function handle(): int
    {
        $force     = (bool) $this->option('force');
        $onlyTable = $this->option('table');

        $this->info($force ? 'RUNNING IN WRITE MODE' : 'DRY RUN (use --force to write)');
        $this->newLine();

        // Cache media_files: storage_key => id  (one query, faster than per-row lookups).
        $mediaMap = MediaFile::query()
            ->pluck('id', 'storage_key')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($mediaMap)) {
            $this->warn('No records in media_files. Run `php artisan media:register-existing` first.');

            return self::FAILURE;
        }

        $totalRowsChanged = 0;
        $totalRefsAdded   = 0;

        foreach ($this->tables as $table => $fields) {
            if ($onlyTable && $table !== $onlyTable) {
                continue;
            }
            if (! Schema::hasTable($table)) {
                $this->line("  - skipped {$table} (table does not exist)");

                continue;
            }

            $rowsChanged = 0;
            $refsAdded   = 0;

            foreach ($fields as $field => $type) {
                if (! Schema::hasColumn($table, $field)) {
                    continue;
                }

                [$rowChanged, $refAdded] = $this->migrateField($table, $field, $type, $mediaMap, $force);
                $rowsChanged += $rowChanged;
                $refsAdded += $refAdded;
            }

            $this->line(sprintf('  - %-15s rows changed: %d, refs added: %d', $table, $rowsChanged, $refsAdded));
            $totalRowsChanged += $rowsChanged;
            $totalRefsAdded += $refsAdded;
        }

        $this->newLine();
        $this->info("Total rows changed: {$totalRowsChanged}, references added: {$totalRefsAdded}.");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int>  $mediaMap
     * @return array{0: int, 1: int} [rowsChanged, refsAdded]
     */
    protected function migrateField(string $table, string $field, string $type, array $mediaMap, bool $force): array
    {
        $rowsChanged = 0;
        $refsAdded   = 0;

        // Pull all rows so we can iterate; for huge tables, chunking could be added later.
        $rows = DB::table($table)->select(['id', $field])->get();

        foreach ($rows as $row) {
            $original = $row->{$field} ?? null;
            if (empty($original)) {
                continue;
            }

            if ($type === 'json') {
                [$newValue, $added] = $this->rewriteJson($original, $mediaMap);
            } else {
                [$newValue, $added] = $this->rewriteString($original, $mediaMap);
            }

            if ($newValue === null || $newValue === $original) {
                continue;
            }

            if ($force) {
                DB::table($table)->where('id', $row->id)->update([$field => $newValue]);
            }
            $rowsChanged++;
            $refsAdded += $added;
        }

        return [$rowsChanged, $refsAdded];
    }

    /**
     * @param  array<string, int>  $mediaMap
     * @return array{0: ?string, 1: int} [newValue, refsAdded]  (newValue null = no change)
     */
    protected function rewriteString(?string $value, array $mediaMap): array
    {
        if (empty($value)) {
            return [null, 0];
        }

        // Skip http(s) URLs and already-migrated media:// references.
        if (str_starts_with($value, 'http') || MediaUrlResolver::isMediaReference($value)) {
            return [null, 0];
        }

        $normalized = $this->normalizeStorageKey($value);
        $mediaId    = $mediaMap[$normalized] ?? null;
        if (! $mediaId) {
            return [null, 0];
        }

        return [MediaUrlResolver::buildReference($mediaId), 1];
    }

    /**
     * @param  array<string, int>  $mediaMap
     * @return array{0: ?string, 1: int}
     */
    protected function rewriteJson(?string $value, array $mediaMap): array
    {
        if (empty($value)) {
            return [null, 0];
        }

        $decoded = json_decode((string) $value, true);
        if (! is_array($decoded) || array_is_list($decoded) === false) {
            // Not a JSON list — fall back to string handling.
            return $this->rewriteString($value, $mediaMap);
        }

        $changed   = false;
        $added     = 0;
        $rewritten = [];
        foreach ($decoded as $entry) {
            if (! is_string($entry)) {
                $rewritten[] = $entry;

                continue;
            }
            [$newEntry, $entryAdded] = $this->rewriteString($entry, $mediaMap);
            if ($newEntry !== null) {
                $rewritten[] = $newEntry;
                $added += $entryAdded;
                $changed = true;
            } else {
                $rewritten[] = $entry;
            }
        }

        return $changed ? [json_encode($rewritten, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $added] : [null, 0];
    }

    /**
     * Normalize a stored value into a storage_key that matches the keys of $mediaMap.
     */
    protected function normalizeStorageKey(string $value): string
    {
        $clean = ltrim($value, '/');

        return str_starts_with($clean, StorageService::STORAGE_PREFIX)
            ? $clean
            : StorageService::storageKey($clean);
    }
}
