@extends('panel::layouts.app')
@section('body-class', 'page-product')

@section('title', __('panel::menu.products'))

@section('page-title-right')
  <a href="{{ panel_route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{ __('panel::common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($products->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <th>{{ __('panel::common.id') }}</th>
            <th class="wp-100">{{ __('panel::common.image') }}</th>
            <th>{{ __('panel::product.name') }}</th>
            <th>{{ __('panel::product.price') }}</th>
            <th>{{ __('panel::product.quantity') }}</th>
            <th>{{ __('panel::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @foreach ($products as $product)
            <tr>
              <td>{{ $product->id }}</td>
              <td>
                <div class="d-flex align-items-center justify-content-center wh-50 border">
                  <img src="{{ image_resize($product->images->first()->path ?? '') }}" class="img-fluid"
                       alt="{{ $product->translation->name ?? '' }}">
                </div>
              </td>
              <td>{{ $product->translation->name ?? '' }}</td>
              <td>{{ currency_format($product->masterSku->price) }}</td>
              <td>{{ $product->masterSku->quantity }}</td>
              <td>
                <a href="{{ panel_route('products.edit', [$product->id]) }}"
                   class="btn btn-outline-primary btn-sm">{{ __('panel::common.edit')}}</a>
                <button class="btn btn-outline-danger btn-sm" type="button">{{ __('panel::common.delete')}}</button>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      {{ $products->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
      <x-common-no-data />
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush