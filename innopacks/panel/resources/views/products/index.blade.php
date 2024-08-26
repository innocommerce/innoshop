@extends('panel::layouts.app')
@section('body-class', 'page-product')

@section('title', __('panel/menu.products'))

@section('page-title-right')
  <a href="{{ panel_route('products.create') }}" class="btn btn-primary"><i
        class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($products->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th>{{ __('panel/common.id') }}</th>
              <th class="wp-100">{{ __('panel/common.image') }}</th>
              <th>{{ __('panel/common.name') }}</th>
              <th>{{ __('panel/product.price') }}</th>
              <th>{{ __('panel/product.quantity') }}</th>
              <th>{{ __('panel/common.created_at') }}</th>
              <th>{{ __('panel/common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
              <tr>
                <td>{{ $product->id }}</td>
                <td>
                  <div class="d-flex align-items-center justify-content-center wh-50 border">
                    <a href="{{ $product->url }}" target="_blank">
                      <img src="{{ image_resize($product->images->first()->path ?? '') }}" class="img-fluid"
                           alt="{{ $product->translation->name ?? '' }}">
                    </a>
                  </div>
                </td>
                <td><a href="{{ $product->url }}" class="text-decoration-none" target="_blank">{{ $product->translation->name ?? '' }}</a></td>
                <td>{{ currency_format($product->masterSku->price) }}</td>
                <td>{{ $product->masterSku->quantity }}</td>
                <td>{{ $product->created_at }}</td>
                <td>
                  <a href="{{ panel_route('products.edit', [$product->id]) }}"
                     class="btn btn-outline-primary btn-sm">{{ __('panel/common.edit')}}</a>
                  <form action="{{ panel_route('products.destroy', [$product->id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('panel/common.delete')}}</button>
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $products->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush