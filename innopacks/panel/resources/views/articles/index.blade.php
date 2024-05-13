@extends('panel::layouts.app')
@section('body-class', 'page-home')

@section('title', __('panel::menu.article'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <div class="d-flex justify-content-between mb-4">
      <a href="{{ panel_route('articles.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> 添加</a>
    </div>

    <table class="table">
      <thead>
        <tr>
          <td>ID</td>
          <td>图片</td>
          <td>标题</td>
          <td>分类</td>
          <td>标签</td>
          <td>SEO 别名</td>
          <td>操作</td>
        </tr>
      </thead>
      @if ($articles->count())
      <tbody>
        @foreach($articles as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td><img src="{{ image_resize($item->translation->image ?? '', 30, 30) }}" style="width: 30px; height: 30px" alt=""></td>
            <td>{{ $item->translation->title ?? '' }}</td>
            <td>{{ $item->catalog->translation->title ?? '-' }}</td>
            <td>{{ $item->tagNames ?? '' }}</td>
            <td>{{ $item->slug }}</td>
            <td>
              <a href="{{ panel_route('articles.edit', [$item->id]) }}" class="btn btn-sm btn-outline-primary">编辑</a>
              <form action="{{ panel_route('articles.destroy', [$item->id]) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">删除</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
      @else
      <tbody><tr><td colspan="5"><x-panel-no-data /></td></tr></tbody>
      @endif
    </table>
    {{ $articles->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
  </div>
</div>
@endsection

@push('footer')
  <script>
  </script>
@endpush