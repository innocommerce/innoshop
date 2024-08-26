@extends('panel::layouts.app')
@section('body-class', 'page-tag')

@section('title', __('panel/menu.tags'))

@section('page-title-right')
  <a href="{{ panel_route('tags.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($tags->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <td>{{ __('panel/common.id')}}</td>
            <td>{{ __('panel/tag.name') }}</td>
            <td>{{ __('panel/common.slug') }}</td>
            <td>{{ __('panel/common.position') }}</td>
            <td>{{ __('panel/common.active') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
          </thead>
          <tbody>
          @foreach($tags as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->translation->name ?? '' }}</td>
              <td>{{ $item->slug }}</td>
              <td>{{ $item->position }}</td>
              <td>@include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('tags.active', $item->id)])</td>
              <td>
                <a href="{{ panel_route('tags.edit', [$item->id]) }}"
                   class="btn btn-sm btn-outline-primary">{{ __('panel/common.edit')}}</a>
                <form action="{{ panel_route('tags.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
      {{ $tags->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
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