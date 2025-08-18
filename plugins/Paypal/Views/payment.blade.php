<!-- Set up a container element for the button -->
<div id="paypal-button-container" class="mt-4"></div>

<!-- Include the PayPal JavaScript SDK -->
@php($currency = strtoupper(plugin_setting('paypal','currency')))
@if($payment_setting['sandbox_mode'])
    <script src="https://www.paypal.com/sdk/js?client-id={{ plugin_setting('paypal.sandbox_client_id') }}&currency={{ $currency }}"></script>
@else
    <script src="https://www.paypal.com/sdk/js?client-id={{ plugin_setting('paypal.live_client_id') }}&currency={{ $currency }}"></script>
@endif

<script>
    // Render the PayPal button into #paypal-button-container
    paypal.Buttons({
        // Call your server to set up the transaction
        createOrder: function (data, actions) {
            const token = $('meta[name="csrf-token"]').attr('content')
            return fetch('{{ front_route('paypal.create') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': token
                },
                body: JSON.stringify({
                    orderNumber: "{{$order->number}}",
                })
            }).then(function (res) {
                return res.json();
            }).then(function (orderData) {
                if (orderData.error) {
                    layer.alert(orderData.error.details[0].description, {
                        title: '{{ __('front/common.text_hint') }}',
                        closeBtn: 0,
                        area: ['400px', 'auto'],
                        btn: ['{{ __('front/common.confirm') }}']
                    }, function(index) {
                      window.location.reload();
                      layer.close(index);
                    });
                }
                return orderData.id;
            });
        },

        // Call your server to finalize the transaction
        onApprove: function (data, actions) {
            const token = $('meta[name="csrf-token"]').attr('content')
            return fetch('{{ front_route('paypal.capture') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': token
                },
                body: JSON.stringify({
                    orderNumber: "{{$order->number}}",
                    paypalOrderId: data.orderID,
                    payment_gateway_id: $("#payapalId").val(),
                })
            }).then(function (res) {
                // console.log(res.json());
                return res.json();
            }).then(function (orderData) {
                // Successful capture! For demo purposes:
                console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
                let captureStatus = orderData.status
                if (captureStatus === 'COMPLETED') {
                    @if (current_customer())
                        window.location.href = "{{ account_route('orders.number_show', $order->number) }}"
                    @else
                        window.location.href = "{{ front_route('payment.success', ['order_number' => $order->number]) }}"
                    @endif
                }
            });
        }
    }).render('#paypal-button-container');
</script>
