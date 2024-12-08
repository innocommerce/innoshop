@extends('layouts.app')

@section('body-class', 'page-home')

@push('header')
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
@endpush

@section('content')
  @hookinsert('home.content.top')

  <section class="module-content">
    @if(isset($modules) && $modules)
      @foreach($modules as $module)
        @include("WebBuilder::modules.{$module['code']}", [
          'content' => $module['content'],
          'module_id' => $loop->index
        ])
      @endforeach
    @endif
  </section>

  @hookinsert('home.content.bottom')
@endsection
