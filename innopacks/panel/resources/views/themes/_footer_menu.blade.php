<div class="tab-pane fade" id="tab-setting-footer-menu">
  <div class="row">
    <div class="col-3">
      <div class="card">
        <div class="card-header">{{ __('panel/menu.categories') }}</div>
        <div class="card-body hp-400 overflow-y-auto">
          @foreach ($categories as $item)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="menu_footer_categories[]"
                     value="{{ $item['id'] }}"
                     id="footer-category-{{ $item['id'] }}" {{ in_array($item['id'], old('menu_footer_categories', system_setting('menu_footer_categories', []) ?: [])) ? 'checked' : '' }}>
              <label class="form-check ps-0"
                     for="footer-category-{{ $item['id'] }}">{{ $item['name'] }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card">
        <div class="card-header">{{ __('panel/setting.catalogs') }}</div>
        <div class="card-body hp-400 overflow-y-auto">
          @foreach ($catalogs as $item)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="menu_footer_catalogs[]"
                     value="{{ $item->id }}"
                     id="footer-catalog-{{ $item->id }}" {{ in_array($item->id, old('menu_footer_catalogs', system_setting('menu_footer_catalogs', []) ?: [])) ? 'checked' : '' }}>
              <label class="form-check ps-0"
                     for="footer-catalog-{{ $item->id }}">{{ $item->title }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card">
        <div class="card-header">{{ __('panel/setting.page') }}</div>
        <div class="card-body hp-400 overflow-y-auto">
          @foreach ($pages as $item)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="menu_footer_pages[]"
                     value="{{ $item->id }}"
                     id="footer-page-{{ $item->id }}" {{ in_array($item->id, old('menu_footer_pages', system_setting('menu_footer_pages', []) ?: [])) ? 'checked' : '' }}>
              <label class="form-check ps-0"
                     for="footer-page-{{ $item->id }}">{{ $item->title }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card">
        <div class="card-header">{{ __('panel/setting.specials') }}</div>
        <div class="card-body hp-400 overflow-y-auto">
          @foreach ($specials as $item)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="menu_footer_specials[]"
                     value="{{ $item['type'] }}"
                     id="footer-page-{{ $item['type'] }}" {{ in_array($item['type'], old('menu_footer_specials', system_setting('menu_footer_specials', []) ?: [])) ? 'checked' : '' }}>
              <label class="form-check ps-0"
                     for="footer-page-{{ $item['type'] }}">{{ $item['title'] }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
