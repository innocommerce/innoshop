@extends('panel::layouts.app')
@section('body-class', 'page-plugins-market')

@section('title', __('panel/menu.theme_market'))

@section('content')
  @include('plugin::shared._token_info')
  
  @if(session('error') || isset($error))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>{{ __('panel/common.error') }}:</strong> {{ session('error') ?? $error ?? '' }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
      <div class="row">
        <div class="col-12">
          @if(isset($error))
            <div class="alert alert-danger" role="alert">
              {{ $error }}
            </div>
          @elseif(isset($product) && is_array($product) && isset($product['success']) && $product['success'])
            @php($product=$product['data'])

            <div class="card border rounded-3 shadow-sm mb-4" style="border-color: #e9ecef !important; border-width: 1.5px;">
              <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #6f42c1 0%, #d63384 100%);"></div>
              <div class="card-body p-4">
                <div class="row g-4">
                  <div class="col-md-4 text-center">
                    <img src="{{ $product['image_big'] ?? $product['image_small'] ?? '' }}" 
                         class="img-fluid rounded-3 shadow-sm w-100" 
                         style="max-height: 400px; object-fit: cover; border: 2px solid #e9ecef; padding: 10px;"
                         alt="{{ $product['name'] }}">
                  </div>
                  <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <h2 class="fw-bold mb-0">{{ $product['name'] }}</h2>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-4 mb-4">
                      @if($product['viewed'] ?? 0)
                        <span class="text-muted">
                          <i class="bi bi-eye me-1"></i>{{ __('panel/plugin.views') }}: <strong>{{ $product['viewed'] }}</strong>
                        </span>
                      @endif
                      @if($product['downloaded'] ?? 0)
                        <span class="text-muted">
                          <i class="bi bi-download me-1"></i>{{ __('panel/plugin.downloads') }}: <strong>{{ $product['downloaded'] }}</strong>
                        </span>
                      @endif
                      @if($product['updated_at'] ?? '')
                        <span class="text-muted">
                          <i class="bi bi-clock me-1"></i>{{ __('panel/plugin.last_updated') }}: <strong>{{ $product['updated_at'] }}</strong>
                        </span>
                      @endif
                    </div>

                    <p class="text-secondary mb-4 fs-6">{{ $product['summary'] ?? '' }}</p>

                    <div class="card bg-light border-0 p-4 mb-4">
                      <div class="row g-4">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <span class="text-muted d-block mb-1">{{ __('panel/plugin.version') }}</span>
                            <span class="fw-bold fs-5">{{ $product['version'] ?? 'N/A' }}</span>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="mb-3">
                            <span class="text-muted d-block mb-1">{{ __('panel/plugin.author') }}</span>
                            <div class="d-flex align-items-center">
                              @if($product['seller_avatar'] ?? '')
                                <img src="{{ $product['seller_avatar'] }}" class="rounded-circle me-2" style="width: 24px; height: 24px;" alt="{{ $product['seller_name'] }}">
                              @endif
                              <span class="fw-bold">{{ $product['seller_name'] ?? '' }}</span>
                            </div>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="border-top pt-3">
                            <span class="text-muted d-block mb-2">{{ __('panel/plugin.price') }}</span>
                            <div class="d-flex align-items-baseline gap-2">
                              @if(isset($product['origin_price']) && $product['origin_price'] > $product['price'])
                                <span class="text-decoration-line-through text-muted fs-6">{{ $product['origin_price_format'] ?? '' }}</span>
                              @endif
                              <span class="text-danger fw-bold" style="font-size: 2rem;">{{ $product['price_format'] ?? '' }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    @if($product['downloadable'] ?? false)
                      <button class="btn btn-primary btn-lg px-5" type="button" id="btnDownload" data-product-type="theme">
                        <i class="bi bi-download me-2"></i>{{ __('panel/plugin.download_now') }}
                      </button>
                    @else
                      @include('plugin::shared._billing_method')
                      <div class="mb-3">
                        <button class="btn btn-danger btn-lg px-5" id="quickBuy" data-sku-id="{{$product['sku_id']}}"
                                data-product-id="{{ $product['product_id'] }}" data-billing-method="">
                          <i class="bi bi-cart-plus me-2"></i>{{ __('panel/plugin.buy_now') }}
                        </button>
                      </div>
                      <div class="alert alert-warning border-0 mb-0" style="background-color: #fff3cd;">
                        <small class="d-block mb-1"><strong>{{ __('panel/plugin.purchase_notice') }}:</strong></small>
                        <small class="d-block mb-1">1. {{ __('panel/plugin.notice_multi_domain') }}</small>
                        <small class="d-block mb-1">2. {{ __('panel/plugin.notice_no_refund') }}</small>
                        <small class="d-block">3. {{ __('panel/plugin.notice_support_expiry') }}</small>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="card border rounded-3 shadow-sm" style="border-color: #e9ecef !important; border-width: 1.5px;">
              <div class="card-body p-4">
                <ul class="nav nav-tabs border-bottom mb-4" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="introduction-tab" data-bs-toggle="tab" data-bs-target="#introduction" type="button" role="tab" aria-controls="introduction" aria-selected="true">
                      <i class="bi bi-info-circle me-2"></i>{{ __('panel/plugin.plugin_introduction') }}
                    </button>
                  </li>
                  @if($product['content'] ?? '')
                    <li class="nav-item" role="presentation">
                      <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="false">
                        <i class="bi bi-file-text me-2"></i>{{ __('panel/plugin.details') }}
                      </button>
                    </li>
                  @endif
                </ul>
                <div class="tab-content">
                  <div class="tab-pane fade show active" id="introduction" role="tabpanel" aria-labelledby="introduction-tab">
                    <div class="text-secondary" style="line-height: 1.8;">
                      {!! $product['summary'] ?? '' !!}
                    </div>
                  </div>
                  @if($product['content'] ?? '')
                    <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                      <div class="text-secondary" style="line-height: 1.8;">
                        {!! $product['content'] ?? '' !!}
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @elseif(isset($product) && is_array($product) && isset($product['error']))
            <div class="alert alert-danger" role="alert">
              {{ $product['error'] }}
            </div>
          @else
            <div class="alert alert-warning" role="alert">
              {{ __('panel/common.product_not_found') }}
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
            @if(isset($product) && is_array($product) && isset($product['product_id']))
            $('#btnDownload').click(function (){
                axios({
                    method: "get",
                    url: '{{ panel_route('marketplaces.download', ['id' => $product['product_id'] ?? 0,'type'=>'theme']) }}',
                    onDownloadProgress : function (progressEvent) {

                    }
                })
                    .then(function (res) {
                        if (res && res.success){
                            layer.msg(res.message || '下载成功', { icon: 1 })
                        } else {
                            layer.msg(res?.message || '{{ __('panel/common.error') }}', { icon: 2 })
                        }
                    }).catch(function (err){
                    layer.msg(err.response?.data?.message || err.message || '{{ __('panel/common.error') }}', { icon: 2 })
                })
            })
            @endif
        })
    </script>
@endpush
