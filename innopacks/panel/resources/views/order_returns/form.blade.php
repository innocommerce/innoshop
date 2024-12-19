@extends('panel::layouts.app')

@section('title', __('panel/menu.order_returns'))

@push('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
@endpush

@section('page-title-right')
  <div class="title-right-btns">
    <div class="status-wrap" id="status-app">
      @foreach($next_statuses as $status)
        <button class="btn btn-primary ms-2" @click="edit('{{ $status['status'] }}')">{{ $status['name'] }}</button>
      @endforeach
      <el-dialog v-model="statusDialog" title="{{ __('panel/order.status') }}" width="500">
        <div class="mb-2">{{ __('panel/order.comment') }}</div>
        <textarea v-model="comment" class="form-control" placeholder="{{ __('panel/order.comment') }}"
                  rows="3"></textarea>
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

@section('content')
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('panel/menu.order_returns') }}</h5>
    </div>

    <div class="card-body">
      <form class="needs-validation" novalidate id="app-form"
            action="{{ $order_return->id ? panel_route('brands.update', [$order_return->id]) : panel_route('brands.store') }}"
            method="POST">
        @csrf
        @method($order_return->id ? 'PUT' : 'POST')
        <table class="table align-middle">
          <thead>
          <tr>
            <th>{{ __('front/return.order_number') }}</th>
            <th>{{ __('front/return.number') }}</th>
            <th>{{ __('front/return.opened') }}</th>
            <th>{{ __('front/return.product_name') }}</th>
            <th>{{ __('front/return.status') }}</th>
            <th>{{ __('front/return.comment') }}</th>
            <th>{{ __('front/return.quantity') }}</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>{{ $order_return->order_number }}</td>
            <td>{{ $order_return->number }}</td>
            <td>{{ $order_return->opened ? __('front/common.yes') : __('front/common.no') }}</td>
            <td>{{ $order_return->product_name }}</td>
            <td>{{ $order_return->status_format }}</td>
            <td>{{ $order_return->comment }}</td>
            <td>{{ $order_return->quantity }}</td>
          </tr>
          </tbody>
        </table>
      </form>
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
        @foreach($order_return->histories as $history)
          <tr>
            <td data-title="State">{{ $history->status_format }}</td>
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
    const {createApp, ref} = Vue
    const api = @json(panel_route('order_returns.change_status', $order_return));
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
          axios.put(api, {status: status, comment: comment.value}).then(() => {
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