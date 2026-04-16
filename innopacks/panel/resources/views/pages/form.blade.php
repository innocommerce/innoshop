@extends('panel::layouts.app')

@section('title', __('panel/menu.pages'))

<x-panel::form.right-btns />

@push('header')
<script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $page->id ? panel_route('pages.update', [$page->id]) : panel_route('pages.store') }}" method="POST">
  @csrf
  @method($page->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12">
      <div class="card mb-3">
        <div class="card-body">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane"
                type="button" role="tab" aria-controls="basic-tab-pane"
                aria-selected="true">{{ __('panel/common.basic_info') }}</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-tab-pane"
                type="button" role="tab" aria-controls="content-tab-pane"
                aria-selected="false">{{ __('panel/article.content') }}</button>
            </li>

            <li class="nav-item" role="presentation">
              <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane"
                type="button" role="tab" aria-controls="seo-tab-pane"
                aria-selected="false">{{ __('panel/product.seo') }}</button>
            </li>
          </ul>

          <div class="tab-content" id="myTabContent">
            @include('panel::pages.panes.tab_pane_basic')
            @include('panel::pages.panes.tab_pane_content')
            @include('panel::pages.panes.tab_pane_seo')
          </div>
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
