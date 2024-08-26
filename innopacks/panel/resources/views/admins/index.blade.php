@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.admins'))
@section('page-title-right')
  <a href="{{ panel_route('admins.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($admins->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <td>{{ __('panel/common.id') }}</td>
            <td>{{ __('panel/admin.name') }}</td>
            <td>{{ __('panel/admin.email') }}</td>
            <td>{{ __('panel/admin.locale') }}</td>
            <td>{{ __('panel/admin.active') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
          </thead>
          <tbody>
          @foreach($admins as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->name }}</td>
              <td>{{ $item->email }}</td>
              <td>{{ $item->locale }}</td>
              <td>@include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('admins.active', $item->id)])</td>
              <td>
                <a href="{{ panel_route('admins.edit', [$item->id]) }}"
                    class="btn btn-sm btn-outline-primary">{{ __('panel/common.edit')}}</a>
                <form action="{{ panel_route('admins.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
      {{ $admins->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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