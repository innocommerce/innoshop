// Add product to cart
export function addCart({skuId, quantity = 1, isBuyNow = false, options = {}}, event, callback) {
  const $btn = $(event);
  $btn.addClass('disabled').prepend('<span class="spinner-border spinner-border-sm me-1"></span>');
  $(document).find('.tooltip').remove();

  const requestData = {
    sku_id: skuId,
    quantity,
    buy_now: isBuyNow
  };

  // Add options to request if available
  if (options && Object.keys(options).length > 0) {
    requestData.options = options;
  }

  axios.post(urls.front_cart_add, requestData).then((res) => {
    if (!isBuyNow) {
      if (typeof layer !== 'undefined') {
        layer.msg(res.message);
      } else {
        alert(res.message);
      }
    }

    // Update cart quantity in header (both desktop & mobile)
    const qty = res.data && res.data.total_format ? res.data.total_format : (res.data && res.data.total) || '';
    $('.header-cart-icon .icon-quantity').text(qty);

    if (callback) {
      callback(res);
    }
  }).catch((err) => {
    const msg = err.response && err.response.message
      ? err.response.message
      : (err.message || 'Add to cart failed');
    if (typeof layer !== 'undefined') {
      layer.msg(msg);
    } else {
      alert(msg);
    }
  }).finally(() => {
    $btn.removeClass('disabled').find('.spinner-border').remove();
  });
}

// Get cart information
export function getCarts() {
  axios.get(urls.front_cart_mini).then((res) => {
    const data = res.data || res;
    const qty = data.total_format ?? data.total ?? 0;
    $('.header-cart-icon .icon-quantity').text(qty);
  }).catch(() => {
    $('.header-cart-icon .icon-quantity').text('0');
  });
}
