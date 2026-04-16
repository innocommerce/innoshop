<div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">

  {{-- 标题 (多语言) --}}
  <div class="mb-3 col-12 col-md-6">
    <label class="form-label">{{ __('panel/article.title') }}</label>
    <x-common-form-locale-input
      name="title"
      :translations="locale_field_data($page, 'title')"
      type="input"
      :required="true"
      :label="__('panel/article.title')"
      :placeholder="__('panel/article.title')"
    />
  </div>

  {{-- 浏览量 --}}
  <div class="mb-3 col-12 col-md-6">
    <x-common-form-input title="{{ __('panel/article.viewed') }}" name="viewed"
      :value="old('viewed', $page->viewed ?? 0)" />
  </div>

  {{-- 面包屑 + 启用 --}}
  <div class="row mb-3">
    <div class="col-12 col-md-3">
      <x-common-form-switch-radio title="{{ __('panel/common.show_breadcrumb') }}" name="show_breadcrumb"
        :value="old('show_breadcrumb', $page->show_breadcrumb ?? true)" />
    </div>
    <div class="col-12 col-md-3">
      <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active"
        :value="old('active', $page->active ?? true)" />
    </div>
  </div>

</div>
