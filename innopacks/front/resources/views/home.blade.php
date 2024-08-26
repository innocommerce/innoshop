@extends('layouts.app')
@section('body-class', 'page-home')

@push('header')
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
@endpush

@section('content')

  @hookinsert('home.content.top')

  <section class="module-content">
    @if (system_setting('slideshow'))
      <section class="module-line">
        <div class="swiper" id="module-swiper-1">
          <div class="module-swiper swiper-wrapper">
            @foreach (system_setting('slideshow', []) as $slide)
              @if ($slide['image'][front_locale_code()] ?? false)
                <div class="swiper-slide">
                  <a href="{{ $slide['link'] ?: 'javascript:void(0)' }}"><img
                        src="{{ image_origin($slide['image'][front_locale_code()]) }}" class="img-fluid"></a>
                </div>
              @endif
            @endforeach
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </section>
      <script>
        var swiper = new Swiper('#module-swiper-1', {
          loop: true,
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
        });
      </script>
    @endif

    @hookinsert('home.swiper.after')

    @if (0)
      <section class="module-line">
        <div class="module-banner-2">
          <div class="container">
            <div class="row">
              <div class="col-12 col-md-4 mb-2 mb-lg-0">
                <a href=""><img src="{{ asset('images/demo/banner/banner-3.jpg') }}" class="img-fluid"></a>
              </div>
              <div class="col-12 col-md-8">
                <a href=""><img src="{{ asset('images/demo/banner/banner-4.jpg') }}" class="img-fluid"></a>
              </div>
            </div>
          </div>
        </div>
      </section>
    @endif

    <section class="module-line">
      <div class="module-product-tab">
        <div class="container">
          <div class="module-title-wrap">
            <div class="module-title">{{ __('front/home.feature_product') }}</div>
            <div class="module-sub-title">{{ __('front/home.feature_product_text') }}</div>
          </div>

          <ul class="nav nav-tabs">
            @foreach ($tab_products as $item)
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                        data-bs-target="#module-product-tab-x-{{ $loop->iteration }}"
                        type="button">{{ $item['tab_title'] }}</button>
              </li>
            @endforeach
          </ul>

          <div class="tab-content">
            @foreach ($tab_products as $item)
              <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}"
                   id="module-product-tab-x-{{ $loop->iteration }}">
                <div class="row gx-3 gx-lg-4">
                  @foreach ($item['products'] as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                      @include('shared.product')
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </section>

    @if (0)
      <section class="module-line">
        <div class="module-banner-1">
          <div class="container">
            <a href=""><img src="{{ asset('images/demo/banner/banner-5.jpg') }}" class="img-fluid"></a>
          </div>
        </div>
      </section>
    @endif

    <section class="module-line">
      <div class="module-product-tab">
        <div class="container">
          <div class="module-title-wrap">
            <div class="module-title">{{ __('front/home.news_blog') }}</div>
            <div class="module-sub-title">{{ __('front/home.news_blog_text') }}</div>
          </div>

          <div class="row gx-3 gx-lg-4">
            @foreach ($news as $new)
              <div class="col-6 col-md-4 col-lg-3">
                @include('shared.blog', ['item'=>$new])
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </section>
  </section>

  @hookinsert('home.content.bottom')

@endsection
