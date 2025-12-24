@extends('layouts.app')

@section('body-class', 'page-home device-' . ($device ?? 'pc'))

@section('title', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getTitle())
@section('description', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getDescription())
@section('keywords', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getKeywords())

@push('header')
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/front/rich-text-responsive.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/front/multi-row-images-responsive.css') }}">
  @if (request()->get('design'))
    <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/front/design-mode.css') }}">
  @endif
@endpush

@section('content')
  @if(empty($modules) && !request()->get('design'))
    <x-front-breadcrumb type="page" :value="$page" />
  @endif

  @hookinsert('page.show.top')

  @php
    $isDesignMode = request()->get('design');
    $hasModules = !empty($modules);
  @endphp

  @if($isDesignMode || $hasModules)
    <div class="m-0 p-0" id="appContent">
      <section class="module-content">
        <div id="page-modules-box" class="modules-box">
          @if ($hasModules)
            @foreach ($modules as $module)
              <section id="module-{{ $module['module_id'] ?? $loop->index }}" class="module-item {{ $isDesignMode ? 'module-item-design' : '' }}" data-module-id="{{ $module['module_id'] ?? $loop->index }}">
                @if($isDesignMode)
                  @include('PageBuilder::front.partials.module-edit-buttons', ['module' => $module])
                @endif
                <div class="module-content">
                  @if (View::exists('PageBuilder::front.modules.' . $module['code']))
                    @include('PageBuilder::front.modules.' . $module['code'], [
                      'module' => $module,
                      'content' => $module['content'],
                      'module_id' => $module['module_id'] ?? $loop->index,
                    ])
                  @endif
                </div>
              </section>
            @endforeach
          @else
            <div class="text-center py-5">
              <p>{{ __('PageBuilder::modules.no_modules_data') }}</p>
            </div>
          @endif
        </div>
      </section>
    </div>
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
