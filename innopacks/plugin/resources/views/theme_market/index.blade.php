@extends('panel::layouts.app')
@section('body-class', 'page-plugins-market')

@section('title', __('panel/menu.theme_market'))

@section('content')
  @include('plugin::shared._token_info')
  <div class="card h-min-600">
    <div class="card-body px-0 px-md-2">
      @include('plugin::theme_market._menu_top')

      <div class="row mx-auto">

        @include('plugin::theme_market._menu_sidebar')

        <div class="col-11 col-sm-12 col-md-8 col-lg-9 col-xl-10 mx-auto px-0">
          <div class="row gx-3 gx-lg-4 my-2" id="marketItemsContent">
            @foreach ($products['data']??[] as $product)
              <div class="col-6 col-md-4 col-lg-3 gy-4 my-3">
                @include('plugin::theme_market._item')
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
