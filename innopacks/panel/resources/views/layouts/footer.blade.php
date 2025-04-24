<!-- Mobile Footer Navigation -->
<div class="mobile-footer d-lg-none fixed-bottom bg-white border-top">
  <div class="d-flex justify-content-around py-2">
    <!-- Market -->
    <div class="dropdown">
      <span class="d-flex flex-column align-items-center text-secondary" data-bs-toggle="dropdown">
        <i class="bi bi-grid fs-5"></i>
        <small>{{ __('panel/common.market') }}</small>
      </span>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ panel_route('plugin_market.index') }}">
          <i class="bi bi-puzzle me-2"></i>{{ __('panel/common.market_plugin') }}
        </a></li>
        <li><a class="dropdown-item" href="{{ panel_route('theme_market.index') }}">
          <i class="bi bi-palette me-2"></i>{{ __('panel/common.market_theme') }}
        </a></li>
      </ul>
    </div>

    <!-- Language -->
    <div class="dropdown">
      <span class="d-flex flex-column align-items-center text-secondary" data-bs-toggle="dropdown">
        <i class="bi bi-globe fs-5"></i>
        <small>{{ current_panel_locale()['name'] }}</small>
      </span>
      <ul class="dropdown-menu">
        @foreach (panel_locales() as $locale)
        <li>
          <a class="dropdown-item d-flex align-items-center" href="{{ panel_route('locale.switch', ['code'=> $locale['code']]) }}">
            <div class="wh-20 me-2"><img src="{{ image_origin($locale['image']) }}" class="img-fluid border"></div>
            {{ $locale['name'] }}
          </a>
        </li>
        @endforeach
      </ul>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
  if (window.innerWidth < 992) {
    document.querySelector('.container-fluid').style.marginBottom = '60px';
  }
});
</script>
