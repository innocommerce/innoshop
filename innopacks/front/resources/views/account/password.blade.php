@extends('layouts.app')
@section('body-class', 'page-edit')

@section('content')
  <x-front-breadcrumb type="route" value="account.password.index" title="{{ __('front/account.password') }}"/>

  @hookinsert('account.password.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box addresses-box">
          @if (session()->has('errors'))
            <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4"/>
          @endif
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4" />
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/password.password') }} </span>
          </div>

          <form action="{{ account_route('password.update') }}" class="needs-validation" novalidate method="POST">
            @csrf
            @method('PUT')

            <x-common-form-input name="old_password" title="{{ __('front/password.old_password') }}" value="" type="password" required="required" placeholder="{{ __('front/password.old_password') }}" />
            <x-common-form-input name="new_password" title="{{ __('front/password.new_password') }}" value="" type="password" required="required" placeholder="{{ __('front/password.new_password') }}" />
            <x-common-form-input name="new_password_confirmation" title="{{ __('front/password.confirm_password') }}" value="" type="password" required="required" placeholder="{{ __('front/password.confirm_password') }}" />

            <button type="submit" class="btn btn-primary btn-lg mt-4 submit-form w-50">{{ __('front/common.submit') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.password.bottom')

@endsection

@push('footer')
  <script></script>
@endpush