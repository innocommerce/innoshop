<div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">

  {{-- 标题 (多语言) --}}
  <div class="mb-3 col-12 col-md-6">
    <label class="form-label">{{ __('panel/article.title') }}</label>
    <x-common-form-locale-input
      name="title"
      :translations="locale_field_data($catalog, 'title')"
      :required="true"
      :label="__('panel/article.title')"
      :placeholder="__('panel/article.title')"
    />
  </div>

  {{-- 摘要 (多语言) --}}
  <div class="mb-3 col-12 col-md-6">
    <label class="form-label">{{ __('panel/common.summary') }}</label>
    <x-common-form-locale-input
      name="summary"
      type="textarea"
      :translations="locale_field_data($catalog, 'summary')"
      :label="__('panel/common.summary')"
      :placeholder="__('panel/common.summary')"
    />
  </div>

  {{-- 上级分类 + 排序 --}}
  <div class="row mb-3">
    <div class="col-12 col-md-3">
      <x-common-form-select title="{{ __('panel/catalog.parent') }}" name="parent_id"
        :value="old('parent_id', $catalog->parent_id ?? 0)"
        :options="$catalogs" key="id" label="name" :emptyOption="false" />
    </div>
    <div class="col-12 col-md-3">
      <x-common-form-input title="{{ __('common/base.position') }}" name="position"
        :value="old('position', $catalog->position ?? 0)"
        placeholder="{{ __('common/base.position') }}" />
    </div>
  </div>

  {{-- 启用状态 --}}
  <div class="mb-3 col-12 col-md-6">
    <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active"
      :value="old('active', $catalog->active ?? true)" />
  </div>

</div>
