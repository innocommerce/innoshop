@extends('panel::layouts.app')

@section('title', __('panel/menu.articles'))

<x-panel::form.right-btns />

@section('content')
  <form class="needs-validation" novalidate id="app-form"
    action="{{ $article->id ? panel_route('articles.update', [$article->id]) : panel_route('articles.store') }}"
    method="POST">
    @csrf
    @method($article->id ? 'PUT' : 'POST')

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
                      <div class="d-flex align-items-center wh-20">
                        <img src="{{ image_origin($locale->image) }}"
                          class="img-fluid {{ default_locale_class($locale->code) }}" alt="{{ $localeName }}">
                      </div>&nbsp;
                      {{ $localeName }}
                    </button>
                  </h2>
                  <div id="data-locale-{{ $localeCode }}"
                    class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#data-locales">
                    <div class="accordion-body">
                      <input name="translations[{{ $localeCode }}][locale]" value="{{ $localeCode }}"
                        class="d-none">

                      <x-common-form-input title="{{ __('panel/article.title') }}"
                        name="translations[{{ $localeCode }}][title]" :translate="true"
                        value="{{ old('translations.' . $localeCode . '.title', $article->translate($localeCode, 'title')) }}" />

                      <x-common-form-rich-text title="{{ __('panel/article.content') }}"
                        name="translations[{{ $localeCode }}][content]" :translate="true" elID="content-{{ $localeCode }}"
                        value="{{ old('translations.' . $localeCode . '.content', $article->translate($localeCode, 'content')) }}" />

                      <x-common-form-textarea title="{{ __('panel/article.summary') }}"
                        name="translations[{{ $localeCode }}][summary]"
                        value="{{ old('translations.' . $localeCode . '.summary', $article->translate($localeCode, 'summary')) }}"
                        :translate="true" column="article_summary"
                        description="{{ __('panel/article.summary_description') }}" />

                      <x-common-form-image title="{{ __('panel/article.image') }}"
                        name="translations[{{ $localeCode }}][image]"
                        value="{{ old('translations.' . $localeCode . '.image', $article->translate($localeCode, 'image')) }}" />

                      <x-common-form-input title="{{ __('panel/setting.meta_title') }}"
                        name="translations[{{ $localeCode }}][meta_title]"
                        value="{{ old('translations.' . $localeCode . '.meta_title', $article->translate($localeCode, 'meta_title')) }}"
                        :translate="true" column="article_title" :generate="true"
                        description="{{ __('panel/article.meta_title_description') }}" />

                      <x-common-form-textarea title="{{ __('panel/setting.meta_description') }}"
                        name="translations[{{ $localeCode }}][meta_description]"
                        value="{{ old('translations.' . $localeCode . '.meta_description', $article->translate($localeCode, 'meta_description')) }}"
                        :translate="true" column="article_description" :generate="true"
                        description="{{ __('panel/article.meta_description_description') }}" />

                      <x-common-form-textarea title="{{ __('panel/setting.meta_keywords') }}"
                        name="translations[{{ $localeCode }}][meta_keywords]"
                        value="{{ old('translations.' . $localeCode . '.meta_keywords', $article->translate($localeCode, 'meta_keywords')) }}"
                        :translate="true" column="article_keywords" :generate="true"
                        description="{{ __('panel/article.meta_keywords_description') }}" />
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
            <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active"
              :value="old('active', $article->active ?? true)" />

            <x-common-form-select title="{{ __('panel/article.catalog') }}" name="catalog_id" :value="old('catalog_id', $article->catalog_id ?? 0)"
              :options="$catalogs" key="id" label="name" :emptyOption="false" />

            <x-common-form-input title="{{ __('panel/common.slug') }}" name="slug" :value="old('slug', $article->slug ?? '')"
              placeholder="{{ __('panel/common.slug') }}" column="article_slug" :generate="true" />

            <x-panel-form-autocomplete-list name="tag_ids[]" :value="old('tag_ids', $article->tags->pluck('id')->toArray() ?? [])"
              placeholder="{{ __('panel/article.tag_search') }}" title="{{ __('panel/article.tag') }}"
              api="/api/panel/tags" />

            <x-common-form-input title="{{ __('panel/article.position') }}" name="position" :value="old('position', $article->position ?? 0)" />

            <x-common-form-input title="{{ __('panel/article.viewed') }}" name="viewed" :value="old('viewed', $article->viewed ?? 0)" />

            <x-common-form-input title="{{ __('panel/article.author') }}" name="author" :value="old('author', $article->author ?? '')" />
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="d-none"></button>
  </form>
@endsection
