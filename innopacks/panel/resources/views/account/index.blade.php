@extends('panel::layouts.app')
@section('body-class', 'account')

@section('title', __('panel::menu.account'))

@section('content')
  {{ $admin->name }}, 你好。 修改个人信息页面, 待完善。
@endsection

@push('footer')
  <script>
  </script>
@endpush