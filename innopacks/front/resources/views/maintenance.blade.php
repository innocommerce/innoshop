@extends('layouts.app')
@section('body-class', 'page-maintenance')

@section('content')
<div class="maintenance-page">
    <div class="container">
        <div class="maintenance-content">
            <div class="maintenance-image">
                <img src="{{ asset('images/maintenance.svg') }}" alt="Store Closed">
            </div>
            <div class="maintenance-text">
                <h1>{{ __('front/maintenance.title') }}</h1>
                <p>{{ __('front/maintenance.description') }}</p>
            </div>
        </div>
    </div>
</div>

<style>
.maintenance-page {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
}

.maintenance-content {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3rem;
}

.maintenance-image {
    flex: 0 0 45%;
    max-width: 400px;
}

.maintenance-image img {
    width: 100%;
    height: auto;
}

.maintenance-text {
    flex: 0 0 45%;
}

.maintenance-text h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 1rem;
    font-weight: 600;
}

.maintenance-text p {
    font-size: 1.25rem;
    color: #666;
    line-height: 1.6;
}

@media (max-width: 991px) {
    .maintenance-content {
        flex-direction: column;
        text-align: center;
        gap: 2rem;
    }

    .maintenance-image {
        flex: 0 0 100%;
        max-width: 300px;
    }

    .maintenance-text {
        flex: 0 0 100%;
    }
}
</style>
@endsection 