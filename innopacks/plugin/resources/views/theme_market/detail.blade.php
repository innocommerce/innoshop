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

        <div class="col-11 col-sm-12 col-md-9 col-lg-9 col-xl-10 mx-auto px-0">
          @if($product['success'])
            @php($product=$product['data'])
            <div class="container py-0" id="marketItemsContent">
              <div class="row gx-3 gx-lg-4 my-2">
                <div class="card mb-3 py-2 px-2 border-0 shadow-sm w-100">
                  <div class="row g-0">
                    <div class="col-md-3">
                      <img src="{{ $product['image_big'] }}" class="img-fluid w-75 rounded-4 shadow-lg d-block mx-auto"
                           alt="{{ $product['name'] }}">
                    </div>
                    <div class="col-md-9">
                      <div class="card-body px-0">
                        <h4 class="card-title">{{ $product['name'] }}</h4>
                        <div class="my-4">
                          @if($product['viewed'] ?? 0)
                            <span class="me-2"><small class="text-muted">{{ __('panel/plugin.views') }}: {{ $product['viewed'] }}</small></span>
                          @endif
                          @if($product['updated_at'] ?? '')
                            <span class="me-2e"><small class="text-muted">{{ __('panel/plugin.last_updated') }}: {{ $product['updated_at'] }}</small></span>
                          @endif
                        </div>

                        <p class="card-text mb-2">{{ $product['summary'] }}</p>

                        <div class="card bg-light px-2 px-md-4 py-2 my-2">
                          <div class="row my-2">
                            <div class="col">
                              <p class="card-title d-inline me-3">{{ __('panel/plugin.version') }}</p>
                              <p class="card-text d-inline">{{ $product['version'] ?? '' }}</p>
                            </div>
                          </div>
                          <div class="row my-2">
                            <div class="col">
                              <p class="card-title d-inline me-3 align-middle">{{ __('panel/plugin.price') }}</p>
                              @if($product['origin_price_format'] > $product['price_format'])
                                <span
                                    class="h4 text-decoration-line-through text-secondary d-inline align-middle">{{ $product['origin_price_format'] }}</span>
                              @endif
                              <p class="h4 text-danger d-inline align-middle">{{ $product['price_format'] }} </p>
                            </div>
                          </div>
                          <div class="row my-2">
                            <div class="col">
                              <p class="card-title d-inline me-3">{{ __('panel/plugin.author') }}</p>
                              <p class="card-text d-inline">{{ $product['seller_name'] }}</p>
                            </div>
                          </div>
                        </div>

                        @if($product['downloadable'])
                            <button class="btn btn-primary" type="button" id="btnDownload">立即下载</button>
                        @else
                          @include('plugin::shared._billing_method')
                          <button class="btn btn-danger" id="quickBuy" data-sku-id="{{$product['sku_id']}}"
                                  data-product-id="{{ $product['product_id'] }}" data-billing-method="">立即购买
                          </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="card shadow-sm border-0 p-2 p-md-4">
                  <h4 class="mb-4">插件简介</h4>
                  {!! $product['summary'] !!}
                </div>
              </div>
            </div>
          @endif
        </div>

      </div>
    </div>
  </div>
@endsection
@push('footer')
    <script>
        $(function () {
            $('#btnDownload').click(function (){
                axios({
                    method: "get",
                    url: '{{ panel_route('marketplaces.download', ['id' => $product['product_id'],'type'=>'theme']) }}',
                    onDownloadProgress : function (progressEvent) {

                    }
                })
                    .then(function (res) {
                        if (res.success){
                            layer.msg(res.message,{ icon:1 })
                        }
                    }).catch(function (err){
                    layer.msg(err.response.data.message,{ icon:2 })
                })
            })
        })
    </script>
@endpush
