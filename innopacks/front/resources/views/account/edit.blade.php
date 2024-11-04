@extends('layouts.app')
@section('body-class', 'page-edit')

@section('content')
  <x-front-breadcrumb type="route" value="account.edit.index" title="{{ __('front/account.edit') }}"/>

  @hookinsert('account.edit.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box addresses-box">
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4" />
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/edit.edit') }} </span>
          </div>

          <form class="needs-validation edit-form" action="{{ account_route('edit.index') }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <x-common-form-image name="avatar" title="{{ __('front/edit.avatar') }}" value="{{ old('avatar', $customer->avatar) }}" />
            <x-common-form-input name="name" title="{{ __('front/edit.name') }}" value="{{ old('name', $customer->name) }}" required="required" placeholder="{{ __('front/edit.name') }}" />
            <x-common-form-input name="email" title="{{ __('front/edit.email') }}" value="{{ old('email', $customer->email) }}" required="required" placeholder="{{ __('front/edit.email') }}" />

            <button type="submit" class="btn btn-primary btn-lg w-50">{{ __('front/common.submit') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.edit.bottom')

@endsection