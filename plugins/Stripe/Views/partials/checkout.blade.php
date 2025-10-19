<script src="https://js.stripe.com/v3/"></script>
<button id="checkout-button" class="btn btn-primary">
  <span id="checkout-button-text">{{ __('front/order.continue_pay') }}</span>
  <span id="checkout-button-spinner" class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true" style="display: none;"></span>
</button>
<script>
  const stripe = Stripe("{{ plugin_setting('stripe.publishable_key') }}");
  const checkoutButton = document.getElementById('checkout-button');
  const buttonText = document.getElementById('checkout-button-text');
  const buttonSpinner = document.getElementById('checkout-button-spinner');

  checkoutButton.addEventListener('click', function () {
    // Disable button and show loading state
    checkoutButton.disabled = true;
    buttonSpinner.style.display = 'inline-block';
    buttonText.textContent = '{{ __('common.loading') ?? 'Processing...' }}';

    axios.post("{{ front_route('stripe_checkout_session') }}", {
      order_number: @json($order->number ?? ''),
    }).then(function (res) {
      return stripe.redirectToCheckout({ sessionId: res.data.session_id });
    }).then(function (result) {
      if (result.error) {
        // Re-enable button on error
        checkoutButton.disabled = false;
        buttonSpinner.style.display = 'none';
        buttonText.textContent = '{{ __('front/order.continue_pay') }}';
        alert(result.error.message);
      }
    }).catch(function (error) {
      // Re-enable button on error
      checkoutButton.disabled = false;
      buttonSpinner.style.display = 'none';
      buttonText.textContent = '{{ __('front/order.continue_pay') }}';
      alert(error.response?.data?.message || error.message || 'An error occurred');
    });
  });
</script>
