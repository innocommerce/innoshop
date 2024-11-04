export default {
  // 通过正则表达式匹配url中的参数，如果匹配到了，就替换掉原来的参数，如果没有匹配到，就添加参数
  updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
      return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
      return uri + separator + key + "=" + value;
    }
  },

  // 通过正则表达式匹配url中的参数，如果匹配到了，就删除掉原来的参数
  removeURLParameters(url, ...parameters) {
    const parsed = new URL(url);
    parameters.forEach(e => parsed.searchParams.delete(e))
    return parsed.toString()
  },

  // 获取url中的参数, url 传入的url，name 要获取的参数名
  getQueryString(name, url = window.location.href) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = url.split('?')[1] ? url.split('?')[1].match(reg) : null;
    if (r != null) return unescape(r[2]);
    return null;
  },

  addCart({skuId, quantity = 1, isBuyNow = false}, event, callback) {
    const base = document.querySelector('base').href;
    const $btn = $(event);
    $btn.addClass('disabled').prepend('<span class="spinner-border spinner-border-sm me-1"></span>');
    $(document).find('.tooltip').remove();

    axios.post(urls.cart_add, {skuId, quantity, buy_now: isBuyNow}).then((res) => {
      if (!isBuyNow) {
        layer.msg(res.message)
      }

      $('.header-cart-icon .icon-quantity').text(res.data.total)

      if (callback) {
        callback(res)
      }
    }).finally(() => {
      $btn.removeClass('disabled').find('.spinner-border').remove();
    })
  },

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
      axios.delete(`${urls.favorites}/${isWishlist}`).then((res) => {
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
      axios.post(`${urls.favorites}`, {product_id: id}).then((res) => {
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

  getCarts() {
    axios.get(urls.cart_mini).then((res) => {
      $('.header-cart-icon .icon-quantity').text(res.data.total)
    })
  },

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

  msg(params = {}, callback = null) {
    let msg = typeof params === 'string' ? params : params.msg || '';
    let time = params.time || 2000;
    layer.msg(msg, {time}, callback);
  },

  alert(params = {}, callback = null) {
    let top = 40;
    let id = Math.random().toString(36).substring(7);
    let msg = typeof params === 'string' ? params : params.msg || '';
    let type = params.type || 'success';
    let icon = 'bi-check-circle-fill';
    if (type != 'success') {
      icon = 'bi-exclamation-circle-fill';
    }

    $('.is-alert').each(function () {
      top += $(this).outerHeight() + 10;
    });

    let html = '';
    html += ` <div id="alert-${id}" class="alert alert-${type} alert-dismissible is-alert position-fixed me-2 z-3">`;
    html += `   <i class="bi ${icon}"></i>`;
    html += '   ' + msg;
    html += '   <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    html += ' </div>';

    $('body').append(html);

    $('#alert-' + id).css({right: '-100%',top}).animate({right: '0'}, 200);

    window.setTimeout(function () {
      $('#alert-' + id).animate({right: '-100%'}, 200, function () {
        $(this).remove();
        top = 40;
        $('.is-alert').each(function () {
          $(this).animate({top}, 100);
          top += $(this).outerHeight() + 10;
        });

        if (callback !== null) {
          callback();
        }
      });
    }, 5000);
  },

  // bootstrap 表单验证, js 验证
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

  openLogin() {
    var area = window.innerWidth < 768 ? '94%' : '500px';

    layer.open({
      type: 2,
      title: '',
      area: area,
      content: `${urls.login}?iframe=true`,
      success: function(layero, index) {
        var iframe = $(layero).find('iframe');
        iframe.css('height', iframe[0].contentDocument.body.offsetHeight + 20);
        $(layero).css('top', (window.innerHeight - iframe[0].offsetHeight) / 2);
      }
    });
  },

  getBase() {
    let url = document.querySelector('base').href;
    if (url.endsWith('/')) {
      url = url.slice(0, -1);
    }
    return url;
  },

  setAppContentMinHeight(){
  let appHeaderHeight=$('#appHeader').outerHeight();
  let appFooterHeight=$('#appFooter').outerHeight(true);
  let windowHeight=$(window).outerHeight();
  $('#appContent').css('min-height', (windowHeight-appHeaderHeight-appFooterHeight-48)+'px');
}
};
