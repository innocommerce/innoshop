<div class="tab-pane fade mt-3" id="content-tab-pane" role="tabpanel" aria-labelledby="content-tab" tabindex="0">
 
  <ul class="nav nav-tabs mb-4" id="locales-content-tab" role="tablist">
    @foreach (locales() as $locale)
      <li class="nav-item" role="presentation">
        <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="locale-{{ $locale->code }}-content-tab"
          data-bs-toggle="tab" data-bs-target="#locale-{{ $locale->code }}-content-pane" type="button"
          role="tab" aria-controls="locale-{{ $locale->code }}-content-pane"
          aria-selected="{{ $loop->first ? 'true' : 'false' }}">
          <img src="{{ image_origin($locale->image) }}" class="img-fluid me-2" style="width: 20px;">
          {{ $locale->name }}
        </button>
      </li>
    @endforeach
  </ul>

  <div class="tab-content" id="locales-content-tabContent">
    @foreach (locales() as $locale)
      <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
        id="locale-{{ $locale->code }}-content-pane" role="tabpanel"
        aria-labelledby="locale-{{ $locale->code }}-content-tab" tabindex="0">

        <div class="mb-3">
          <label class="form-label">{{ __('panel/common.summary') }}</label>
          <textarea rows="4" name="translations[{{ $locale->code }}][summary]" class="form-control"
            placeholder="{{ __('panel/common.summary') }}">{{ old('translations.' . $locale->code . '.summary', $article->translate($locale->code, 'summary')) }}</textarea>
          <div class="form-text">{{ __('panel/common.summary_description') }}</div>
        </div>

        <div class="mb-3">
          <label class="form-label">{{ __('panel/article.content') }}</label>
          <textarea rows="16" type="text" name="translations[{{ $locale->code }}][content]"
            class="tinymce" id="content-{{ $locale->code }}"
            @if(is_setting_locale($locale->code)) required @endif
            placeholder="{{ __('panel/article.content') }}">{{ old('translations.' . $locale->code . '.content', $article->translate($locale->code, 'content')) }}</textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">{{ __('panel/article.locale_image') }}</label>
          <x-common-form-image name="translations[{{ $locale->code }}][image]"
            value="{{ old('translations.' . $locale->code . '.image', $article->translate($locale->code, 'image')) }}" />
          <div class="form-text">{{ __('panel/article.locale_image_description') }}</div>
        </div>

      </div>
    @endforeach
  </div>
</div>

@hookinsert('panel.article.edit.content.bottom')