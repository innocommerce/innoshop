<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Customer;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Customer;

class Transaction extends BaseModel
{
    protected $table = 'customer_transactions';

    protected $fillable = [
        'customer_id', 'amount', 'type', 'comment',
    ];

    const TYPES = [
        'recharge', 'withdraw', 'refund', 'consumption',
    ];

    const TYPE_RECHARGE = 'recharge';

    const TYPE_WITHDRAW = 'withdraw';

    const TYPE_REFUND = 'refund';

    const TYPE_CONSUMPTION = 'consumption';

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * @return string
     */
    public function getTypeFormatAttribute(): string
    {
        return trans('common/transaction.'.$this->type);
    }
}
