@extends('panel::layouts.app')
@section('body-class', 'theme')

@section('title', __('panel/menu.themes'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    @if($themes)
      <div class="themes-wrap">
        <div class="row">
          @foreach($themes as $theme)
          <div class="col-6 col-lg-4 mb-3">
            <div class="card rounded-3 themes-item overflow-hidden shadow-sm h-100">
              <div class="image border-bottom"><img src="{{ theme_image($theme['code'], 'images/preview.jpg', 700, 500) }}" class="img-fluid"></div>
              <div class="d-flex justify-content-between align-items-center p-3">
                <span class="fw-bold">{{ $theme['name'] }}</span>
                <span>@include('panel::shared.list_switch', ['value' => $theme['value'] ?? 0, 'url' => panel_route('themes.active', $theme['code']), 'reload' => true])</span>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    @else
    <x-common-no-data :text="__('panel/themes.no_custom_theme')" />
    @endif
  </div>
</div>
@endsection
