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
          <div class="mb-3 col-12 col-md-6">
            <label class="form-label">{{ __('panel/article.title') }}</label>
            <x-common-form-locale-input
              name="name"
              :translations="locale_field_data($tag, 'name')"
              :required="true"
              :label="__('panel/article.title')"
              :placeholder="__('panel/article.title')"
            />
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
          <x-common-form-input title="{{ __('common/base.position') }}" name="position" :value="old('position', $tag->position ?? 0)"
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
