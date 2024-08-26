@extends('panel::layouts.app')
@section('body-class', 'account')

@section('title', __('panel/menu.account'))

<x-panel::form.right-btns />

@section('content')

<div class="card h-min-600">
  <div class="card-body">
    <form class="needs-validation w-max-500 m-auto mt-3" id="app-form" novalidate action="{{ panel_route('account.update') }}" method="POST">
      @csrf
      @method('put')

      <x-common-form-input title="{{ __('panel/common.name') }}" name="name" value="{{ old('name', $admin->name) }}" required />
      <x-common-form-input title="{{ __('panel/common.email') }}" name="email" value="{{ old('email', $admin->email) }}" required />
      <x-common-form-input title="{{ __('panel/common.password') }}" name="password" value="" type="password" />
    </form>
  </div>
</div>
@endsection

@push('footer')
<script>
</script>
@endpush