<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Product;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku\VariantValue;
use InnoShop\Common\Models\Product\Variant\Value;
use InnoShop\Common\Services\ProductPriceService;

/**
 * @property int $quantity
 */
class Sku extends BaseModel
{
    protected $table = 'product_skus';

    protected $fillable = [
        'product_id', 'images', 'model', 'code', 'price', 'origin_price', 'quantity', 'is_default', 'position',
        'weight',
    ];

    protected $casts = [
        'images' => 'array',
        'weight' => 'float',
    ];

    protected $appends = ['image'];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Variant values picked by this SKU across all dimensions.
     *
     * Normalized replacement for the legacy product_skus.variants JSON index
     * array. The pivot table's UNIQUE(sku_id, variant_id) constraint
     * guarantees one value per dimension at the DB layer.
     *
     * @return BelongsToMany
     */
    public function variantValues(): BelongsToMany
    {
        return $this->belongsToMany(
            Value::class,
            'product_sku_variant_values',
            'sku_id',
            'value_id'
        )
            ->withPivot('variant_id')
            ->using(VariantValue::class)
            ->orderBy('position');
    }

    /**
     * Render localized labels for this SKU across its variant dimensions.
     *
     * Reads from normalized variant_values + translations. Output order
     * follows the product's variant dimensions (Color, Size, ...), NOT the
     * pivot's natural order, so labels stay consistent across SKUs.
     *
     * @return array
     */
    public function getLocaleLabels(): array
    {
        $this->loadMissing([
            'variantValues.translation',
            'variantValues.variant.translation',
            'product.variants',
        ]);

        if ($this->variantValues->isEmpty() || $this->product->variants->isEmpty()) {
            return [];
        }

        $valueByVariant = $this->variantValues->keyBy('variant_id');
        $labels         = [];
        foreach ($this->product->variants as $variant) {
            $value = $valueByVariant->get($variant->id);
            if (! $value) {
                continue;
            }
            $labels[] = [
                'name'  => $variant->translation?->name ?? '',
                'value' => $value->translation?->name ?? '',
            ];
        }

        return $labels;
    }

    /**
     * Get sku final price.
     * @return mixed
     */
    public function getFinalPrice(): mixed
    {
        $price = ProductPriceService::getInstance($this)->getFinal();
        $data  = [
            'sku'   => $this,
            'price' => $price,
        ];
        $data = fire_hook_filter('model.sku.final_price', $data);

        return $data['price'];
    }

    /**
     * Get image path from SKU or SPU.
     *
     * @return string
     */
    public function getImagePath(): string
    {
        $skuImage = $this->image;
        if ($skuImage) {
            return $skuImage;
        }

        return $this->product->image ?? '';
    }

    /**
     * Get image url form SKU or SPU.
     *
     * @param  int  $width
     * @param  int  $height
     * @return string
     * @throws Exception
     */
    public function getImageUrl(int $width = 100, int $height = 100): string
    {
        $imagePath = $this->getImagePath();

        return image_resize($imagePath, $width, $height);
    }

    /**
     * @return string
     */
    public function getImageAttribute(): string
    {
        $images = $this->images ?? [];

        return $images[0] ?? '';
    }

    /**
     * Get sku final price.
     * @return mixed
     */
    public function getFinalPriceFormat(): mixed
    {
        return currency_format($this->getFinalPrice());
    }

    /**
     * @return string
     */
    public function getPriceFormatAttribute(): string
    {
        return currency_format($this->price);
    }

    /**
     * @return string
     */
    public function getOriginPriceFormatAttribute(): string
    {
        return currency_format($this->origin_price);
    }

    /**
     * @return string
     */
    public function getImagePathAttribute(): string
    {
        return $this->image ?? '';
    }

    /**
     * @return string
     */
    public function getVariantLabelAttribute(): string
    {
        $vLabel = '';
        $labels = $this->getLocaleLabels();
        if (empty($labels)) {
            return '';
        }

        foreach ($labels as $label) {
            $vLabel .= $label['name'].':'.$label['value'].'; ';
        }

        $data = [
            'vLabel' => $vLabel,
            'sku'    => $this,
        ];
        $data = fire_hook_filter('model.sku.variant_label_attribute', $data);

        return trim($data['vLabel']);
    }

    /**
     * Get full SKU name (product name + variant label)
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $productName  = $this->product->translation->name ?? '';
        $variantLabel = $this->variant_label;

        return $productName.($variantLabel ? ' ('.$variantLabel.')' : '');
    }
}
