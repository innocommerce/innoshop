<?php

namespace Plugin\SmsBao\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\Customer;

class CustomerAuth extends Customer
{
    public $table = 'customer_mobiles';

    public $fillable = [
        'customer_id',
        'mobile_code',
        'mobile',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
