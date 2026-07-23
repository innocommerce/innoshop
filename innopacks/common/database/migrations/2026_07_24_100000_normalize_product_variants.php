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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Normalize product variants: convert products.variables JSON plus the
 * product_skus.variants index array into relational tables, backfill the
 * legacy data in one pass, then drop the legacy columns.
 *
 * Resulting tables:
 *   product_variants                    Variant dimension (e.g. Color)
 *   product_variant_translations        Variant name translations
 *   product_variant_values              Variant value (e.g. Red)
 *   product_variant_value_translations  Variant value name translations
 *   product_sku_variant_values          SKU x variant value mapping (replaces index array)
 *
 * Key constraint: UNIQUE(sku_id, variant_id) ensures one SKU can only have
 * one value per dimension. This DB-level guarantee was impossible with the
 * legacy JSON index scheme, and is the root fix for the index-shift bug.
 *
 * The legacy columns products.variables / product_skus.variants are dropped
 * once the data is migrated. down() cannot restore the historical JSON data;
 * rolling back requires a DB backup restore.
 */
return new class extends Migration
{
    private const LOG_PREFIX = '[variant-migration] ';

    public function up(): void
    {
        $this->createTables();
        $this->migrateLegacyData();
        $this->dropLegacyColumns();
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sku_variant_values');
        Schema::dropIfExists('product_variant_value_translations');
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variant_translations');
        Schema::dropIfExists('product_variants');
    }

    /**
     * Build the five normalized tables.
     */
    private function createTables(): void
    {
        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->comment('Product Variant Dimension (e.g. Color, Size)');
                $table->bigIncrements('id')->comment('ID');
                $table->unsignedBigInteger('product_id')->index('pv_product_id')->comment('Product ID');
                $table->integer('position')->default(0)->comment('Sort order');
                $table->boolean('is_image')->default(false)->comment('Bind image to this dimension');
                $table->timestamps();
                $table->index(['product_id', 'position'], 'pv_product_position');
            });
        }

        if (! Schema::hasTable('product_variant_translations')) {
            Schema::create('product_variant_translations', function (Blueprint $table) {
                $table->comment('Product Variant Translation');
                $table->bigIncrements('id')->comment('ID');
                $table->unsignedBigInteger('variant_id')->index('pvt_variant_id')->comment('Variant ID');
                $table->string('locale')->default('')->comment('Locale Code');
                $table->string('name')->default('')->comment('Name');
                $table->timestamps();
                $table->index(['variant_id', 'locale'], 'pvt_variant_locale');
            });
        }

        if (! Schema::hasTable('product_variant_values')) {
            Schema::create('product_variant_values', function (Blueprint $table) {
                $table->comment('Product Variant Value (e.g. Red, L)');
                $table->bigIncrements('id')->comment('ID');
                $table->unsignedBigInteger('variant_id')->index('pvv_variant_id')->comment('Variant ID');
                $table->string('image')->default('')->comment('Value image path');
                $table->integer('position')->default(0)->comment('Sort order');
                $table->timestamps();
                $table->index(['variant_id', 'position'], 'pvv_variant_position');
            });
        }

        if (! Schema::hasTable('product_variant_value_translations')) {
            Schema::create('product_variant_value_translations', function (Blueprint $table) {
                $table->comment('Product Variant Value Translation');
                $table->bigIncrements('id')->comment('ID');
                $table->unsignedBigInteger('value_id')->index('pvvt_value_id')->comment('Value ID');
                $table->string('locale')->default('')->comment('Locale Code');
                $table->string('name')->default('')->comment('Name');
                $table->timestamps();
                $table->index(['value_id', 'locale'], 'pvvt_value_locale');
            });
        }

        if (! Schema::hasTable('product_sku_variant_values')) {
            Schema::create('product_sku_variant_values', function (Blueprint $table) {
                $table->comment('SKU to Variant Value mapping (replaces product_skus.variants JSON index)');
                $table->bigIncrements('id')->comment('ID');
                $table->unsignedBigInteger('sku_id')->index('psvv_sku_id')->comment('SKU ID');
                $table->unsignedBigInteger('variant_id')->index('psvv_variant_id')->comment('Variant ID');
                $table->unsignedBigInteger('value_id')->index('psvv_value_id')->comment('Variant Value ID');
                $table->timestamps();

                // One SKU can only have one value per dimension. This DB-level
                // constraint is the cornerstone that the legacy JSON index design
                // could not enforce, and is what eliminates the index-shift bug.
                $table->unique(['sku_id', 'variant_id'], 'psvv_sku_variant_unique');
                $table->index(['value_id', 'sku_id'], 'psvv_value_sku');
            });
        }
    }

    /**
     * Migrate legacy JSON data into the normalized tables.
     *
     * Skipped silently on fresh installs where the legacy columns never existed.
     * Idempotent: wipes and rebuilds each product's normalized rows so re-runs
     * (e.g. after a partial failure) converge to the same end state.
     */
    private function migrateLegacyData(): void
    {
        if (! Schema::hasColumn('products', 'variables') || ! Schema::hasColumn('product_skus', 'variants')) {
            return;
        }

        $now          = now();
        $stats        = ['products' => 0, 'variants' => 0, 'values' => 0, 'skus' => 0, 'mappings' => 0, 'warnings' => 0];
        $productIdMap = [];

        DB::table('products')
            ->orderBy('id')
            ->chunkById(500, function ($products) use (&$stats, &$productIdMap, $now) {
                foreach ($products as $product) {
                    try {
                        $variables = $this->decodeVariables($product->variables);
                        if (empty($variables)) {
                            continue;
                        }

                        $this->clearProductNormalizedData($product->id);

                        $productIdMap[$product->id] = $this->migrateProductVariables($product, $variables, $now, $stats);
                        $stats['products']++;
                    } catch (Throwable $e) {
                        $stats['warnings']++;
                        Log::warning(self::LOG_PREFIX."product {$product->id} failed: ".$e->getMessage());
                    }
                }
            });

        DB::table('product_skus')
            ->orderBy('id')
            ->chunkById(500, function ($skus) use (&$stats, &$productIdMap, $now) {
                foreach ($skus as $sku) {
                    try {
                        $indexes = $this->decodeVariantIndexes($sku->variants);
                        if (empty($indexes)) {
                            continue;
                        }
                        if (! isset($productIdMap[$sku->product_id])) {
                            $stats['warnings']++;
                            Log::warning(self::LOG_PREFIX."sku {$sku->id} references product {$sku->product_id} with no migrated variables");

                            continue;
                        }

                        $this->migrateSkuVariantIndexes($sku, $indexes, $productIdMap[$sku->product_id], $now, $stats);
                        $stats['skus']++;
                    } catch (Throwable $e) {
                        $stats['warnings']++;
                        Log::warning(self::LOG_PREFIX."sku {$sku->id} failed: ".$e->getMessage());
                    }
                }
            });

        Log::info(self::LOG_PREFIX.'completed', $stats);
    }

    /**
     * Drop the legacy JSON columns after data has been migrated.
     */
    private function dropLegacyColumns(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'variables')) {
                $table->dropColumn('variables');
            }
        });

        Schema::table('product_skus', function (Blueprint $table) {
            if (Schema::hasColumn('product_skus', 'variants')) {
                $table->dropColumn('variants');
            }
        });
    }

    /**
     * Parse products.variables JSON defensively.
     */
    private function decodeVariables(?string $json): array
    {
        if (empty($json)) {
            return [];
        }
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return [];
        }

        return array_values($data);
    }

    private function decodeVariantIndexes(?string $json): array
    {
        if (empty($json)) {
            return [];
        }
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return [];
        }

        return array_values($data);
    }

    /**
     * Remove pre-existing normalized rows for a product (idempotent re-runs).
     */
    private function clearProductNormalizedData(int $productId): void
    {
        $variantIds = DB::table('product_variants')
            ->where('product_id', $productId)
            ->pluck('id')
            ->all();

        if (empty($variantIds)) {
            return;
        }

        $valueIds = DB::table('product_variant_values')
            ->whereIn('variant_id', $variantIds)
            ->pluck('id')
            ->all();

        DB::table('product_variant_value_translations')->whereIn('value_id', $valueIds)->delete();
        DB::table('product_variant_values')->whereIn('variant_id', $variantIds)->delete();
        DB::table('product_variant_translations')->whereIn('variant_id', $variantIds)->delete();
        DB::table('product_variants')->where('product_id', $productId)->delete();

        $skuIds = DB::table('product_skus')->where('product_id', $productId)->pluck('id')->all();
        if (! empty($skuIds)) {
            DB::table('product_sku_variant_values')->whereIn('sku_id', $skuIds)->delete();
        }
    }

    /**
     * Migrate one product's variables JSON into the normalized tables.
     * Returns: [variant_position => ['variant_id' => id, 'values' => [value_position => value_id]]].
     */
    private function migrateProductVariables(object $product, array $variables, object $now, array &$stats): array
    {
        $map = [];
        foreach ($variables as $vPos => $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $product->id,
                'position'   => $vPos,
                'is_image'   => (bool) ($variant['isImage'] ?? false),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $stats['variants']++;

            foreach ($variant['name'] ?? [] as $locale => $name) {
                if (! is_string($name) || $name === '') {
                    continue;
                }
                DB::table('product_variant_translations')->insert([
                    'variant_id' => $variantId,
                    'locale'     => $locale,
                    'name'       => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $map[$vPos] = ['variant_id' => $variantId, 'values' => []];
            foreach ($variant['values'] ?? [] as $valPos => $value) {
                if (! is_array($value)) {
                    continue;
                }
                $valueId = DB::table('product_variant_values')->insertGetId([
                    'variant_id' => $variantId,
                    'image'      => is_string($value['image'] ?? null) ? $value['image'] : '',
                    'position'   => $valPos,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $stats['values']++;

                foreach ($value['name'] ?? [] as $locale => $name) {
                    if (! is_string($name) || $name === '') {
                        continue;
                    }
                    DB::table('product_variant_value_translations')->insert([
                        'value_id'   => $valueId,
                        'locale'     => $locale,
                        'name'       => $name,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $map[$vPos]['values'][$valPos] = $valueId;
            }
        }

        return $map;
    }

    /**
     * Translate sku.variants = [0, 1, 0] into product_sku_variant_values rows.
     */
    private function migrateSkuVariantIndexes(object $sku, array $indexes, array $productMap, object $now, array &$stats): void
    {
        $rows = [];
        foreach ($indexes as $vPos => $valIdx) {
            if (! isset($productMap[$vPos])) {
                $stats['warnings']++;
                Log::warning(self::LOG_PREFIX."sku {$sku->id} has extra variant index {$vPos}, skipping");

                continue;
            }
            if (! isset($productMap[$vPos]['values'][$valIdx])) {
                $stats['warnings']++;
                Log::warning(self::LOG_PREFIX."sku {$sku->id} variant {$vPos} value index {$valIdx} out of range, skipping");

                continue;
            }

            $rows[] = [
                'sku_id'     => $sku->id,
                'variant_id' => $productMap[$vPos]['variant_id'],
                'value_id'   => $productMap[$vPos]['values'][$valIdx],
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $stats['mappings']++;
        }

        if (! empty($rows)) {
            DB::table('product_sku_variant_values')->insert($rows);
        }
    }
};
