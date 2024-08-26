<div class="header-box d-flex justify-content-between align-items-center">
  <div class="mb-menu d-lg-none"><i class="bi bi-list"></i></div>
  <div class="header-logo">
    <a href="{{ panel_route('home.index') }}" class="sidebar-logo">
      <img src="{{ image_origin(system_setting('panel_logo', 'images/logo-panel.png')) }}" class="img-fluid">
    </a>
  </div>
  <div class="d-flex justify-content-end right-tool">
    <div class="header-item dropdown d-flex align-items-center d-none d-lg-flex">
      <div class="wh-20 me-2"><img src="{{ image_origin('images/flag/'. panel_locale_code().'.png') }}" class="img-fluid"></div>
      <span class="">{{ current_panel_locale()['name'] }} <i class="bi bi-chevron-down"></i></span>
      <ul class="dropdown-menu">
        @foreach (panel_locales() as $locale)
        <li>
          <a class="dropdown-item d-flex" href="{{ panel_route('locale.switch', ['code'=> $locale['code']]) }}">
            <div class="wh-20 me-2"><img src="{{ image_origin($locale['image']) }}" class="img-fluid border"></div>
            {{ $locale['name'] }}
          </a>
        </li>
        @endforeach
      </ul>
    </div>
    <div class="header-item dropdown d-flex align-items-center">
      <div class="wh-40 rounded-circle overflow-hidden d-none d-lg-block">
        <img src="{{ image_resize() }}" class="img-fluid">
      </div>
      <span class="ms-2">{{ current_admin()->name }} <i class="bi bi-chevron-down"></i></span>

      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ front_route('home.index') }}" target="_blank">{{ __('panel/dashboard.frontend') }}</a></li>
        <li><a class="dropdown-item" href="{{ panel_route('account.index') }}">{{ __('panel/dashboard.profile') }}</a></li>
        <li><a class="dropdown-item" href="{{ panel_route('logout.index') }}">{{ __('panel/dashboard.sign_out') }}</a></li>
      </ul>
    </div>
  </div>
</div>