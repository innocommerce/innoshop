<script src="https://js.stripe.com/v3/"></script>
<button id="checkout-button" class="btn btn-primary">{{ __('front/order.continue_pay') }}</button>
<script>
  const stripe = Stripe("{{ plugin_setting('stripe.publishable_key') }}");
  document.getElementById('checkout-button').addEventListener('click', function () {
    axios.post("{{ front_route('stripe_checkout_session') }}", {
      order_number: @json($order->number ?? ''),
    }).then(function (res) {
      return stripe.redirectToCheckout({ sessionId: res.data.session_id });
    }).then(function (result) {
      if (result.error) {
        alert(result.error.message);
      }
    });
  });
</script> 