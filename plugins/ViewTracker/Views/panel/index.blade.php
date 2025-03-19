@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('访问记录'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">

      @if ($items->count())
        <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('access_logs.index')"/>
        <table class="table table-hover">
          <thead>
          <tr>
            <td>{{ __('ViewTracker::panel.id') }}</td>
            <td>{{ __('ViewTracker::panel.customer') }}</td>
            <td>{{ __('ViewTracker::panel.ip_address') }}</td>
            <td>{{ __('ViewTracker::panel.request_address') }}</td>
            <td>{{ __('ViewTracker::panel.user_agent') }}</td>
            <td>{{ __('ViewTracker::panel.time') }}</td>
            <td>{{ __('ViewTracker::panel.action') }}</td>
          </tr>
          </thead>
          <tbody>
          @foreach($items as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>
                @if($item->customer_id)
                  <a href="{{ panel_route('customers.edit', $item->customer_id) }}" target="_blank" class="text-decoration-none" >{{ $item->customer->name ?? '-' }}</a>
                @else
                  -
                @endif
              </td>
              <td>IP: {{ $item->client_ip }}<br>
                @if($item->country) 位置: {{ $item->country }} {{ $item->city }} @else - @endif
              </td>
              <td>
                <a href="{{ $item->page_url }}" target="_blank" class="text-decoration-none" data-bs-toggle="tooltip"
                   title="{{ $item->page_url }}">
                  {{ sub_string($item->page_url, 32) }}
                </a>
              </td>
              <td data-bs-toggle="tooltip" title="{{ $item->user_agent }}">{{ sub_string($item->user_agent, 32) }}</td>
              <td>{{ $item->created_at }}<br>
                {{ $item->updated_at }}
              </td>
              <td>
                <a href="{{ panel_route('view_logs.edit', [$item->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('ViewTracker::panel.action') }}</a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
        {{ $items->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush