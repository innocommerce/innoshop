<div class="header-box">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="logo">
      <h1 class="mb-0">
        <a href="{{ route('home.index') }}">
          <img src="{{ image_origin(system_setting('front_logo', 'images/logo.svg')) }}" class="img-fluid">
        </a>
      </h1>
    </div>
    <div class="header-menu">
      <nav class="navbar navbar-expand-md navbar-light">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link {{ is_route_name('home.index') ? 'active' : '' }}" aria-current="page"
               href="{{ route('home.index') }}">首页</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ is_route_param('pages.show', ['slug'=>'products']) ? 'active' : '' }}"
               href="{{ url('products') }}">产品</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ is_route_param('pages.show', ['slug'=>'services']) ? 'active' : '' }}"
               href="{{ url('services') }}">服务</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ is_route_name('articles.index') ? 'active' : ''}}"
               href="{{ url('articles') }}">新闻</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ is_route_param('pages.show', ['slug'=>'about']) ? 'active' : '' }}"
               href="{{ url('about') }}">关于</a>
          </li>
        </ul>
      </nav>

      <div class="offcanvas offcanvas-start" tabindex="-1" id="mobile-menu-offcanvas">
        <div class="offcanvas-header">
          <div class="mb-logo"><img src="{{ asset('images/logo.svg') }}" class="img-fluid"></div>
        </div>
        <div class="close-offcanvas" data-bs-dismiss="offcanvas"><i class="bi bi-chevron-compact-left"></i></div>
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link {{ is_route_name('home.index') ? 'active' : '' }}" aria-current="page"
               href="{{ route('home.index') }}">首页</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ is_route_param('pages.show', ['slug'=>'products']) ? 'active' : '' }}"
               href="{{ url('products') }}">产品</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ is_route_param('pages.show', ['slug'=>'services']) ? 'active' : '' }}"
               href="{{ url('services') }}">服务</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ is_route_name('articles.index') ? 'active' : ''}}"
               href="{{ url('articles') }}">新闻</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ is_route_param('pages.show', ['slug'=>'about']) ? 'active' : '' }}"
               href="{{ url('about') }}">关于</a>
          </li>
        </ul>
      </div>
      <div class="mb-icon" data-bs-toggle="offcanvas" data-bs-target="#mobile-menu-offcanvas"
           aria-controls="offcanvasExample"><i class="bi bi-list"></i></div>
    </div>
  </div>
</div>