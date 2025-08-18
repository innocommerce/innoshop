@extends('layouts.app')

@section('body-class', 'page-home device-' . ($device ?? 'pc'))

@push('header')
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
  @if (request()->get('design'))
    <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/front/design-mode.css') }}">
  @endif
@endpush

@section('content')
  <div class="m-0 p-0" id="appContent">
    <section class="module-content">
      <div id="home-modules-box" class="modules-box">
        @if (!empty($modules))
          @foreach ($modules as $module)
            <section id="module-{{ $module['module_id'] ?? $loop->index }}" class="module-item {{ request()->get('design') ? 'module-item-design' : '' }}" data-module-id="{{ $module['module_id'] ?? $loop->index }}">
              
              @include('PageBuilder::front.partials.module-edit-buttons', ['module' => $module])
              
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
            <p>暂无模块数据</p>
          </div>
        @endif
      </div>
    </section>
  </div>

@endsection
