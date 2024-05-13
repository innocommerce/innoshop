@extends('panel::layouts.app')

@section('title', __('panel::menu.page'))

@push('header')
<script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <form class="needs-validation" novalidate
            action="{{ $page->id ? panel_route('pages.update', [$page->id]) : panel_route('pages.store') }}"
            method="POST">
        @csrf
        @method($page->id ? 'PUT' : 'POST')

        <ul class="nav nav-tabs mb-4" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-data" type="button">单页内容
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-info" type="button">其他信息</button>
          </li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane fade show active" id="tab-data">
            <div class="accordion accordion-flush locales-accordion" id="data-locales">
              @foreach (locales() as $locale)
                @php($localeCode = $locale->code)
                @php($localeName = $locale->name)
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#data-locale-{{ $localeCode }}"
                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                            aria-controls="data-locale-{{ $localeCode }}">
                      <div class="wh-20 me-2">
                        <img src="{{ image_origin($locale->image) }}" class="img-fluid">
                      </div>
                      {{ $localeName }}
                    </button>
                  </h2>
                  <div id="data-locale-{{ $localeCode }}"
                       class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                       data-bs-parent="#data-locales">
                    <div class="accordion-body">
                      <input name="translations[{{$localeCode}}][locale]" value="{{$localeCode}}" class="d-none">

                      <x-panel-form-input title="标题" name="translations[{{$localeCode}}][title]"
                                          value="{{ old('translations.' . $localeCode . '.title', $page->translate($localeCode, 'title')) }}"
                                          required placeholder="标题"/>

                    <x-panel::form.row title="内容" width="900">
                      <ul class="nav nav-tabs mb-3 code-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-contentx-{{ $localeCode }}" type="button">内容
                          </button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-code-{{ $localeCode }}" type="button">模板</button>
                        </li>
                      </ul>

                      <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-contentx-{{ $localeCode }}">
                          <textarea rows="4" type="text" name="translations[{{$localeCode}}][content]" class="tinymce" placeholder="内容">{{ old('translations.' . $localeCode . '.content', $page->translate($localeCode, 'content')) }}</textarea>
                        </div>
                        <div class="tab-pane fade show" id="tab-code-{{ $localeCode }}">
                          <x-panel-form-codemirror title="模板" name="translations[{{$localeCode}}][template]"
                            value="{{ old('translations.' . $localeCode . '.template', $page->translate($localeCode, 'template')) }}"
                            required
                            placeholder="模板"/>
                        </div>
                      </div>

                    </x-panel::form.row>

                      <x-panel-form-input title="Meta Title" name="translations[{{$localeCode}}][meta_title]"
                                          value="{{ old('translations.' . $localeCode . '.meta_title', $page->translate($localeCode, 'meta_title')) }}"
                                          placeholder="Meta Title"/>

                      <x-panel-form-input title="Meta Keywords" name="translations[{{$localeCode}}][meta_keywords]"
                                          value="{{ old('translations.' . $localeCode . '.meta_keywords', $page->translate($localeCode, 'meta_keywords')) }}"
                                          placeholder="Meta Keywords"/>

                      <x-panel-form-input title="Meta Description" name="translations[{{$localeCode}}][meta_description]"
                                          value="{{ old('translations.' . $localeCode . '.meta_description', $page->translate($localeCode, 'meta_description')) }}"
                                          placeholder="Meta Description"/>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div class="tab-pane fade" id="tab-info">
            <x-panel-form-input title="SEO 别名" name="slug" :value="old('slug', $page->slug ?? '')"
                                placeholder="SEO 别名" />
            <x-panel-form-input title="浏览次数" name="viewed" :value="old('viewed', $page->viewed ?? 0)"
                                placeholder="浏览次数"/>
            <x-panel-form-switch-radio title="是否启用" name="active" :value="old('active', $page->active ?? true)"
              placeholder="是否启用"/>
          </div>
        </div>

        <x-panel::form.bottom-btns />
      </form>
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush