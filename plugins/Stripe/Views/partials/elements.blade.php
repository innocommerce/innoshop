<script src="{{ plugin_asset('stripe', '/js/vue.min.js') }}"></script>
<script src="https://js.stripe.com/v3/"></script>

<link rel="stylesheet" href="{{ plugin_asset('stripe', '/css/demo.css') }}">
<div class="mt-5" id="stripe-form" v-cloak>
  <hr class="mb-4">
  <div class="checkout-black">
    <h5 class="checkout-title">{{ __('Stripe::common.title_info') }}</h5>
    <div class="">
      <div class="mb-4">
        <div class="payment-icon">
          <img src="{{ asset('images/demo/payment/1.png') }}" class="img-fluid">
          <img src="{{ asset('images/demo/payment/2.png') }}" class="img-fluid">
          <img src="{{ asset('images/demo/payment/3.png') }}" class="img-fluid">
          <img src="{{ asset('images/demo/payment/4.png') }}" class="img-fluid">
          <img src="{{ asset('images/demo/payment/5.png') }}" class="img-fluid">
        </div>
      </div>
      <div class="stripe-card w-max-600">
        <div class="mb-3">
          <div class="mb-2">Cardholder Name</div>
          <div id="card-cardholder-name">
            <input type="text" @input="cardholderNameInput"
                   :class="['form-control', errors.cardholderName ? 'border-danger' : '']"
                   v-model="form.cardholder_Name"
                   placeholder="Cardholder Name" style="height: 40px; border-color: #dee2e6">
          </div>
        </div>
        <div v-if="errors.cardholderName" class="text-danger mt-n2 mb-3">@{{ errors.cardholderName }}</div>

        <div class="mb-3">
          <div class="mb-2">Credit Card Number</div>
          <div id="card-number-element" :class="['px-2 border card-input', errors.cardNumber ? 'border-danger' : '']">
          </div>
        </div>
        <div class="text-danger mt-n2 mb-3" v-if="errors.cardNumber">@{{ errors.cardNumber }}</div>

        <div class="mb-3">
          <div class="mb-2">Expiration Date</div>
          <div :class="['px-2 border card-input', errors.cardExpiry ? 'border-danger' : '']" id="card-expiry-element">
          </div>
        </div>
        <div class="text-danger mt-n2 mb-3" v-if="errors.cardExpiry">@{{ errors.cardExpiry }}</div>
        <div class="mb-3">
          <div class="mb-2">CVV</div>
          <div :class="['px-2 border card-input', errors.cardCvc ? 'border-danger' : '']" id="card-cvc-element"></div>
        </div>
        <div class="text-danger mt-n2 mb-3" v-if="errors.cardCvc">@{{ errors.cardCvc }}</div>
      </div>
    </div>
    <div>
      <button class="btn btn-primary btn-lg" type="button" @click="checkedBtnCheckoutConfirm">{{
        __('Stripe::common.btn_submit') }}</button>
    </div>
  </div>
</div>
<script>
  let cardNumberElement = null, cardExpiryElement = null, cardCvcElement = null, stripe = null, elements = null;
  const orderNumber = @json($order->number ?? '');

  var stripeForm = new Vue({
    el: '#stripe-form',

    data: {
      form: {
        order_number: '',
        cardnum: '',
        year: '',
        month: '',
        cvv: '',
        remember: false,

        // 以上为以前自定义表单的字段
        cardholder_Name: '',
      },

      errors: {
        cardNumber: '',
        cardExpiry: '',
        cardCvc: '',
        cardholderName: ''
      },
    },

    beforeMount() {
      stripe = Stripe("{{ plugin_setting('stripe.publishable_key') }}");
    },

    mounted() {
      this.createAndMountFormElements()
    },

    methods: {
      // stripe生成卡号校验部分
      createAndMountFormElements() {
        // stripe 样式，带边框
        const style = {
          base: {
            color: "#32325d",
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: "antialiased",
            lineHeight: "40px",
            fontSize: "16px",
            "::placeholder": {
              color: "#aab7c4"
            }
          },
          invalid: {
            color: "#fa755a",
            iconColor: "#fa755a"
          }
        }

        elements = stripe.elements({
          locale: "en" // 设置默认显示语种   en 英文 cn 中文 auto 自动获取语种
        })

        // 创建cardNumber并实例化
        cardNumberElement = elements.create("cardNumber", {
          style: style,
          showIcon: true, // 设置卡片icon，默认值为false
          placeholder: this.cardNumberplaceholder
        })

        cardNumberElement.mount("#card-number-element")

        // 创建cardExpiry并实例化
        cardExpiryElement = elements.create("cardExpiry", {style: style})
        cardExpiryElement.mount("#card-expiry-element")

        // 创建cardCvc并实例化
        cardCvcElement = elements.create("cardCvc", {style: style, placeholder: 'CVV'})
        cardCvcElement.mount("#card-cvc-element")
      },

      cardholderNameInput(e) {
        if (e.target.value == '') {
          this.errors.cardholderName = 'Please fill out a cardholder name.'
        } else {
          this.errors.cardholderName = ''
        }
      },

      checkedBtnCheckoutConfirm(e) {
        // 判断 stripeForm.errors 里面的值是否都为空
        if (stripeForm.form.cardholder_Name == '') {
          stripeForm.errors.cardholderName = 'Please fill out a cardholder name.'
          return;
        }

        const submitButton = e.target;
        submitButton.disabled = true;

        // Create loading text
        const originalText = submitButton.textContent;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';

        const options = {
          name: stripeForm.form.cardholder_Name,
        };

        stripe.createToken(cardNumberElement, options).then(function (stripeResult) {
          if (stripeResult.error) {
            // Re-enable button on error
            submitButton.disabled = false;
            submitButton.textContent = originalText;

            if (typeof layer !== 'undefined') {
              layer.msg(stripeResult.error.message, () => {})
            } else {
              alert(stripeResult.error.message);
            }
          } else {
            axios.post(`{{ front_route('stripe_capture') }}`, {
              token: stripeResult.token.id,
              order_number: orderNumber
            }).then(function (res) {
              const responseData = res;
              const isSuccess = responseData && responseData.success === true;

              if (isSuccess) {
                const successUrl = "{{ front_route('payment.success') }}?order_number=" + orderNumber;
                console.log('Redirecting to:', successUrl);
                window.location.href = successUrl;

                return;
              } else {
                submitButton.disabled = false;
                submitButton.textContent = originalText;

                const errorMsg = (responseData && responseData.message) || 'Payment failed';
                console.log('Showing error message:', errorMsg);

                if (typeof layer !== 'undefined') {
                  layer.msg(errorMsg, () => {})
                } else {
                  alert(errorMsg);
                }
              }
            }).catch(function (error) {
              // Re-enable button on error
              submitButton.disabled = false;
              submitButton.textContent = originalText;

              const errorMsg = error.response?.data?.message || error.message || 'An error occurred';
              if (typeof layer !== 'undefined') {
                layer.msg(errorMsg, () => {})
              } else {
                alert(errorMsg);
              }
            })
          }
        })
      },
    }
  })
</script>
