@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.visits'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <x-panel-data-data-search
      :action="panel_route('visits.index')"
      :searchFields="$searchFields ?? []"
      :filters="$filterButtons ?? []"
      :enableDateRange="true"
    />

    @if ($visits->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('common/base.id') }}</td>
            <td>{{ __('panel/visit.session_id') }}</td>
            <td>{{ __('panel/visit.ip_address') }}</td>
            <td>{{ __('panel/visit.location') }}</td>
            <td>{{ __('panel/visit.device_type') }}</td>
            <td>{{ __('panel/visit.first_visited_at') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($visits as $visit)
          <tr>
            <td>{{ $visit->id }}</td>
            <td><code class="small">{{ \Illuminate\Support\Str::limit($visit->session_id, 20) }}</code></td>
            <td>{{ $visit->ip_address }}</td>
            <td>
              @if($visit->country_code)
                <span class="badge bg-info">{{ $visit->country_code }}</span>
                @if($visit->city)
                  <span class="ms-1">{{ $visit->city }}</span>
                @endif
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              @if($visit->device_type)
                <span class="badge bg-secondary">{{ $visit->device_type_display }}</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>{{ $visit->first_visited_at }}</td>
            <td>
              <a href="{{ panel_route('visits.show', [$visit->id]) }}" class="btn btn-sm btn-outline-primary">
                {{ __('common/base.view') }}
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $visits->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
