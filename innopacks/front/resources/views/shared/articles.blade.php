<div class="container mt-3 mt-md-5">
  <div class="row">
    <div class="col-12 col-md-9">
      @if ($articles->count())
        <div class="newest-box">
          @foreach($articles as $article)
          <div class="newest-item">
            <div class="item-img">
              <a href="{{ $article->url }}">
                <img src="{{ image_resize($article->translation->image ?? '', 200, 150) }}" class="img-fluid">
              </a>
            </div>
            <div class="item-content d-flex flex-column justify-content-between">
              <div class="content-top">
                <div class="item-title"><a href="{{ $article->url }}">{{ $article->translation->title }}</a></div>
                @if ($article->tags->count())
                <div class="newes-tags">
                  <i class="bi bi-tags me-1"></i>
                  <div class="d-flex">
                    @foreach($article->tags as $tag)
                      <a href="{{ $tag->url }}">{{ $tag->translation->name ?? '' }}</a>
                    @endforeach
                  </div>
                </div>
                @endif
                <div class="item-summary">{{ $article->translation->summary }}</div>
              </div>
              <div class="item-date text-secondary">
                <span><i class="bi bi-clock"></i> {{ $article->created_at->format('Y-m-d') }}</span>
                <span class="ms-3"><i class="bi bi-eye"></i> {{ $article->viewed }}</span>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      @else
        @include('shared.no-data', ['text' => '没有数据 ~'])
      @endif
    </div>
    <div class="col-12 col-md-3">
      <div class="newes-sidebar">
        <div class="search-box">
          <div class="input-group input-group-lg">
            <input type="text" class="form-control" value="{{ request('keyword') }}" placeholder="请输入关键字">
            <button class="btn btn-primary" type="button">搜索</button>
          </div>
        </div>

        @if(isset($catalogs) && $catalogs)
          <div class="sidebar-item">
            <div class="sidebar-title">新闻分类</div>
            <div class="sidebar-list">
              <ul>
                @foreach($catalogs as $catalog)
                  <li><a
                        href="{{ $catalog->url }}">{{ $catalog->translation->title ?? '' }}</a>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif

        @if(isset($tags) && $tags)
          <div class="sidebar-item">
            <div class="sidebar-title">新闻标签</div>
            <div class="sidebar-list">
              <ul>
                @foreach($tags as $tag)
                  <li><a href="{{ $tag->url }}">{{ $tag->translation->name ?? '' }}</a></li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@push('footer')
  <script>
    $(function () {
      $('.search-box button').click(function () {
        var keyword = $('.search-box input').val();
        if (keyword) {
          window.location.href = inno.updateQueryStringParameter(window.location.href, 'keyword', keyword);
          return;
        }

        window.location.href = inno.removeURLParameters(window.location.href, 'keyword')
      });

      $('.search-box input').keydown(function (e) {
        if (e.keyCode === 13) {
          $('.search-box button').trigger('click');
        }
      });
    });
  </script>
@endpush