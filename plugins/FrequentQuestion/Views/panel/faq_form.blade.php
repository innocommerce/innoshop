@extends('panel::layouts.app')

@section('title',  __('FrequentQuestion::common.faq') )
<x-panel::form.right-btns/>

@push('header')
  <script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

@section('content')
  <form class="needs-validation" novalidate id="app-form"
        action="{{ $faq->id ? panel_route('faqs.update', [$faq->id]) : panel_route('faqs.store') }}" method="POST">
    @csrf
    @method($faq->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-12 col-md-9">
        <div class="card mb-3">
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

                      <x-common-form-input title="{{ __('FrequentQuestion::common.question') }}"
                                           name="translations[{{$localeCode}}][question]" required
                                           value="{{ old('translations.' . $localeCode . '.question', $faq->translate($localeCode, 'question')) }}"/>

                      <x-panel::form.row title="{{ __('FrequentQuestion::common.answer') }}" required>
                        <div class="tab-content">
                          <div class="tab-pane fade show active" id="tab-answer-{{ $localeCode }}">
                            <textarea rows="4" type="text" name="translations[{{$localeCode}}][answer]"
                                      class="form-control"
                                      placeholder="{{ __('FrequentQuestion::common.content') }}">{{ old('translations.' . $localeCode . '.answer', $faq->translate($localeCode, 'answer')) }}</textarea>
                          </div>
                        </div>
                      </x-panel::form.row>

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
            <x-common-form-select title="FAQ分类" name="faq_category_id"  :emptyOption="false"
                                  :value="old('faq_category_id', $product->faq_category_id ?? 0)" :options="$categories"
                                  key="id" label="title"/>

            <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active"
                                        :value="old('active', $faq->active ?? true)"
                                        placeholder="{{ __('panel/common.whether_enable') }}"/>
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
