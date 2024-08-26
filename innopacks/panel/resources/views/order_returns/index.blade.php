@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.order_returns'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($order_returns->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <td>{{ __('panel/common.id') }}</td>
            <td>{{ __('panel/order_return.customer') }}</td>
            <td>{{ __('panel/order_return.order_number') }}</td>
            <td>{{ __('panel/order_return.number') }}</td>
            <td>{{ __('panel/order_return.name') }}</td>
            <td>{{ __('panel/order_return.email') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
          </thead>
            <tbody>
            @foreach($order_returns as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td><img src="{{ image_resize($item->logo) }}" class="img-fluid wh-40"></td>
                <td>{{ $item->name ?? '' }}</td>
                <td>{{ $item->position }}</td>
                <td>{{ $item->first }}</td>
                <td>{{ $item->active }}</td>
                <td>
                  <a href="{{ panel_route('order_returns.edit', [$item->id]) }}"
                     class="btn btn-sm btn-outline-primary">{{ __('panel/common.edit')}}</a>
                  <form action="{{ panel_route('order_returns.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
      {{ $order_returns->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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