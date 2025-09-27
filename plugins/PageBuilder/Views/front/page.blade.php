@extends('layouts.app')

@section('title', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getTitle())
@section('description', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getDescription())
@section('keywords', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getKeywords())

@push('header')
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
@endpush

@section('content')
  @if(empty($modules))
    <x-front-breadcrumb type="page" :value="$page" />
  @endif

  @hookinsert('page.show.top')

  @if(isset($modules) && $modules)
    <section class="module-content">
      @foreach ($modules as $module)
        @include("PageBuilder::front.modules.{$module['code']}", [
            'content' => $module['content'],
            'module_id' => $loop->index,
        ])
      @endforeach
    </section>
  @elseif(isset($result))
    {!! $result !!}
  @else
    <div class="page-service-content">
      <div class="container">
        <div class="row">
          {!! $page->translation->content !!}
        </div>
      </div>
    </div>
  @endif

  @hookinsert('page.show.bottom')
@endsection
