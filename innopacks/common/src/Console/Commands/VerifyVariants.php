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

class VerifyVariants extends Command
{
    protected $signature = 'variants:verify
                            {--product_id= : Verify only one product}
                            {--locale= : Verify only one locale (default: all active locales)}
                            {--strict : Treat warnings as failures}';

    protected $description = 'Verify product variants data integrity (orphans, missing translations, UNIQUE soft-guard, position anomalies)';

    public function handle(): int
    {
        if (! $this->tablesExist()) {
            $this->error('Variant tables do not exist. Run migrations first.');

            return self::FAILURE;
        }

        $productId = $this->option('product_id');
        $locale    = $this->option('locale');
        $strict    = (bool) $this->option('strict');

        $locales = $locale ? [$locale] : $this->activeLocales();
        if (empty($locales)) {
            $this->warn('No active locales found. Falling back to all distinct locales in translations.');
            $locales = $this->distinctTranslationLocales();
        }

        $this->info('Verifying variants integrity...');
        $this->line('  Locales:    '.implode(', ', $locales));
        $this->line('  Product:    '.($productId ?? 'all'));
        $this->newLine();

        $stats = $this->collectStats($productId, $locales);
        $this->renderStats($stats);

        $errors   = $this->errorCount($stats);
        $warnings = $this->warningCount($stats);

        $this->newLine();
        if ($errors > 0) {
            $this->error("FAIL: {$errors} error(s) found.");

            return self::FAILURE;
        }
        if ($strict && $warnings > 0) {
            $this->error("FAIL (--strict): {$warnings} warning(s) treated as errors.");

            return self::FAILURE;
        }
        if ($warnings > 0) {
            $this->warn("OK with warnings: {$warnings} warning(s).");

            return self::SUCCESS;
        }
        $this->info('OK: all checks passed.');

        return self::SUCCESS;
    }

    /**
     * Gather counts for every check in one pass per query.
     */
    private function collectStats(?int $productId, array $locales): array
    {
        $productClause = fn (string $col) => $productId === null
            ? ''
            : "AND {$col} = ".(int) $productId;

        return [
            'counts'          => $this->rowCounts($productId),
            'orphan_variants' => DB::table('product_variants as pv')
                ->leftJoin('products as p', 'p.id', '=', 'pv.product_id')
                ->whereNull('p.id')
                ->when($productId, fn ($q) => $q->where('pv.product_id', $productId))
                ->count(),
            'orphan_values' => DB::table('product_variant_values as v')
                ->leftJoin('product_variants as pv', 'pv.id', '=', 'v.variant_id')
                ->whereNull('pv.id')
                ->count(),
            'orphan_vt' => DB::table('product_variant_translations as t')
                ->leftJoin('product_variants as pv', 'pv.id', '=', 't.variant_id')
                ->whereNull('pv.id')
                ->count(),
            'orphan_vvt' => DB::table('product_variant_value_translations as t')
                ->leftJoin('product_variant_values as v', 'v.id', '=', 't.value_id')
                ->whereNull('v.id')
                ->count(),
            'orphan_pivot_sku' => DB::table('product_sku_variant_values as psv')
                ->leftJoin('product_skus as s', 's.id', '=', 'psv.sku_id')
                ->whereNull('s.id')
                ->count(),
            'orphan_pivot_val' => DB::table('product_sku_variant_values as psv')
                ->leftJoin('product_variant_values as v', 'v.id', '=', 'psv.value_id')
                ->whereNull('v.id')
                ->count(),
            'orphan_pivot_var' => DB::table('product_sku_variant_values as psv')
                ->leftJoin('product_variants as pv', 'pv.id', '=', 'psv.variant_id')
                ->whereNull('pv.id')
                ->count(),
            'unique_violations' => DB::table('product_sku_variant_values')
                ->select('sku_id', 'variant_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('sku_id', 'variant_id')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
            'sku_dim_mismatch'   => $this->skuDimensionMismatch($productId),
            'missing_variant_tr' => $this->missingTranslations('product_variant_translations', 'variant_id', $locales, $productId),
            'missing_value_tr'   => $this->missingTranslations('product_variant_value_translations', 'value_id', $locales, $productId),
            'image_dim_no_image' => $this->imageDimensionValuesWithoutImage($productId),
            'dup_variant_pos'    => DB::table('product_variants')
                ->select('product_id', 'position')
                ->groupBy('product_id', 'position')
                ->havingRaw('COUNT(*) > 1')
                ->when($productId, fn ($q) => $q->where('product_id', $productId))
                ->count(),
            'dup_value_pos' => DB::table('product_variant_values as v')
                ->join('product_variants as pv', 'pv.id', '=', 'v.variant_id')
                ->select('v.variant_id', 'v.position')
                ->groupBy('v.variant_id', 'v.position')
                ->havingRaw('COUNT(*) > 1')
                ->when($productId, fn ($q) => $q->where('pv.product_id', $productId))
                ->count(),
        ];
    }

    /**
     * Row counts for the header summary.
     */
    private function rowCounts(?int $productId): array
    {
        $variantQuery = DB::table('product_variants');
        if ($productId) {
            $variantQuery->where('product_id', $productId);
        }
        $variantIds = $variantQuery->pluck('id')->all();

        $valueQuery = DB::table('product_variant_values')->whereIn('variant_id', $variantIds);
        $skuIds     = $productId
            ? DB::table('product_skus')->where('product_id', $productId)->pluck('id')->all()
            : [];

        return [
            'products' => $productId ? 1 : DB::table('products')->count(),
            'variants' => count($variantIds),
            'values'   => $variantIds ? $valueQuery->count() : 0,
            'mappings' => DB::table('product_sku_variant_values')
                ->when($productId, fn ($q) => $q->whereIn('sku_id', $skuIds))
                ->count(),
        ];
    }

    /**
     * Count SKUs whose pivot value count doesn't match the parent product's
     * variant count. Written as raw SQL because Laravel's prefix-on-alias
     * behavior makes the equivalent query builder form brittle.
     */
    private function skuDimensionMismatch(?int $productId): int
    {
        $prefix = DB::getTablePrefix();
        $pid    = (int) $productId;
        $where  = $productId ? "WHERE s.product_id = {$pid} " : '';

        $sql = "SELECT COUNT(*) AS cnt FROM (
            SELECT s.id
            FROM {$prefix}product_skus s
            INNER JOIN {$prefix}product_variants pv ON pv.product_id = s.product_id
            LEFT JOIN {$prefix}product_sku_variant_values psv
                ON psv.sku_id = s.id AND psv.variant_id = pv.id
            {$where}
            GROUP BY s.id, pv.product_id
            HAVING COUNT(psv.id) != (
                SELECT COUNT(*) FROM {$prefix}product_variants pv2
                WHERE pv2.product_id = pv.product_id
            )
        ) AS drift";

        return (int) (DB::selectOne($sql)->cnt ?? 0);
    }

    /**
     * Count rows in a translations table whose parent (variant_id or value_id)
     * is missing a translation for at least one of the given locales.
     */
    private function missingTranslations(string $table, string $foreignKey, array $locales, ?int $productId): int
    {
        $parentTable = $table === 'product_variant_translations'
            ? 'product_variants'
            : 'product_variant_values';
        $parentKey = $table === 'product_variant_translations' ? 'id' : 'id';

        $query = DB::table("{$parentTable} as p");
        if ($productId && $parentTable === 'product_variants') {
            $query->where('p.product_id', $productId);
        }
        if ($productId && $parentTable === 'product_variant_values') {
            $query->join('product_variants as pv', 'pv.id', '=', 'p.variant_id')
                ->where('pv.product_id', $productId);
        }

        foreach ($locales as $loc) {
            $alias = 't_'.$loc;
            $query->leftJoin("{$table} as {$alias}", function ($join) use ($alias, $foreignKey, $parentKey, $loc) {
                $join->on("{$alias}.{$foreignKey}", '=', "p.{$parentKey}")
                    ->where("{$alias}.locale", '=', $loc);
            })->whereNull("{$alias}.id");
        }

        return $query->count();
    }

    /**
     * Values on an image-binding dimension that lack an image path.
     */
    private function imageDimensionValuesWithoutImage(?int $productId): int
    {
        return DB::table('product_variant_values as v')
            ->join('product_variants as pv', 'pv.id', '=', 'v.variant_id')
            ->where('pv.is_image', true)
            ->where(function ($q) {
                $q->whereNull('v.image')->orWhere('v.image', '=', '');
            })
            ->when($productId, fn ($q) => $q->where('pv.product_id', $productId))
            ->count();
    }

    private function activeLocales(): array
    {
        return DB::table('locales')->where('active', true)->pluck('code')->all()
            ?: [app()->getLocale()];
    }

    private function distinctTranslationLocales(): array
    {
        $variant = DB::table('product_variant_translations')->distinct()->pluck('locale')->all();
        $value   = DB::table('product_variant_value_translations')->distinct()->pluck('locale')->all();

        return array_values(array_unique(array_merge($variant, $value)));
    }

    private function tablesExist(): bool
    {
        foreach ([
            'product_variants',
            'product_variant_translations',
            'product_variant_values',
            'product_variant_value_translations',
            'product_sku_variant_values',
        ] as $t) {
            if (! DB::getSchemaBuilder()->hasTable($t)) {
                return false;
            }
        }

        return true;
    }

    private function renderStats(array $stats): void
    {
        $c = $stats['counts'];
        $this->line(sprintf(
            "Products checked:    %d\nVariants checked:    %d\nValues checked:      %d\nSKU mappings:        %d",
            $c['products'], $c['variants'], $c['values'], $c['mappings']
        ));
        $this->newLine();

        $errors = [
            ['E1', 'Orphan variants (product missing)',          $stats['orphan_variants']],
            ['E2', 'Orphan values (variant missing)',            $stats['orphan_values']],
            ['E3', 'Orphan variant translations',                $stats['orphan_vt']],
            ['E4', 'Orphan value translations',                  $stats['orphan_vvt']],
            ['E5', 'Orphan pivot rows (sku side)',               $stats['orphan_pivot_sku']],
            ['E6', 'Orphan pivot rows (value side)',             $stats['orphan_pivot_val']],
            ['E7', 'Orphan pivot rows (variant side)',           $stats['orphan_pivot_var']],
            ['E8', 'UNIQUE(sku_id, variant_id) violations',      $stats['unique_violations']],
            ['E9', 'SKU dimension count mismatch',               $stats['sku_dim_mismatch']],
            ['E10', 'Variants missing active-locale translation', $stats['missing_variant_tr']],
            ['E11', 'Values missing active-locale translation',   $stats['missing_value_tr']],
            ['E12', 'Image-binding dimension values w/o image',   $stats['image_dim_no_image']],
        ];
        $this->line('Errors:');
        foreach ($errors as [$code, $label, $count]) {
            $mark = $count > 0 ? '✗' : '✓';
            $this->line(sprintf('  %s [%s] %-50s %d', $mark, $code, $label, $count));
        }

        $this->newLine();
        $warnings = [
            ['W1', 'Duplicate position on variants',  $stats['dup_variant_pos']],
            ['W2', 'Duplicate position on values',    $stats['dup_value_pos']],
        ];
        $this->line('Warnings:');
        foreach ($warnings as [$code, $label, $count]) {
            $mark = $count > 0 ? '!' : '✓';
            $this->line(sprintf('  %s [%s] %-50s %d', $mark, $code, $label, $count));
        }
    }

    private function errorCount(array $stats): int
    {
        return (int) (
            $stats['orphan_variants']
            + $stats['orphan_values']
            + $stats['orphan_vt']
            + $stats['orphan_vvt']
            + $stats['orphan_pivot_sku']
            + $stats['orphan_pivot_val']
            + $stats['orphan_pivot_var']
            + $stats['unique_violations']
            + $stats['sku_dim_mismatch']
            + $stats['missing_variant_tr']
            + $stats['missing_value_tr']
            + $stats['image_dim_no_image']
        );
    }

    private function warningCount(array $stats): int
    {
        return (int) ($stats['dup_variant_pos'] + $stats['dup_value_pos']);
    }
}
