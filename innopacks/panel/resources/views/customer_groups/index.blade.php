@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.customer_groups'))
@section('page-title-right')
  <a href="{{ panel_route('customer_groups.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($groups->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <td>{{ __('panel/common.id') }}</td>
            <td>{{ __('panel/common.name') }}</td>
            <td>{{ __('panel/customer.level') }}</td>
            <td>{{ __('panel/customer.mini_cost') }}</td>
            <td>{{ __('panel/customer.discount_rate') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
          </thead>
          <tbody>
          @foreach($groups as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->translation->name }}</td>
              <td>{{ $item->level }}</td>
              <td>{{ currency_format($item->mini_cost, system_setting('currency')) }}</td>
              <td>{{ $item->discount_rate }}</td>
              <td>
                <a href="{{ panel_route('customer_groups.edit', [$item->id]) }}"
                    class="btn btn-sm btn-outline-primary">{{ __('panel/common.edit')}}</a>
                <form action="{{ panel_route('customer_groups.destroy', [$item->id]) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('panel/common.delete')}}</button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      {{ $groups->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
      <x-common-no-data />
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush