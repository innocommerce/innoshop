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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Handlers\TranslationHandler;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\Product\RelationRepo;
use InnoShop\Common\Repositories\Product\VariantRepo;
use Throwable;

class ProductRepo extends BaseRepo
{
    const AVAILABLE_SORT_FIELDS = [
        'updated_at',
        'created_at',
        'ps.price',
        'pt.name',
        'sales',
        'viewed',
        //'rating'
    ];

    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'keyword', 'type' => 'input', 'label' => trans('panel/common.name')],
            ['name' => 'price', 'type' => 'range', 'label' => trans('panel/product.price')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('panel/common.created_at')],
        ];
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $sort  = $filters['sort']  ?? 'updated_at';
        $order = $filters['order'] ?? 'desc';

        return $this->builder($filters)->orderBy($sort, $order)->paginate($filters['per_page'] ?? 15);
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getFrontList(array $filters = []): LengthAwarePaginator
    {
        $sort    = $filters['sort']     ?? 'created_at';
        $order   = $filters['order']    ?? 'desc';
        $perPage = $filters['per_page'] ?? 15;

        $builder = $this->withActive()->builder($filters);

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
            $sort = 'updated_at';
        }

        if (! in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        if ($sort && $order) {
            $builder->orderBy($sort, $order);
        }

        return $builder->paginate($perPage);
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
        $item->skus()->delete();
        $item->translations()->delete();
        $item->delete();
    }

    /**
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
            'videos',
        ]);
        $copy = $product->replicate();

        $copy->slug .= '-'.rand(0, 99999);
        $copy->push();

        foreach ($product->getRelations() as $relation => $entries) {
            foreach ($entries as $entry) {
                $newEntry = $entry->replicate();
                if ($relation == 'skus') {
                    $newEntry->code .= '-'.rand(0, 99999);
                } elseif ($relation == 'categories') {
                    $copy->categories()->attach($entry->id);

                    continue;
                }
                if ($newEntry->push()) {
                    $copy->{$relation}()->save($newEntry);
                }
            }
        }

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
            $productData = $this->handleProductData($data);
            $product->fill($productData);
            $product->saveOrFail();

            if ($isUpdating) {
                $product->skus()->delete();
                $product->translations()->delete();
                $product->productAttributes()->delete();
                $product->relations()->delete();
            }

            $translations = $this->handleTranslations($data['translations'] ?? []);
            if ($translations) {
                $product->translations()->createMany($translations);
            }

            $product->productAttributes()->createMany($this->handleAttributes($data['attributes'] ?? []));
            RelationRepo::getInstance()->handleBidirectionalRelations($product, $data['related_ids'] ?? []);
            $product->categories()->sync($data['categories'] ?? []);

            $skus = $this->handleSkus($data['skus'] ?? []);
            if (isset($data['price_type']) && $data['price_type'] === 'single' && ! empty($skus)) {
                $product->update(['variables' => []]);
                $skus = [$skus[0]];
            }
            $product->skus()->createMany($skus);

            DB::commit();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
            if (isset($data['variants'])) {
                $variables         = VariantRepo::getInstance()->mergeVariant($product->variables ?? [], $data['variants']);
                $data['variables'] = $variables;
            }

            $product->fill($data);
            $product->saveOrFail();

            if (isset($data['translations'])) {
                $translations = $this->handleTranslations($data['translations']);
                foreach ($translations as $translation) {
                    $existTranslation = $product->translations()->where('locale', $translation['locale'])->first();
                    if ($existTranslation) {
                        $existTranslation->update($translation);
                    } else {
                        $product->translations()->create($translation);
                    }
                }
            }

            if (isset($data['attributes'])) {
                $product->productAttributes()->delete();
                $product->productAttributes()->createMany($this->handleAttributes($data['attributes']));
            }

            if (isset($data['related_ids'])) {
                RelationRepo::getInstance()->handleBidirectionalRelations($product, $data['related_ids']);
            }

            if (isset($data['categories'])) {
                $product->categories()->sync($data['categories']);
            }

            if (isset($data['skus'])) {
                $product->skus()->delete();
                $product->skus()->createMany($this->handleSkus($data['skus']));
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
    public function handleProductData($data): array
    {
        $variables = $data['variables'] ?? ($data['variants'] ?? []);
        if (is_string($variables)) {
            $variables = json_decode($variables, true);
        }

        return [
            'spu_code'     => $data['spu_code']     ?? null,
            'slug'         => $data['slug']         ?? null,
            'brand_id'     => $data['brand_id']     ?? 0,
            'images'       => $data['images']       ?? [],
            'tax_class_id' => $data['tax_class_id'] ?? 0,
            'variables'    => $variables,
            'position'     => (int) ($data['position'] ?? 0),
            'weight'       => $data['weight']       ?? 0,
            'weight_class' => $data['weight_class'] ?? '',
            'sales'        => (int) ($data['sales'] ?? 0),
            'viewed'       => (int) ($data['viewed'] ?? 0),
            'published_at' => $data['published_at'] ?? now(),
            'active'       => (bool) ($data['active'] ?? true),
        ];
    }

    /**
     * @param  $skus
     * @return array
     */
    public function handleSkus($skus): array
    {
        if (is_string($skus)) {
            $skus = json_decode($skus, true);
        }
        $onlyOneSku = count($skus) == 1;

        $items = [];
        foreach ($skus as $sku) {
            $variants = $sku['variants'] ?? [];
            if (is_string($variants)) {
                $variants = json_decode($variants);
            }

            if ($onlyOneSku) {
                $isDefault = true;
            } else {
                $isDefault = $sku['is_default'] ?? false;
            }

            $code = $sku['code'];

            $items[] = [
                'images'       => [$sku['image'] ?? ''],
                'variants'     => $variants,
                'code'         => $code,
                'model'        => $sku['model'] ?? $code,
                'price'        => (float) ($sku['price'] ?? 0),
                'origin_price' => (float) ($sku['origin_price'] ?? 0),
                'quantity'     => (int) ($sku['quantity'] ?? 0),
                'is_default'   => (bool) $isDefault,
                'position'     => (int) ($sku['position'] ?? 0),
            ];
        }

        return $items;
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
     * @param  $attributes
     * @return array
     */
    private function handleAttributes($attributes): array
    {
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        }

        $items = [];
        foreach ($attributes as $attribute) {
            if (empty($attribute['attribute_id'] ?? []) || empty($attribute['attribute_value_id'] ?? [])) {
                continue;
            }
            $items[] = $attribute;
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
            'masterSku',
            'translation',
            'categories.translation',
            'favorites',
        ];

        $relations = array_merge($this->relations, $relations);

        $builder = $this->baseBuilder()->with($relations);

        $filters = array_merge($this->filters, $filters);

        $categoryId = $filters['category_id'] ?? 0;
        if ($categoryId) {
            $builder->whereHas('categories', function (Builder $query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }

        $categorySlug = $filters['category_slug'] ?? '';
        if ($categorySlug) {
            $category = Category::query()->where('slug', $categorySlug)->first();
            if ($category) {
                $categories = CategoryRepo::getInstance()->builder(['parent_id' => $category->id])->get();

                $filters['category_ids']   = $categories->pluck('id');
                $filters['category_ids'][] = $category->id;
            }
        }

        $categoryIds = $filters['category_ids'] ?? [];
        if ($categoryIds instanceof Collection) {
            $categoryIds = $categoryIds->toArray();
        }
        $categoryIds = array_unique($categoryIds);
        if ($categoryIds) {
            $builder->whereHas('categories', function (Builder $query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            });
        }

        $attr = $filters['attr'] ?? [];
        if ($attr) {
            $attributes = parse_attr_filters($attr);
            foreach ($attributes as $attribute) {
                $builder->whereHas('productAttributes', function ($query) use ($attribute) {
                    $query->where('attribute_id', $attribute['attr'])
                        ->whereIn('attribute_value_id', $attribute['value']);
                });
            }
        }

        $attributeValueIds = parse_int_filters($filters['attribute_value_ids'] ?? []);
        if ($attributeValueIds) {
            $builder->whereHas('productAttributes', function (Builder $query) use ($attributeValueIds) {
                $query->whereIn('attribute_value_id', $attributeValueIds);
            });
        }

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->whereHas('translation', function (Builder $query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%");
            });
        }

        $brandID = $filters['brand_id'] ?? 0;
        if ($brandID) {
            $builder->where('brand_id', $brandID);
        }

        $slug = $filters['slug'] ?? '';
        if ($slug) {
            $builder->where('slug', $slug);
        }

        $productIDs = $filters['product_ids'] ?? [];
        if ($productIDs) {
            $builder->whereIn('products.id', $productIDs);
        }

        if (isset($filters['active'])) {
            $builder->where('products.active', (bool) $filters['active']);
        }

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        $priceStart = $filters['price_start'] ?? '';
        if ($priceStart) {
            $builder->whereHas('masterSku', function (Builder $query) use ($priceStart) {
                $query->where('price', '>', $priceStart);
            });
        }

        $priceEnd = $filters['price_end'] ?? '';
        if ($priceEnd) {
            $builder->whereHas('masterSku', function (Builder $query) use ($priceEnd) {
                $query->where('price', '<', $priceEnd);
            });
        }

        return fire_hook_filter('repo.product.builder', $builder);
    }

    /**
     * @param  int  $limit
     * @return mixed
     * @throws Exception
     */
    public function getBestSellerProducts(int $limit = 8): mixed
    {
        return $this->withActive()->builder()
            ->whereHas('translation')
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->limit($limit)
            ->get();
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
     * @return mixed
     */
    public function getListByProductIDs(mixed $productIDs): mixed
    {
        if (empty($productIDs)) {
            return [];
        }
        if (is_string($productIDs)) {
            $productIDs = explode(',', $productIDs);
        }

        return Product::query()
            ->with(['translation', 'masterSku'])
            ->whereIn('id', $productIDs)
            ->orderByRaw('FIELD(id, '.implode(',', $productIDs).')')
            ->get();
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
        if (empty($spuCode)) {
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
        $builder = Product::query()->with(['translation', 'masterSku']);
        if ($keyword) {
            $builder->whereHas('translation', function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            });
        }

        return $builder->limit($limit)->get();
    }

    /**
     * @param  $id
     * @return string
     */
    public function getNameByID($id): string
    {
        return Product::query()->find($id)->description->name ?? '';
    }
}
