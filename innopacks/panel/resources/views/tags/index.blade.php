@extends('panel::layouts.app')
@section('body-class', 'page-tag')

@section('title', __('panel::menu.tag'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <div class="d-flex justify-content-between mb-4">
        <a href="{{ panel_route('tags.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
          添加</a>
      </div>
      <table class="table">
        <thead>
        <tr>
          <td>ID</td>
          <td>别名</td>
          <td>名称</td>
          <td>排序</td>
          <td>启用</td>
          <td>操作</td>
        </tr>
        </thead>
        <tbody>
        @foreach($tags as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->slug }}</td>
            <td>{{ $item->translation->name ?? '' }}</td>
            <td>{{ $item->position }}</td>
            <td>{{ $item->active }}</td>
            <td>
              <a href="{{ panel_route('tags.edit', [$item->id]) }}"
                 class="btn btn-sm btn-outline-primary">编辑</a>
              <form action="{{ panel_route('tags.destroy', [$item->id]) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">删除</button>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
      {{ $tags->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush