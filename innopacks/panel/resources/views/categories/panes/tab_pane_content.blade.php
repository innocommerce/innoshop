<div class="tab-pane fade mt-3" id="content-tab-pane" role="tabpanel" aria-labelledby="content-tab" tabindex="0">
  {{-- 多语言内容Tab导航 --}}
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

  {{-- 多语言内容Tab面板 --}}
  <div class="tab-content" id="locales-content-tabContent">
    @foreach (locales() as $locale)
      <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
        id="locale-{{ $locale->code }}-content-pane" role="tabpanel"
        aria-labelledby="locale-{{ $locale->code }}-content-tab" tabindex="0">

        {{-- 分类介绍 --}}
        <div class="mb-3">
          <label class="form-label">{{ panel_trans('category.summary') }}</label>
          <textarea rows="3" name="translations[{{ $locale->code }}][summary]" class="form-control"
            placeholder="{{ panel_trans('category.summary') }}">{{ old('translations.' . $locale->code . '.summary', $category->translate($locale->code, 'summary')) }}</textarea>
          <div class="mt-2 text-muted small">
            <i class="bi bi-info-circle me-1"></i>{{ panel_trans('category.summary_description') }}
          </div>
        </div>

        {{-- 分类详细描述 --}}
        <div class="mb-3">
          <label class="form-label">{{ panel_trans('category.content') }}</label>
          <x-common-form-rich-text name="translations[{{ $locale->code }}][content]"
                                   elID="content-{{ $locale->code }}"
                                   value="{{ old('translations.' . $locale->code . '.content', $category->translate($locale->code, 'content')) }}"
                                   placeholder="{{ panel_trans('category.content') }}"
                                   data-locale="{{ $locale->code }}"/>
          <div class="mt-2 text-muted small">
            <i class="bi bi-info-circle me-1"></i>{{ panel_trans('category.content_description') }}
          </div>
        </div>



      </div>
    @endforeach
  </div>
</div>