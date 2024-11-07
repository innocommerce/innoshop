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
            @if(isset($products['meta']) && $products['meta']['last_page'] > 1)
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item {{ $products['meta']['current_page'] <= 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $products['meta']['current_page'] - 1]) }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @for($i = 1; $i <= $products['meta']['last_page']; $i++)
                        <li class="page-item {{ $products['meta']['current_page'] == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">
                                {{ $i }}
                            </a>
                        </li>
                    @endfor

                    <li class="page-item {{ $products['meta']['current_page'] >= $products['meta']['last_page'] ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $products['meta']['current_page'] + 1]) }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
