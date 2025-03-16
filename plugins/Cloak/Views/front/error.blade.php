@extends('front::layouts.master')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-warning display-1"></i>
                    </div>

                    <h1 class="card-title">{{ $title ?? __('Cloak::front.error_generic') }}</h1>
                    <p class="card-text lead">{{ $message ?? __('Cloak::front.error_generic_message') }}</p>

                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="bi bi-house-door-fill me-2"></i>
                            {{ __('Cloak::front.return_home') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
