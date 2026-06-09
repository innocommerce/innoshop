<div class="checkout-payment-form" data-code="Stripe" style="display:none;">
  <div class="stripe-embedded-area mt-2 mb-3">
    <div id="stripe-checkout-embedded-container">
      <div class="d-flex align-items-center gap-2 text-muted py-3">
        <i class="bi bi-shield-lock"></i>
        <span>Supports Apple Pay, Google Pay, Link and credit cards</span>
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

      return axios.post("{{ front_route('stripe_embedded_session') }}", {
        order_number: orderNumber,
      }).then(function(res) {
        var clientSecret = res.data.client_secret;
        if (!clientSecret) {
          throw new Error('Missing client_secret');
        }

        return stripe.initEmbeddedCheckout({ clientSecret: clientSecret });
      }).then(function(checkout) {
        var container = document.getElementById('stripe-checkout-embedded-container');
        container.innerHTML = '';
        checkout.mount('#stripe-checkout-embedded-container');
      });
    };
  })();
  </script>
@endpush
