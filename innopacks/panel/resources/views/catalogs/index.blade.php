@extends('panel::layouts.app')
@section('body-class', 'page-home')

@section('title', __('panel::menu.catalog'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <div class="d-flex justify-content-between mb-4">
        <a href="{{ panel_route('catalogs.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
          添加</a>
      </div>
      <table class="table">
        <thead>
        <tr>
          <td>ID</td>
          <td>分类名称</td>
          <td>上级分类</td>
          <td>SEO 别名</td>
          <td>排序</td>
          <td>启用</td>
          <td>操作</td>
        </tr>
        </thead>
        @if ($catalogs->count())
          <tbody>
          @foreach($catalogs as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->translation->title ?? '' }}</td>
              <td>{{ $item->parent->translation->title ?? '-' }}</td>
              <td>{{ $item->slug }}</td>
              <td>{{ $item->position }}</td>
              <td>{{ $item->active }}</td>
              <td>
                <a href="{{ panel_route('catalogs.edit', [$item->id]) }}"
                   class="btn btn-sm btn-outline-primary">编辑</a>
                <form action="{{ panel_route('catalogs.destroy', [$item->id]) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">删除</button>
                </form>
              </td>
            </tr>
          @endforeach
          </tbody>
        @else
          <tbody>
          <tr>
            <td colspan="5">
              <div class="d-flex align-items-center flex-column py-4">
                <img src="{{ asset('images/no-data.svg') }}" class="img-fluid wp-400">
                <span class="fs-4 text-secondary">没有数据 ~</span>
              </div>
            </td>
          </tr>
          </tbody>
        @endif
      </table>
      {{ $catalogs->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush