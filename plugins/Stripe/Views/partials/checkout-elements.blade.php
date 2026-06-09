<div class="checkout-payment-form" data-code="Stripe" style="display:none;">
  <div class="stripe-checkout-wrap mt-2 mb-3">

    @php
      $currencyCode = system_setting('currency', 'USD');
      $cartTotal = session('checkout_total', 0);
      if (!$cartTotal) {
        $cartTotal = \InnoShop\Common\Services\CheckoutService::getInstance()->getCheckoutResult()['amount'] ?? 0;
      }
      $stripeAmount = in_array(strtoupper($currencyCode), ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'])
        ? floor($cartTotal)
        : round($cartTotal * 100);
    @endphp

    <div id="stripe-express-checkout" style="display:none;">
      <div id="stripe-payment-request-button" class="mb-3"></div>
      <div class="stripe-divider d-flex align-items-center my-3">
        <span class="stripe-divider-line flex-grow-1 border-top"></span>
        <span class="px-3 text-muted small">{{ __('Stripe::common.or_pay_with_card') }}</span>
        <span class="stripe-divider-line flex-grow-1 border-top"></span>
      </div>
    </div>

    <div id="stripe-card-form">
      <div class="mb-2">
        <label class="form-label small text-muted mb-1">{{ __('Stripe::common.cardholder_name') }}</label>
        <input type="text" id="stripe-checkout-cardholder"
               class="form-control" placeholder="Jane Doe" style="height: 42px;">
      </div>
      <div class="mb-2">
        <label class="form-label small text-muted mb-1">{{ __('Stripe::common.card_number') }}</label>
        <div id="stripe-checkout-card-number" style="height: 42px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 6px;"></div>
      </div>
      <div class="row g-2 mb-1">
        <div class="col-sm-6">
          <label class="form-label small text-muted mb-1">{{ __('Stripe::common.expiration_date') }}</label>
          <div id="stripe-checkout-card-expiry" style="height: 42px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 6px;"></div>
        </div>
        <div class="col-sm-6">
          <label class="form-label small text-muted mb-1">CVV</label>
          <div id="stripe-checkout-card-cvc" style="height: 42px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 6px;"></div>
        </div>
      </div>
      <div id="stripe-checkout-errors" class="text-danger small mt-2" style="display:none;"></div>
    </div>

  </div>
</div>
@push('footer')
  <script src="https://js.stripe.com/v3/"></script>
  <script>
  (function() {
    var stripe = Stripe("{{ plugin_setting('stripe.publishable_key') }}");
    var elements = stripe.elements({ locale: "{{ current_locale() === 'zh-cn' ? 'zh' : 'en' }}" });

    var elementStyle = {
      base: {
        color: "#32325d",
        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        fontSmoothing: "antialiased",
        fontSize: "15px",
        "::placeholder": { color: "#aab7c4" }
      },
      invalid: { color: "#dc3545", iconColor: "#dc3545" }
    };

    var cardNumber = elements.create("cardNumber", { style: elementStyle, showIcon: true });
    cardNumber.mount("#stripe-checkout-card-number");

    var cardExpiry = elements.create("cardExpiry", { style: elementStyle });
    cardExpiry.mount("#stripe-checkout-card-expiry");

    var cardCvc = elements.create("cardCvc", { style: elementStyle, placeholder: "CVV" });
    cardCvc.mount("#stripe-checkout-card-cvc");

    cardNumber.on('change', function(event) {
      var errorEl = document.getElementById('stripe-checkout-errors');
      if (event.error) {
        errorEl.textContent = event.error.message;
        errorEl.style.display = 'block';
      } else {
        errorEl.style.display = 'none';
      }
    });

    // Express Checkout (Apple Pay / Google Pay / Link)
    var paymentRequest = stripe.paymentRequest({
      country: "{{ strtoupper(system_setting('country_code', 'US')) }}",
      currency: "{{ strtolower($currencyCode) }}",
      total: {
        label: "{{ system_setting_locale('meta_title', 'Order Payment') }}",
        amount: {{ $stripeAmount }},
      },
      requestPayerName: true,
      requestPayerEmail: true,
    });

    var prButton = elements.create('paymentRequestButton', {
      paymentRequest: paymentRequest,
      style: {
        paymentRequestButton: {
          type: 'buy',
          theme: 'dark',
          height: '42px',
        },
      },
    });

    paymentRequest.canMakePayment().then(function(result) {
      if (result) {
        document.getElementById('stripe-express-checkout').style.display = 'block';
        prButton.mount('#stripe-payment-request-button');
      }
    });

    paymentRequest.on('paymentmethod', function(ev) {
      var checkoutData = {};
      try {
        checkoutData = JSON.parse(JSON.stringify(checkoutApp.current));
      } catch(e) {
        ev.complete('fail');
        return;
      }

      var confirmUrl = "{{ front_route('checkout.confirm') }}";
      var intentUrl = "{{ front_route('stripe_payment_intent') }}";
      var successUrl = "{{ front_route('payment.success') }}";
      var orderNumber = '';

      axios.post(confirmUrl, checkoutData).then(function(res) {
        if (!res.data || !res.data.success) throw new Error(res.data && res.data.message ? res.data.message : 'Order creation failed');
        orderNumber = res.data.data.number;
        return axios.post(intentUrl, { order_number: orderNumber });
      }).then(function(res) {
        if (!res.data || !res.data.success) throw new Error('Payment setup failed');
        return stripe.confirmCardPayment(res.data.data.client_secret, {
          payment_method: ev.paymentMethod.id
        });
      }).then(function(result) {
        if (result.error) {
          ev.complete('fail');
          document.getElementById('stripe-checkout-errors').textContent = result.error.message;
          document.getElementById('stripe-checkout-errors').style.display = 'block';
        } else {
          ev.complete('success');
          location.href = successUrl + '?order_number=' + orderNumber;
        }
      }).catch(function(err) {
        ev.complete('fail');
        document.getElementById('stripe-checkout-errors').textContent = err.message || 'Payment failed';
        document.getElementById('stripe-checkout-errors').style.display = 'block';
      });
    });

    // Card payment handler (called by checkout page submitCheckout)
    window.innoPaymentHandlers = window.innoPaymentHandlers || {};
    window.innoPaymentHandlers['Stripe'] = function(orderNumber) {
      var cardholderName = document.getElementById('stripe-checkout-cardholder').value.trim();
      if (!cardholderName) {
        return Promise.reject(new Error('{{ __("Stripe::common.error_cardholder") }}'));
      }

      var errorEl = document.getElementById('stripe-checkout-errors');
      var successUrl = "{{ front_route('payment.success') }}";

      return axios.post("{{ front_route('stripe_payment_intent') }}", {
        order_number: orderNumber,
      }).then(function(res) {
        if (!res.data || !res.data.success) throw new Error('Payment setup failed');
        return stripe.confirmCardPayment(res.data.data.client_secret, {
          payment_method: {
            card: cardNumber,
            billing_details: { name: cardholderName }
          }
        });
      }).then(function(result) {
        if (result.error) {
          errorEl.textContent = result.error.message;
          errorEl.style.display = 'block';
          throw new Error(result.error.message);
        }
        if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
          location.href = successUrl + '?order_number=' + orderNumber;
        }
      });
    };
  })();
  </script>
@endpush
