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
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-4"/>
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/edit.edit') }} </span>
          </div>

          <form class="needs-validation edit-form" action="{{ account_route('edit.index') }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <x-common-form-imagep name="avatar" title="{{ __('front/edit.avatar') }}"
                                 value="{{ old('avatar', $customer->avatar) }}"/>
            <x-common-form-input name="name" title="{{ __('front/edit.name') }}"
                                 value="{{ old('name', $customer->name) }}" required="required"
                                 placeholder="{{ __('front/edit.name') }}"/>
            <x-common-form-input name="email" title="{{ __('front/edit.email') }}"
                                 value="{{ old('email', $customer->email) }}" required="required"
                                 placeholder="{{ __('front/edit.email') }}"/>

            <div class="form-group mb-4">
              <label class="form-label">{{ __('front/edit.telephone') }}</label>
              <div class="row mb-3">
                <div class="col-4">
                  <input type="text" class="form-control" name="calling_code" 
                         placeholder="{{ __('front/edit.calling_code') }}" 
                         value="{{ old('calling_code', $customer->calling_code ?? '+86') }}" />
                </div>
                <div class="col-8">
                  <input type="tel" class="form-control" name="telephone" 
                         placeholder="{{ __('front/edit.telephone') }}" 
                         value="{{ old('telephone', $customer->telephone) }}" />
                </div>
              </div>
              <div class="input-group mb-3">
                <input type="text" class="form-control" name="code" 
                       placeholder="{{ __('front/edit.sms_code') }}" maxlength="6" />
                <button type="button" class="btn btn-outline-secondary" id="send-sms-code">
                  {{ __('front/edit.send_code') }}
                </button>
              </div>
              <div class="text-secondary"><small>{{ __('front/edit.phone_update_hint') }}</small></div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-50">{{ __('front/common.submit') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.edit.bottom')

@endsection

@push('footer')
<script>
  // Send SMS code for phone number update
  $('#send-sms-code').on('click', function() {
    const callingCode = $('input[name="calling_code"]').val();
    const telephone = $('input[name="telephone"]').val();
    
    if (!callingCode || !telephone) {
      layer.msg('{{ __('front/edit.please_enter_phone') }}', {icon: 2});
      return;
    }
    
    const btn = $(this);
    btn.prop('disabled', true);
    btn.text('{{ __('front/edit.sending') }}...');
    
    axios.post('{{ account_route('edit.sms-code') }}', {
      calling_code: callingCode,
      telephone: telephone,
      _token: '{{ csrf_token() }}'
    }).then(function(res) {
      if (res.success) {
        layer.msg(res.message, {icon: 1});
        // Start countdown
        let countdown = 60;
        const timer = setInterval(function() {
          btn.text(countdown + 's');
          countdown--;
          if (countdown < 0) {
            clearInterval(timer);
            btn.prop('disabled', false);
            btn.text('{{ __('front/edit.send_code') }}');
          }
        }, 1000);
      } else {
        layer.msg(res.message, {icon: 2});
        btn.prop('disabled', false);
        btn.text('{{ __('front/edit.send_code') }}');
      }
    }).catch(function(error) {
      const message = error.response?.data?.message || '{{ __('front/edit.send_code_failed') }}';
      layer.msg(message, {icon: 2});
      btn.prop('disabled', false);
      btn.text('{{ __('front/edit.send_code') }}');
    });
  });
</script>
@endpush
