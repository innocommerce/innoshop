<?php

namespace Plugin\ProductVariable\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCustomSkusTranslations extends Model
{
    public $table = 'product_custom_sku_translations';

    protected $fillable = [
        'custom_sku_id',
        'locale',
        'name',
    ];

    public function customSku()
    {
        return $this->belongsTo(ProductCustomSkus::class, 'custom_sku_id');
    }
}
