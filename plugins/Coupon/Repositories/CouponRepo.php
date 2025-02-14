<?php

namespace Plugin\Coupon\Repositories;

use InnoShop\Common\Repositories\BaseRepo;
use Plugin\Coupon\Models\Coupon;

class CouponRepo extends BaseRepo
{
    protected string $model = Coupon::class;

    /**
     * 自定义数据处理，专为优惠券逻辑
     *
     * @param  $data
     * @return array
     */
    private function handleData($data)
    {
        // 这里根据你的业务逻辑调整字段处理
        return [
            'code'              => $data['code']              ?? '',
            'type'              => $data['type']              ?? 'fixed',
            'value'             => $data['value']             ?? 0,
            'start_at'          => $data['start_at']          ?? now()->format('Y-m-d'),
            'end_at'            => $data['end_at']            ?? null,
            'active'            => $data['active']            ?? true,
            'max_uses'          => $data['max_uses']          ?? null,
            'max_uses_per_user' => $data['max_uses_per_user'] ?? null,
            'daily_limit'       => $data['daily_limit']       ?? null,
            'use_interval'      => $data['use_interval']      ?? null,
            'times_used'        => $data['times_used']        ?? null,
        ];
    }

    /**
     * 根据状态和类型筛选优惠券。
     *
     * @param  string|null  $status
     * @param  string|null  $type
     * @param  int  $paginate
     * @return mixed
     */
    public function findCouponsByFilters(?string $status, ?string $type, $paginate = 20): mixed
    {
        $query = Coupon::query();

        if ($status) {
            if ($status === 'used') {
                $query->where('is_used', true);
            } elseif ($status === 'not_used') {
                $query->where('is_used', false);
            } elseif ($status === 'expired') {
                $query->where('end_at', '<', now());
            } elseif ($status === 'not_expired') {
                $query->where('end_at', '>=', now());
            }
        }

        if ($type) {
            $query->where('type', $type);
        }

        return $query->paginate($paginate);
    }

    /**
     * 使用自定义的数据处理方法来创建优惠券
     *
     * @param  $data
     * @return mixed
     */
    public function create($data): mixed
    {
        $data = $this->handleData($data);

        return parent::create($data);
    }

    /**
     * 使用自定义的数据处理方法更新优惠券
     *
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     */
    public function update($item, $data): mixed
    {
        $data = $this->handleData($data);

        return parent::update($item, $data);
    }

    /**
     * 查找特定优惠码的优惠券
     *
     * @param  string  $code
     * @return mixed
     */
    public function findByCode(string $code): mixed
    {
        return Coupon::query()->where('code', $code)->first();
    }
}
