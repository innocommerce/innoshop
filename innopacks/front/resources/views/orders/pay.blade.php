@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

  <x-front-breadcrumb type="static" value="{{ front_route('orders.pay', ['number'=>$order->number]) }}" title="{{ $order->number }}"/>

  @hookinsert('order.pay.top')

  <div class="container">
    @if(isset($success))
      <div class="alert alert-success alert-dismissible fade show text-center" role="alert" style="max-width: 800px; margin: 2rem auto; font-size: 1.1rem;">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>{{ $success }}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @error('error')
    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert" style="max-width: 800px; margin: 2rem auto;">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      {{ $message }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @enderror

    @if(isset($error))
      <div class="alert alert-danger alert-dismissible fade show text-center" role="alert" style="max-width: 800px; margin: 2rem auto;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ $error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <table class="table w-max-800 mx-auto mb-3 mb-md-5 checkout-success-table">
      <thead>
      <tr>
        <th>{{ __('front/order.order_number') }}</th>
        <th>{{ __('front/order.order_billing') }}</th>
        <th>{{ __('front/order.order_total') }}</th>
        <th>{{ __('front/order.order_status') }}</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td>{{ $order->number }}</td>
        <td>{{ $order->billing_method_name }}</td>
        <td>{{ currency_format($order->total) }}</td>
        <td>{{ $order->status_format }}</td>
      </tr>
      </tbody>
    </table>

    <div class="d-flex flex-column justify-content-center w-max-800 mx-auto">
      @if(isset($success))
        {{-- Show action buttons when order is already paid --}}
        <div class="text-center mt-4 mb-5">
          @if(current_customer())
            <a href="{{ account_route('orders.number_show', ['number'=>$order->number]) }}" class="btn btn-primary btn-lg me-3">
              <i class="bi bi-receipt me-2"></i>{{ __('front/payment.view_order') }}
            </a>
          @else
            <a href="{{ front_route('orders.number_show', ['number'=>$order->number]) }}" class="btn btn-primary btn-lg me-3">
              <i class="bi bi-receipt me-2"></i>{{ __('front/payment.view_order') }}
            </a>
          @endif
          <a href="{{ front_route('home.index') }}" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-shop me-2"></i>{{ __('front/payment.continue_shopping') }}
          </a>
        </div>
      @elseif(isset($view_path) && isset($view_data))
        @include($view_path, $view_data)
      @endif
    </div>
  </div>

  @hookinsert('order.pay.bottom')

@endsection

@push('footer')
  <script></script>
@endpush
