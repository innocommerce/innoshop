@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/withdrawal.customer_withdrawals'))

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

      <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('withdrawals.index')"/>

      @if ($withdrawals->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('panel/common.id') }}</td>
              <td>{{ __('panel/withdrawal.customer_name') }}</td>
              <td>{{ __('panel/withdrawal.customer_email') }}</td>
              <td>{{ __('panel/withdrawal.amount') }}</td>
              <td>{{ __('panel/withdrawal.account_type') }}</td>
              <td>{{ __('panel/withdrawal.account_number') }}</td>
              <td>{{ __('panel/withdrawal.status') }}</td>
              <td>{{ __('panel/withdrawal.created_at') }}</td>
              @hookinsert('panel.withdrawals.index.thead.bottom')
              <td>{{ __('panel/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach($withdrawals as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="{{ panel_route('customers.edit', [$item->customer->id]) }}" class="text-decoration-none">
                    {{ $item->customer->name ?? '' }}
                  </a>
                </td>
                <td>{{ $item->customer->email ?? '' }}</td>
                <td>
                  <span class="fw-bold text-primary">{{ currency_format($item->amount) }}</span>
                </td>
                <td>{{ $item->account_type_format }}</td>
                <td>
                  <span class="text-muted font-monospace">{{ substr($item->account_number, 0, 6) }}****{{ substr($item->account_number, -4) }}</span>
                </td>
                <td>
                  @switch($item->status)
                    @case('pending')
                      <span class="badge bg-warning">{{ $item->status_format }}</span>
                      @break
                    @case('approved')
                      <span class="badge bg-info">{{ $item->status_format }}</span>
                      @break
                    @case('paid')
                      <span class="badge bg-success">{{ $item->status_format }}</span>
                      @break
                    @case('rejected')
                      <span class="badge bg-danger">{{ $item->status_format }}</span>
                      @break
                    @default
                      <span class="badge bg-secondary">{{ $item->status_format }}</span>
                  @endswitch
                </td>
                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                @hookinsert('panel.withdrawals.index.tbody.bottom', $item)
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ panel_route('withdrawals.show', [$item->id]) }}" class="btn btn-primary btn-sm">
                      {{ __('panel/withdrawal.view_detail') }}
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $withdrawals->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data text="{{ __('panel/withdrawal.no_withdrawals') }}"/>
      @endif
    </div>
  </div>
@endsection

@push('footer')

@endpush 