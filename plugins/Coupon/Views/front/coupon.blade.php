<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- 优惠码应用表单 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Coupon::front.apply_promo_code') }}</h5>
                </div>
                <div class="card-body">
                    <form id="promoCodeForm">
                        <div class="mb-3">
                            <label for="promoCode" class="form-label">{{ __('Coupon::front.have_a_promo_code') }}</label>
                            <input type="text" class="form-control" id="promoCode" name="promoCode" placeholder="{{ __('Coupon::front.enter_promo_code') }}">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="applyPromoCode()">{{ __('Coupon::front.apply') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('footer')
    <script>
        function applyPromoCode() {
            var code = document.getElementById('promoCode').value;
            if (code) {
                axios('{{panel_route('api.apply-coupon')}}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    data: JSON.stringify({ code: code })
                })
                    .then(response => {
                        console.log(response);
                        const data = response;
                        // 根据优惠券类型更新 UI 或计算逻辑
                        if (data.type === 'percentage') {
                            // 应用百分比折扣逻辑
                        } else if (data.type === 'fixed') {
                            // 应用固定金额抵扣逻辑
                        }
                        if (data.success === true) {
                            layer.msg('{{ __('Coupon::front.discount_applied_successfully') }}', { icon: 1 });
                            setTimeout(()=>location.reload(),1000);
                        } else if(data.success === false) {
                            layer.msg('{{ __('Coupon::front.failed_to_apply_discount') }}' + data.message, { icon: 2 });
                        }
                    })
                    .catch(error => {
                        console.error('{{ __('Coupon::front.error') }}:', error);
                        layer.msg(error.message, { icon: 2 });
                    });
            } else {
                layer.msg('{{ __('Coupon::front.please_enter_a_promo_code') }}',{ icon:2 })
            }
        }
    </script>
@endpush
