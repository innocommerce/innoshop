<div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">
  {{-- 分类名称多语言输入 --}}
  <div class="mb-3 col-12 col-md-8">
    <div class="mb-1 fs-6">{{ panel_trans('category.name') }}</div>
    @foreach (locales() as $locale)
      @php($localeCode = $locale->code)
      @php($localeName = $locale->name)
      <div class="input-group mb-2">
        <div class="input-group-text">
          <div class="d-flex align-items-center wh-20">
            <img src="{{ image_origin($locale->image) }}"
                 class="img-fluid {{ default_locale_class($locale->code) }}"
                 alt="{{ $localeName }}">
          </div>
        </div>
        <input type="text" class="form-control" name="translations[{{ $localeCode }}][name]"
               value="{{ old('translations.' . $localeCode . '.name', $category->translate($localeCode, 'name')) }}"
               required placeholder="{{ panel_trans('category.name') }}" aria-label="{{ $localeName }}"
               aria-describedby="basic-addon1" data-locale="{{ $localeCode }}">
        <input type="hidden" name="translations[{{ $localeCode }}][locale]" value="{{ $localeCode }}">
      </div>
    @endforeach
    <div class="mt-1 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{{ panel_trans('category.name') }}{{ panel_trans('category.name_required') }}
    </div>
  </div>

  {{-- 主图片（统一，不区分语言） --}}
  <div class="mb-3 col-12 col-md-8">
    <x-common-form-image title="{{ panel_trans('category.image') }}" name="image"
                        value="{{ old('image', $category->image ?? '') }}"/>
    <div class="mt-2 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{{ panel_trans('category.image_description') }}
    </div>
  </div>

  {{-- 启用状态 --}}
  <div class="mb-3 col-12 col-md-8">
    <x-common-form-switch-radio title="{{ panel_trans('common.whether_enable') }}" name="active"
      :value="old('active', $category->active ?? true)" 
      placeholder="{{ panel_trans('common.whether_enable') }}" />
  </div>
</div>

@hookinsert('panel.category.edit.basic.bottom')