@extends('panel::layouts.app')

@section('title', __('panel/menu.order_returns'))

@section('page-title-right')
  <div class="title-right-btns">
    <div id="status-app">
      @foreach ($next_statuses as $status)
        <button class="btn btn-primary ms-2" @click="edit('{{ $status['status'] }}')">{{ $status['name'] }}</button>
      @endforeach
      <el-dialog v-model="statusDialog" title="{{ __('panel/order.status') }}" width="500">
        <div class="mb-2">{{ __('panel/order.comment') }}</div>
        <textarea v-model="comment" class="form-control" placeholder="{{ __('panel/order.comment') }}" rows="3"></textarea>
        <template #footer>
          <div class="dialog-footer">
            <el-button @click="statusDialog = false">{{ __('common/base.close') }}</el-button>
            <el-button type="primary" @click="submit">{{ __('panel/common.btn_save') }}</el-button>
          </div>
        </template>
      </el-dialog>
    </div>
  </div>
@endsection

@section('content')
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('front/return.number') }}: {{ $order_return->number }}</h5>
    </div>
    <div class="card-body">
      <div class="row g-2">
        <div class="col-lg-3 col-md-4 d-flex">
          <div class="fw-bold me-2">{{ __('panel/order_return.order_number') }}:</div>
          <p class="mb-0">
            @if($order_return->order)
              <a href="{{ panel_route('orders.edit', $order_return->order_id) }}" target="_blank">{{ $order_return->order_number }}</a>
            @else
              {{ $order_return->order_number }}
            @endif
          </p>
        </div>
        <div class="col-lg-3 col-md-4 d-flex">
          <div class="fw-bold me-2">{{ __('panel/order_return.customer') }}:</div>
          <p class="mb-0">
            @if($order_return->customer)
              <a href="{{ panel_route('customers.edit', $order_return->customer_id) }}" target="_blank">{{ $order_return->customer->name }}</a>
            @endif
          </p>
        </div>
        <div class="col-lg-3 col-md-4 d-flex">
          <div class="fw-bold me-2">{{ __('panel/order_return.email') }}:</div>
          <p class="mb-0">{{ $order_return->customer->email ?? '' }}</p>
        </div>
        <div class="col-lg-3 col-md-4 d-flex">
          <div class="fw-bold me-2">{{ __('common/base.status') }}:</div>
          <p class="mb-0"><span class="badge bg-{{ $order_return->status_color }}">{{ $order_return->status_format }}</span></p>
        </div>
        <div class="col-lg-3 col-md-4 d-flex">
          <div class="fw-bold me-2">{{ __('front/return.opened') }}:</div>
          <p class="mb-0">{{ $order_return->opened ? __('common/base.yes') : __('common/base.no') }}</p>
        </div>
        <div class="col-lg-3 col-md-4 d-flex">
          <div class="fw-bold me-2">{{ __('front/return.quantity') }}:</div>
          <p class="mb-0">{{ $order_return->quantity }}</p>
        </div>
        <div class="col-lg-3 col-md-4 d-flex">
          <div class="fw-bold me-2">{{ __('panel/order.create_time') }}:</div>
          <p class="mb-0">{{ $order_return->created_at }}</p>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('panel.order_returns.form.info.after')

  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/order.product') }}</h5>
    </div>
    <div class="card-body">
      <div class="d-flex align-items-center">
        @if($order_return->product)
          <div class="wh-60 border rounded me-3">
            <img src="{{ $order_return->product->image_url }}" alt="{{ $order_return->product_name }}" class="img-fluid rounded">
          </div>
        @endif
        <div>
          <div class="fw-bold">{{ $order_return->product_name }}</div>
          <div class="text-muted small">{{ __('panel/order.sku_code') }}: {{ $order_return->product_sku }}</div>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('panel.order_returns.form.product.after')

  @if($order_return->comment)
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('front/return.comment') }}</h5>
    </div>
    <div class="card-body">
      <p class="mb-0">{{ $order_return->comment }}</p>
    </div>
  </div>
  @endif

  @hookinsert('panel.order_returns.form.comment.after')

  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/order.history') }}</h5>
    </div>
    <div class="card-body">
      @if($order_return->histories->count())
        <table class="table table-response align-middle">
          <thead>
          <tr>
            <th>{{ __('panel/order.status') }}</th>
            <th>{{ __('panel/order.comment') }}</th>
            <th>{{ __('panel/order.date_time') }}</th>
          </tr>
          </thead>
          <tbody>
          @foreach($order_return->histories as $history)
            <tr>
              <td><span class="badge bg-secondary">{{ trans("common/rma.$history->status") }}</span></td>
              <td>{{ $history->comment }}</td>
              <td>{{ $history->created_at }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>

  @hookinsert('panel.order_returns.form.history.after')
@endsection

@push('footer')
<script>
  const { createApp, ref } = Vue;
  const api = @json(panel_route('order_returns.change_status', $order_return));
  const statusApp = createApp({
    setup() {
      const statusDialog = ref(false);
      const comment = ref('');
      let status = '';

      const edit = (code) => {
        statusDialog.value = true;
        status = code;
      };

      const submit = () => {
        axios.put(api, {
          status: status,
          comment: comment.value,
        }).then(() => {
          statusDialog.value = false;
          window.location.reload();
        });
      };

      return { edit, submit, comment, statusDialog };
    },
  });
  statusApp.use(ElementPlus);
  statusApp.mount('#status-app');
</script>
@endpush
