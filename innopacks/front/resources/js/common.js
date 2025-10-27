export default {
  // Update URL parameter using regex, replace if exists, add if not
  updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
      return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
      return uri + separator + key + "=" + value;
    }
  },

  // Remove URL parameters using regex
  removeURLParameters(url, ...parameters) {
    const parsed = new URL(url);
    parameters.forEach(e => parsed.searchParams.delete(e))
    return parsed.toString()
  },

  // Get URL parameter by name
  getQueryString(name, url = window.location.href) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = url.split('?')[1] ? url.split('?')[1].match(reg) : null;
    if (r != null) return unescape(r[2]);
    return null;
  },

  // Add product to cart
  addCart({skuId, quantity = 1, isBuyNow = false, options = {}}, event, callback) {
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
  },

  // Add/remove wishlist
  addWishlist(id, isWishlist, event, callback) {
    if (!config.isLogin) {
      this.openLogin()
      return;
    }
    const $btn = $(event);
    const btnHtml = $btn.html();
    const loadHtml = '<span class="spinner-border spinner-border-sm"></span>';
    $(document).find('.tooltip').remove();

    if (isWishlist) {
      $btn.html(loadHtml).prop('disabled', true);
      axios.post(`${urls.front_favorite_cancel}`, {product_id: id}).then((res) => {
        layer.msg(res.message)
        $btn.attr('data-in-wishlist', 0);
        if (callback) {
          callback(res)
        }
      }).finally((e) => {
        $btn.html(btnHtml).prop('disabled', false).find('i.bi').prop('class', 'bi bi-heart')
      })
    } else {
      $btn.html(loadHtml).prop('disabled', true);
      axios.post(`${urls.front_favorites}`, {product_id: id}).then((res) => {
        layer.msg(res.message)
        $btn.attr('data-in-wishlist', 1);
        $btn.html(btnHtml).prop('disabled', false).find('i.bi').prop('class', 'bi bi-heart-fill')
        if (callback) {
          callback(res)
        }
      }).catch((e) => {
        $btn.html(btnHtml).prop('disabled', false)
      })
    }
  },

  // Get cart information
  getCarts() {
    axios.get(urls.front_cart_mini).then((res) => {
      $('.header-cart-icon .icon-quantity').text(res.data.total_format)
    })
  },

  // Convert serialized string to object
  serializedToObj(serializedStr) {
    const obj = {};
    const pairs = serializedStr.split('&');
    pairs.forEach(function(pair) {
      const [key, value] = pair.split('=').map(decodeURIComponent);
      if (obj[key]) {
        if (Array.isArray(obj[key])) {
          obj[key].push(value);
        } else {
          obj[key] = [obj[key], value];
        }
      } else {
        obj[key] = value;
      }
    });
    return obj;
  },

  // Show message notification
  msg(params = {}, callback = null) {
    let msg = typeof params === 'string' ? params : params.msg || '';
    let time = params.time || 2000;
    layer.msg(msg, {time}, callback);
  },

  // Show alert notification
  alert(params = {}, callback = null) {
    let msg = typeof params === 'string' ? params : params.msg || '';
    let type = params.type || 'success';
    let icon = type === 'success' ? 1 : 2;
    
    layer.msg(msg, {
      icon: icon,
      shade: 0.3,
      shadeClose: true,
      time: 5000
    }, callback);
  },

  // Bootstrap form validation
  validateAndSubmitForm(form, callback) {
    $(document).on('click', `${form} .form-submit`, function(event) {
      if ($(form)[0].checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
      }

      $(form).addClass('was-validated');

      if ($(form)[0].checkValidity() === true) {
        callback($(form).serialize());
      }
    })

    $(document).on('keypress', `${form} input`, function(event) {
      if (event.keyCode === 13) {
        $(`${form} .form-submit`).trigger('click');
      }
    })
  },

  // Open login modal
  openLogin() {
    var area = window.innerWidth < 768 ? '94%' : '500px';

    layer.open({
      type: 2,
      title: '',
      area: area,
      content: `${urls.front_login}?iframe=true`,
      success: function(layero, index) {
        var iframe = $(layero).find('iframe');
        iframe.css('height', iframe[0].contentDocument.body.offsetHeight + 20);
        $(layero).css('top', (window.innerHeight - iframe[0].offsetHeight) / 2);
      }
    });
  },

  // Get base URL
  getBase() {
    let url = document.querySelector('base').href;
    if (url.endsWith('/')) {
      url = url.slice(0, -1);
    }
    return url;
  },

  // Currency format function
  // Currency formatting with global config
  formatCurrency(amount, currencyConfig = null) {
    // Use global config if no specific config provided
    const currency = currencyConfig || (config && config.currency) || {
      symbol_left: '$',
      symbol_right: '',
      decimal_place: 2,
      rate: 1
    };
    
    const price = parseFloat(amount) * currency.rate;
    const formattedAmount = price.toFixed(currency.decimal_place);
    
    let result = '';
    if (currency.symbol_left) {
      result += currency.symbol_left;
    }
    result += formattedAmount;
    if (currency.symbol_right) {
      result += ' ' + currency.symbol_right;
    }
    
    return result;
  },

  // Legacy currency format function (kept for backward compatibility)
  currencyFormat(amount, symbol = '$', decimals = 2) {
    const num = parseFloat(amount) || 0;
    return symbol + num.toFixed(decimals);
  },

  // Set app content minimum height
  setAppContentMinHeight(){
    let appHeaderHeight = $('#appHeader').outerHeight();
    let appFooterHeight = $('#appFooter').outerHeight(true);
    let windowHeight = $(window).outerHeight();
    $('#appContent').css('min-height', (windowHeight - appHeaderHeight - appFooterHeight - 48) + 'px');
  }
};
