<style>
.stripe-checkout-wrap .stripe-field-wrap { margin-bottom: 12px; }
.stripe-checkout-wrap .stripe-field-wrap label {
  display: block; font-size: 13px; font-weight: 500; color: #333; margin-bottom: 4px;
}
.stripe-checkout-wrap .stripe-card-element {
  height: 44px; padding: 10px 12px;
  border: 1px solid #d9d9d9; border-radius: 8px; background: #fff;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.stripe-checkout-wrap .stripe-card-element.StripeElement--focus {
  border-color: #000; box-shadow: 0 0 0 1px #000;
}
.stripe-checkout-wrap .stripe-card-element.StripeElement--invalid {
  border-color: #dc3545; box-shadow: 0 0 0 1px #dc3545;
}
.stripe-checkout-wrap .stripe-input {
  height: 44px; border-radius: 8px; border: 1px solid #d9d9d9;
  padding: 0 12px; font-size: 15px; transition: border-color 0.2s, box-shadow 0.2s;
}
.stripe-checkout-wrap .stripe-input:focus {
  border-color: #000; box-shadow: 0 0 0 1px #000; outline: none;
}
.stripe-checkout-wrap .stripe-divider {
  display: flex; align-items: center; margin: 16px 0;
}
.stripe-checkout-wrap .stripe-divider::before,
.stripe-checkout-wrap .stripe-divider::after {
  content: ''; flex: 1; border-top: 1px solid #e5e5e5;
}
.stripe-checkout-wrap .stripe-divider span {
  padding: 0 12px; font-size: 13px; color: #999;
}
.stripe-checkout-wrap .stripe-errors {
  font-size: 13px; color: #dc3545; margin-top: 8px; display: none;
}
</style>

<div class="checkout-payment-form" data-code="Stripe" style="display:none;">
  <div class="stripe-checkout-wrap mt-1 mb-3">

    @php
      $currencyCode = system_setting('currency', 'USD');
      $cartTotal = \InnoShop\Common\Services\CheckoutService::getInstance()->getCheckoutResult()['amount'] ?? 0;
      $stripeAmount = in_array(strtoupper($currencyCode), ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'])
        ? floor($cartTotal)
        : round($cartTotal * 100);
    @endphp

    <div id="stripe-express-checkout" style="display:none;">
      <div id="stripe-payment-request-button"></div>
      <div class="stripe-divider"><span>{{ __('Stripe::common.or_pay_with_card') }}</span></div>
    </div>

    <div id="stripe-card-form">
      <div class="stripe-field-wrap">
        <label for="stripe-checkout-cardholder">{{ __('Stripe::common.cardholder_name') }}</label>
        <input type="text" id="stripe-checkout-cardholder"
               class="form-control stripe-input" placeholder="Jane Doe">
      </div>
      <div class="stripe-field-wrap">
        <label>{{ __('Stripe::common.card_number') }}</label>
        <div id="stripe-checkout-card-number" class="stripe-card-element"></div>
      </div>
      <div class="row g-2">
        <div class="col-6 stripe-field-wrap">
          <label>{{ __('Stripe::common.expiration_date') }}</label>
          <div id="stripe-checkout-card-expiry" class="stripe-card-element"></div>
        </div>
        <div class="col-6 stripe-field-wrap">
          <label>CVV</label>
          <div id="stripe-checkout-card-cvc" class="stripe-card-element"></div>
        </div>
      </div>
      <div id="stripe-checkout-errors" class="stripe-errors"></div>
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
        color: "#333",
        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif',
        fontSmoothing: "antialiased",
        fontSize: "15px",
        "::placeholder": { color: "#aaa" }
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
          height: '44px',
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
        orderNumber = res.data.number;
        return axios.post(intentUrl, { order_number: orderNumber });
      }).then(function(res) {
        if (!res.data || !res.data.success) throw new Error('Payment setup failed');
        return stripe.confirmCardPayment(res.data.client_secret, {
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

    // Card payment handler
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
        return stripe.confirmCardPayment(res.data.client_secret, {
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
