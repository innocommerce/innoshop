<?php

namespace Plugin\ProductVariable\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCustomSkus extends Model
{
    public $table = 'product_custom_skus';

    protected $fillable = [
        'product_id',
    ];

    public function translation()
    {
        return $this->hasOne(ProductCustomSkusTranslations::class, 'product_custom_skus_id')->where('locale', locale_code());
    }

    public function translations()
    {
        return $this->hasMany(ProductCustomSkusTranslations::class, 'product_custom_skus_id');
    }
}
