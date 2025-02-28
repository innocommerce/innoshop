@extends('panel::layouts.app')
@section('body-class', 'page-product-form')

@section('title', 'AI 智绘')

@section('content')
  <iframe src="{{ plugin_setting('image_bot','ai_url') }}" width="100%" height="800px"></iframe>
@endsection

@push('footer')
  <script>
  </script>
@endpush
