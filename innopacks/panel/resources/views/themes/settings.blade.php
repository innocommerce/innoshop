@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.themes_settings'))

<x-panel::form.right-btns/>

@push('header')
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
@endpush

@section('content')
  @php
    $slideshowLinkTypeOptions = [
      ['value' => 'custom', 'label' => __('panel/setting.link_type_custom')],
      ['value' => 'product', 'label' => __('panel/setting.link_type_product')],
      ['value' => 'category', 'label' => __('panel/setting.link_type_category')],
      ['value' => 'brand', 'label' => __('panel/setting.link_type_brand')],
      ['value' => 'page', 'label' => __('panel/setting.link_type_page')],
      ['value' => 'article', 'label' => __('panel/setting.link_type_article')],
      ['value' => 'catalog', 'label' => __('panel/setting.link_type_catalog')],
    ];
  @endphp
  <form class="needs-validation" novalidate action="{{ panel_route('themes_settings.update') }}" method="POST"
        id="app-form">
    @csrf
    @method('put')
    <div class="row">
      <div class="col-3">
        <div class="card h-min-600" id="setting-menu">
          <div class="card-header">{{ __('panel/menu.themes_settings') }}</div>
          <div class="card-body">
            <ul class="nav flex-column settings-nav">
              <a class="nav-link active" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-header-menu">{{ __('panel/setting.header_menu') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-footer-menu">{{ __('panel/setting.footer_menu') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-slideshow">{{ __('panel/setting.slideshow') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-hot-products">{{ __('panel/setting.hot_products') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-home-categories">{{ __('panel/setting.home_categories') }}</a>
              <a class="nav-link" href="#" data-bs-toggle="tab"
                 data-bs-target="#tab-setting-home-articles">{{ __('panel/setting.home_articles') }}</a>
              @hookinsert('panel.themes.settings.hot_products.tab')
            </ul>
          </div>
        </div>
      </div>
      <div class="col-9">
        <div class="card h-min-600">
          <div class="card-header setting-header">{{ __('panel/setting.header_menu') }}</div>
          <div class="card-body">
            <div class="tab-content">
              @include('panel::themes._header_menu')
              @include('panel::themes._footer_menu')
              @include('panel::themes._slideshow')
              @include('panel::themes._hot_products')
              @include('panel::themes._home_categories')
              @include('panel::themes._home_articles')

              @hookinsert('panel.themes.settings.hot_products.tab_content')
            </div>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="d-none"></button>
  </form>

  @once
    @if (empty(old('slideshow', system_setting('slideshow', []))))
      <div class="d-none" aria-hidden="true">
        <x-common-form-image title="" name="_slideshow_filemanager_asset_placeholder" value="" />
      </div>
    @endif
  @endonce
@endsection

@push('footer')
  <script>
    // ========== Shared variables ==========
    const countryCode = @json(old('country_code', system_setting('country_code')));
    const stateCode = @json(old('state_code', system_setting('state_code')));
    const locales = @json(locales());

    // Slideshow shared constants (used by _slideshow partial JS)
    const panelLinkEmpty = { type: 'page', value: '', entity_label: '', link: '', entity_image: '', entity_price: '' };
    const hfLinkTypeOptions = @json($slideshowLinkTypeOptions);
    const linkTypeSelectPlaceholder = @json(__('panel/setting.link_type'));
    const urlLabel = @json(__('panel/setting.slideshow_url_or_path'));
    const linkPickerHint = @json(__('panel/setting.slideshow_link_picker_hint'));
    const linkPickerPlaceholder = @json(__('panel/setting.slideshow_search_placeholder'));
    const linkPickerTitleTemplate = @json(__('panel/setting.slideshow_link_picker_title'));
    const linkChooseLabel = @json(__('panel/setting.slideshow_choose_target'));
    const linkChangeLabel = @json(__('panel/setting.slideshow_change_target'));
    const linkClearLabel = @json(__('panel/setting.slideshow_clear_target'));
    const slideTextHint = @json(__('panel/setting.slideshow_slide_text_hint'));
    const slideTitleLabel = @json(__('panel/setting.slideshow_slide_title'));
    const slideSubtitleLabel = @json(__('panel/setting.slideshow_slide_subtitle'));

    // ========== Tab header click handler ==========
    $('.settings-nav').on('click', 'a', function () {
      var text = $(this).text();
      $('.setting-header').text(text);
    });

    // Fix aria-hidden focus conflict on Bootstrap modals
    $(document).on('hide.bs.modal', '.modal', function () {
      if (this.contains(document.activeElement)) {
        document.activeElement.blur();
      }
    });

    // ========== Country / State ==========
    getCountries()
    if (countryCode) {
      getZones(countryCode)
    }

    $('select[name="country_code"]').on('change', function () {
      var countryId = $(this).val();
      getZones(countryId);
    });

    function getCountries() {
      axios.get('{{ front_route('countries.index') }}').then(function (res) {
        var countries = res.data;
        var countrySelect = $('select[name="country_code"]');
        countrySelect.empty();
        countrySelect.append('<option value="">请选择国家</option>');
        countries.forEach(function (country) {
          countrySelect.append('<option value="' + country.code + '"' + (country.code == countryCode ? ' selected' : '') + '>' + country.name + '</option>');
        });
      });
    }

    function getZones(countryId) {
      axios.get('{{ front_route('countries.index') }}/' + countryId).then(function (res) {
        var zones = res.data;
        var zoneSelect = $('select[name="state_code"]');
        zoneSelect.prop('disabled', false).empty();
        zoneSelect.append('<option value="">请选择省份</option>');
        zones.forEach(function (zone) {
          zoneSelect.append('<option value="' + zone.code + '"' + (zone.code == stateCode ? ' selected' : '') + '>' + zone.name + '</option>');
        });
      });
    }
  </script>
  @hookinsert('panel.themes.settings.script.after')
@endpush
