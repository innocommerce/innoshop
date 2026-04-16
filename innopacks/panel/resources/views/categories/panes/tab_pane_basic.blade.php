<div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">
  {{-- 分类名称多语言输入 --}}
  <div class="mb-3 col-12 col-md-6">
    <div class="mb-1 fs-6">{{ panel_trans('category.name') }}</div>
    <x-common-form-locale-input
      name="name"
      :translations="locale_field_data($category, 'name')"
      type="input"
      :required="true"
      :label="panel_trans('category.name')"
      :placeholder="panel_trans('category.name')"
    />
    <div class="mt-1 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{{ panel_trans('category.name') }}{{ panel_trans('category.name_required') }}
    </div>
  </div>

  {{-- 主图片（统一，不区分语言） --}}
  <div class="mb-3 col-12 col-md-6">
    <x-common-form-image title="{{ panel_trans('category.image') }}" name="image"
                        value="{{ old('image', $category->image ?? '') }}"/>
    <div class="mt-2 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{{ panel_trans('category.image_description') }}
    </div>
  </div>

  {{-- 启用状态 --}}
  <div class="mb-3 col-12 col-md-6">
    <x-common-form-switch-radio title="{{ panel_trans('common.whether_enable') }}" name="active"
      :value="old('active', $category->active ?? true)"
      placeholder="{{ panel_trans('common.whether_enable') }}" />
  </div>
</div>

@hookinsert('panel.category.edit.basic.bottom')
