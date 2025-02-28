@extends('layouts.app')
@section('body-class', 'verified')

@section('content')

  <div class="container">
    @if($verified)
       <div class="d-flex justify-content-center align-items-center flex-column mt-2">
       <i class="fs-3 bi bi-check-circle-fill text-success pt-5 pb-3"></i>
      <p>
        {{ __('front/mail.verified_success') }}
       </div>
    @else
     <div class="d-flex justify-content-center align-items-center flex-column mt-2">
        <i class="fs-3 bi bi-x-circle text-danger pt-5 pb-3"></i>
        <p>
          {{ __('front/mail.verified_failed') }}
        </p>
      </div>
    @endif
  </div>
@endsection

