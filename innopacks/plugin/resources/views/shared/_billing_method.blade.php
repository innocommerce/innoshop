<div class="my-4" id="billingMethodsContent">
    <h5 class="mb-3">{{ __('panel/plugin.payment_method') }}</h5>

</div>

@push('footer')
    <script>
    $(function () {

        var billingMethod=''

        var paymentMethodTranslations = {
            'alipay': '{{ __('panel/plugin.payment_alipay') }}',
            'alipay_payment': '{{ __('panel/plugin.payment_alipay') }}',
            'wechatpay': '{{ __('panel/plugin.payment_wechatpay') }}',
            'wechat_pay': '{{ __('panel/plugin.payment_wechat_pay') }}',
            'we_chat_pay': '{{ __('panel/plugin.payment_wechat_pay') }}',
            'wechat': '{{ __('panel/plugin.payment_wechatpay') }}',
        };

        function getPaymentMethodName(code, originalName) {
            var normalizedCode = code.toLowerCase().replace(/[^a-z0-9]/g, '_');
            if (paymentMethodTranslations[normalizedCode]) {
                return paymentMethodTranslations[normalizedCode];
            }
            var translationKey = 'payment_' + normalizedCode;
            if (paymentMethodTranslations[translationKey]) {
                return paymentMethodTranslations[translationKey];
            }
            return originalName;
        }

        axios.get('/{{ panel_name() }}/marketplaces/billing_methods', { hload: true }).then(function (res) {
            var methods = Array.isArray(res) ? res : (res.data || []);
            for (i = 0; i < methods.length; i++) {
                var paymentCode = methods[i].code;
                var paymentName = getPaymentMethodName(paymentCode, methods[i].name);

                if (i === 0){
                    $('#billingMethodsContent').append(
                        "<div class='form-check form-check-inline method-checks rounded-1 p-1 cursor-pointer' id='method-"+ paymentCode +"' style='border: 1px solid #409eff'>" +
                        "<input class='form-check-input billing-method-check mx-1 my-2' checked type='radio' name='shipping_method_code' id='shippingMethodCode" + paymentCode + "' value=" + paymentCode + ">" +
                        "<label class='form-check-label align-middle cursor-pointer' for='shippingMethodCode" + paymentCode + "'><img src='" + methods[i].icon + "' alt='" + paymentName + "' style='height: 2rem' class='mx-2 rounded align-middle'>" + paymentName + "</label>" +
                        "</div>")
                    billingMethod = paymentCode
                    continue
                }
                $('#billingMethodsContent').append(
                    "<div class='form-check form-check-inline method-checks rounded-1 p-1 cursor-pointer' id='method-"+ paymentCode +"'>" +
                    "<input class='form-check-input billing-method-check mx-1 my-2' type='radio' name='shipping_method_code' id='shippingMethodCode" + paymentCode + "' value=" + paymentCode + ">" +
                    "<label class='form-check-label align-middle cursor-pointer' for='shippingMethodCode" + paymentCode + "'><img src='" + methods[i].icon + "' alt='" + paymentName + "' style='height: 2rem' class='mx-2 rounded align-middle'>" + paymentName + "</label>" +
                    "</div>")
            }
            $('.billing-method-check').on('change', function () {
                $('.method-checks').css('border','0')
                if ($(this).attr('checked',true)){
                    $('#method-'+$(this).val()+'').css('border','1px solid #409eff')
                }

                $('#quickBuy').attr('data-billing-method', $(this).val())
                billingMethod= $(this).val()
            })
        })

        $('#quickBuy').on('click',function () {
            if (billingMethod === '') {
                layer.msg('{{ __('panel/plugin.payment_method_confirm') }}', {icon: 2})
                return
            }
            axios.post('/{{ panel_name() }}/marketplaces/quick_checkout', {
                'sku_id': $(this).data('sku-id'),
                'product_id': $(this).data('product-id'),
                'billing_method_code': billingMethod,
            }, { hload: true }).then(function (res) {
                if (res.status === 422){
                    layer.msg( res.message ,{ icon:2 })
                    return
                }
                if (res.error){
                    layer.msg( res.error, { icon:2 })
                    return
                }
                if (!res.success){
                    layer.msg( res.message || 'Order failed', { icon:2 })
                    return
                }

                var paymentData = res.data && res.data.payment_data ? res.data.payment_data : null;

                if (paymentData && paymentData.billing_params) {
                    var code = (paymentData.billing_method_code || '').toLowerCase();
                    if (code === 'alipay' && paymentData.billing_params.pay_url) {
                        window.open(paymentData.billing_params.pay_url, '_blank');
                        layer.msg(res.message || '{{ __("panel/plugin.auth_order_created") }}', {icon: 1});
                    } else {
                        showPaymentQR(paymentData);
                    }
                } else {
                    layer.msg(res.message || '{{ __("panel/plugin.auth_order_created") }}', {icon: 1});
                }
            }).catch(function (err) {
                layer.msg(err.message || 'Error', {icon: 2});
            })
        })

        function showPaymentQR(paymentData) {
            var params = paymentData.billing_params || {};
            var qrUrl = params.qr_code || params.qr_url || params.pay_url || params.payUrl || '';

            if (!qrUrl) {
                layer.msg('{{ __("panel/plugin.auth_order_created") }}', {icon: 1});
                return;
            }

            var orderNumber = paymentData.order_number || '';
            var statusUrl = '/{{ panel_name() }}/marketplaces/order_status/' + orderNumber;

            var html = '<div style="text-align:center;padding:20px;" id="paymentQRBox">';
            html += '<p style="margin-bottom:16px;font-size:1rem;">' + (paymentData.billing_method_name || '') + '</p>';
            html += '<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(qrUrl) + '" style="width:200px;height:200px;border:1px solid #eee;border-radius:8px;" />';
            html += '<p style="margin-top:16px;color:#999;font-size:0.85rem;">{{ __("panel/plugin.auth_scan_to_pay") }}</p>';
            html += '<p style="color:#999;font-size:0.8rem;">{{ __("panel/plugin.auth_order_number") }}: ' + orderNumber + '</p>';
            html += '<p id="paymentStatusTip" style="margin-top:8px;color:#409eff;font-size:0.85rem;display:none;"></p>';
            html += '</div>';

            var layerIndex = layer.open({
                type: 1,
                title: '{{ __("panel/plugin.auth_pay_now") }}',
                area: ['360px', '400px'],
                content: html,
                cancel: function () {
                    clearInterval(pollTimer);
                }
            });

            // Poll order status every 3 seconds, stop after 5 minutes
            var pollCount = 0;
            var maxPolls = 100;
            var pollTimer = setInterval(function () {
                pollCount++;
                if (pollCount > maxPolls) {
                    clearInterval(pollTimer);
                    return;
                }
                axios.get(statusUrl, { hload: true }).then(function (res) {
                    if (res.success && res.data && res.data.paid) {
                        clearInterval(pollTimer);
                        $('#paymentStatusTip').text('{{ __("panel/plugin.auth_pay_success") }}').show();
                        setTimeout(function () {
                            layer.close(layerIndex);
                            location.reload();
                        }, 1500);
                    }
                }).catch(function () {});
            }, 3000);
        }
    })
    </script>
@endpush
