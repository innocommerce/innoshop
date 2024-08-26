@extends('panel::layouts.app')

@section('title', __('panel/menu.categories'))

<x-panel::form.right-btns />

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $category->id ? panel_route('categories.update', [$category->id]) : panel_route('categories.store') }}"
  method="POST">
  @csrf
  @method($category->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('panel/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <div class="accordion accordion-flush locales-accordion" id="data-locales">
            @foreach (locales() as $locale)
            @php($localeCode = $locale->code)
            @php($localeName = $locale->name)
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                  data-bs-toggle="collapse" data-bs-target="#data-locale-{{ $localeCode }}"
                  aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="data-locale-{{ $localeCode }}">
                  <div class="wh-20 me-2">
                    <img src="{{ image_origin($locale->image) }}" class="img-fluid">
                  </div>
                  {{ $localeName }}
                </button>
              </h2>
              <div id="data-locale-{{ $localeCode }}"
                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#data-locales">
                <div class="accordion-body">
                  <input name="translations[{{$localeCode}}][locale]" value="{{$localeCode}}" class="d-none">

                  <x-common-form-input title="标题" name="translations[{{$localeCode}}][name]"
                    value="{{ old('translations.' . $localeCode . '.name', $category->translate($localeCode, 'name')) }}"
                    required placeholder="标题" />

                  <x-common-form-textarea title="内容" name="translations[{{$localeCode}}][content]"
                    value="{{ old('translations.' . $localeCode . '.content', $category->translate($localeCode, 'content')) }}"
                    placeholder="内容" />

                  <x-common-form-input title="Meta Title" name="translations[{{$localeCode}}][meta_title]"
                    value="{{ old('translations.' . $localeCode . '.meta_title', $category->translate($localeCode, 'meta_title')) }}"
                    placeholder="Meta Title" />

                  <x-common-form-input title="Meta Keywords" name="translations[{{$localeCode}}][meta_keywords]"
                    value="{{ old('translations.' . $localeCode . '.meta_keywords', $category->translate($localeCode, 'meta_keywords')) }}"
                    placeholder="Meta Keywords" />

                  <x-common-form-input title="Meta Description" name="translations[{{$localeCode}}][meta_description]"
                    value="{{ old('translations.' . $localeCode . '.meta_description', $category->translate($localeCode, 'meta_description')) }}"
                    placeholder="Meta Description" />
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="状态" name="active" :value="old('active', $category->active ?? true)"
            placeholder="状态" />
          <x-common-form-input title="{{ __('panel/common.slug') }}" name="slug" :value="old('slug', $category->slug ?? '')"
            placeholder="{{ __('panel/common.slug') }}" />
          <x-common-form-select title="上级分类" name="parent_id" :value="old('parent_id', $catalog->parent_id ?? 0)"
            :options="$categories" key="id" label="name" />
          <x-common-form-input title="排序" name="position" :value="old('position', $category->position ?? 0)"
            placeholder="排序" />
          <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active" :value="old('active', $category->active ?? true)"
            placeholder="{{ __('panel/common.whether_enable') }}" / </div>
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection

@push('footer')
<script></script>
@endpush