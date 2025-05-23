@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.transactions'))
@section('page-title-right')
  <a href="{{ panel_route('transactions.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

      <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('transactions.index')"/>

      @if ($transactions->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('panel/common.id') }}</td>
              <td>{{ __('panel/transaction.customer') }}</td>
              <td>{{ __('panel/common.email') }}</td>
              <td>{{ __('panel/transaction.type') }}</td>
              <td>{{ __('panel/transaction.amount') }}</td>
              <td>{{ __('panel/transaction.balance') }}</td>
              @hookinsert('panel.transactions.index.thead.bottom')
              <td>{{ __('panel/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach($transactions as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td><a href="{{ panel_route('customers.edit', [$item->customer->id]) }}" class="text-decoration-none">
                  {{ $item->customer->name ?? '' }}
                </a></td>
                <td>{{ $item->customer->email ?? '' }}</td>
                <td>{{ $item->type_format }}</td>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->balance }}</td>
                @hookinsert('panel.transactions.index.tbody.bottom', $item)
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ panel_route('transactions.show', [$item->id]) }}" class="btn btn-primary btn-sm">
                      {{ __('panel/common.view') }}
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $transactions->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')

@endpush
