@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.roles'))
@section('page-title-right')
  <a href="{{ panel_route('roles.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}
  </a>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($roles->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th>{{ __('panel/common.id') }}</th>
              <th>{{ __('panel/common.name') }}</th>
              <th>{{ __('panel/common.created_at') }}</th>
              <th>{{ __('panel/common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($roles as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->created_at }}</td>
                <td>
                  <a href="{{ panel_route('roles.edit', [$item->id]) }}"
                     class="btn btn-outline-primary btn-sm">{{ __('panel/common.edit')}}</a>
                  <form action="{{ panel_route('roles.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
        {{ $roles->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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