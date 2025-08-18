<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ panel_route('home.index') }}">
  <title>{{ $plugin->getLocaleName() }} - InnoShop</title>
  <meta name="keywords" content="@yield('keywords', 'InnoShop, Innovative, Open Source, Laravel, Multi-language, Multi-currency, Hook, Plugin Architecture, Flexible, Powerful')">
  <meta name="generator" content="InnoShop {{ innoshop_version() }}">
  <meta name="asset" content="{{ asset('/') }}">
  <meta name="description" content="@yield('description', 'InnoShop')">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="api-token" content="{{ session('panel_api_token') }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">
  <link rel="stylesheet" href="{{ asset('vendor/element-ui/element-ui.css') }}">
  <link rel="stylesheet" href="{{ mix('build/panel/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/cropper.min.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/design/base.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/design/editor-unified.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/design/header.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/design/sidebar.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/design/preview.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/design/components.css') }}">
  <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/design/link-selector.css') }}">

  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/vuedraggable.umd.min.js') }}"></script>
  <script src="{{ asset('vendor/element-ui/element-ui.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script src="{{ mix('build/panel/js/app.js') }}"></script>
  <script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
  <script src="{{ plugin_asset('PageBuilder', 'js/vuedraggable.js') }}"></script>
  <script src="{{ plugin_asset('PageBuilder', 'js/cropper.min.js') }}"></script>
  
  @include('PageBuilder::design.scripts.app')
  
</head>

<body class="page-design">
  <div id="app" class="bg-light">

    @include('PageBuilder::design.layouts.header')

    <div class="design-box">
      @include('PageBuilder::design.layouts.sidebar')
      
      <div class="preview-iframe">
        <iframe src="{{ front_route('home.index') }}?design=1" frameborder="0" id="preview-iframe" width="100%" height="100%"></iframe>
      </div>
    </div>
  </div>

  {{-- Vue App --}}
  @include('PageBuilder::design.scripts.vue-app')

  {{-- Iframe Events --}}
  @include('PageBuilder::design.scripts.iframe-events')

  {{-- Module Editors --}}
  @include('PageBuilder::design.editors.slideshow')
  @include('PageBuilder::design.editors.card-slider')
  @include('PageBuilder::design.editors.single-image')
  @include('PageBuilder::design.editors.custom-products')
  @include('PageBuilder::design.editors.category-products')
  @include('PageBuilder::design.editors.latest-products')
  @include('PageBuilder::design.editors.four-image')
  @include('PageBuilder::design.editors.four-image-plus')
  @include('PageBuilder::design.editors.multi-row-images')
  @include('PageBuilder::design.editors.left-image-right-text')
  @include('PageBuilder::design.editors.article')
  @include('PageBuilder::design.editors.rich-text')
  @include('PageBuilder::design.editors.video')
  @include('PageBuilder::design.editors.image-text-list')
  @include('PageBuilder::design.editors.brands')
  @include('PageBuilder::design.editors.brand-products')
  
  {{-- Public Components --}}
  @include('PageBuilder::design.components.single-image-selector')
  @include('PageBuilder::design.components.multi-image-selector')
  @include('PageBuilder::design.components.link-selector')
  @include('PageBuilder::design.components.i18n')

</body>
</html>
