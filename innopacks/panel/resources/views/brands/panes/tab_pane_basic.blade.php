<div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">

  {{-- 品牌名称多语言输入 --}}
  <div class="mb-3 col-12 col-md-6">
    <div class="mb-1 fs-6">{{ __('panel/brand.name') }}</div>
    <x-common-form-locale-input
      name="name"
      :translations="locale_field_data($brand, 'name')"
      type="input"
      :required="true"
      :label="__('panel/brand.name')"
      :placeholder="__('panel/brand.name')"
    />
  </div>

  {{-- Logo --}}
  <div class="mb-3 col-12 col-md-6">
    <x-common-form-image title="{{ __('panel/brand.logo') }}" name="logo"
      value="{{ old('logo', $brand->logo ?? '') }}" required />
  </div>

  {{-- 首字母 --}}
  <div class="mb-3 col-12 col-md-6">
    <x-common-form-input title="{{ __('panel/brand.first') }}" name="first"
      :value="old('first', $brand->first ?? '')" required
      placeholder="{{ __('panel/brand.first') }}" />
  </div>

  {{-- 排序 --}}
  <div class="mb-3 col-12 col-md-6">
    <x-common-form-input title="{{ __('common/base.position') }}" name="position"
      :value="old('position', $brand->position ?? 0)"
      placeholder="{{ __('common/base.position') }}" />
  </div>

  {{-- 启用状态 --}}
  <div class="mb-3 col-12 col-md-6">
    <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active"
      :value="old('active', $brand->active ?? true)"
      placeholder="{{ __('panel/common.whether_enable') }}" />
  </div>
</div>
