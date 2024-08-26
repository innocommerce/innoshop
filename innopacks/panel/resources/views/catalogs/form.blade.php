@extends('panel::layouts.app')

@section('title', __('panel/menu.catalogs'))

<x-panel::form.right-btns />

@section('content')
<form class="needs-validation" novalidate
      id="app-form"
      action="{{ $catalog->id ? panel_route('catalogs.update', [$catalog->id]) : panel_route('catalogs.store') }}"
      method="POST">
  @csrf
  @method($catalog->id ? 'PUT' : 'POST')

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

                    <x-common-form-input title="{{ __('panel/article.title') }}" name="translations[{{$localeCode}}][title]"
                                        value="{{ old('translations.' . $localeCode . '.title', $catalog->translate($localeCode, 'title')) }}"
                                        required />

                    <x-common-form-input title="{{ __('panel/article.summary') }}" name="translations[{{$localeCode}}][summary]"
                                        value="{{ old('translations.' . $localeCode . '.summary', $catalog->translate($localeCode, 'summary')) }}"
                                        />

                    <x-common-form-input title="{{ __('panel/setting.meta_title') }}" name="translations[{{$localeCode}}][meta_title]"
                                        value="{{ old('translations.' . $localeCode . '.meta_title', $catalog->translate($localeCode, 'meta_title')) }}"
                                        />

                    <x-common-form-input title="{{ __('panel/setting.meta_keywords') }}" name="translations[{{$localeCode}}][meta_keywords]"
                                        value="{{ old('translations.' . $localeCode . '.meta_keywords', $catalog->translate($localeCode, 'meta_keywords')) }}"
                                        />

                    <x-common-form-input title="{{ __('panel/setting.meta_description') }}" name="translations[{{$localeCode}}][meta_description]"
                                        value="{{ old('translations.' . $localeCode . '.meta_description', $catalog->translate($localeCode, 'meta_description')) }}"
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
          <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active" :value="old('active', $catalog->active ?? true)"
            />
          <x-common-form-select title="{{ __('panel/catalog.parent') }}" name="parent_id" :value="old('parent_id', $catalog->parent_id ?? 0)"
                              :options="$catalogs" key="id" label="name" />
          <x-common-form-input title="{{ __('panel/common.slug') }}" name="slug" :value="old('slug', $catalog->slug ?? '')"
                              placeholder="{{ __('panel/common.slug') }}" />
          <x-common-form-input title="{{ __('panel/common.position') }}" name="position" :value="old('position', $catalog->position ?? 0)"
                              />
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