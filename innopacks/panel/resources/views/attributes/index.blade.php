@extends('panel::layouts.app')
@section('body-class', 'page-page')

@section('title', __('panel/menu.attributes'))
@section('page-title-right')
  <a href="{{ panel_route('attributes.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($attributes->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <td>{{ __('panel/common.id')}}</td>
            <td>{{ __('panel/common.name')}}</td>
            <td>{{ __('panel/menu.attribute_groups')}}</td>
            <td>{{ __('panel/common.created_at')}}</td>
            <td>{{ __('panel/common.actions')}}</td>
          </tr>
          </thead>
          <tbody>
          @foreach($attributes as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->translation->name ?? '' }}</td>
              <td>{{ $item->group->translation->name ?? '' }}</td>
              <td>{{ $item->created_at }}</td>
              <td>
                <a href="{{ panel_route('attributes.edit', [$item->id]) }}"
                   class="btn btn-sm btn-outline-primary">{{ __('panel/common.edit')}}</a>
                <form action="{{ panel_route('attributes.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
      {{ $attributes->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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