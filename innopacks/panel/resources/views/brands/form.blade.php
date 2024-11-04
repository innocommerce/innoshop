@extends('panel::layouts.app')

@section('title', __('panel/menu.brands'))

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/menu.brands') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $brand->id ? panel_route('brands.update', [$brand->id]) : panel_route('brands.store') }}"
      method="POST">
      @csrf
      @method($brand->id ? 'PUT' : 'POST')

      <x-common-form-input title="名称" name="name" value="{{ old('name', $brand->name) }}" required placeholder="名称" />
      <x-common-form-image title="图标" name="logo" value="{{ old('logo', $brand->logo) }}" required />
      <x-common-form-input title="首字母" name="first" value="{{ old('first', $brand->first) }}" required placeholder="首字母" />
      <x-common-form-input title="排序" name="position" value="{{ old('position', $brand->position) }}" placeholder="排序" />
      <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)" placeholder="{{ __('panel/common.whether_enable') }}"/>
      <x-common-form-input title="{{ __('panel/common.slug') }}" name="slug" value="{{ old('slug', $brand->slug) }}" placeholder="{{ __('panel/common.slug') }}" />

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script></script>
@endpush