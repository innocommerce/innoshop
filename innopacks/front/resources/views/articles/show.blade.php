@extends('layouts.app')

@section('body-class', 'page-news-details')

@section('content')

  <x-front-breadcrumb type="article" :value="$article" />

  @hookinsert('article.show.top')

  <div class="container mt-3 mt-md-5">
    <div class="row">
      <div class="col-12 col-md-9">
        <div class="newest-box">
          <div class="newes-title">{{ $article->translation->title }}</div>
          @if ($article->tags->count())
          <div class="newes-tags mb-3 mt-n2">
            <i class="bi bi-tags me-1"></i>
            <div class="d-flex">
              @foreach($article->tags as $tag)
                <a href="{{ $tag->url }}">{{ $tag->translation->name }}</a>
              @endforeach
            </div>
          </div>
          @endif
          <div class="newes-top">
            <div class="newes-time"><i class="bi bi-clock"></i> {{ $article->created_at->format('Y-m-d') }}</div>
            <div class="newes-author"><i class="bi bi-person-square"></i> {{ $article->author ?? '' }}</div>
            <div class="newes-author"><i class="bi bi-ui-radios-grid"></i> {{ $article->catalog->translation->title ?? '' }}</div>
            <div class="newes-author"><i class="bi bi-eye"></i> {{ $article->viewed }}</div>
          </div>
          <div class="content">
            {!! $article->translation->content !!}
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="newes-sidebar">
          <div class="search-box">
            <div class="input-group input-group-lg">
              <input type="text" class="form-control" value="{{ request('keyword') }}" placeholder="请输入关键字">
              <button class="btn btn-primary" type="button">搜索</button>
            </div>
          </div>
          <div class="sidebar-title">新闻分类</div>
          <div class="sidebar-list">
            <ul>
              @foreach($catalogs as $catalog)
                <li><a href="{{ $catalog->url }}">{{ $catalog->translation->title ?? '' }}</a></li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('article.show.bottom')

@endsection