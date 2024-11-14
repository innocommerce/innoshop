@extends('panel::layouts.app')

@section('title', __('enterprise::file_manager.title'))

<x-panel::form.right-btns/>

@section('content')
  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('enterprise::file_manager.title') }}</h5>
    </div>
    <div class="card-body">
      <p>
        这里是文件管理器 /innopacks/enterprise/resources/views/file_manager/index.blade.php
      </p>
      <ul>
        <li></li>
      </ul>
    </div>
  </div>
@endsection

@push('footer')
  <script></script>
@endpush
