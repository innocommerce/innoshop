@extends('layouts.app')
@section('body-class', 'page-wishlist')

@section('content')
<x-front-breadcrumb type="route" value="account.favorites.index" title="{{ __('front/account.favorites') }}" />

@hookinsert('account.favorites.top')

<div class="container">
  <div class="row">
    <div class="col-12 col-lg-3">
      @include('shared.account-sidebar')
    </div>
    <div class="col-12 col-lg-9">
      <div class="account-card-box wishlist-box">
        <div class="account-card-title d-flex justify-content-between align-items-center">
          <span class="fw-bold">{{ __('front/favorites.favorites') }}</span>
        </div>

        @if ($favorites->count())
          <div class="row">
            @foreach ($favorites as $product)
            @php($product = $product->product)
            <div class="col-6 col-md-3">
              <div class="product-grid-item">
                <div class="image">
                  <div class="cancel-favorite" data-id="{{ $product->id }}" data-in-wishlist="1"><i class="bi bi-trash"></i></div>
                  <a href="{{ $product->url }}">
                    <img src="{{ $product->image_url }}" class="img-fluid">
                  </a>
                </div>
                <div class="product-item-info">
                  <div class="product-name"><a href="{{ $product->url }}">{{ $product->translation->name }}</a></div>
                  <div class="product-bottom">
                    <div class="product-bottom-btns">
                      <div class="btn-add-cart cursor-pointer" data-id="{{ $product->id }}"
                        data-sku-id="{{ $product->product_sku_id }}">{{ __('front/product.add_to_cart') }}</div>
                    </div>
                    <div class="product-price">
                      @if ($product->masterSku->origin_price)
                      <div class="price-old">{{ $product->masterSku->origin_price_format }}</div>
                      @endif
                      <div class="price-new">{{ $product->masterSku->price_format }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        @else
          <x-common-no-data />
        @endif
      </div>
    </div>
  </div>
</div>

@hookinsert('account.favorites.bottom')

@endsection

@push('footer')
<script>
  $('.cancel-favorite').on('click', function () {
    const id = $(this).attr('data-id');
    inno.addWishlist(id, 1, null, function () {
      setTimeout(() => {
        location.reload();
      }, 800);
    })
  });
</script>
@endpush