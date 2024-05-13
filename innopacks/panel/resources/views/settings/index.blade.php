@extends('panel::layouts.app')
@section('body-class', 'page-home')

@section('title', '系统设置')

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <form class="needs-validation" novalidate action="{{ panel_route('settings.update') }}" method="POST">
        @csrf
        @method('put')

        <x-panel-form-image title="前台 Logo" name="front_logo" value="{{ old('front_logo', system_setting('front_logo')) }}"/>

        <x-panel-form-image title="后台 Logo" name="panel_logo" value="{{ old('panel_logo', system_setting('panel_logo')) }}"/>

        <x-panel-form-image title="缺省图" name="placeholder" value="{{ old('placeholder', system_setting('placeholder')) }}"/>

        <x-panel-form-image title="浏览器小图标" name="favicon" value="{{ old('favicon', system_setting('favicon')) }}"/>

        <x-panel-form-input title="Meta 标题" name="meta_title"
                            value="{{ old('meta_title', system_setting('meta_title')) }}" required
                            placeholder="Meta 标题"/>

        <x-panel-form-input title="Meta 关键词" name="meta_keywords"
                            value="{{ old('meta_keywords', system_setting('meta_keywords')) }}"
                            placeholder="Meta 关键词"/>

        <x-panel-form-textarea title="Meta 描述" name="meta_description"
                               value="{{ old('meta_description', system_setting('meta_description')) }}"
                               placeholder="Meta 描述"/>

        <x-panel-form-input title="ICP备案号" name="icp_number" value="{{ old('icp_number', system_setting('icp_number')) }}"/>

        <x-panel-form-textarea title="第三方JS代码" name="js_code"
                               value="{{ old('js_code', system_setting('js_code')) }}"
                               placeholder="第三方JS代码"/>

        <x-panel::form.bottom-btns/>
      </form>
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush