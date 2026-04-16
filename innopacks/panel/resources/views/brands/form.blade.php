@extends('panel::layouts.app')

@section('title', __('panel/menu.brands'))

@push('header')
<script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

<x-panel::form.right-btns formid="app-form" />

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

      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane"
            type="button" role="tab" aria-controls="basic-tab-pane"
            aria-selected="true">{{ __('panel/common.basic_info') }}</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-tab-pane" type="button"
            role="tab" aria-controls="content-tab-pane" aria-selected="false">{{ __('panel/brand.content') }}</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button"
            role="tab" aria-controls="seo-tab-pane" aria-selected="false">{{ __('panel/product.seo') }}</button>
        </li>
        @hookinsert('panel.brand.edit.tab.nav.bottom')
      </ul>

      <div class="tab-content" id="myTabContent">
        @include('panel::brands.panes.tab_pane_basic', ['brand' => $brand])
        @include('panel::brands.panes.tab_pane_content', ['brand' => $brand])
        @include('panel::brands.panes.tab_pane_seo', ['brand' => $brand])
        @hookinsert('panel.brand.edit.tab.pane.bottom')
      </div>

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script></script>
@endpush
