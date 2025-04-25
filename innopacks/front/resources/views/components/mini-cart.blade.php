<!-- Mini Cart Component -->
<div class="offcanvas offcanvas-end" style="width: 400px;" tabindex="-1" id="miniCart" aria-labelledby="miniCartLabel">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="miniCartLabel">{{ __('front/cart.cart') }}</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0 d-flex flex-column" style="height: calc(100vh - 60px);" v-cloak>
    <!-- Empty cart state -->
    <div v-if="isEmpty" class="text-center py-5">
      <img :src="asset_url + 'images/icons/empty-cart.svg'" class="img-fluid" style="max-width: 200px;" class="mb-4">
      <h5>{{ __('front/cart.empty_cart') }}</h5>
      <a class="btn btn-primary mt-3" :href="urls.base_url">{{ __('front/cart.continue') }}</a>
    </div>

    <!-- Cart content -->
    <div v-else class="d-flex flex-column h-100">
      <div class="cart-items flex-grow-1 overflow-auto p-3">
        <div v-for="item in cartItems" :key="item.id" class="py-3 border-bottom" :data-id="item.id">
          <div class="d-flex">
            <div class="d-flex align-items-center me-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" :id="'item-' + item.id" 
                  :checked="item.selected"
                  @change="updateSelection($event.target.checked, [item.id])"
                  :disabled="item.item_type !== 'normal'">
              </div>
            </div>
            <div class="flex-shrink-0" style="width: 80px; height: 80px;">
              <img :src="item.image" class="img-fluid w-100 h-100 object-fit-cover">
            </div>
            <div class="flex-grow-1 ms-3 overflow-hidden">
              <div class="text-truncate">
                <a :href="item.url" class="text-decoration-none text-body hover-primary">@{{ item.product_name }}</a>
              </div>
              <div class="text-secondary mt-1">
                @{{ item.sku_code }}
                <span v-if="item.variant_label">- @{{ item.variant_label }}</span>
                <span v-if="item.item_type !== 'normal'" class="badge bg-danger ms-2">@{{ item.item_type_label }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="d-flex align-items-center gap-2">
                  <div class="fs-6">@{{ item.price_format }}</div>
                  <div class="d-flex align-items-center" style="width: 60px;">
                    <input type="number" 
                           :class="item.item_type !== 'normal' ? 'form-control form-control-sm text-center p-0 bg-light' : 'form-control form-control-sm text-center p-0'"
                           style="width: 60px; height: 26px;" 
                           v-model.number="item.quantity" 
                           min="1" 
                           @change="quantityChanged(item)"
                           :readonly="item.item_type !== 'normal'">
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <div class="text-primary small">@{{ item.subtotal_format }}</div>
                  <button type="button" class="btn btn-link text-danger p-0 border-0 d-flex align-items-center justify-content-center" 
                          style="width: 26px; height: 26px;" 
                          @click="deleteItem(item.id)">
                    <i class="bi bi-x-circle-fill"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cart footer -->
      <div class="border-top p-3">
        <div class="d-flex align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="selectAll" 
              :checked="allSelected"
              @change="toggleSelectAll">
            <label class="form-check-label" for="selectAll">{{ __('front/cart.select_all') }}</label>
          </div>
          <div class="ms-auto">
            <span class="fs-5">{{ __('front/cart.total') }}</span>
            <span class="fs-5 text-primary ms-2">@{{ totalAmount }}</span>
          </div>
        </div>
        <a class="btn btn-primary btn-lg fw-bold w-100 to-checkout" :href="urls.checkout">
          {{ __('front/cart.go_checkout') }}
        </a>
        <a class="btn btn-outline-secondary btn-lg fw-bold w-100 mt-2" :href="urls.cart">
          {{ __('front/cart.view_cart') }}
        </a>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script src="{{ asset('vendor/vue/3.5/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
<script>
if (!window.cartApp) {
  const { createApp, ref, computed, onMounted } = Vue

  window.cartApp = createApp({
    setup() {
      // Reactive states
      const cartItems = ref([])
      const totalAmount = ref('')
      
      // Computed properties
      const isEmpty = computed(() => !cartItems.value.length)
      const allSelected = computed(() => {
        const normalItems = cartItems.value.filter(item => item.item_type === 'normal')
        return normalItems.length > 0 && normalItems.every(item => item.selected)
      })
      
      // Methods
      const loadCart = async () => {
        try {
          const response = await axios.get(urls.cart_mini)
          if (response.success) {
            cartItems.value = response.data.list || []
            totalAmount.value = response.data.amount_format
            updateCartIconQuantity(response.data.total_format)
          }
        } catch (error) {
          inno.msg('Failed to load cart', 'error')
        }
      }

      const updateSelection = async (selected, ids) => {
        // Filter normal product IDs
        const normalIds = ids.filter(id => {
          const item = cartItems.value.find(item => item.id === id)
          return item && item.item_type === 'normal'
        })

        if (!normalIds.length) return

        try {
          const response = await axios.post(`${urls.cart_add}/${selected ? 'select' : 'unselect'}`, {
            cart_ids: normalIds
          })
          if (response.success) {
            inno.msg(response.message)
            await loadCart()
          }
        } catch (error) {
          inno.msg('Failed to update selection', 'error')
        }
      }

      const toggleSelectAll = () => {
        const normalIds = cartItems.value
          .filter(item => item.item_type === 'normal')
          .map(item => item.id)
        updateSelection(!allSelected.value, normalIds)
      }

      const updateQuantity = async (id, quantity) => {
        if (quantity < 1) return
        
        try {
          const response = await axios.put(`${urls.cart_add}/${id}`, { quantity })
          if (response.success) {
            inno.msg(response.message)
            await loadCart()
            if (window.location.pathname.includes('/cart')) {
              window.location.reload()
            }
          }
        } catch (error) {
          inno.msg('Failed to update quantity', 'error')
        }
      }

      const deleteItem = async (id) => {
        try {
          const response = await axios.delete(`${urls.cart_add}/${id}`)
          if (response.success) {
            inno.msg(response.message)
            await loadCart()
            if (window.location.pathname.includes('/cart')) {
              window.location.reload()
            }
          }
        } catch (error) {
          inno.msg('Failed to delete item', 'error')
        }
      }

      const updateCartIconQuantity = (quantity) => {
        document.querySelectorAll('.header-cart-icon .icon-quantity').forEach(el => {
          el.textContent = quantity
        })
      }

      // Helper methods
      const increaseQuantity = (item) => {
        updateQuantity(item.id, item.quantity + 1)
      }

      const decreaseQuantity = (item) => {
        if (item.quantity > 1) {
          updateQuantity(item.id, item.quantity - 1)
        }
      }

      const quantityChanged = (item) => {
        if (item.quantity < 1) {
          item.quantity = 1
        }
        updateQuantity(item.id, item.quantity)
      }

      // Lifecycle hooks
      onMounted(() => {
        loadCart()
        
        // Listen for cart show event
        const miniCart = document.getElementById('miniCart')
        if (miniCart) {
          miniCart.addEventListener('show.bs.offcanvas', () => {
            loadCart()
          })
        }
      })

      return {
        cartItems,
        totalAmount,
        isEmpty,
        allSelected,
        urls,
        asset_url,
        increaseQuantity,
        decreaseQuantity,
        quantityChanged,
        deleteItem,
        updateSelection,
        toggleSelectAll
      }
    }
  }).mount('#miniCart')
}
</script>
@endpush
