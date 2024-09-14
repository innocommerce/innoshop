@hookinsert('layout.footer.top')

<footer id="appFooter">
  <div class="footer-box">
    <div class="container">
      <div class="footer-top-links">
        <div class="row">
          <div class="col-12 col-md-4 footer-item">
            <div class="about">
              <div class="footer-link-title">
                <span>About us</span>
                <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
              </div>
              <div class="about-text footer-item-content">
                <p>
                  <b>{{ system_setting_locale('meta_description') }}</b>
                </p>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-8">
            <div class="row">
              <div class="col-12 col-md-4 footer-item">
                <div class="footer-links">
                  <div class="footer-link-title">
                    <span>{{ __('front/common.products') }}</span>
                    <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
                  </div>
                  <ul class="footer-item-content">
                    @foreach($footer_menus['categories'] as $item)
                      <li><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></li>
                    @endforeach
                  </ul>
                </div>
              </div>
              <div class="col-12 col-md-4 footer-item">
                <div class="footer-links">
                  <div class="footer-link-title">
                    <span>{{ __('front/common.news') }}</span>
                    <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
                  </div>
                  <ul class="footer-item-content">
                    @foreach($footer_menus['catalogs'] as $item)
                      <li><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></li>
                    @endforeach
                  </ul>
                </div>
              </div>
              <div class="col-12 col-md-4 footer-item">
                <div class="footer-links">
                  <div class="footer-link-title">
                    <span>{{ __('front/common.pages') }}</span>
                    <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
                  </div>
                  <ul class="footer-item-content">
                    @foreach($footer_menus['pages'] as $item)
                      <li><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="bottom-box">
        <div class="row">
          <div class="col-md-6">
            <div class="left-links">
              Powered By <a href="https://www.innoshop.com" target="_blank">InnoShop</a>
              <!-- Powered By InnoShop {{ innoshop_version() }} -->
              <span class="copyright-text">
                <a href="{{ front_route('home.index') }}" class="ms-2" target="_blank">{{ config('app.name') }}</a>
                &copy; {{ date('Y') }} All Rights Reserved
                @if(system_setting('icp_number'))
                  <a href="https://beian.miit.gov.cn" class="ms-2" target="_blank">{{ system_setting('icp_number') }}</a>
                @endif
              </span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="payment-icon">
              <img src="{{ asset('images/demo/payment/1.png') }}" class="img-fluid">
              <img src="{{ asset('images/demo/payment/2.png') }}" class="img-fluid">
              <img src="{{ asset('images/demo/payment/3.png') }}" class="img-fluid">
              <img src="{{ asset('images/demo/payment/4.png') }}" class="img-fluid">
              <img src="{{ asset('images/demo/payment/5.png') }}" class="img-fluid">
            </div>
          </div>
      </div>
      </div>
    </div>
  </div>
</footer>

@hookinsert('layout.footer.bottom')

@if (system_setting('js_code'))
  {!! system_setting('js_code') !!}
@endif
