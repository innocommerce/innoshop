<div class="checkout-payment-form" data-code="Stripe" style="display:none;">
  <div class="stripe-inline-form mt-2 mb-3">
    <div class="mb-3">
      <label class="form-label">{{ __('Stripe::common.title_info') }}</label>
      <div class="mb-2">
        <input type="text" id="stripe-checkout-cardholder"
               class="form-control" placeholder="Cardholder Name" style="height: 40px;">
      </div>
      <div class="mb-2">
        <div id="stripe-checkout-card-number" class="form-control px-2" style="height: 40px; padding-top: 8px; border-color: #dee2e6;"></div>
      </div>
      <div class="row">
        <div class="col-6 mb-2">
          <div id="stripe-checkout-card-expiry" class="form-control px-2" style="height: 40px; padding-top: 8px; border-color: #dee2e6;"></div>
        </div>
        <div class="col-6 mb-2">
          <div id="stripe-checkout-card-cvc" class="form-control px-2" style="height: 40px; padding-top: 8px; border-color: #dee2e6;"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@push('footer')
  <script src="https://js.stripe.com/v3/"></script>
  <script>
  (function() {
    var stripe = Stripe("{{ plugin_setting('stripe.publishable_key') }}");
    var elements = stripe.elements({locale: "en"});

    var style = {
      base: {
        color: "#32325d",
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        fontSmoothing: "antialiased",
        fontSize: "16px",
        "::placeholder": { color: "#aab7c4" }
      },
      invalid: { color: "#fa755a", iconColor: "#fa755a" }
    };

    var cardNumber = elements.create("cardNumber", {style: style, showIcon: true});
    cardNumber.mount("#stripe-checkout-card-number");

    var cardExpiry = elements.create("cardExpiry", {style: style});
    cardExpiry.mount("#stripe-checkout-card-expiry");

    var cardCvc = elements.create("cardCvc", {style: style, placeholder: 'CVV'});
    cardCvc.mount("#stripe-checkout-card-cvc");

    window.innoPaymentHandlers = window.innoPaymentHandlers || {};
    window.innoPaymentHandlers['Stripe'] = function(orderNumber) {
      var cardholderName = document.getElementById('stripe-checkout-cardholder').value;
      if (!cardholderName) {
        return Promise.reject(new Error('Please fill out a cardholder name.'));
      }

      return axios.post("{{ front_route('stripe_payment_intent') }}", {
        order_number: orderNumber,
      }).then(function(res) {
        return stripe.confirmCardPayment(res.data.client_secret, {
          payment_method: {
            card: cardNumber,
            billing_details: { name: cardholderName }
          }
        });
      }).then(function(result) {
        if (result.error) {
          throw new Error(result.error.message);
        }
        if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
          layer.closeAll('loading');
          location.href = "{{ front_route('payment.success') }}?order_number=" + orderNumber;
        }
      });
    };
  })();
  </script>
@endpush
