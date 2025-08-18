@extends('panel::layouts.app')

@section('title', $plugin->getLocaleName())

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <div class="row mb-4 align-items-center pb-3 plugin-header-bar">
      <div class="col-auto">
        @if($plugin->getIconUrl())
          <img src="{{ $plugin->getIconUrl() }}" alt="logo" class="img-fluid rounded me-4 plugin-header-logo">
        @endif
      </div>
      <div class="col ps-0">
        <div class="fw-bold fs-4 mb-1">
          {{ $plugin->getLocaleName() }}
          @if($plugin->getVersion())
            <small class="text-muted ms-2">{{ $plugin->getVersion() }}</small>
          @endif
        </div>
        @if($plugin->getLocaleDescription())
          <div class="mb-2 text-secondary plugin-header-desc">{{ $plugin->getLocaleDescription() }}</div>
        @endif
        <div class="d-flex flex-wrap gap-3">
          <div class="text-secondary small">
            <i class="bi bi-person"></i> {{ __('panel/plugin.author') }}: {{ $plugin->getAuthorName() }}
            @if($plugin->getAuthorEmail())
              <span class="ms-2"><i class="bi bi-envelope"></i> {{ __('panel/plugin.email') }}: <a href="mailto:{{ $plugin->getAuthorEmail() }}" class="text-decoration-none">{{ $plugin->getAuthorEmail() }}</a></span>
            @endif
          </div>
          @if($plugin->getType())
            <div class="text-secondary small"><i class="bi bi-tag"></i> {{ __('panel/plugin.type') }}：{{ $plugin->getTypeFormat() }}</div>
          @endif
        </div>
      </div>
    </div>
    
    <ul class="nav nav-tabs mt-4" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#config-tab" role="tab">
          <i class="bi bi-gear"></i> {{ trans('panel/plugin.config_settings') }}
        </button>
      </li>
      @if($plugin->getReadmeHtml())
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#readme-tab" role="tab">
          <i class="bi bi-book"></i> {{ trans('panel/plugin.usage_documentation') }}
        </button>
      </li>
      @endif
    </ul>

    <!-- 选项卡内容 -->
    <div class="tab-content mt-3">
      <!-- 配置选项卡 -->
      <div class="tab-pane fade show active" id="config-tab" role="tabpanel">
        <form class="needs-validation" id="app-form" novalidate action="{{ panel_route('plugins.update', [$plugin->getCode()]) }}" method="POST">
          @csrf
          {{ method_field('put') }}
          <div class="row">
            <div class="col-12 col-md-8">
              @include('plugin::plugins.fields', ['fields' => $fields, 'errors' => $errors])
            </div>
          </div>
        </form>
      </div>

      <!-- 说明文档选项卡 -->
      @if($plugin->getReadmeHtml())
      <div class="tab-pane fade" id="readme-tab" role="tabpanel">
        <div class="markdown-body">
          {!! $plugin->getReadmeHtml() !!}
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('header')
  <link rel="stylesheet" href="{{ asset('vendor/github/github-markdown.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/github/highlight-github.min.css') }}">
  <style>
    .markdown-body {
      max-width: 900px;
      margin: 0 auto;
      padding: 24px;
      background: #fff;
      border-radius: 8px;
      font-size: 14px;
    }
    .plugin-header-bar {
      border-bottom: 1.5px solid #eee;
    }
    .plugin-header-logo {
      max-height: 100px;
      border: 1px solid #e9e9e9;
      box-sizing: border-box;
    }
    .plugin-header-desc {
      font-size: 15px;
    }
  </style>
@endpush
