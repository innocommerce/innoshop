<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Handlers\TranslationHandler;
use InnoShop\Common\Models\Attribute;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Order\Item;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Repositories\Product\BundleRepo;
use InnoShop\Common\Repositories\Product\OptionValueRepo;
use InnoShop\Common\Repositories\Product\RelationRepo;
use InnoShop\Common\Repositories\Product\VariantRepo;
use InnoShop\Common\Services\ProductQueryBuilder;
use Throwable;

class ProductRepo extends BaseRepo
{
    /**
     * How long the front page product blocks stay cached (seconds).
     */
    const BESTSELLER_CACHE_TTL = 600;

    /**
     * How many products are taken from the sales ranking before the
     * active / translation filters are applied.
     */
    const BESTSELLER_CANDIDATE_LIMIT = 100;

    const AVAILABLE_SORT_FIELDS = [
        'position',
        'rating',
        'sales',
        'viewed',
        'updated_at',
        'created_at',
        'ps.price',
        'pt.name',
    ];

    /**
     * Get available sort options for products
     *
     * @return array
     */
    public static function getSortOptions(): array
    {
        $options = [
            'pt.name'    => __('common/base.name'),
            'ps.price'   => __('panel/product.price'),
            'position'   => __('common/base.position'),
            'sales'      => __('panel/product.sales'),
            'viewed'     => __('panel/product.viewed'),
            'created_at' => __('common/base.created_at'),
            'updated_at' => __('common/base.updated_at'),
        ];

        return fire_hook_filter('common.repo.product.sort_options', $options);
    }

    /**
     * Get available product types
     *
     * @return array
     */
    public static function getProductTypes(): array
    {
        $types = [
            'normal' => __('panel/product.type_normal'),
            'bundle' => __('panel/product.type_bundle'),
            // 'virtual' => __('panel/product.type_virtual'),
            // 'card'    => __('panel/product.type_card'),
        ];

        return fire_hook_filter('common.repo.product.types', $types);
    }

    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        $criteria = [
            ['name' => 'keyword', 'type' => 'input', 'label' => trans('common/base.name')],
            [
                'name'              => 'sku_code',
                'type'              => 'autocomplete',
                'label'             => trans('panel/product.sku_code'),
                'url'               => route('api.panel.products.sku_autocomplete'),
                'hidden_input_name' => 'sku_id',
                'option_label_key'  => 'display_name',
                'input_value_key'   => 'code',
            ],
            [
                'name'  => 'category',
                'type'  => 'autocomplete',
                'label' => trans('panel/product.category'),
                'url'   => route('api.panel.categories.autocomplete'),
            ],
            [
                'name'  => 'brand',
                'type'  => 'autocomplete',
                'label' => trans('panel/product.brand'),
                'url'   => route('api.panel.brands.autocomplete'),
            ],
            ['name' => 'price', 'type' => 'range', 'label' => trans('panel/product.price')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('common/base.created_at')],
        ];

        return fire_hook_filter('common.repo.product.criteria', $criteria);
    }

    /**
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'name', 'label' => trans('panel/product.name')],
            ['value' => 'sku_code', 'label' => trans('panel/product.sku_code')],
            ['value' => 'spu_code', 'label' => trans('panel/product.spu_code')],
        ];

        return fire_hook_filter('common.repo.product.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $filters = [
            [
                'name'    => 'active',
                'label'   => trans('panel/common.status'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => '1', 'label' => trans('panel/common.active_yes')],
                    ['value' => '0', 'label' => trans('panel/common.active_no')],
                ],
            ],
            [
                'name'    => 'type',
                'label'   => trans('panel/product.type'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => 'normal', 'label' => trans('panel/product.type_normal')],
                    ['value' => 'bundle', 'label' => trans('panel/product.type_bundle')],
                ],
            ],
            [
                'name'  => 'price',
                'label' => trans('panel/product.price'),
                'type'  => 'range',
            ],
        ];

        return fire_hook_filter('common.repo.product.filter_button_options', $filters);
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $builder = $this->builder($filters);
        $this->applySorting($builder, $filters);

        return $builder->paginate($filters['per_page'] ?? system_setting('product_per_page', 12));
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getFrontList(array $filters = []): LengthAwarePaginator
    {
        $builder = $this->withActive()->builder($filters);
        $this->applySorting($builder, $filters);

        return $builder->paginate($filters['per_page'] ?? system_setting('product_per_page', 12));
    }

    /**
     * Apply sorting to the builder.
     *
     * @param  Builder  $builder
     * @param  array  $filters
     * @return void
     * @throws Exception
     */
    private function applySorting(Builder $builder, array $filters): void
    {
        $sort  = $filters['sort'] ?? system_setting('product_default_sort', 'created_at');
        $order = $filters['order'] ?? system_setting('product_default_order', 'desc');

        if ($sort == 'pt.name') {
            $builder->select(['products.*', 'pt.name', 'pt.content']);
            $builder->join('product_translations as pt', function ($join) {
                $join->on('products.id', '=', 'pt.product_id')
                    ->where('pt.locale', locale_code());
            });
        } elseif ($sort == 'ps.price') {
            $builder->select(['products.*', 'ps.price']);
            $builder->join('product_skus as ps', function ($query) {
                $query->on('ps.product_id', '=', 'products.id')
                    ->where('is_default', true);
            });
        }

        if (! in_array($sort, self::AVAILABLE_SORT_FIELDS)) {
            $sort = 'created_at';
        }

        if (! in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        if ($sort && $order) {
            $builder->orderBy($sort, $order);
        }

        // Stable tie-breaker: same sort value (e.g. position=0) leaves row order to MySQL,
        // which differs across servers / versions. Pin to id DESC for predictable output.
        $builder->orderBy('id', 'desc');
    }

    /**
     * Create product.
     *
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $product = new Product;

        $product = $this->createOrUpdate($product, $data);

        return fire_hook_filter('common.repo.product.create.after', $product);
    }

    /**
     * Update product.
     *
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws Exception
     * @throws Throwable
     */
    public function update($item, $data): mixed
    {
        $product = $this->createOrUpdate($item, $data);

        return fire_hook_filter('common.repo.product.update.after', $product);
    }

    /**
     * @param  mixed  $item
     * @return void
     */
    public function destroy(mixed $item): void
    {
        fire_hook_action('common.repo.product.destroy.before', $item);

        $item->productAttributes()->delete();
        $item->categories()->sync([]);
        $item->relations()->delete();
        BundleRepo::getInstance()->deleteBundles($item);
        $item->skus()->delete();
        $item->translations()->delete();
        $item->delete();
    }

    /**
     * Bulk update products
     *
     * @param  array  $ids
     * @param  string  $action
     * @param  array  $data
     * @return array
     * @throws Throwable
     */
    public function bulkUpdate(array $ids, string $action, array $data = []): array
    {
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            $products = Product::query()->whereIn('id', $ids)->get();

            foreach ($products as $product) {
                switch ($action) {
                    case 'price':
                        $this->updateProductPrice($product, $data);
                        break;
                    case 'categories':
                        $this->updateProductCategories($product, $data);
                        break;
                    case 'quantity':
                        $this->updateProductQuantity($product, $data);
                        break;
                    case 'publish':
                        $product->update(['active' => true]);
                        break;
                    case 'unpublish':
                        $product->update(['active' => false]);
                        break;
                }
                $updatedCount++;
            }

            DB::commit();

            return ['success' => true, 'count' => $updatedCount];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update product price
     *
     * @param  Product  $product
     * @param  array  $data
     * @return void
     */
    private function updateProductPrice(Product $product, array $data): void
    {
        $mode  = $data['mode'] ?? 'reset';
        $value = (float) ($data['value'] ?? 0);

        if ($value <= 0) {
            return;
        }

        $skus = $product->skus;
        foreach ($skus as $sku) {
            $currentPrice = $sku->price;

            switch ($mode) {
                case 'reset':
                    $newPrice = $value;
                    break;
                case 'increase':
                    $newPrice = $currentPrice + $value;
                    break;
                case 'decrease':
                    $newPrice = max(0, $currentPrice - $value);
                    break;
                default:
                    continue 2;
            }

            $sku->update(['price' => $newPrice]);
        }
    }

    /**
     * Update product categories
     *
     * @param  Product  $product
     * @param  array  $data
     * @return void
     */
    private function updateProductCategories(Product $product, array $data): void
    {
        if (empty($data) || ! is_array($data)) {
            return;
        }

        $categoryIds = array_filter($data, 'is_numeric');
        $product->categories()->sync($categoryIds);
    }

    /**
     * Update product quantity
     *
     * @param  Product  $product
     * @param  array  $data
     * @return void
     */
    private function updateProductQuantity(Product $product, array $data): void
    {
        $mode  = $data['mode'] ?? 'reset';
        $value = (int) ($data['value'] ?? 0);

        if ($value < 0) {
            return;
        }

        $skus = $product->skus;
        foreach ($skus as $sku) {
            $currentQuantity = $sku->quantity;

            switch ($mode) {
                case 'reset':
                    $newQuantity = $value;
                    break;
                case 'increase':
                    $newQuantity = $currentQuantity + $value;
                    break;
                case 'decrease':
                    $newQuantity = max(0, $currentQuantity - $value);
                    break;
                default:
                    continue 2;
            }

            $sku->update(['quantity' => $newQuantity]);
        }
    }

    /**
     * Bulk destroy products
     *
     * @param  array  $ids
     * @return int
     * @throws Throwable
     */
    public function bulkDestroy(array $ids): int
    {
        $deletedCount = 0;

        DB::beginTransaction();
        try {
            $products = Product::query()->whereIn('id', $ids)->get();

            foreach ($products as $product) {
                $this->destroy($product);
                $deletedCount++;
            }

            DB::commit();

            return $deletedCount;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Copy product and related data
     *
     * @param  Product  $product
     * @return mixed
     */
    public function copy(Product $product): mixed
    {
        $product->load([
            'skus',
            'translations',
            'categories',
            'productAttributes',
            'relations',
            'bundles',
            'productOptions',
            'productOptionValues',
        ]);
        $copy = $product->replicate();

        $copy->slug .= '-'.rand(0, 99999);
        if ($copy->spu_code) {
            $copy->spu_code .= '-'.rand(0, 99999);
        }
        $copy->push();

        foreach ($product->getRelations() as $relation => $entries) {
            foreach ($entries as $entry) {
                $newEntry = $entry->replicate();
                if ($relation == 'skus') {
                    $newEntry->code .= '-'.rand(0, 99999);
                } elseif ($relation == 'categories') {
                    $copy->categories()->attach($entry->id);

                    continue;
                } elseif ($relation == 'bundles') {
                    continue;
                } elseif ($relation == 'productOptions') {
                    $newEntry->product_id = $copy->id;
                } elseif ($relation == 'productOptionValues') {
                    $newEntry->product_id = $copy->id;
                }
                if ($newEntry->push()) {
                    $copy->{$relation}()->save($newEntry);
                }
            }
        }

        BundleRepo::getInstance()->copyBundles($product, $copy);

        return $copy;
    }

    /**
     * Crate or update product.
     *
     * @param  Product  $product
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    private function createOrUpdate(Product $product, $data): mixed
    {
        $isUpdating = $product->id > 0;
        DB::beginTransaction();

        try {
            if ($isUpdating) {
                $data['type'] = $product->type;
            }
            $productData = $this->handleProductData($data);
            $product->fill($productData);
            $product->updated_at = now();
            $product->saveOrFail();

            // Sync normalized variant dimensions and capture valueIdMap for SKU
            // index translation. Variant definitions come in as `variables` or
            // legacy `variants` key from the front-end payload.
            $variableDefs = $this->extractVariableDefs($data);
            $variantMaps  = VariantRepo::getInstance()->syncVariables($product, $variableDefs);
            // Build client_id → (variant_id, value_id) map for the ID-based
            // SKU payload (sku.variant_value_ids). Empty when the payload uses
            // the legacy positional `sku.variants: [0, 1]` shape.
            $clientIdMap = $this->buildClientIdMap($variableDefs, $variantMaps);

            if ($isUpdating) {
                $product->translations()->delete();
                $product->productAttributes()->delete();
                $product->relations()->delete();
            }

            $translations = $this->handleTranslations($data['translations'] ?? []);
            if ($translations) {
                $product->translations()->createMany($translations);
            }

            $product->productAttributes()->createMany($this->handleAttributes(
                $this->mergeLongTextAttributes($data['attributes'] ?? [], $data['long_attributes'] ?? [])
            ));
            RelationRepo::getInstance()->handleBidirectionalRelations($product, $data['related_ids'] ?? []);
            $product->categories()->sync($data['categories'] ?? []);

            if (isset($data['bundles'])) {
                BundleRepo::getInstance()->handleBundles($product, $data['bundles'] ?? []);
            }

            // Handle product option configuration
            if (isset($data['product_options'])) {
                OptionValueRepo::getInstance()->createProductOptionValues($product->id, $data['product_options']);
            }

            $skus = $this->handleSkus($data['skus'] ?? [], $productData, $clientIdMap);
            if (isset($data['price_type']) && $data['price_type'] === 'single' && ! empty($skus)) {
                // Single-price mode: drop variant dimensions and keep only one SKU.
                VariantRepo::getInstance()->clearProductNormalizedData($product->id);
                $skus = [$skus[0]];
            }

            if (! empty($skus)) {
                // Replace the SKU set. The old rows are dropped here (not
                // earlier) so a payload without SKUs keeps them untouched.
                $product->skus()->delete();
                $this->createProductSkus($product, $skus);
            } elseif ($isUpdating && ! array_key_exists('skus', $data)) {
                // Update without a SKU payload: keep the existing SKUs, the
                // caller only changed other attributes (price/quantity are
                // carried by the SKU, so they are not touched either).
                $product->unsetRelation('skus');
                $product->unsetRelation('masterSku');
            } else {
                // A product must keep at least one SKU: every storefront path
                // (price, cart, order item) resolves it through the master
                // SKU. Reject the write instead of leaving a SKU-less product
                // behind — the caller rolls the transaction back.
                throw new Exception('A product must have at least one SKU.');
            }

            DB::commit();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Persist a product's SKUs, converting a DB unique collision on the SKU
     * code into a user-facing message so raw SQLSTATE 1062 never surfaces.
     * Runs inside the caller's transaction; a throw rolls the caller back too.
     *
     * @param  Product  $product
     * @param  array  $skus
     * @return void
     * @throws Exception
     */
    private function createProductSkus(Product $product, array $skus): void
    {
        if (empty($skus)) {
            return;
        }
        try {
            // The internal `_variant_value_ids` key is used to attach pivot rows below;
            // it is not a real column. Relation::createMany() bypasses $fillable and
            // writes every array key, so strip it before persisting the SKUs.
            $skusToCreate = [];
            foreach ($skus as $index => $sku) {
                unset($sku['_variant_value_ids']);
                $skusToCreate[$index] = $sku;
            }
            $created = $product->skus()->createMany($skusToCreate);

            // Attach normalized variant_value mappings (replaces product_skus.variants JSON indexes).
            foreach ($created as $index => $sku) {
                $variantValueRows = $skus[$index]['_variant_value_ids'] ?? [];
                if (empty($variantValueRows)) {
                    continue;
                }
                $pivotRows = [];
                foreach ($variantValueRows as $row) {
                    $pivotRows[$row['value_id']] = ['variant_id' => $row['variant_id']];
                }
                $sku->variantValues()->attach($pivotRows);
            }
        } catch (QueryException $e) {
            // product_skus.code is the only unique index on this table, so any
            // duplicate-key collision here is an SKU code clash. Match it across
            // drivers: MySQL (1062 / "Duplicate entry" / index name sku_code)
            // and SQLite (19 / "UNIQUE constraint failed").
            $sqlCode    = $e->errorInfo[1] ?? null;
            $message    = (string) $e->getMessage();
            $isSkuClash = $sqlCode === 1062
                || $sqlCode === 19
                || str_contains($message, 'sku_code')
                || str_contains($message, 'Duplicate entry')
                || str_contains($message, 'UNIQUE constraint failed');
            if ($isSkuClash) {
                // Name the offending code(s) so the message points at the exact
                // value the merchant must change, not a vague "sku cannot repeat".
                $code = $this->findConflictingSkuCode($skus);

                throw new Exception(panel_trans('product.error_sku_repeat', ['code' => $code]));
            }
            throw $e;
        }
    }

    /**
     * Resolve which SKU code(s) in the payload actually collided, so the error
     * message can name the exact value. By the time we land here the caller has
     * already deleted this product's own SKUs, so a DB hit means the code is
     * held by another product; if there is no DB hit the clash is inside the
     * submitted form itself (two variants sharing one code).
     *
     * @param  array  $skus
     * @return string
     */
    private function findConflictingSkuCode(array $skus): string
    {
        $codes = collect($skus)
            ->pluck('code')
            ->filter(fn ($c) => ! empty($c))
            ->values()
            ->all();

        if (empty($codes)) {
            return '';
        }

        $taken = Sku::query()
            ->whereIn('code', $codes)
            ->pluck('code')
            ->unique()
            ->values()
            ->all();

        if (! empty($taken)) {
            return implode(' / ', $taken);
        }

        // No DB hit → the collision is between rows in the same submission.
        $counts = array_count_values($codes);
        $dups   = array_keys(array_filter($counts, fn ($n) => $n > 1));

        return implode(' / ', $dups) ?: ($codes[0] ?? '');
    }

    /**
     * Patch a product.
     *
     * @param  Product  $product
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function patch(Product $product, $data): mixed
    {
        DB::beginTransaction();

        try {
            // Front-end submits variant definitions as `variants` (legacy key name).
            // Normalize into `variables` for downstream processing.
            if (isset($data['variants']) && ! isset($data['variables'])) {
                $data['variables'] = $data['variants'];
            }

            // variables/variants are consumed by syncVariables() below; they
            // must not bleed into $product->fill() (the products table no longer
            // has a variables column since P4).
            $variableDefs = $this->extractVariableDefs($data);
            unset($data['variables'], $data['variants']);

            $product->fill($data);
            $product->saveOrFail();

            // Sync normalized variants whenever variables were patched; capture
            // valueIdMap for SKU index translation below.
            $variantMaps = ['variantIdMap' => [], 'valueIdMap' => []];
            $clientIdMap = [];
            if (! empty($variableDefs)) {
                $variantMaps = VariantRepo::getInstance()->syncVariables($product, $variableDefs);
                $clientIdMap = $this->buildClientIdMap($variableDefs, $variantMaps);
            }

            if (isset($data['translations'])) {
                $translations = $this->handleTranslations($data['translations']);
                foreach ($translations as $translation) {
                    $existTranslation = $product->translations()->where('locale', $translation['locale'])->first();
                    if ($existTranslation) {
                        $existTranslation->update($translation);
                    } else {
                        // A brand-new locale row must carry name (the only NOT NULL
                        // column): prefer the name being written in this same
                        // request, then the stored fallback, so a meta-only patch
                        // stays valid instead of violating the
                        // product_translations.name constraint. empty()
                        // deliberately treats '' / '0' as missing.
                        if (empty($translation['name'])) {
                            $translation['name'] = $this->newLocaleName($translations, $product);
                        }
                        $product->translations()->create($translation);
                    }
                }
            }

            if (isset($data['attributes']) || isset($data['long_attributes'])) {
                $product->productAttributes()->delete();
                $product->productAttributes()->createMany($this->handleAttributes(
                    $this->mergeLongTextAttributes($data['attributes'] ?? [], $data['long_attributes'] ?? [])
                ));
            }

            if (isset($data['related_ids'])) {
                RelationRepo::getInstance()->handleBidirectionalRelations($product, $data['related_ids']);
            }

            if (isset($data['categories'])) {
                $product->categories()->sync($data['categories']);
            }

            if (isset($data['skus'])) {
                $defaults = ['weight' => $data['weight'] ?? $product->weight];
                $skus     = $this->handleSkus($data['skus'], $defaults, $clientIdMap);
                // An empty SKU list would delete every SKU of the product and
                // leave it unsellable; reject the patch instead of wiping it.
                if (empty($skus)) {
                    throw new Exception('A product must have at least one SKU.');
                }
                $product->skus()->delete();
                $this->createProductSkus($product, $skus);
            }

            if (isset($data['bundles'])) {
                BundleRepo::getInstance()->handleBundles($product, $data['bundles']);
            }

            // Handle product option configuration
            if (isset($data['product_options'])) {
                OptionValueRepo::getInstance()->createProductOptionValues($product->id, $data['product_options']);
            }

            DB::commit();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  $data
     * @return string[]
     */
    /**
     * Resolve the name for a brand-new locale row: prefer the name being
     * written in the same request (default locale), then the stored fallback.
     *
     * @param  array  $translations  Normalized translation rows (list format)
     * @param  Product  $product
     * @return string
     */
    private function newLocaleName(array $translations, Product $product): string
    {
        $localeCode = setting_locale_code();
        foreach ($translations as $row) {
            if (($row['locale'] ?? '') === $localeCode && ! empty($row['name'])) {
                return $row['name'];
            }
        }

        return $product->fallbackName('name');
    }

    /**
     * Pull the variant definitions from the payload, accepting either the
     * canonical `variables` key or the legacy `variants` key the Panel Vue
     * editor still submits.
     */
    private function extractVariableDefs(array $data): array
    {
        $defs = $data['variables'] ?? ($data['variants'] ?? []);
        if (is_string($defs)) {
            $decoded = json_decode($defs, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($defs) ? $defs : [];
    }

    public function handleProductData($data): array
    {
        $images = $data['images'] ?? null;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        $video = $data['video'] ?? null;
        if (is_string($video)) {
            $video = json_decode($video, true);
        }

        $slug = $data['slug'] ?? null;
        if (is_string($slug) && empty($slug)) {
            $slug = null;
        }

        $spuCode = $data['spu_code'] ?? null;
        if (is_string($spuCode) && empty($spuCode)) {
            $spuCode = null;
        }

        $data = [
            'type'         => $data['type'] ?? Product::TYPE_NORMAL,
            'spu_code'     => $spuCode,
            'slug'         => $slug,
            'brand_id'     => $data['brand_id'] ?? 0,
            'images'       => $images,
            'hover_image'  => $data['hover_image'] ?? '',
            'video'        => $video,
            'tax_class_id' => $data['tax_class_id'] ?? 0,
            'position'     => (int) ($data['position'] ?? 0),
            'weight'       => $data['weight'] ?? 0,
            'weight_class' => $data['weight_class'] ?? '',
            'sales'        => (int) ($data['sales'] ?? 0),
            'viewed'       => (int) ($data['viewed'] ?? 0),
            'minimum'      => (int) ($data['minimum'] ?? 1),
            'published_at' => $data['published_at'] ?? now(),
            'active'       => (bool) ($data['active'] ?? false),
        ];

        $data = fire_hook_filter('common.product.handle_data', $data);

        return $data;
    }

    /**
     * @param  $skus
     * @param  array  $defaults
     * @param  array  $clientIdMap  [client_value_id => ['variant_id'=>int,'value_id'=>int]] for new ID-based payload
     * @return array
     */
    public function handleSkus($skus, array $defaults = [], array $clientIdMap = []): array
    {
        if (is_string($skus)) {
            $skus = json_decode($skus, true);
        }
        if (! is_array($skus)) {
            $skus = [];
        }
        $onlyOneSku = count($skus) == 1;

        $defaultWeight = $defaults['weight'] ?? 0;

        $items = [];
        foreach ($skus as $sku) {
            $variantValueIds  = $sku['variant_value_ids'] ?? [];
            $variantValueRows = $this->resolveVariantValueRowsFromClientIds($variantValueIds, $clientIdMap);

            if ($onlyOneSku) {
                $isDefault = true;
            } else {
                $isDefault = $sku['is_default'] ?? false;
            }

            $code = $sku['code'];

            $items[] = [
                'images'       => [$sku['image'] ?? ''],
                'code'         => $code,
                'model'        => $sku['model'] ?? $code,
                'price'        => (float) ($sku['price'] ?? 0),
                'origin_price' => (float) ($sku['origin_price'] ?? 0),
                'quantity'     => (int) ($sku['quantity'] ?? 0),
                'is_default'   => (bool) $isDefault,
                'position'     => (int) ($sku['position'] ?? 0),
                'weight'       => (float) ($sku['weight'] ?? $defaultWeight),

                // Consumed by createProductSkus() to populate product_sku_variant_values.
                // Stripped from the createMany payload by Eloquent's mass-assignment guard.
                '_variant_value_ids' => $variantValueRows,
            ];
        }

        return $items;
    }

    /**
     * Build client_id → (variant_id, value_id) lookup from the variable defs
     * the front-end sent. Each value carries a stable `id` (either a DB id
     * echoed back from the API or a client-generated UUID for newly created
     * values). Lets SKU payloads reference values by id without depending on
     * positional ordering.
     */
    private function buildClientIdMap(array $variableDefs, array $variantMaps): array
    {
        if (empty($variableDefs)) {
            return [];
        }

        $map = [];
        foreach (array_values($variableDefs) as $vPos => $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $variantId = $variantMaps['variantIdMap'][$vPos] ?? null;
            if ($variantId === null) {
                continue;
            }
            foreach ($variant['values'] ?? [] as $valPos => $value) {
                if (! is_array($value)) {
                    continue;
                }
                $clientId  = $value['id'] ?? null;
                $dbValueId = $variantMaps['valueIdMap'][$vPos][$valPos] ?? null;
                if ($clientId === null || $dbValueId === null) {
                    continue;
                }
                $map[(string) $clientId] = [
                    'variant_id' => (int) $variantId,
                    'value_id'   => (int) $dbValueId,
                ];
            }
        }

        return $map;
    }

    /**
     * Translate a SKU's variant_value_ids (client IDs) into normalized pivot rows.
     */
    private function resolveVariantValueRowsFromClientIds(array $clientIds, array $clientIdMap): array
    {
        $rows = [];
        foreach ($clientIds as $clientId) {
            $clientId = (string) $clientId;
            if (! isset($clientIdMap[$clientId])) {
                continue;
            }
            $rows[] = $clientIdMap[$clientId];
        }

        return $rows;
    }

    /**
     * @param  $translations
     * @return array
     * @throws Exception
     */
    private function handleTranslations($translations): array
    {
        if (empty($translations)) {
            return [];
        }

        // Define field mapping for name to other fields
        $fieldMap = [
            'name' => ['summary', 'selling_point', 'content', 'meta_title', 'meta_description', 'meta_keywords'],
        ];

        // Process translations using TranslationHandler
        return TranslationHandler::process($translations, $fieldMap);
    }

    /**
     * Merge the dedicated long-text tab payload (`long_attributes[id][...]`,
     * keyed by attribute id) into the flat attribute row list, so both entry
     * points share the same normalization in handleAttributes().
     *
     * @param  $attributes
     * @param  $longAttributes
     * @return array
     */
    private function mergeLongTextAttributes($attributes, $longAttributes): array
    {
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true) ?: [];
        }
        if (! is_array($attributes)) {
            $attributes = [];
        }

        foreach ((array) $longAttributes as $attributeId => $row) {
            if (! is_array($row)) {
                continue;
            }
            $attributes[] = [
                'attribute_id' => (int) $attributeId,
                'value_text'   => $row['value_text'] ?? '',
                'is_visible'   => (bool) ($row['is_visible'] ?? false),
            ];
        }

        return $attributes;
    }

    /**
     * Normalize submitted attribute rows by the attribute type:
     * select rows keep the preset attribute_value_id, free-input rows
     * carry value_text / value_number instead. Flat wide-table design,
     * one row per product x attribute.
     *
     * @param  $attributes
     * @return array
     */
    private function handleAttributes($attributes): array
    {
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        }

        $attributeTypes = Attribute::query()->pluck('type', 'id');

        $items = [];
        foreach ($attributes as $attribute) {
            $attributeId = $attribute['attribute_id'] ?? 0;
            if (empty($attributeId)) {
                continue;
            }

            $type = $attributeTypes[$attributeId] ?? Attribute::TYPE_SELECT;
            $row  = ['attribute_id' => $attributeId];

            if ($type === Attribute::TYPE_NUMBER) {
                $raw = $attribute['value_number'] ?? $attribute['value_text'] ?? null;
                if ($raw === null || trim((string) $raw) === '') {
                    continue;
                }
                $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $raw);
                if ($cleaned === '' || ! is_numeric($cleaned)) {
                    continue;
                }
                $row['value_number']       = (float) $cleaned;
                $row['value_text']         = null;
                $row['attribute_value_id'] = null;
            } elseif (in_array($type, Attribute::longTextTypes(), true)) {
                $raw = $attribute['value_text'] ?? null;
                if (is_array($raw)) {
                    // Panel submits locale=>content map; keep non-empty locales only
                    $map = [];
                    foreach ($raw as $locale => $text) {
                        $text = trim((string) $text);
                        if ($text !== '') {
                            $map[$locale] = $text;
                        }
                    }
                    if (empty($map)) {
                        continue;
                    }
                    $row['value_text'] = json_encode($map, JSON_UNESCAPED_UNICODE);
                } else {
                    if ($raw === null || trim((string) $raw) === '') {
                        continue;
                    }
                    $row['value_text'] = trim((string) $raw);
                }
                $row['value_number']       = null;
                $row['attribute_value_id'] = null;
                $row['is_visible']         = (bool) ($attribute['is_visible'] ?? true);
            } elseif (in_array($type, [Attribute::TYPE_INPUT, Attribute::TYPE_DATE], true)) {
                $raw = $attribute['value_text'] ?? null;
                if ($raw === null || trim((string) $raw) === '') {
                    continue;
                }
                $raw                       = trim((string) $raw);
                $row['value_text']         = $raw;
                $row['value_number']       = null;
                $row['attribute_value_id'] = null;
            } else {
                $valueId = $attribute['attribute_value_id'] ?? null;
                if (empty($valueId)) {
                    continue;
                }
                $row['attribute_value_id'] = (int) $valueId;
                $row['value_text']         = null;
                $row['value_number']       = null;
            }

            $items[] = $row;
        }

        return $items;
    }

    /**
     * @return Builder
     */
    public function baseBuilder(): Builder
    {
        return Product::query();
    }

    /**
     * attr format: attr=1:1,2,3|5:6,7
     * @param  array  $filters
     * @return Builder
     * @throws Exception
     */
    public function builder(array $filters = []): Builder
    {
        $relations = [
            'skus',
            'masterSku',
            'translation',
            'categories.translation',
            'favorites',
            'bundles.sku.product.translation',
        ];

        $relations = array_merge($this->relations, $relations);

        $builder = $this->baseBuilder()->with($relations);

        $filters = array_merge($this->filters, $filters);

        // Use ProductQueryBuilder to handle filtering logic
        $queryBuilder = new ProductQueryBuilder;

        $builder = $queryBuilder->applyCategoryFilters($builder, $filters);
        $builder = $queryBuilder->applyAttributeFilters($builder, $filters);
        $builder = $queryBuilder->applyBrandFilters($builder, $filters);
        $builder = $queryBuilder->applyPriceFilters($builder, $filters);
        $builder = $queryBuilder->applyStockFilters($builder, $filters);
        $builder = $queryBuilder->applySearchFilters($builder, $filters);
        $builder = $queryBuilder->applySkuFilters($builder, $filters);
        $builder = $queryBuilder->applyBasicFilters($builder, $filters);
        $builder = $queryBuilder->applyDateFilters($builder, $filters);

        return fire_hook_filter('repo.product.builder', $builder);
    }

    /**
     * Best selling products, cached per locale.
     *
     * @param  int  $limit
     * @return mixed
     * @throws Exception
     */
    public function getBestSellerProducts(int $limit = 8): mixed
    {
        $limit = max(1, $limit);

        return Cache::remember(
            'product_bestseller_'.locale_code().'_'.$limit,
            self::BESTSELLER_CACHE_TTL,
            fn () => $this->queryBestSellerProducts($limit)
        );
    }

    /**
     * Rank products by their order item count.
     *
     * Ranking the whole catalogue in SQL makes MySQL scan every product row
     * and sort it in a temporary table (12K rows on this site): 19ms while
     * idle, but 3~5s once a dozen crawler workers request the page at the
     * same time — that is what made the front page TTFB swing between 0.3s
     * and 1.9s.
     *
     * The ranking now starts from the (tiny) order_items table, so only a
     * handful of product rows are touched. Products without sales fill the
     * remaining slots, exactly like the old query did, and every returned
     * model carries the `order_items_count` attribute the panel charts read.
     *
     * @param  int  $limit
     * @return Collection
     * @throws Exception
     */
    protected function queryBestSellerProducts(int $limit): Collection
    {
        $sales = DB::table((new Item)->getTable())
            ->select('product_id', DB::raw('COUNT(*) AS sales_count'))
            ->groupBy('product_id')
            ->orderByDesc('sales_count')
            ->pluck('sales_count', 'product_id');

        $ranked = collect();

        if ($sales->isNotEmpty()) {
            $candidateIds = $sales->keys()->take(self::BESTSELLER_CANDIDATE_LIMIT)->all();

            $ranked = $this->withActive()->builder()
                ->whereHas('translation')
                ->whereIn('id', $candidateIds)
                ->withCount('orderItems')
                ->orderByDesc('order_items_count')
                ->limit($limit)
                ->get();
        }

        if ($ranked->count() < $limit) {
            $exclude = $ranked->pluck('id')->all();

            $filler = $this->withActive()->builder()
                ->whereHas('translation')
                ->when($exclude, fn ($query) => $query->whereNotIn('id', $exclude))
                ->orderBy('id')
                ->limit($limit - $ranked->count())
                ->get();

            foreach ($filler as $product) {
                $product->setAttribute('order_items_count', (int) $sales->get($product->id, 0));
            }

            $ranked = $ranked->concat($filler);
        }

        return $ranked;
    }

    /**
     * @param  int  $limit
     * @return mixed
     * @throws Exception
     */
    public function getLatestProducts(int $limit = 8): mixed
    {
        return $this->withActive()->builder()
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get product list by IDs.
     *
     * @param  mixed  $productIDs
     * @return Collection
     */
    public function getListByProductIDs(mixed $productIDs): Collection
    {
        if (empty($productIDs)) {
            return collect();
        }
        $productIDs = $this->normalizeIdList($productIDs);

        return $this->sortByIdList(
            Product::query()
                ->with(['translation', 'masterSku'])
                ->whereIn('id', $productIDs)
                ->get(),
            $productIDs
        );
    }

    /**
     * @param  $spuCode
     * @return ?Product
     */
    public function findBySpuCode($spuCode): ?Product
    {
        if (empty($spuCode)) {
            return null;
        }

        return Product::query()->where('spu_code', $spuCode)->first();
    }

    /**
     * @param  $slug
     * @return ?Product
     */
    public function findBySlug($slug): ?Product
    {
        if (empty($slug)) {
            return null;
        }

        return Product::query()->where('slug', $slug)->first();
    }

    /**
     * @param  $keyword
     * @param  int  $limit
     * @return mixed
     */
    public function autocomplete($keyword, int $limit = 10): mixed
    {
        $keyword = trim((string) $keyword);
        $builder = Product::query()->with(['translation', 'masterSku']);
        if ($keyword !== '') {
            $builder->where(function ($q) use ($keyword) {
                $q->whereHas('translation', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })->orWhereHas('masterSku', function ($query) use ($keyword) {
                    $query->where('code', 'like', "%{$keyword}%");
                });
            });
        }

        return $builder->orderByDesc('id')->limit($limit)->get();
    }

    /**
     * @param  $id
     * @return string
     */
    public function getNameByID($id): string
    {
        return Product::query()->find($id)->description->name ?? '';
    }

    /**
     * Get bundle items for product display
     *
     * @param  Product  $product
     * @return Collection
     */
    public function getBundleItems(Product $product): Collection
    {
        return BundleRepo::getInstance()->getBundleItemsForDisplay($product);
    }

    /**
     * Get category options for cascader component
     *
     * @return array
     */
    public static function getCategoryOptions(): array
    {
        // Load ALL active categories + translations in 3 queries, build tree in memory
        $allCategories = Category::query()->where('active', true)
            ->with(['translation', 'translations'])
            ->orderBy('position')
            ->get();

        $itemsById = $allCategories->keyBy('id');

        // Build tree: attach children to parents, collect roots
        $tree = collect();
        foreach ($allCategories as $category) {
            if ($category->parent_id && isset($itemsById[$category->parent_id])) {
                $parent = $itemsById[$category->parent_id];
                if (! isset($parent->inlineChildren)) {
                    $parent->inlineChildren = collect();
                }
                $parent->inlineChildren->push($category);
            } else {
                $tree->push($category);
            }
        }

        return CategoryRepo::formatCategoriesForCascaderInline($tree);
    }
}
