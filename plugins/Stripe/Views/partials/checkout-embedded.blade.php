<div class="checkout-payment-form" data-code="Stripe" style="display:none;">
  <div class="stripe-embedded-area mt-2 mb-3">
    <div id="stripe-checkout-embedded-container">
      <div class="text-center py-4">
        <div class="d-flex justify-content-center gap-2 mb-2">
          <i class="bi bi-credit-card-2-front fs-5 text-muted"></i>
          <i class="bi bi-wallet2 fs-5 text-muted"></i>
          <i class="bi bi-shield-check fs-5 text-muted"></i>
        </div>
        <p class="text-muted small mb-0">{{ __('Stripe::common.embedded_hint') }}</p>
        <p class="text-muted small">{{ __('Stripe::common.embedded_hint_sub') }}</p>
      </div>
    </div>
  </div>
</div>
@push('footer')
  <script src="https://js.stripe.com/v3/"></script>
  <script>
  (function() {
    window.innoPaymentHandlers = window.innoPaymentHandlers || {};
    window.innoPaymentHandlers['Stripe'] = function(orderNumber) {
      var stripe = Stripe("{{ plugin_setting('stripe.publishable_key') }}");
      var container = document.getElementById('stripe-checkout-embedded-container');
      var successUrl = "{{ front_route('payment.success') }}";

      container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="text-muted small mt-2">{{ __("Stripe::common.loading_payment") }}</p></div>';

      return axios.post("{{ front_route('stripe_embedded_session') }}", {
        order_number: orderNumber,
      }).then(function(res) {
        var clientSecret = res.data.data.client_secret;
        if (!clientSecret) {
          throw new Error('Missing client_secret');
        }
        return stripe.initEmbeddedCheckout({ clientSecret: clientSecret });
      }).then(function(checkout) {
        container.innerHTML = '';
        checkout.mount('#stripe-checkout-embedded-container');
      }).then(function() {
        var checkInterval = setInterval(function() {
          axios.get("{{ front_route('stripe_embedded_return') }}?order_number=" + orderNumber).then(function(res) {
            if (res.data && res.data.success) {
              clearInterval(checkInterval);
              location.href = successUrl + '?order_number=' + orderNumber;
            }
          }).catch(function() {});
        }, 3000);
      });
    };
  })();
  </script>
@endpush
