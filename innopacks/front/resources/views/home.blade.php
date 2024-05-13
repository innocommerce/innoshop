@extends('front::layouts.app')
@section('body-class', 'page-home')
@section('content')

  @push('header')
    <script src="{{ asset('vendor/aos/aos.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/aos/aos.css') }}">
  @endpush

  <div class="home-banner">
    <div class="home-banner-info">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <div class="home-banner-left">
              <h1 data-aos="fade-up" data-aos-duration="1000">InnoShop</h1>
              <p class="sub-title" data-aos="fade-up" data-aos-duration="1500">轻量级
                <span class="text-primary">企业官网CMS</span></p>
              <p class="sub-title-2" data-aos="fade-up" data-aos-duration="1800">
                - 打造企业官网，从未如此简单！<br/>
                - 轻量级CMS，专为快速开发和上线设计，从构想到现实，仅需几步!<br/>
                - 易用性与高效性并存，让您的团队轻松上手，快速掌握。<br>
              </p>
              <div data-aos="fade-up" data-aos-duration="2000" class="left-btn">
                <button type="button" class="btn btn-lg btn-primary">立即探索</button>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="home-banner-right">
              <img src="{{ asset('images/cms/home/top-bg-4.png') }}" class="img-fluid" data-aos="fade-up" data-aos-duration="2000">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="bottom-bg">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280 140" preserveAspectRatio="none"><path d="M320 28c320 0 320 84 640 84 160 0 240-21 320-42v70H0V70c80-21 160-42 320-42z"></path></svg>
    </div>
  </div>

  <div class="home-business">
    <div class="container home-business-container">
      <div class="business-top">
        <div class="module-title" data-aos="fade-up">我们的产品</div>
        <div class="module-sub-title" data-aos="fade-up">
          InnoShop 是一款专为企业官网快速建站而设计的轻量级内容管理系统（CMS）。它以其简洁、高效、易用的特性，帮助企业快速搭建起专业、美观且功能齐全的官方网站。InnoShop 旨在提供一个稳定而灵活的平台，让企业能够轻松管理网站内容，同时保持网站界面的现代化和用户友好性。
        </div>
      </div>
      <div class="business-info">
        <div class="row">
          <div class="col-12 col-md-3">
            <div data-aos="fade-up" class="business-item">
              <div class="icon"><i class="bi bi-boxes"></i></div>
              <div class="title">快速建站</div>
              <div class="sub-title">
                InnoShop 提供了一套完整的网站模板和定制选项，企业可以根据自己的品牌形象和需求，快速搭建起一个全新的官网。
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3">
            <div data-aos="fade-up" class="business-item" data-aos-duration="600">
              <div class="icon"><i class="bi bi-bounding-box-circles"></i></div>
              <div class="title">轻量级架构</div>
              <div class="sub-title">
                系统设计注重性能优化，确保网站加载速度快，用户体验流畅，尤其适合对速度和性能有较高要求的企业。
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3">
            <div data-aos="fade-up" class="business-item" data-aos-duration="1200">
              <div class="icon"><i class="bi bi-graph-up-arrow"></i></div>
              <div class="title">易于管理</div>
              <div class="sub-title">
                直观的后台管理界面，让非技术人员也能轻松上手，进行内容更新和管理。
              </div>
            </div>
          </div>
          <div class="col-12 col-md-3">
            <div data-aos="fade-up" class="business-item" data-aos-duration="1800">
              <div class="icon"><i class="bi bi-pencil-square"></i></div>
              <div class="title">高度可定制</div>
              <div class="sub-title">
                提供丰富的插件和扩展，企业可以根据业务发展需要，灵活添加或调整网站功能。
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="home-contact">
    <div class="container">
      <div class="title" data-aos="fade-up">如果您需要与我们取得联系, 以下是我们的联系方式</div>
      <div class="contact-icon">
        <img src="{{ asset('images/front/home/home-3.png') }}" class="img-fluid" data-aos="fade-up">
      </div>
      <div class="row">
        <div class="col-12 col-lg-4">
          <div class="contact-item" data-aos="fade-up">
            <div class="icon"><i class="bi bi-telephone-fill"></i></div>
            <div class="right">
              <div class="text-1">联系电话</div>
              <div class="text-2">
                <a href="tel:17828469818"><i class="bi bi-telephone-fill text-primary"></i> 17828469818</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="contact-item" data-aos="fade-up">
            <div class="icon"><i class="bi bi-envelope-fill"></i></div>
            <div class="right">
              <div class="text-1">联系邮箱</div>
              <div class="text-2">
                <a href="mailto:team@innoshop.com"><i class="bi bi-envelope-fill text-primary"></i> team@innoshop.com</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="contact-item wechat-box" data-aos="fade-up">
            <div class="icon"><i class="bi bi-wechat"></i></div>
            <div class="right">
              <div class="text-1">微信联系</div>
              <div class="text-2"><i class="bi bi-wechat text-primary"></i> innoshop666</div>
              <div class="w-code">
                <img src="{{ asset('images/front/home/w-code.png') }}" class="img-fluid">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('footer')
  <script>
    AOS.init({
      duration: 300,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });

    $(".home-banner .left-btn button").click(function () {
      $('html, body').animate({
        scrollTop: $(".home-business").offset().top - 100
      }, 200);
    });
  </script>
@endpush