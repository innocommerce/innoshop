<script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
<script src="https://checkout.airwallex.com/assets/elements.bundle.min.js"></script>

<div class="mt-1" id="airwallex-form" v-cloak>
  <hr class="mb-4">
  <h5 class="checkout-title">{{ __('Airwallex::common.title_pay_info') }}</h5>
  <div class="">
    <div id="dropIn"></div>
  </div>
</div>

<script>
  let cardNumberElement = null, cardExpiryElement = null, cardCvcElement = null, airwallex = null, elements = null;
  const orderNumber = @json($order->number ?? '');

  const airwallexForm = new Vue({
    el: '#airwallex-form',
    data() {
      return {
        enableTest: {{ (int)plugin_setting('airwallex.test_mode') }}
      };
    },
    computed: {
      env() {
        return this.enableTest ? 'demo' : 'prod';
      }
    },
    beforeMount() {
      this.payment();
    },
    methods: {
      payment() {
        axios.post(`{{ front_route('airwallex.payment') }}`, { order_number: orderNumber }).then((res) => {
          Airwallex.init({
            env: this.env,
            origin: window.location.origin, // Set up your event target to receive the browser events message
          });

          let data = res.message[1];
          let request = {
            env: this.env,
            mode: 'payment',
            intent_id: data.id,
            client_secret: data.client_secret,
            currency: data.currency,
            withBilling: true,
            requiredBillingContactFields: ['name', 'email', 'address'],
          };

          const dropIn = Airwallex.createElement('dropIn', request);
          dropIn.mount('dropIn');
        });
      }
    }
  });
</script>

