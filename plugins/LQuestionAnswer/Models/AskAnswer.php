<?php

namespace Plugin\LQuestionAnswer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Product;

class AskAnswer extends Model
{
    public $table = 'product_ask_answer';

    public function children(): HasMany
    {
        return $this->hasMany(AskAnswer::class, 'parent_id', 'id');

    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(AskAnswer::class, 'reply_id', 'id');
    }
}
