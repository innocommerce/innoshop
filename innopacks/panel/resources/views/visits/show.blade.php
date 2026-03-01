@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/visit.visit_detail'))

@section('page-title-right')
<div class="title-right-btns">
  <a href="{{ panel_route('visits.index') }}" class="btn btn-outline-secondary">{{ __('common/base.back') }}</a>
</div>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('panel/visit.basic_info') }}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <th width="150">{{ __('common/base.id') }}:</th>
                <td>{{ $visit->id }}</td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.session_id') }}:</th>
                <td><code>{{ $visit->session_id }}</code></td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.ip_address') }}:</th>
                <td>{{ $visit->ip_address }}</td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.customer') }}:</th>
                <td>
                  @if($visit->customer_id && $visit->customer)
                    <a href="{{ panel_route('customers.edit', $visit->customer_id) }}" target="_blank">
                      {{ $visit->customer->name }} (ID: {{ $visit->customer_id }})
                    </a>
                  @else
                    <span class="text-muted">{{ __('panel/visit.guest') }}</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.first_visited_at') }}:</th>
                <td>{{ $visit->first_visited_at }}</td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.last_visited_at') }}:</th>
                <td>{{ $visit->last_visited_at }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <th width="150">{{ __('panel/visit.page_views') }}:</th>
                <td><span class="badge bg-primary">{{ $visit->page_views }}</span></td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.visit_duration') }}:</th>
                <td>
                  @if($visit->visit_duration > 0)
                    <span class="badge bg-info">{{ gmdate('H:i:s', $visit->visit_duration) }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.conversion_event') }}:</th>
                <td>
                  @if($visit->conversion_event)
                    <span class="badge bg-success">{{ $visit->conversion_event_display }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.locale') }}:</th>
                <td>{{ $visit->locale ?: '-' }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('panel/visit.location_info') }}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <th width="150">{{ __('panel/visit.country_code') }}:</th>
                <td>
                  @if($visit->country_code)
                    <span class="badge bg-info">{{ $visit->country_code }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.country_name') }}:</th>
                <td>{{ $visit->country_name ?: '-' }}</td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.city') }}:</th>
                <td>{{ $visit->city ?: '-' }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <th width="150">{{ __('panel/visit.device_type') }}:</th>
                <td>
                  @if($visit->device_type)
                    <span class="badge bg-secondary">{{ $visit->device_type_display }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.browser') }}:</th>
                <td>{{ $visit->browser ?: '-' }}</td>
              </tr>
              <tr>
                <th>{{ __('panel/visit.os') }}:</th>
                <td>{{ $visit->os ?: '-' }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    @if($visit->referrer)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('panel/visit.referrer') }}</h5>
      </div>
      <div class="card-body">
        <a href="{{ $visit->referrer }}" target="_blank" class="text-break">{{ $visit->referrer }}</a>
      </div>
    </div>
    @endif

    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('panel/visit.user_agent') }}</h5>
      </div>
      <div class="card-body">
        <code class="small text-break">{{ $visit->user_agent }}</code>
      </div>
    </div>
  </div>
</div>
@endsection

