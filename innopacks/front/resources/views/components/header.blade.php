@hookinsert('layout.header.top')

@php
  $announcementItems = fire_hook_filter('front.announcements', []);
@endphp

@if(!empty($announcementItems))
<div class="announcement-bar">
  <div class="container">
    <div class="swiper announcement-bar__swiper" id="announcementBarSwiper">
      <div class="swiper-wrapper">
        @foreach($announcementItems as $item)
          <div class="swiper-slide">
            @if(!empty($item['url']))
              <a href="{{ $item['url'] }}" class="announcement-bar__link">{{ $item['text'] }}</a>
            @else
              <span>{{ $item['text'] }}</span>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif

<header id="appHeader">
  <div class="header-top">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="language-switch d-flex align-items-center">
        @if($currentLocale && locales()->isNotEmpty())
          <div class="dropdown">
            <button class="btn dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="{{ asset($currentLocale->image) }}" width="20" height="14" alt="{{ $currentLocale->name }}"> {{ $currentLocale->name }}
            </button>
            <div class="dropdown-menu">
              @foreach (locales() as $locale)
                <a class="dropdown-item d-flex" href="{{ front_route('locales.switch', ['code' => $locale->code]) }}">
                  <div class="me-2"><img src="{{ image_origin($locale['image']) }}" width="20" height="14" alt="{{ $locale['name'] }}">
                  </div>
                  {{ $locale->name }}
                </a>
              @endforeach
            </div>
          </div>
        @endif
        @if(current_currency() && currencies()->isNotEmpty())
          <div class="dropdown {{ $currentLocale && locales()->isNotEmpty() ? 'ms-4' : '' }}">
            <button class="btn dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              {{ current_currency()->name }}
            </button>
            <div class="dropdown-menu">
              @foreach (currencies() as $currency)
                <a class="dropdown-item" href="{{ front_route('currencies.switch', ['code' => $currency->code]) }}">
                  {{ $currency->name }} ({{ $currency->symbol_left }})
                </a>
              @endforeach
            </div>
          </div>
        @endif
        @hookinsert('layouts.header.currency.after')
      </div>

      <div class="top-info">
        @hookinsert('layouts.header.news.before')
        <a href="{{ front_route('articles.index') }}">News</a>

        @hookupdate('layouts.header.telephone')
        @if (system_setting('telephone'))
          <a href="tel:{{ system_setting('telephone') }}">
            <span><i class="bi bi-telephone-outbound"></i> {{ system_setting('telephone') }}</span>
          </a>
        @endif
        @endhookupdate
      </div>
    </div>
  </div>
  <div class="header-desktop">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="left">
        @php $frontLogo = system_setting('front_logo'); @endphp
        <h1 class="logo">
          <a href="{{ front_route('home.index') }}" class="d-flex align-items-center text-decoration-none gap-2">
            @if ($frontLogo)
              <img src="{{ image_origin($frontLogo) }}" class="img-fluid" alt="{{ front_store_name() }}">
            @else
              <span class="d-inline-flex align-items-center" style="width:36px;height:36px;color:rgb(139,82,224);flex-shrink:0;">@include('components.logo-icon')</span>
              <span class="fw-bold" style="font-size:20px;color:#222;">InnoShop</span>
            @endif
          </a>
        </h1>
        <div class="menu">
          <nav class="navbar navbar-expand-md navbar-light">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link" aria-current="page"
                   href="{{ front_route('home.index') }}">{{ __('front/common.home') }}</a>
              </li>

              @hookupdate('layouts.header.menu.pc')
              @foreach ($headerMenus as $menu)
                @if ($menu['children'] ?? [])
                  <li class="nav-item">
                    <div class="dropdown">
                      @if ($menu['name'])
                        <a class="nav-link {{ (request()->route() && equal_url($menu['url'])) ? 'active' : '' }}"
                           href="{{ $menu['url'] }}">{{ $menu['name'] }}</a>
                      @endif
                      <div class="dropdown-menu">
                        @foreach ($menu['children'] as $child)
                          @if ($child['name'])
                            <a class="dropdown-item" href="{{ $child['url'] }}">{{ $child['name'] }}</a>
                          @endif
                        @endforeach
                      </div>
                    </div>
                  </li>
                @else
                  @if ($menu['name'])
                    <li class="nav-item">
                      <a class="nav-link {{ (request()->route() && equal_url($menu['url'])) ? 'active' : '' }}"
                         href="{{ $menu['url'] }}">{{ $menu['name'] }}</a>
                    </li>
                  @endif
                @endif
              @endforeach
              @endhookupdate
            </ul>
          </nav>
        </div>
      </div>
      <div class="right">
        <button type="button" class="search-overlay-btn" aria-label="Search">
          <i class="bi bi-search"></i>
        </button>
        <div class="icons">
          <div class="item">
            <div class="dropdown account-icon">
              <a class="btn dropdown-toggle px-0" href="{{ front_route('account.index') }}">
                <img src="{{ asset('images/icons/account.svg') }}" class="img-fluid" alt="Account">
              </a>

              <div class="dropdown-menu dropdown-menu-end">
                @if ($customer)
                  <a href="{{ front_route('account.index') }}"
                     class="dropdown-item">{{ __('front/account.account') }}</a>
                  <a href="{{ front_route('account.orders.index') }}"
                     class="dropdown-item">{{ __('front/account.orders') }}</a>
                  <a href="{{ front_route('account.favorites.index') }}"
                     class="dropdown-item">{{ __('front/account.favorites') }}</a>
                  <a href="{{ front_route('account.logout') }}"
                     class="dropdown-item">{{ __('front/account.logout') }}</a>
                @else
                  <a href="{{ front_route('login.index') }}" class="dropdown-item">{{ __('front/common.login') }}</a>
                  <a href="{{ front_route('register.index') }}"
                     class="dropdown-item">{{ __('front/common.register') }}</a>
                @endif
              </div>
            </div>
          </div>
          <div class="item">
            <a href="{{ account_route('favorites.index') }}"><img src="{{ asset('images/icons/love.svg') }}" alt="Favorites"
                                                                  class="img-fluid"><span
                class="icon-quantity">{{ $favTotal }}</span></a>
          </div>
          <div class="item">
            <button type="button" class="header-cart-icon border-0 bg-transparent" data-bs-toggle="offcanvas"
               data-bs-target="#miniCart" aria-controls="miniCart">
              <img src="{{ asset('images/icons/cart.svg') }}" class="img-fluid" alt="Cart">
              <span class="icon-quantity">0</span>
            </button>
          </div>
          @hookinsert('layouts.header.cart.after')
        </div>
      </div>
    </div>
  </div>
  <div class="header-mobile">
    <div class="mb-icon" data-bs-toggle="offcanvas" data-bs-target="#mobile-menu-offcanvas"
         aria-controls="offcanvasExample">
      <i class="bi bi-list"></i>
    </div>

    <div class="logo">
      <a href="{{ front_route('home.index') }}" class="d-flex align-items-center text-decoration-none gap-1">
        @if ($frontLogo)
          <img src="{{ image_origin($frontLogo) }}" class="img-fluid" alt="{{ front_store_name() }}">
        @else
          <span class="d-inline-flex align-items-center" style="width:28px;height:28px;color:rgb(139,82,224);flex-shrink:0;">@include('components.logo-icon')</span>
          <span class="fw-bold" style="font-size:16px;color:#222;">InnoShop</span>
        @endif
      </a>
    </div>

    <a href="{{ front_route('carts.index') }}" class="header-cart-icon"><img src="{{ asset('images/icons/cart.svg') }}"
                                                                             class="img-fluid" alt="Cart"><span
        class="icon-quantity">0</span></a>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobile-menu-offcanvas">
      <div class="offcanvas-header">
        <form action="{{ front_route('products.index') }}" method="get" class="search-group">
          <input type="text" class="form-control" name="keyword" placeholder="{{ __('common/base.search') }}"
                 value="{{ request('keyword') }}">
          <button type="submit" class="btn"><i class="bi bi-search"></i></button>
        </form>
        <a class="account-icon" href="{{ front_route('account.index') }}">
          <img src="{{ asset('images/icons/account.svg') }}" class="img-fluid" alt="Account">
        </a>
      </div>
      <div class="close-offcanvas" data-bs-dismiss="offcanvas"><i class="bi bi-chevron-compact-left"></i></div>
      <div class="offcanvas-body mobile-menu-wrap">
        <div class="accordion accordion-flush" id="menu-accordion">
          <div class="accordion-item">
            <div class="nav-item-text">
              <a class="nav-link {{ (request()->route() && equal_route_name('home.index')) ? 'active' : '' }}" aria-current="page"
                 href="{{ front_route('home.index') }}">{{ __('front/common.home') }}</a>
            </div>
          </div>

          @hookupdate('layouts.header.menu.mobile')
          @foreach ($headerMenus as $key => $menu)
            @if ($menu['name'])
              <div class="accordion-item">
                <div class="nav-item-text">
                  <a class="nav-link" href="{{ $menu['url'] }}"
                     data-bs-toggle="{{ !$menu['url'] ? 'collapse' : '' }}">
                    {{ $menu['name'] }}
                  </a>
                  @if (isset($menu['children']) && $menu['children'])
                    <span class="collapsed" data-bs-toggle="collapse"
                          data-bs-target="#flush-menu-{{ $key }}"><i class="bi bi-chevron-down"></i></span>
                  @endif
                </div>

                @if (isset($menu['children']) && $menu['children'])
                  <div class="accordion-collapse collapse" id="flush-menu-{{ $key }}"
                       data-bs-parent="#menu-accordion">
                    <div class="children-group">
                      <ul class="nav flex-column ul-children">
                        @foreach ($menu['children'] as $c_key => $child)
                          @if ($child['name'])
                            <li class="nav-item">
                              <a class="nav-link" href="{{ $child['url'] }}">{{ $child['name'] }}</a>
                            </li>
                          @endif
                        @endforeach
                      </ul>
                    </div>
                  </div>
                @endif
              </div>
            @endif
          @endforeach
          @endhookupdate

        </div>
      </div>
    </div>
  </div>
</header>

@hookinsert('layout.header.bottom')

<div class="search-overlay" id="searchOverlay">
  <div class="search-overlay__backdrop"></div>
  <div class="search-overlay__content">
    <button type="button" class="search-overlay__close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    <form action="{{ front_route('products.index') }}" method="get" class="search-overlay__form">
      <div class="search-overlay__input-wrap">
        <i class="bi bi-search"></i>
        <input type="text" class="form-control" name="keyword" placeholder="{{ __('common/base.search') }}"
               value="{{ request('keyword') }}" autofocus>
      </div>
    </form>
  </div>
</div>

@if(!empty($announcementItems))
@push('footer')
<script>
  if (typeof Swiper !== 'undefined') {
    new Swiper('#announcementBarSwiper', {
      direction: 'vertical',
      loop: true,
      autoplay: { delay: 3500, disableOnInteraction: false },
      allowTouchMove: false,
      speed: 500,
    });
  }
</script>
@endpush
@endif