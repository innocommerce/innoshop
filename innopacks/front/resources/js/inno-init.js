import { updateQueryStringParameter, removeURLParameters, getQueryString } from './inno-url';
import { addCart, getCarts } from './inno-cart';
import { addWishlist } from './inno-wishlist';
import { formatCurrency } from './inno-currency';
import { msg, alert, validateAndSubmitForm, openLogin, getBase, setAppContentMinHeight } from './inno-ui';

// Re-export all for direct use by themes
export {
  updateQueryStringParameter, removeURLParameters, getQueryString,
  addCart, getCarts,
  addWishlist,
  formatCurrency,
  msg, alert, validateAndSubmitForm, openLogin, getBase, setAppContentMinHeight,
};

// Setup window.inno and axios defaults
export function initInno() {
  window.inno = {
    updateQueryStringParameter, removeURLParameters, getQueryString,
    addCart, getCarts,
    addWishlist,
    formatCurrency,
    msg, alert, validateAndSubmitForm, openLogin, getBase, setAppContentMinHeight,
  };

  const apiToken = $('meta[name="api-token"]').attr('content');
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + apiToken;
  axios.defaults.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');
}

// Bind common global DOM events
export function bindGlobalEvents() {
  getCarts();

  // Alert close animation
  $(document).on('click', '.is-alert .btn-close', function () {
    let top = 40;
    $('.is-alert').each(function () {
      $(this).animate({top}, 100);
      top += $(this).outerHeight() + 10;
    });
  });

  // Global wishlist button
  $('.add-wishlist').on('click', function () {
    const id = $(this).attr('data-id');
    const isWishlist = $(this).attr('data-in-wishlist') * 1;
    addWishlist(id, isWishlist, this);
  });

  // Global add-to-cart button
  $('.btn-add-cart').on('click', function () {
    const skuId = $(this).data('sku-id');
    addCart({skuId}, this);
  });

  setAppContentMinHeight();
  $(window).on('resize', setAppContentMinHeight);
}
