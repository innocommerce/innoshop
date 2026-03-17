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
      layer.msg(res.message)
    }

    $('.header-cart-icon .icon-quantity').text(res.data.total_format)

    if (callback) {
      callback(res)
    }
  }).finally(() => {
    $btn.removeClass('disabled').find('.spinner-border').remove();
  })
}

// Get cart information
export function getCarts() {
  axios.get(urls.front_cart_mini).then((res) => {
    $('.header-cart-icon .icon-quantity').text(res.data.total_format)
  })
}
