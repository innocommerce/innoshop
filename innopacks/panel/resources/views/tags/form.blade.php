@extends('panel::layouts.app')

@section('title', __('panel/menu.tags'))

<x-panel::form.right-btns />

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $tag->id ? panel_route('tags.update', [$tag->id]) : panel_route('tags.store') }}" method="POST">
  @csrf
  @method($tag->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3 h-min-400">
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

                  <x-common-form-input title="{{ __('panel/article.title') }}" name="translations[{{$localeCode}}][name]"
                    value="{{ old('translations.' . $localeCode . '.name', $tag->translate($localeCode, 'name')) }}"
                    required />

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
          <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active" :value="old('active', $tag->active ?? true)"
            placeholder="{{ __('panel/common.whether_enable') }}" />
          <x-common-form-input title="{{ __('panel/common.slug') }}" name="slug" :value="old('slug', $tag->slug ?? '')" placeholder="{{ __('panel/common.slug') }}" />
          <x-common-form-input title="{{ __('panel/common.position') }}" name="position" :value="old('position', $tag->position ?? 0)"
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