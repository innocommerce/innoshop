@extends('panel::layouts.app')

@section('title', __('panel::menu.catalog'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <form class="needs-validation" novalidate
            action="{{ $catalog->id ? panel_route('catalogs.update', [$catalog->id]) : panel_route('catalogs.store') }}"
            method="POST">
        @csrf
        @method($catalog->id ? 'PUT' : 'POST')

        <ul class="nav nav-tabs mb-4" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-data" type="button">分类内容
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
                                          value="{{ old('translations.' . $localeCode . '.title', $catalog->translate($localeCode, 'title')) }}"
                                          required placeholder="标题"/>

                      <x-panel-form-input title="摘要说明" name="translations[{{$localeCode}}][summary]"
                                          value="{{ old('translations.' . $localeCode . '.summary', $catalog->translate($localeCode, 'summary')) }}"
                                          placeholder="摘要说明"/>

                      <x-panel-form-input title="Meta Title" name="translations[{{$localeCode}}][meta_title]"
                                          value="{{ old('translations.' . $localeCode . '.meta_title', $catalog->translate($localeCode, 'meta_title')) }}"
                                          placeholder="Meta Title"/>

                      <x-panel-form-input title="Meta Keywords" name="translations[{{$localeCode}}][meta_keywords]"
                                          value="{{ old('translations.' . $localeCode . '.meta_keywords', $catalog->translate($localeCode, 'meta_keywords')) }}"
                                          placeholder="Meta Keywords"/>

                      <x-panel-form-input title="Meta Description" name="translations[{{$localeCode}}][meta_description]"
                                          value="{{ old('translations.' . $localeCode . '.meta_description', $catalog->translate($localeCode, 'meta_description')) }}"
                                          placeholder="Meta Description"/>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div class="tab-pane fade" id="tab-info">
            <x-panel-form-select title="上级分类" name="parent_id" :value="old('parent_id', $catalog->parent_id ?? 0)"
                                :options="$catalogs" key="id" label="name" />
            <x-panel-form-input title="SEO 别名" name="slug" :value="old('slug', $catalog->slug ?? '')"
                                placeholder="SEO 别名" />
            <x-panel-form-input title="分类排序" name="position" :value="old('position', $catalog->position ?? 0)"
                                placeholder="文章排序"/>
            <x-panel-form-switch-radio title="是否启用" name="active" :value="old('active', $catalog->active ?? true)"
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