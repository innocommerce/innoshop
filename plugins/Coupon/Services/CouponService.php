<?php

namespace Plugin\Coupon\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Plugin\Coupon\Models\Coupon;
use Plugin\Coupon\Models\Order;
use Plugin\Coupon\Repositories\CouponRepo;
use Carbon\Carbon;

class CouponService
{
    protected $couponRepo;

    public function __construct(CouponRepo $couponRepo)
    {
        $this->couponRepo = $couponRepo;
    }

    /**
     * 根据筛选条件获取优惠券列表。
     *
     * @param  string|null  $status
     * @param  string|null  $type
     * @return mixed
     */
    public function getCouponsFiltered(?string $status, ?string $type, $paginate = 20): mixed
    {
        return $this->couponRepo->findCouponsByFilters($status, $type, $paginate);
    }

    /**
     * 创建优惠券
     *
     * @param  array  $data
     * @return mixed
     */
    public function createCoupon(Request $request): Coupon
    {
        $validatedData = $request->validate([
            'code'              => 'required|string|unique:coupons,code',
            'type'              => 'required|in:percentage,fixed',
            'value'             => 'required|numeric',
            'start_at'          => 'required|date',
            'end_at'            => 'nullable|date|after_or_equal:start_at',
            'active'            => 'required|boolean',
            'max_uses'          => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'daily_limit'       => 'nullable|integer|min:1',
            'use_interval'      => 'nullable|integer|min:1',
        ]);

        return $this->couponRepo->create($validatedData);
    }

    /**
     * 更新优惠券信息
     *
     * @param  Request  $request
     * @param  $code
     * @return bool
     */
    public function update(Request $request, $code)
    {
        $coupon        = $this->couponRepo->findByCode($code);
        $validatedData = $request->validate([
            'code'              => 'required|string|unique:coupons,code,'.$coupon->id,
            'type'              => 'required|in:percentage,fixed',
            'value'             => 'required|numeric',
            'start_at'          => 'required|date',
            'end_at'            => 'nullable|date|after_or_equal:start_at',
            'active'            => 'required|boolean',
            'max_uses'          => 'nullable|integer|min:0',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'daily_limit'       => 'nullable|integer|min:1',
            'use_interval'      => 'nullable|integer|min:1',
            // 确认其他字段的验证规则
        ]);
        $this->couponRepo->update($coupon, $validatedData);

        return true;
    }

    /**
     * 应用优惠券
     *
     * @param  string  $code
     * @return array
     */
    public function applyCoupon($code)
    {
        $coupon = $this->couponRepo->findByCode($code);

        // 检查优惠券是否存在并且尚未使用
        if (!$coupon) {
            return ['success' => false, 'message' => __('Coupon::common.coupon_not_found')];
        }

        // 检查优惠券是否过期（如果有结束日期的话）
        if ($coupon->end_at && Carbon::parse($coupon->end_at)->isPast()) {
            return ['success' => false, 'message' => __('Coupon::common.coupon_is_expired')];
        }

        if ($coupon->is_used) {
            return ['success' => false, 'message' => __('Coupon::front.coupon_was_used')];
        }

        // 存储优惠券代码到会话
        session(['coupon_code' => $code]);

        // 返回优惠券详细信息，包括类型和具体优惠值
        return [
            'success'     => true,
            'message'     => __('Coupon::common.coupon_is_valid'),
            'type'        => $coupon->type, // 'percentage' or 'fixed'
            'discount'    => $coupon->value, // 优惠值，可以是折扣百分比或具体金额
            'description' => $coupon->description, // 如果有的话，也可以返回优惠券描述
        ];
    }

    /**
     * @return string
     */
    public function getCouponCode(): string
    {
        return session('coupon_code', ''); // 如果会话中没有优惠券代码，则返回''
    }

    /**
     * @return void
     */
    public function forgetCouponCodeInSession(): void
    {
        session()->forget('coupon_code');
    }

    /**
     * 根据Id查找记录。
     *
     * @return mixed
     */
    public function findById($id)
    {
        return Coupon::find($id)->firstOrFail();
    }

    /**
     * 根据code查找记录。
     *
     * @return mixed
     */
    public function findByCode($code)
    {
        return Coupon::where('code', $code)->firstOrFail();
    }

    /**
     * 核销优惠券
     * @param  $couponCode
     * @param  int  $orderId  订单ID
     * @return bool
     */
    public function redeemCoupon($couponCode, $orderId, $userId)
    {
        DB::beginTransaction();
        try {
            $coupon = Coupon::where('code', $couponCode)->firstOrFail();
            $order  = Order::findOrFail($orderId);

            if ($coupon->isValid($order->customer_id)) {
                //                return response()->json(['success' => false, 'message' => __('Coupon::common.coupon_is_not_valid')]);

                if ($coupon->is_used) {
                    throw new \Exception(__('Coupon::common.coupon_already_used'));
                }

                // 核销优惠券
                $coupon->increment('times_used');  // 增加使用次数
                $coupon->redemptions()->create([
                    'user_id'      => $userId,
                    'order_id'     => $orderId,
                    'last_used_at' => now(),
                    'date_used'    => today(),
                ]);
                // 如果优惠券现在达到了最大使用次数，标记为完全使用
                if ($coupon->times_used >= $coupon->max_uses) {
                    $coupon->is_used = true;
                }
                $coupon->save();

                // 更新订单信息
                $order->coupon_id = $coupon->id;
                $order->save();

                DB::commit();
                session()->forget('coupon_code');

                return true;
            }

            throw new \Exception(__('Coupon::common.invalid_coupon_or_order'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            report($e); // 记录具体的模型找不到异常

            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            report($e); // 记录一般异常

            return false;
        }

    }

    /**
     * 使用优惠券
     *
     * @param Coupon $coupon
     * @param int $userId
     * @param int|null $orderId
     * @return bool
     */
    public function useCoupon(Coupon $coupon, int $userId, ?int $orderId = null): bool
    {
        try {
            DB::beginTransaction();

            // 创建优惠券使用记录
            CouponRedemption::create([
                'coupon_id' => $coupon->id,
                'user_id' => $userId,
                'order_id' => $orderId,
                'last_used_at' => now(),
                'date_used' => now()->toDateString(),
            ]);

            // 更新优惠券使用次数
            $coupon->increment('times_used');

            // 如果达到最大使用次数，标记为已使用
            if ($coupon->max_uses && $coupon->times_used >= $coupon->max_uses) {
                $coupon->update(['is_used' => true]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('使用优惠券失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 取消使用优惠券
     *
     * @param Coupon $coupon
     * @param int $userId
     * @param int|null $orderId
     * @return bool
     */
    public function cancelUseCoupon(Coupon $coupon, int $userId, ?int $orderId = null): bool
    {
        try {
            DB::beginTransaction();

            // 删除优惠券使用记录
            $redemption = CouponRedemption::where([
                'coupon_id' => $coupon->id,
                'user_id' => $userId,
                'order_id' => $orderId,
            ])->first();

            if ($redemption) {
                $redemption->delete();

                // 减少使用次数
                $coupon->decrement('times_used');

                // 如果之前是已用完状态，现在恢复为未使用
                if ($coupon->is_used && $coupon->times_used < $coupon->max_uses) {
                    $coupon->update(['is_used' => false]);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('取消使用优惠券失败：' . $e->getMessage());
            return false;
        }
    }
}
