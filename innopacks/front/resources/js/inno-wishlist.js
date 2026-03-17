import { openLogin } from './inno-ui';

// Add/remove wishlist
export function addWishlist(id, isWishlist, event, callback) {
  if (!config.isLogin) {
    openLogin()
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
}
