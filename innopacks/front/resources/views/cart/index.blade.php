@extends('layouts.app')
@section('body-class', 'page-cart')

@section('content')
  @push('header')
    <script src="{{ asset('vendor/vue/3.5/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
  @endpush

  <x-front-breadcrumb type="route" value="carts.index" title="{{ __('front/cart.cart') }}" />

  @hookinsert('cart.top')

  <div class="container">
    @if (session()->has('errors'))
      <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4" />
    @endif
    @if (session('error'))
      <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-4" />
    @endif
    @if (session('success'))
      <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4" />
    @endif

    <div id="app-cart" v-cloak>
      <div class="row" v-if="list.length">
        <div class="col-12 col-md-9">
          @hookinsert('cart.table.before')

          <table class="table products-table align-middle">
            <thead>
              <tr>
                <th scope="col">
                  <input class="form-check-input product-all-check" type="checkbox"
                    :checked="allSelected"
                    @change="toggleAllSelection">
                </th>
                <th scope="col">{{ __('front/cart.product') }}</th>
                <th scope="col"></th>
                <th scope="col">{{ __('front/cart.price') }}</th>
                <th scope="col">{{ __('front/cart.quantity') }}</th>
                <th scope="col">{{ __('front/cart.subtotal') }}</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in list" :key="item.id" :data-id="item.id">
                <td class="td-product-check">
                  <input class="form-check-input product-item-check" :value="item.id" type="checkbox"
                    :checked="item.selected"
                    @change="updateSelection($event.target.checked, [item.id])"
                    :disabled="item.item_type !== 'normal'">
                </td>
                <td class="td-image">
                  <div class="product-image"><img :src="item.image" class="img-fluid"></div>
                </td>
                <td class="td-product-info">
                  <div class="product-item">
                    <div class="product-info">
                      <div class="product-name">
                        <a :href="item.url">@{{ item.product_name }}</a>
                        <div class="text-secondary mt-1">
                          @{{ item.sku_code }}
                          <template v-if="item.variant_label">
                            - @{{ item.variant_label }}
                          </template>
                          <span v-if="!item.is_stock_enough" class="badge bg-danger ms-2">
                            {{ __('front/common.stock_not_enough') }}
                          </span> 
                          <span v-if="item.item_type_label" class="badge bg-danger ms-2">
                            @{{ item.item_type_label }}
                          </span>
                        </div>
                      </div>
                      <div class="mb-price mt-1">@{{ item.price_format }}</div>
                      <div class="quantity-wrap mt-1 d-lg-none" v-if="item.item_type === 'normal'">
                        <div class="minus" @click="updateQuantity(item.id, item.quantity - 1)">
                          <i class="bi bi-dash-lg"></i>
                        </div>
                        <input type="number" class="form-control" v-model.number="item.quantity"
                          @change="updateQuantity(item.id, item.quantity)">
                        <div class="plus" @click="updateQuantity(item.id, item.quantity + 1)">
                          <i class="bi bi-plus-lg"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="td-price">@{{ item.price_format }}</td>
                <td class="td-quantity d-none d-lg-table-cell">
                  <div class="quantity-wrap" v-if="item.item_type === 'normal'">
                    <div class="minus" @click="updateQuantity(item.id, item.quantity - 1)">
                      <i class="bi bi-dash-lg"></i>
                    </div>
                    <input type="number" class="form-control" v-model.number="item.quantity"
                      @change="updateQuantity(item.id, item.quantity)">
                    <div class="plus" @click="updateQuantity(item.id, item.quantity + 1)">
                      <i class="bi bi-plus-lg"></i>
                    </div>
                  </div>
                  <div v-else>@{{ item.quantity }}</div>
                </td>
                <td class="td-subtotal">@{{ item.subtotal_format }}</td>
                <td class="td-delete">
                  <div class="delete-cart text-danger fs-5 cursor-pointer"
                    v-if="item.item_type === 'normal'"
                    @click="deleteItem(item.id)">
                    <i class="bi bi-x-circle-fill"></i>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          @hookinsert('cart.table.after')
        </div>

        <div class="col-12 col-md-3">
          <div class="cart-data">
            <div class="title">{{ __('front/cart.cart_total') }}</div>
            <ul class="cart-data-list">
              <li><span>{{ __('front/cart.selected') }} </span><span class="total-total">@{{ total }}</span></li>
              <li><span>{{ __('front/cart.total') }}</span><span class="total-amount">@{{ amount_format }}</span></li>
            </ul>
            @if(!system_setting('disable_online_order'))
              <button class="btn btn-primary btn-lg fw-bold w-100 to-checkout"
                :disabled="!selectedItems.length || hasStockNotEnough"
                @click="goToCheckout">
                {{ __('front/cart.go_checkout') }}
              </button>
            @endif
          </div>
        </div>
      </div>
      <div v-else class="text-center pm-5 pb-5">
        <img src="{{ asset('images/icons/empty-cart.svg') }}" class="img-fluid w-max-300 mb-5">
        <h2>{{ __('front/cart.empty_cart') }}</h2>
        <a class="btn btn-primary btn-lg mt-3"
          href="{{ front_route('home.index') }}">{{ __('front/cart.continue') }}</a>
      </div>
    </div>
  </div>

  @hookinsert('cart.bottom')
@endsection

@push('footer')
  <script>
    const { createApp, ref, computed } = Vue

    createApp({
      setup() {
        const list = ref(@json($list))
        const total = ref(@json($total))
        const amount_format = ref(@json($amount_format))

        // Computed properties
        const allSelected = computed(() => {
          const normalItems = list.value.filter(item => item.item_type === 'normal')
          return normalItems.length > 0 && normalItems.every(item => item.selected)
        })

        const selectedItems = computed(() => {
          return list.value.filter(item => item.selected && item.item_type === 'normal')
        })

        const hasStockNotEnough = computed(() => selectedItems.value.some(item => !item.is_stock_enough));

        // Methods
        const updateCartState = (data) => {
          list.value = data.list
          total.value = data.total_format
          amount_format.value = data.amount_format
          $('.header-cart-icon .icon-quantity').text(data.total_format)
        }

        const updateQuantity = async (id, quantity) => {
          const item = list.value.find(item => item.id === id)
          if (!item || item.item_type !== 'normal' || quantity < 1) return

          try {
            const res = await axios.put(`${urls.cart_add}/${id}`, { quantity })
            if (res.success) {
              inno.msg(res.message)
              updateCartState(res.data)
            }
          } catch (error) {
            console.error('Failed to update quantity:', error)
          }
        }

        const updateSelection = async (selected, ids) => {
          const normalIds = ids.filter(id => {
            const item = list.value.find(item => item.id === id)
            return item && item.item_type === 'normal'
          })

          if (!normalIds.length) return

          try {
            const res = await axios.post(`${urls.cart_add}/${selected ? 'select' : 'unselect'}`, {
              cart_ids: normalIds
            })
            if (res.success) {
              inno.msg(res.message)
              updateCartState(res.data)
            }
          } catch (error) {
            console.error('Failed to update selection:', error)
          }
        }

        const toggleAllSelection = () => {
          const normalIds = list.value
            .filter(item => item.item_type === 'normal')
            .map(item => item.id)
          updateSelection(!allSelected.value, normalIds)
        }

        const deleteItem = async (id) => {
          const item = list.value.find(item => item.id === id)
          if (!item || item.item_type !== 'normal') return

          try {
            const res = await axios.delete(`${urls.cart_add}/${id}`)
            if (res.success) {
              inno.msg(res.message)
              if (list.value.length === 1) {
                window.location.reload()
                return
              }
              updateCartState(res.data)
            }
          } catch (error) {
            console.error('Failed to delete item:', error)
          }
        }

        const goToCheckout = () => {
          if (!selectedItems.value.length) {
            inno.msg('Please select the product to checkout!')
            return
          }
          window.location.href = urls.checkout
        }

        // Return reactive state and methods
        return {
          list,
          total,
          amount_format,
          allSelected,
          selectedItems,
          updateQuantity,
          updateSelection,
          toggleAllSelection,
          deleteItem,
          goToCheckout
        }
      }
    }).mount('#app-cart')
  </script>
@endpush
