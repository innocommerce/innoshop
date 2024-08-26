@extends('panel::layouts.app')

@section('title', __('panel/menu.pages'))

<x-panel::form.right-btns />

@push('header')
<script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $page->id ? panel_route('pages.update', [$page->id]) : panel_route('pages.store') }}" method="POST">
  @csrf
  @method($page->id ? 'PUT' : 'POST')

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

                  <x-common-form-input title="{{ __('panel/article.title') }}" name="translations[{{$localeCode}}][title]"
                    value="{{ old('translations.' . $localeCode . '.title', $page->translate($localeCode, 'title')) }}"
                    required />

                  <x-panel::form.row title="{{ __('panel/article.content') }}" width="900">
                    <ul class="nav nav-tabs mb-3 code-tabs" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab"
                          data-bs-target="#tab-contentx-{{ $localeCode }}" type="button">{{ __('panel/article.content') }}
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-code-{{ $localeCode }}"
                          type="button">{{ __('panel/page.theme') }}</button>
                      </li>
                    </ul>

                    <div class="tab-content">
                      <div class="tab-pane fade show active" id="tab-contentx-{{ $localeCode }}">
                        <textarea rows="4" type="text" name="translations[{{$localeCode}}][content]" class="tinymce"
                          placeholder="{{ __('panel/article.content') }}">{{ old('translations.' . $localeCode . '.content', $page->translate($localeCode, 'content')) }}</textarea>
                      </div>
                      <div class="tab-pane fade show" id="tab-code-{{ $localeCode }}">
                        <x-panel-form-codemirror title="{{ __('panel/page.theme') }}" name="translations[{{$localeCode}}][template]"
                          value="{{ old('translations.' . $localeCode . '.template', $page->translate($localeCode, 'template')) }}"
                          required  />
                      </div>
                    </div>

                  </x-panel::form.row>

                  <x-common-form-input title="{{ __('panel/setting.meta_title') }}" name="translations[{{$localeCode}}][meta_title]"
                    value="{{ old('translations.' . $localeCode . '.meta_title', $page->translate($localeCode, 'meta_title')) }}"
                     />

                  <x-common-form-input title="{{ __('panel/setting.meta_keywords') }}" name="translations[{{$localeCode}}][meta_keywords]"
                    value="{{ old('translations.' . $localeCode . '.meta_keywords', $page->translate($localeCode, 'meta_keywords')) }}"
                     />

                  <x-common-form-input title="{{ __('panel/setting.meta_description') }}" name="translations[{{$localeCode}}][meta_description]"
                    value="{{ old('translations.' . $localeCode . '.meta_description', $page->translate($localeCode, 'meta_description')) }}"
                     />
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
          <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)"
            placeholder="{{ __('panel/common.whether_enable') }}" />
          <x-common-form-switch-radio title="{{ __('panel/common.show_breadcrumb') }}" name="show_breadcrumb" :value="old('show_breadcrumb', $page->show_breadcrumb ?? true)"
            placeholder="{{ __('panel/common.show_breadcrumb') }}" />
          <x-common-form-input title="{{ __('panel/common.slug') }}" name="slug" :value="old('slug', $page->slug ?? '')" placeholder="{{ __('panel/common.slug') }}" />
          <x-common-form-input title="{{ __('panel/article.viewed') }}" name="viewed" :value="old('viewed', $page->viewed ?? 0)"
            />
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection

@push('footer')
<script>
</script>
@endpush
