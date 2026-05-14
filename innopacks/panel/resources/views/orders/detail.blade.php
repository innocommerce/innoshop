@php
  $routePrefix       = $routePrefix ?? 'panel';
  $layoutView        = $layoutView ?? 'panel::layouts.app';
  $showPrint         = $showPrint ?? true;
  $showPayments      = $showPayments ?? true;
  $showComments      = $showComments ?? true;
  $showStatusChange  = $showStatusChange ?? true;
  $showAddresses     = $showAddresses ?? true;
  $showShipments     = $showShipments ?? true;
@endphp
@extends($layoutView)
@section('title', __('panel/menu.orders'))

@section('page-title-right')
  <div class="title-right-btns">
    <div class="status-wrap" id="status-app">
      @if($showStatusChange)
      @foreach ($next_statuses as $status)
        <button class="btn btn-primary ms-2" @click="edit('{{ $status['status'] }}')">{{ $status['name'] }}</button>
      @endforeach
      @endif

      @if($showPrint)
      <a class="btn btn-success ms-2" href="{{ route($routePrefix . '.orders.printing', $order) }}"
        target="_blank"><i class="bi bi-printer me-1"></i>{{ panel_trans('order.print') }}</a>
      @hookinsert('panel.orders.detail.print.after')
      @endif

      @if($showStatusChange)
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
      @endif
    </div>
  </div>
@endsection

@section('content')
  {{-- Order Info --}}
  @include('panel::orders.detail.info')

  {{-- Order Items --}}
  @include('panel::orders.detail.items')

  {{-- Addresses --}}
  @if($showAddresses)
  @include('panel::orders.detail.addresses')
  @endif

  @if($showPayments)
  {{-- Payments --}}
  @include('panel::orders.detail.payments')
  @endif

  {{-- Shipments --}}
  @if($showShipments)
  @include('panel::orders.detail.shipments')
  @endif

  @if($showComments)
  {{-- Comments --}}
  @include('panel::orders.detail.comments')
  @endif

  {{-- History --}}
  @include('panel::orders.detail.history')

  {{-- Bundle Modal --}}
  @include('panel::orders.bundle.modal')

@endsection

@push('footer')
  @include('panel::orders.detail.scripts')
  @include('panel::orders.bundle.scripts')
@endpush
