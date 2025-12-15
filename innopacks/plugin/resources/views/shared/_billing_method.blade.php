<div class="my-4" id="billingMethodsContent">
    <h5 class="mb-3">{{ __('panel/plugin.payment_method') }}</h5>

</div>
@push('footer')
    <script>
        $(function () {

            var billingMethod=''

            // Payment method name translations
            var paymentMethodTranslations = {
                'alipay': '{{ __('panel/plugin.payment_alipay') }}',
                'alipay_payment': '{{ __('panel/plugin.payment_alipay') }}',
                'wechatpay': '{{ __('panel/plugin.payment_wechatpay') }}',
                'wechat_pay': '{{ __('panel/plugin.payment_wechat_pay') }}',
                'we_chat_pay': '{{ __('panel/plugin.payment_wechat_pay') }}',
                'wechat': '{{ __('panel/plugin.payment_wechatpay') }}',
            };

            // Function to get translated payment method name
            function getPaymentMethodName(code, originalName) {
                var normalizedCode = code.toLowerCase().replace(/[^a-z0-9]/g, '_');
                // Try exact match first
                if (paymentMethodTranslations[normalizedCode]) {
                    return paymentMethodTranslations[normalizedCode];
                }
                // Try matching with 'payment_' prefix
                var translationKey = 'payment_' + normalizedCode;
                if (paymentMethodTranslations[translationKey]) {
                    return paymentMethodTranslations[translationKey];
                }
                // Return original name if no translation found
                return originalName;
            }

            axios.get("{{config('innoshop.api_url').'/api/checkout/billing_methods'}}").then(function (res) {
                var methods = res.data
                for (i = 0; i < res.data.length; i++) {
                    var paymentCode = res.data[i].code;
                    var paymentName = getPaymentMethodName(paymentCode, res.data[i].name);
                    
                    if (i === 0){
                        $('#billingMethodsContent').append(
                            "<div class='form-check form-check-inline method-checks rounded-1 p-1 cursor-pointer' id='method-"+ paymentCode +"' style='border: 1px solid #409eff'>" +
                            "<input class='form-check-input billing-method-check mx-1 my-2' checked type='radio' name='shipping_method_code' id='shippingMethodCode" + paymentCode + "' value=" + paymentCode + ">" +
                            "<label class='form-check-label align-middle cursor-pointer' for='shippingMethodCode" + paymentCode + "'><img src='" + res.data[i].icon + "' alt='" + paymentName + "' style='height: 2rem' class='mx-2 rounded align-middle'>" + paymentName + "</label>" +
                            "</div>")
                        billingMethod = paymentCode
                        continue
                    }
                    $('#billingMethodsContent').append(
                        "<div class='form-check form-check-inline method-checks rounded-1 p-1 cursor-pointer' id='method-"+ paymentCode +"'>" +
                        "<input class='form-check-input billing-method-check mx-1 my-2' type='radio' name='shipping_method_code' id='shippingMethodCode" + paymentCode + "' value=" + paymentCode + ">" +
                        "<label class='form-check-label align-middle cursor-pointer' for='shippingMethodCode" + paymentCode + "'><img src='" + res.data[i].icon + "' alt='" + paymentName + "' style='height: 2rem' class='mx-2 rounded align-middle'>" + paymentName + "</label>" +
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
                }).then(function (res) {
                    if (res.success){
                        layer.msg( res.message, { icon:1 })
                    }
                    if (res.status === 422){
                        layer.msg( res.message ,{ icon:2 })
                        return
                    }
                    if (res.error){
                        layer.msg( res.error, { icon:2 })
                        return
                    }
                    window.open('{{config('innoshop.api_url')}}' + '/orders/' + res.data.number + '/pay', '_blank')
                })
            })
        })
    </script>
@endpush
