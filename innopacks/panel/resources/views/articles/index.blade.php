@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.articles'))
@section('page-title-right')
  <a href="{{ panel_route('articles.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('panel/common.id')}}</td>
            <td>{{ __('panel/article.image') }}</td>
            <td>{{ __('panel/article.title') }}</td>
            <td>{{ __('panel/article.catalog') }}</td>
            <td>{{ __('panel/article.tag') }}</td>
            <td>{{ __('panel/common.slug') }}</td>
            <td>{{ __('panel/common.position') }}</td>
            <td>{{ __('panel/common.active') }}</td>
            <td>{{ __('panel/common.actions') }}</td>
          </tr>
        </thead>
        @if ($articles->count())
        <tbody>
          @foreach($articles as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td><img src="{{ image_resize($item->translation->image ?? '', 30, 30) }}" style="width: 30px; height: 30px" alt=""></td>
              <td>{{ sub_string($item->translation->title ?? '') }}</td>
              <td>{{ $item->catalog->translation->title ?? '-' }}</td>
              <td>{{ $item->tagNames ?? '' }}</td>
              <td>{{ sub_string($item->slug) }}</td>
              <td>{{ $item->position }}</td>
              <td>@include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('articles.active', $item->id)])</td>
              <td>
                <a href="{{ panel_route('articles.edit', [$item->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('panel/common.edit')}}</a>
                <form action="{{ panel_route('articles.destroy', [$item->id]) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('panel/common.delete')}}</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
        @else
        <tbody><tr><td colspan="5"><x-common-no-data /></td></tr></tbody>
        @endif
      </table>
    </div>
    {{ $articles->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
  </div>
</div>
@endsection

@push('footer')
  <script>
  </script>
@endpush