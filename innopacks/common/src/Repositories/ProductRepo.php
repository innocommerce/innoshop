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
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\Product\ImageRepo;
use Throwable;

class ProductRepo extends BaseRepo
{
    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderByDesc('id')->paginate();
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
        $item->productAttributes()->delete();
        $item->categories()->sync([]);
        $item->images()->delete();
        $item->relations()->delete();
        $item->skus()->delete();
        $item->translations()->delete();
        $item->videos()->delete();
        $item->delete();
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
            }

            $product->skus()->createMany($this->handleSkus($product, $data['skus']));
            $product->translations()->createMany($this->handleTranslations($data['translations']));
            $product->productAttributes()->createMany($data['attributes'] ?? []);
            $product->categories()->sync($data['categories'] ?? []);

            if (isset($data['images'])) {
                //$product->images()->delete();
                $this->syncImages($product, $data['images'] ?: []);
            }

            $masterSku = $product->skus()->where('is_default', true)->first();

            $product->product_sku_id   = $masterSku->id;
            $product->product_image_id = $product->images()->first()->id ?? 0;
            $product->saveOrFail();

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
    private function handleProductData($data): array
    {
        $variables = $data['variables'] ?? ($data['variants'] ?? []);
        if (is_string($variables)) {
            $variables = json_decode($variables, true);
        }

        return [
            'slug'             => $data['slug'],
            'brand_id'         => $data['brand_id']         ?? 0,
            'product_image_id' => $data['product_image_id'] ?? 0,
            'product_video_id' => $data['product_video_id'] ?? 0,
            'product_sku_id'   => $data['product_sku_id']   ?? 0,
            'tax_class_id'     => $data['tax_class_id']     ?? 0,
            'variables'        => $variables,
            'position'         => $data['position']     ?? 0,
            'weight'           => $data['weight']       ?? 0,
            'weight_class'     => $data['weight_class'] ?? '',
            'sales'            => $data['sales']        ?? 0,
            'viewed'           => $data['viewed']       ?? 0,
            'published_at'     => $data['published_at'] ?? now(),
            'active'           => true,
        ];
    }

    /**
     * @param  $translations
     * @return array
     */
    private function handleTranslations($translations): array
    {
        $items = [];
        foreach ($translations as $translation) {
            $name    = $translation['name'];
            $items[] = [
                'locale'           => $translation['locale'],
                'name'             => $name,
                'summary'          => $translation['summary']          ?? $name,
                'selling_point'    => $translation['selling_point']    ?? $name,
                'content'          => $translation['content']          ?? $name,
                'meta_title'       => $translation['meta_title']       ?? $name,
                'meta_description' => $translation['meta_description'] ?? $name,
                'meta_keywords'    => $translation['meta_keywords']    ?? $name,
            ];
        }

        return $items;
    }

    /**
     * @param  $product
     * @param  $skus
     * @return array
     */
    private function handleSkus($product, $skus): array
    {
        if (is_string($skus)) {
            $skus = json_decode($skus, true);
        }
        $onlyOneSku = count($skus) == 1;

        $items = [];
        foreach ($skus as $sku) {
            $path = $sku['image'] ?? '';
            if ($path) {
                $image   = ImageRepo::getInstance()->findOrCreate($product, $path);
                $imageID = $image->id ?? 0;
            } else {
                $imageID = $sku['product_image_id'] ?? 0;
            }

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
                'product_image_id' => $imageID,
                'variants'         => $variants,
                'code'             => $code,
                'model'            => $sku['model']        ?? $code,
                'price'            => $sku['price']        ?? 0,
                'origin_price'     => $sku['origin_price'] ?? 0,
                'quantity'         => $sku['quantity']     ?? 0,
                'is_default'       => $isDefault,
                'position'         => $sku['position'] ?? 0,
            ];
        }

        return $items;
    }

    /**
     * Sync product images.
     *
     * @param  Product  $product
     * @param  $images
     * @return void
     */
    private function syncImages(Product $product, $images): void
    {
        if (empty($images)) {
            return;
        }
        foreach ($images as $image) {
            ImageRepo::getInstance()->findOrCreate($product, $image);
        }
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
            'image',
            'images',
            'masterSku',
            'translation',
            'categories.translation',
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

        if (isset($filters['active'])) {
            $builder->where('products.active', (bool) $filters['active']);
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
}
