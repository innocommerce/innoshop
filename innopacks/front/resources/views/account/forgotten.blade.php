@extends('layouts.app')

@section('body-class', 'page-forgotten')

@push('header')
@endpush


@section('content')
  <x-front-breadcrumb type="route" value="register.index" title="{{ __('front/account.forgotten') }}" />

  <div class="container" id="page-forgotten">
    <div class="row my-5 justify-content-md-center">
      <div class="col-lg-6 col-xxl-6">
        <div class="card border-0 shadow-sm p-3 my-4">
            <div id="sendCardContent">
                <div class="mb-3">
                    <h4 class="h4 text-dark">{{ __('front/forgotten.title') }}</h4>
                    <h5 class="h5 text-secondary">{{ __('front/forgotten.subtitle_send') }}</h5>
                </div>
                <x-common-form-input title="{{ __('front/forgotten.email') }}" name="email" placeholder="{{ __('front/forgotten.email_address') }}" required></x-common-form-input>
                <div class="col-md-4 my-3">
                    <button type="button" id="btnSend" class="btn btn-primary">{{ __('front/forgotten.send_code') }}</button>
                </div>
            </div>

            <div id="verifyCardContent" class="d-none">
                <div>
                    <div class="mb-3">
                        <h4 class="h4 text-dark">{{ __('front/forgotten.title') }}</h4>
                        <h5 class="h5 text-secondary">{{ __('front/forgotten.subtitle_confirm') }}</h5>
                    </div>

                    <form action="{{ front_route('forgotten.password') }}" class="needs-validation" novalidate method="POST">
                        @csrf
                        <input type="hidden" name="email" value="" id="inputEmail">
                        <x-common-form-input name="code" title="{{ __('front/forgotten.verification_code') }}" value="" required="required" placeholder="{{ __('front/forgotten.verification_code') }}" />
                        <x-common-form-input name="password" title="{{ __('front/forgotten.new_password') }}" value="" type="password" required="required" placeholder="{{ __('front/forgotten.new_password') }}" />
                        <x-common-form-input name="password_confirmation" title="{{ __('front/forgotten.confirm_password') }}" value="" type="password" required="required" placeholder="{{ __('front/password.confirm_password') }}" />

                        <button type="button" id="btnSubmit" class="btn btn-primary btn-lg mt-4 submit-form w-50">{{ __('front/common.submit') }}</button>
                    </form>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" data-bs-backdrop="static" tabindex="-1" id="modalHint">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">{{ __('front/forgotten.hint') }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <p>{{ __('front/forgotten.verification_code_sent') }}</p>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
              </div>
          </div>
      </div>
  </div>
@endsection

@push('footer')
  <script>
      (function ($) {
          $.getUrlParam = function (name) {
              var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
              var r = window.location.search.substr(1).match(reg);
              if (r != null) return unescape(r[2]); return null;
          }
      })(jQuery);

    $(function () {
        var url = window.location.href;
        if ($.getUrlParam('code') && $.getUrlParam('email')){
            $('#sendCardContent').addClass('d-none')
            $('#verifyCardContent').removeClass('d-none')
            $('input[name="code"]').val($.getUrlParam('code'))
            $('input[name="email"]').val($.getUrlParam('email'))
        }

        const modalHint = new bootstrap.Modal('#modalHint', {
            keyboard: false
        })

        $('#btnSend').click(function () {
            layer.load(2, {shade: [0.3, '#fff']})
            axios.post('{{ front_route('forgotten.verify_code') }}',{
                '_token' : '{{ csrf_token() }}',
                'email' : $('input[name="email"]').val()
            }).then(function (res){
                if (res.success==true){
                    parent.layer.closeAll()
                    modalHint.show()
                    $('#inputEmail').val($('input[name="email"]').val() ? $('input[name="email"]').val() : $.getUrlParam('email'))
                    $('#sendCardContent').addClass('d-none')
                    $('#verifyCardContent').removeClass('d-none')
                }
            }).catch(function (err) {
                parent.layer.closeAll()
                layer.msg(err.message,{ icon:2 })
            })
        })

        $('#btnSubmit').click(function () {
            layer.load(2, {shade: [0.3, '#fff']})
            axios.post('{{ front_route('forgotten.password') }}',{
                '_token' : '{{ csrf_token() }}',
                'code'   :$('input[name="code"]').val(),
                'email' : $('input[name="email"]').val(),
                'password'   :$('input[name="password"]').val(),
                'password_confirmation' :$('input[name="password_confirmation"]').val(),
            }).then(function (res){
                if (res.success==true){
                    parent.layer.closeAll()
                    layer.msg(res.message,{ icon:1 })
                    window.location.href='{{ front_route('login.index') }}'
                }
            }).catch(function (err) {
                parent.layer.closeAll()
                layer.msg(err.message,{ icon:2 })
            })
        })
    })
  </script>
@endpush
