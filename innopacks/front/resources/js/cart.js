/**
 * Global cart functionality
 */

console.log('Cart.js loaded successfully!');

document.addEventListener('DOMContentLoaded', function () {
  console.log('DOM content loaded in cart.js');
  // Initialize cart offcanvas
  initCartOffcanvas();

  // Test cart mini endpoint
  testCartMiniEndpoint();

  // Add event listener for cart icon click
  document.querySelectorAll('.header-cart-icon').forEach(function (icon) {
    console.log('Found cart icon:', icon);
    icon.addEventListener('click', function (e) {
      console.log('Cart icon clicked');
      // Manually trigger loadMiniCart
      loadMiniCart();
    });
  });

  // Add event listener for add to cart buttons
  document.querySelectorAll('.add-to-cart').forEach(function (button) {
    button.addEventListener('click', function (e) {
      if (this.dataset.id) {
        e.preventDefault();
        addToCart(this.dataset.id, this.dataset.quantity || 1);
      }
    });
  });

  // Manually load mini cart on page load
  loadMiniCart();
});

/**
 * Initialize cart offcanvas functionality
 */
function initCartOffcanvas() {
  console.log('Initializing cart offcanvas');
  // Load mini cart data when offcanvas is shown
  const cartOffcanvas = document.getElementById('cartOffcanvas');
  console.log('Cart offcanvas element:', cartOffcanvas);
  if (cartOffcanvas) {
    cartOffcanvas.addEventListener('show.bs.offcanvas', function () {
      console.log('Cart offcanvas show event triggered');
      loadMiniCart();
    });
  } else {
    console.error('Cart offcanvas element not found');
  }
}

/**
 * Add product to cart
 *
 * @param {number} id - Product ID
 * @param {number} quantity - Quantity to add
 */
function addToCart(id, quantity = 1) {
  axios.post(urls.cart_add, {
    product_id: id,
    quantity: quantity
  }).then(function (response) {
    const res = response;
    if (res.success) {
      // Show success message
      inno.msg(res.message);

      // Update cart icon quantity
      updateCartIconQuantity(res.data.total_format);

      // Show cart offcanvas
      const cartOffcanvas = new bootstrap.Offcanvas(document.getElementById('cartOffcanvas'));
      cartOffcanvas.show();

      // Load mini cart data
      loadMiniCart();
    } else {
      inno.msg(res.message, 'error');
    }
  }).catch(function (error) {
    console.error('Add to cart error:', error);
    inno.msg('Failed to add product to cart', 'error');
  });
}

/**
 * Load mini cart data
 */
function loadMiniCart() {
  console.log('Loading mini cart data from:', urls.cart_mini);
  axios.get(urls.cart_mini).then(function (response) {
    const res = response;
    console.log('Mini cart response:', res);
    if (res.success) {
      // Update cart offcanvas content
      updateCartOffcanvasContent(res.data);
    } else {
      console.error('Mini cart request failed:', res.message);
    }
  }).catch(function (error) {
    console.error('Load mini cart error:', error);
  });
}

/**
 * Update cart icon quantity
 *
 * @param {string} quantity - Quantity to display
 */
function updateCartIconQuantity(quantity) {
  document.querySelectorAll('.header-cart-icon .icon-quantity').forEach(function (el) {
    el.textContent = quantity;
  });
}

/**
 * Update cart offcanvas content
 *
 * @param {Object} data - Cart data
 */
function updateCartOffcanvasContent(data) {
  console.log('Updating cart offcanvas with data:', data);
  const cartOffcanvas = document.getElementById('cartOffcanvas');
  if (!cartOffcanvas) {
    console.error('Cart offcanvas element not found');
    return;
  }

  const offcanvasBody = cartOffcanvas.querySelector('.offcanvas-body');
  if (!offcanvasBody) {
    console.error('Offcanvas body element not found');
    return;
  }

  // If no items in cart, show empty cart message
  if (!data.list || !data.list.length) {
    console.log('No items in cart, showing empty cart message');
    offcanvasBody.innerHTML = `
      <div class="text-center py-5">
        <img src="${asset_url}images/icons/empty-cart.svg" class="img-fluid w-max-200 mb-4">
        <h5>${translations.empty_cart || 'Your shopping cart is empty'}</h5>
        <a class="btn btn-primary mt-3" href="${urls.base_url}">${translations.continue || 'Continue Shopping'}</a>
      </div>
    `;
    return;
  }

  // Build cart items HTML
  let cartItemsHtml = '<div class="cart-items">';

  data.list.forEach(function (product) {
    cartItemsHtml += `
      <div class="cart-item" data-id="${product.id}">
        <div class="d-flex">
          <div class="cart-item-image">
            <img src="${product.image}" class="img-fluid">
          </div>
          <div class="cart-item-details ms-3">
            <div class="cart-item-name">
              <a href="${product.url}">${product.product_name}</a>
            </div>
            <div class="text-secondary mt-1">
              ${product.sku_code}
              ${product.variant_label ? '- ' + product.variant_label : ''}
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div class="cart-item-price fs-5">${product.price_format}</div>
              <div class="quantity-wrap small-quantity mx-3">
                <div class="minus"><i class="bi bi-dash-lg"></i></div>
                <input type="number" class="form-control fs-6" value="${product.quantity || 1}">
                <div class="plus"><i class="bi bi-plus-lg"></i></div>
              </div>
              <div class="delete-cart text-danger cursor-pointer"><i class="bi bi-x-circle-fill fs-5"></i></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
            </div>
          </div>
        </div>
      </div>
    `;
  });

  cartItemsHtml += '</div>';

  // Add cart summary
  cartItemsHtml += `
    <div class="cart-summary mt-4">
      <div class="bg-light p-3 mb-4">
        <div class="d-flex justify-content-between">
          <span class="fs-5">${translations.total || 'Total'}</span>
          <span class="fs-5 total-amount">${data.amount_format}</span>
        </div>
      </div>
      <a class="btn btn-primary btn-lg fw-bold w-100 to-checkout" href="${urls.checkout}">${translations.go_checkout || 'Checkout'}</a>
      <a class="btn btn-outline-secondary btn-lg fw-bold w-100 mt-2" href="${urls.cart}">${translations.view_cart || 'View Cart'}</a>
    </div>
  `;

  // Update offcanvas content
  offcanvasBody.innerHTML = cartItemsHtml;

  // Add event listeners to new elements
  initCartItemEvents();
}

/**
 * Initialize cart item events
 */
function initCartItemEvents() {
  // Quantity controls
  document.querySelectorAll('.cart-offcanvas .quantity-wrap .plus, .cart-offcanvas .quantity-wrap .minus').forEach(function (el) {
    el.addEventListener('click', function () {
      var input = this.parentNode.querySelector('input');
      var quantity = parseInt(input.value);

      if (this.classList.contains('plus')) {
        input.value = quantity + 1;
      } else {
        if (quantity > 1) {
          input.value = quantity - 1;
        }
      }

      var cartItem = this.closest('.cart-item');
      updateOffcanvasCart(cartItem.dataset.id, input.value * 1);
    });
  });

  // Quantity input change
  document.querySelectorAll('.cart-offcanvas .quantity-wrap input').forEach(function (el) {
    el.addEventListener('change', function () {
      var cartItem = this.closest('.cart-item');
      updateOffcanvasCart(cartItem.dataset.id, this.value * 1);
    });
  });

  // Delete cart item
  document.querySelectorAll('.cart-offcanvas .delete-cart').forEach(function (el) {
    el.addEventListener('click', function () {
      var cartItem = this.closest('.cart-item');
      updateOffcanvasCart(cartItem.dataset.id, 0, 'delete');
    });
  });
}

/**
 * Update cart item in offcanvas
 *
 * @param {number} id - Cart item ID
 * @param {number} quantity - New quantity
 * @param {string} method - HTTP method (put or delete)
 */
function updateOffcanvasCart(id, quantity, method = 'put') {
  axios[method](`${urls.cart_add}/${id}`, { quantity }).then(function (response) {
    const res = response;
    if (res.success) {
      inno.msg(res.message);

      // Update totals
      document.querySelectorAll('.total-amount').forEach(function (el) {
        el.textContent = res.data.amount_format;
      });

      document.querySelectorAll('.header-cart-icon .icon-quantity').forEach(function (el) {
        el.textContent = res.data.total_format;
      });

      if (method === 'delete') {
        var cartItem = document.querySelector(`.cart-item[data-id="${id}"]`);
        if (cartItem) {
          cartItem.remove();
        }

        // If no items left, reload to show empty cart
        if (!document.querySelectorAll('.cart-item').length) {
          loadMiniCart();
        }
      } else {
        // Update subtotal for this item
        var item = res.data.list.find(item => item.id === id);
        if (item) {
          var subtotalEl = document.querySelector(`.cart-item[data-id="${id}"] .cart-item-subtotal`);
          if (subtotalEl) {
            subtotalEl.textContent = item.subtotal_format;
          }
        }
      }
    }
  });
}

/**
 * Test cart mini endpoint
 */
function testCartMiniEndpoint() {
  console.log('Testing cart mini endpoint:', urls.cart_mini);
  fetch(urls.cart_mini)
    .then(response => {
      console.log('Cart mini response status:', response.status);
      return response.json();
    })
    .then(data => {
      console.log('Cart mini response data:', data);
    })
    .catch(error => {
      console.error('Cart mini fetch error:', error);
    });
}
