@extends('panel::layouts.app')

@section('title', __('panel/menu.orders'))

@push('header')
<script src="{{ asset('vendor/vue/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
<script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
<script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
@endpush

@section('content')

@section('page-title-right')
<div class="title-right-btns">
  <div class="status-wrap" id="status-app">
    @foreach($next_statuses as $status)
      <button class="btn btn-primary ms-2" @click="edit('{{ $status['status'] }}')">{{ $status['name'] }}</button>
    @endforeach
    <el-dialog
      v-model="statusDialog"
      title="{{ __('panel/order.status') }}"
      width="500">
      <div class="mb-2">{{ __('panel/order.comment') }}</div>
      <textarea v-model="comment" class="form-control" placeholder="{{ __('panel/order.comment') }}" rows="3"></textarea>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="statusDialog = false">{{ __('panel/common.close') }}</el-button>
          <el-button type="primary" @click="submit">{{ __('panel/common.btn_save') }}</el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</div>
@endsection

<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/order.order_info') }}</h5>
  </div>
  <div class="card-body">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>{{ __('panel/order.number') }}</th>
          <th>{{ __('panel/order.created_at') }}</th>
          <th>{{ __('panel/order.total') }}</th>
          <th>{{ __('panel/order.billing_method_code') }}</th>
          <th>{{ __('panel/order.shipping_method_code') }}</th>
          <th>{{ __('panel/common.status') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ $order->number }}</td>
          <td>{{ $order->created_at }}</td>
          <td>{{ $order->total_format }}</td>
          <td>{{ $order->billing_method_code }}</td>
          <td>{{ $order->shipping_method_code }}</td>
          <td>{{ $order->status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/order.order_items') }}</h5>
  </div>
  <div class="card-body">
    <table class="table products-table align-middle">
      <thead>
        <tr>
          <th>{{ __('panel/common.id') }}</th>
          <th>{{ __('panel/order.product') }}</th>
          <th>{{ __('panel/order.sku_code') }}</th>
          <th>{{ __('panel/order.quantity') }}</th>
          <th>{{ __('panel/order.unit_price') }}</th>
          <th>{{ __('panel/order.subtotal') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($order->items as $product)
        <tr>
          <td>{{ $product->id }}</td>
          <td>
            <div class="product-item d-flex align-items-center">
              <div class="product-image wh-40 border"><img src="{{ $product->image }}" class="img-fluid"></div>
              <div class="product-info ms-2">
                <div class="name">{{ $product->name }}</div>
              </div>
            </div>
          </td>
          <td>{{ $product->product_sku }}</td>
          <td>{{ $product->quantity }}</td>
          <td>{{ $product->price_format }}</td>
          <td>{{ $product->subtotal_format }}</td>
        </tr>
        @endforeach
        @foreach ($order->fees as $total)
        <tr>
          <td></td><td></td><td></td><td></td>
          <td><strong>{{ $total->title }}</strong></td>
          <td>{{ $total->value_format }}</td>
        </tr>
        @endforeach
        <tr>
          <td></td><td></td><td></td><td></td>
          <td><strong>{{ __('panel/order.total') }}</strong></td>
          <td>{{ $order->total_format }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/order.address') }}</h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-12 col-md-6">
        <div class="address-card">
          <div class="address-card-header mb-3">
            <h5 class="address-card-title">{{ __('panel/order.shipping_address') }}</h5>
          </div>
          <div class="address-card-body">
            <p>{{ $order->shipping_customer_name }}</p>
            <p>{{ $order->shipping_telephone }} {{ $order->shipping_zipcode }}</p>
            <p>{{ $order->shipping_address_1 }} {{ $order->shipping_address_2 }} </p>
            <p>{{ $order->shipping_city }} {{ $order->shipping_state }} {{ $order->shipping_country }}</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="address-card">
          <div class="address-card-header mb-3">
            <h5 class="address-card-title">{{ __('panel/order.billing_address') }}</h5>
          </div>
          <div class="address-card-body">
            <p>{{ $order->billing_customer_name }}</p>
            <p>{{ $order->billing_telephone }} {{ $order->billing_zipcode }}</p>
            <p>{{ $order->billing_address_1 }} {{ $order->billing_address_2 }} </p>
            <p>{{ $order->billing_city }} {{ $order->billing_state }} {{ $order->billing_country }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/order.history') }}</h5>
  </div>
  <div class="card-body">
    <table class="table table-response align-middle">
      <thead>
      <tr>
        <th>{{ __('panel/order.status') }}</th>
        <th>{{ __('panel/order.comment') }}</th>
        <th>{{ __('panel/order.date_time') }}</th>
      </tr>
      </thead>
      <tbody>
      @foreach($order->histories as $history)
        <tr>
          <td data-title="State">{{ $history->status }}</td>
          <td data-title="Remark">{{ $history->comment }}</td>
          <td data-title="Update Time">{{ $history->created_at }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection

@push('footer')
<script>
  const { createApp, ref } = Vue
  const api = @json(panel_route('orders.change_status', $order));
  const statusApp = createApp({
    setup() {
      const statusDialog = ref(false)
      const comment = ref('')
      let status = '';

      const edit = (code) => {
        statusDialog.value = true
        status = code
      }

      const submit = () => {
        axios.put(api, { status: status, comment: comment.value }).then(() => {
          statusDialog.value = false
          window.location.reload()
        })
      }

      return {
        edit,
        submit,
        comment,
        statusDialog,
      }
    }
  })

  statusApp.use(ElementPlus);
  statusApp.mount('#status-app');
</script>
@endpush