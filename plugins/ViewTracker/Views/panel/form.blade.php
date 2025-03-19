@extends('panel::layouts.app')

@section('title', __('ViewTracker::panel.access_record'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.customer') }}" name="type" :value="$item->customer->name ?? '-'" readonly/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.ip_address') }}" name="domain" :value="$item->client_ip ?? '-'" readonly/>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.country') }}" name="country" :value="$item->country ?? '-'" readonly/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.city') }}" name="city" :value="$item->city ?? '-'" readonly/>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.method') }}" name="method" :value="$item->method ?? '-'" readonly/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.status_code') }}" name="status_code" :value="$item->status_code ?? '-'" readonly/>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.created_at') }}" name="created_at" :value="$item->created_at ?? '-'" readonly/>
          </div>
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.updated_at') }}" name="updated_at" :value="$item->updated_at ?? '-'" readonly/>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <x-common-form-textarea title="{{ __('ViewTracker::panel.user_agent') }}" name="user_agent" :value="$item->user_agent ?? '-'" readonly/>
          </div>
          <div class="col-md-6">
            <x-common-form-textarea title="{{ __('ViewTracker::panel.language') }}" name="language" :value="$item->language ?? '-'" readonly/>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <x-common-form-input title="{{ __('ViewTracker::panel.request_address') }}" name="page_url" :value="$item->page_url ?? '-'" readonly/>
          </div>
        </div>
    </div>
  </div>
@endsection

@push('footer')
@endpush