<script src="https://js.stripe.com/v3/"></script>
<div class="mt-4">
  <div id="embedded-checkout-loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2 text-muted">{{ __('Stripe::common.loading_payment') }}</p>
  </div>
  <div id="embedded-checkout-container" style="display:none;"></div>
</div>
<script>
  (function () {
    const stripe = Stripe("{{ plugin_setting('stripe.publishable_key') }}");
    const orderNumber = @json($order->number ?? '');

    axios.post("{{ front_route('stripe_embedded_session') }}", {
      order_number: orderNumber,
    }).then(function (res) {
      const clientSecret = res.data.data.client_secret;
      if (!clientSecret) {
        throw new Error('Missing client_secret');
      }

      document.getElementById('embedded-checkout-loading').style.display = 'none';
      document.getElementById('embedded-checkout-container').style.display = 'block';

      stripe.initEmbeddedCheckout({
        clientSecret: clientSecret,
      }).then(function (checkout) {
        checkout.mount('#embedded-checkout-container');
      }).catch(function (err) {
        console.error('Embedded checkout mount error:', err);
        document.getElementById('embedded-checkout-container').innerHTML =
          '<div class="alert alert-danger">' + err.message + '</div>';
      });
    }).catch(function (err) {
      console.error('Failed to create embedded session:', err);
      const msg = err.response?.data?.message || err.message || 'Payment initialization failed';
      document.getElementById('embedded-checkout-loading').innerHTML =
        '<div class="alert alert-danger">' + msg + '</div>';
    });
  })();
</script>
