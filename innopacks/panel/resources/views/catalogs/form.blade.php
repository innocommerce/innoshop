@extends('panel::layouts.app')

@section('title', __('panel/menu.catalogs'))

<x-panel::form.right-btns />

@section('content')
  <form class="needs-validation" novalidate id="app-form"
    action="{{ $catalog->id ? panel_route('catalogs.update', [$catalog->id]) : panel_route('catalogs.store') }}"
    method="POST">
    @csrf
    @method($catalog->id ? 'PUT' : 'POST')

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
                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane"
                  type="button" role="tab" aria-controls="seo-tab-pane"
                  aria-selected="false">{{ __('panel/product.seo') }}</button>
              </li>
            </ul>

            <div class="tab-content" id="myTabContent">
              @include('panel::catalogs.panes.tab_pane_basic')
              @include('panel::catalogs.panes.tab_pane_seo')
            </div>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="d-none"></button>
  </form>
@endsection

@push('footer')
  <script></script>
@endpush
