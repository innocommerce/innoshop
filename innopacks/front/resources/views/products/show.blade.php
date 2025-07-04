@extends('layouts.app')
@section('body-class', 'page-product')

@section('title', \InnoShop\Common\Libraries\MetaInfo::getInstance($product)->getTitle())
@section('description', \InnoShop\Common\Libraries\MetaInfo::getInstance($product)->getDescription())
@section('keywords', \InnoShop\Common\Libraries\MetaInfo::getInstance($product)->getKeywords())

@push('header')
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">

  <script src="{{ asset('vendor/photoswipe/umd/photoswipe.umd.min.js') }}"></script>
  <script src="{{ asset('vendor/photoswipe/umd/photoswipe-lightbox.umd.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/photoswipe/photoswipe.css') }}">
@endpush

@section('content')

  <x-front-breadcrumb type="product" :value="$product"/>

  @hookinsert('product.show.top')

  <div class="container">
    <div class="page-product-top">
      <div class="row">
        <div class="col-12 col-lg-6 product-left-col">
         
          <div class="product-images">

            @if(is_array($product->images))
              <div class="sub-product-img">
                <div class="swiper" id="sub-product-img-swiper">
                  <div class="swiper-wrapper">
                    @foreach($product->images as $image)
                      <div class="swiper-slide">
                        <a href="{{ image_resize($image, 600, 600) }}" data-pswp-width="800"
                           data-pswp-height="800">
                          <img src="{{ image_resize($image) }}" class="img-fluid">
                        </a>
                      </div>
                    @endforeach
                  </div>
                  <div class="sub-product-btn">
                    <div class="sub-product-prev"><i class="bi bi-chevron-compact-up"></i></div>
                    <div class="sub-product-next"><i class="bi bi-chevron-compact-down"></i></div>
                  </div>
                  <div class="swiper-pagination sub-product-pagination"></div>
                </div>
              </div>
            @endif

            <div class="main-product-img position-relative">
              @hookinsert('front.product.show.image.before')
              <img src="{{ $product->image_url }}" class="img-fluid">
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="product-info">
            <h1 class="product-title">{{ $product->fallbackName() }}</h1>
            @hookupdate('front.product.show.price')
            <div class="product-price">
              <span class="price">{{ $sku['price_format'] }}</span>
              @if($sku['origin_price'])
                <span class="old-price ms-2">{{ $sku['origin_price_format'] }}</span>
              @endif
            </div>
            @endhookupdate

            <div class="stock-wrap">
              @if($sku['quantity'] > 0)
                <div class="in-stock badge">{{ __('front/product.in_stock') }}</div>
              @else
                <div class="out-stock badge d-none">{{ __('front/product.out_stock') }}</div>
              @endif
            </div>

            <div class="sub-product-title">{{ $product->fallbackName('summary') }}</div>

            @include('products._bundle_items')

            <ul class="product-param">
              <li class="sku"><span class="title">{{ __('front/product.sku_code') }}:</span> <span
                  class="value">{{ $sku['code'] }}</span></li>
              <li class="model {{ !($sku['model'] ?? false) ? 'd-none' : '' }}"><span class="title">{{ __('front/product.model') }}:</span>
                <span class="value">{{ $sku['model'] }}</span></li>
              @if ($product->categories->count())
                <li class="category">
                  <span class="title">{{ __('front/product.category') }}:</span>
                  <span class="value">
                @foreach ($product->categories as $category)
                      <a href="{{ $category->url }}"
                         class="text-dark">{{ $category->fallbackName() }}</a>{{ !$loop->last ? ', ' : '' }}
                    @endforeach
              </span>
                </li>
              @endif
              @if($product->brand)
                <li class="brand">
                  <span class="title">{{ __('front/product.brand') }}:</span> <span class="value">
                <a href="{{ $product->brand->url }}"> {{ $product->brand->name }} </a>
              </span>
                </li>
              @endif
              @hookinsert('product.detail.brand.after')
            </ul>

            @include('products._variants')

            @if(!system_setting('disable_online_order'))
              <div class="product-info-bottom">
                <div class="quantity-wrap">
                  <div class="minus"><i class="bi bi-dash-lg"></i></div>
                  <input type="number" class="form-control product-quantity" value="1"
                         data-sku-id="{{ $sku['id'] }}">
                  <div class="plus"><i class="bi bi-plus-lg"></i></div>
                </div>
                <div class="product-info-btns">
                  <button class="btn btn-primary add-cart" data-id="{{ $product->id }}"
                          data-price="{{ $product->masterSku->price }}">
                    {{ __('front/product.add_to_cart') }}
                  </button>
                  <button class="btn buy-now ms-2" data-id="{{ $product->id }}"
                          data-price="{{ $product->masterSku->price }}">
                    {{ __('front/product.buy_now') }}
                  </button>
                  @hookinsert('product.detail.cart.after')
                </div>
              </div>
            @endif

            <div class="add-wishlist" data-in-wishlist="{{ $product->hasFavorite() }}"
                 data-id="{{ $product->id }}"
                 data-price="{{ $product->masterSku->price }}">
              <i
                class="bi bi-heart{{ $product->hasFavorite() ? '-fill' : '' }}"></i> {{ __('front/product.add_wishlist') }}
            </div>
            @hookinsert('product.detail.after')
          </div>
        </div>
      </div>
    </div>

    <div class="product-description">
      <ul class="nav nav-tabs tabs-plus">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab"
                  data-bs-target="#product-description-description"
                  type="button">{{ __('front/product.description') }}</button>
        </li>
        @if($attributes)
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#product-description-attribute"
                    type="button">{{ __('front/product.attribute') }}</button>
          </li>
        @endif
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#product-review"
                  type="button">{{ __('front/product.review') }}</button>
        </li>
        <li class="nav-item">
          <button class="nav-link correlation" data-bs-toggle="tab"
                  data-bs-target="#product-description-correlation"
                  type="button">{{__('front/product.related_product')}}
          </button>
        </li>
        @hookinsert('product.detail.tab.link.after')
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="product-description-description">
          @if($product->fallbackName('selling_point'))
            {!! parsedown($product->fallbackName('selling_point')) !!}
          @endif
          {!! $product->fallbackName('content') !!}
        </div>

        @if($attributes)
          <div class="tab-pane fade" id="product-description-attribute" role="tabpanel">
            <table class="table table-bordered attribute-table">
              @foreach ($attributes as $group)
                <thead class="table-light">
                <tr>
                  <td colspan="2"><strong>{{ $group['attribute_group_name'] }}</strong></td>
                </tr>
                </thead>
                <tbody>
                @foreach ($group['attributes'] as $item)
                  <tr>
                    <td>{{ $item['attribute'] }}</td>
                    <td>{{ $item['attribute_value'] }}</td>
                  </tr>
                @endforeach
                </tbody>
              @endforeach
            </table>
          </div>
        @endif

        <div class="tab-pane fade" id="product-review" role="tabpanel">
          @include('products.review')
        </div>
        <div class="tab-pane fade" id="product-description-correlation">
          <div class="row gx-3 gx-lg-4">
            @foreach ($related as $relatedItem)
              <div class="col-6 col-md-4 col-lg-3">
                @include('shared.product', ['product'=>$relatedItem])
              </div>
            @endforeach
          </div>
        </div>
        @hookinsert('product.detail.tab.pane.after')
      </div>
    </div>

    @hookinsert('product.show.bottom')

  </div>

@endsection

@push('footer')
  <script>
    const isMobile = window.innerWidth < 992;

    if (isMobile) {
      $('.sub-product-img .swiper-slide').each(function () {
        $(this).find('a > img').attr('src', $(this).find('a').attr('href'));
      });
    }

    let subProductSwiper = new Swiper('#sub-product-img-swiper', {
      direction: !isMobile ? 'vertical' : 'horizontal',
      autoHeight: !isMobile ? true : false,
      slidesPerView: !isMobile ? 5 : 1,
      spaceBetween: !isMobile ? 10 : 0,
      navigation: {
        nextEl: '.sub-product-next',
        prevEl: '.sub-product-prev',
      },
      pagination: {
        el: '.sub-product-pagination',
        clickable: true,
      },
      observer: true,
      observeParents: true,
    });

    let lightbox = new PhotoSwipeLightbox({
      gallery: '#sub-product-img-swiper',
      children: 'a',
      // dynamic import is not supported in UMD version
      pswpModule: PhotoSwipe
    });
    lightbox.init();

    $('.main-product-img').on('click', function () {
      $('#sub-product-img-swiper .swiper-slide').eq(0).find('a').get(0).click();
    });

    $('.quantity-wrap .plus, .quantity-wrap .minus').on('click', function () {
      if ($(this).parent().hasClass('disabled')) {
        return;
      }

      let quantity = parseInt($(this).siblings('input').val());
      if ($(this).hasClass('plus')) {
        $(this).siblings('input').val(quantity + 1);
      } else {
        if (quantity > 1) {
          $(this).siblings('input').val(quantity - 1);
        }
      }
    });

    $('.add-cart, .buy-now').on('click', function () {
      const quantity = $('.product-quantity').val();
      const skuId = $('.product-quantity').data('sku-id');
      const isBuyNow = $(this).hasClass('buy-now');

      inno.addCart({skuId, quantity, isBuyNow}, this, function (res) {
        if (isBuyNow) {
          window.location.href = '{{ front_route('carts.index') }}';
        }
      })
    });
  </script>
@endpush
