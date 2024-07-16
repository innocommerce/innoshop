<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\Product\ImageRepo;

class ProductRepo extends BaseRepo
{
    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderByDesc('updated_at')->paginate();
    }

    /**
     * Create product.
     *
     * @param  $data
     * @return mixed
     * @throws \Throwable
     */
    public function create($data): mixed
    {
        $product = new Product();

        return $this->createOrUpdate($product, $data);
    }

    /**
     * Update product.
     *
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function update($item, $data): mixed
    {
        return $this->createOrUpdate($item, $data);
    }

    /**
     * @param  mixed  $item
     * @return void
     */
    public function destroy(mixed $item): void
    {
        $item->attributes()->delete();
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
     * @throws \Throwable
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
                //$product->attributes()->delete();
            }

            $product->translations()->createMany($this->handleTranslations($data['translations']));
            $product->skus()->createMany($this->handleSkus($product, $data['skus']));
            $product->categories()->sync($data['categories'] ?? []);

            $masterSku = $product->skus()->where('is_default', true)->first();

            $product->product_sku_id   = $masterSku->id;
            $product->product_image_id = $product->images()->first()->id ?? 0;
            $product->saveOrFail();

            if (isset($data['images'])) {
                $product->images()->delete();
                $this->syncImages($product, $data['images'] ?? []);
            }

            DB::commit();

            return $product;
        } catch (\Exception $e) {
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
        $variables = $data['variables'] ?? [];
        if (is_string($variables)) {
            $variables = json_decode($variables);
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
     * @param  array  $filters
     * @return Builder
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
        $builder = Product::query()->with($relations);

        $filters = array_merge($this->filters, $filters);

        $categoryId = $filters['category_id'] ?? 0;
        if ($categoryId) {
            $builder->whereHas('categories', function (Builder $query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }

        $categoryIds = $filters['category_ids'] ?? [];
        if ($categoryIds) {
            $builder->whereHas('categories', function (Builder $query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
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
            $builder->where('active', (bool) $filters['active']);
        }

        return fire_hook_filter('repo.product.builder', $builder);
    }

    /**
     * @param  int  $limit
     * @return mixed
     */
    public function getBestSellerProducts(int $limit = 8): mixed
    {
        return $this->withActive()->builder()
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  int  $limit
     * @return mixed
     */
    public function getLatestProducts(int $limit = 8): mixed
    {
        return $this->withActive()->builder()
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }
}
