<div class="tab-pane fade show active mt-3" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab" tabindex="0">

  <div class="mb-3 col-12 col-md-6">
    <div class="mb-1 fs-6">{{ __('panel/article.title') }}</div>
    <x-common-form-locale-input
      name="title"
      :translations="locale_field_data($article, 'title')"
      type="input"
      :required="true"
      :label="__('panel/article.title')"
      :placeholder="__('panel/article.title')"
    />
    <div class="mt-1 text-muted small">
      <i class="bi bi-info-circle me-1"></i>{{ __('panel/article.title_required_hint') }}
    </div>
  </div>

  {{-- 主图：支持图片或视频，视频上传后自动抽取稳定一帧作为封面（共享组件 type="file"） --}}
  <div class="mb-3 col-12 col-md-8">
    <x-common-form-image title="{{ __('panel/article.main_image') }}" name="image"
                         value="{{ old('image', $article->getRawOriginal('image') ?? '') }}"
                         type="file"
                         :description="__('panel/article.main_image_description')"/>
  </div>

  <x-common-form-switch-radio :title="__('panel/common.whether_enable')" name="active"
                              :value="old('active', $article->active ?? true)" />

</div>

@hookinsert('panel.article.edit.basic.bottom')
