{{-- 文章侧边栏 --}}
<div class="newes-sidebar">
  <div class="search-box">
    <div class="input-group input-group-lg">
      <input type="text" class="form-control" value="{{ request('keyword') }}" placeholder="{{ __("front/article.keyword") }}">
      <button class="btn btn-primary" type="button">{{__("front/article.search")}}</button>
    </div>
  </div>

  @if(isset($catalogs) && $catalogs)
    <div class="sidebar-item">
      <div class="sidebar-title">{{__("front/article.news_classification")}}</div>
      <div class="sidebar-list">
        <ul>
          @foreach($catalogs as $catalog)
            <li><a
                  href="{{ $catalog->url }}" {{ isset($currentCatalogId) && $catalog->id === $currentCatalogId ? 'class="fw-bold text-primary"' : '' }}>{{ $catalog->translation?->title ?? '' }}</a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  @if(isset($tags) && $tags)
    <div class="sidebar-item">
      <div class="sidebar-title">{{__("front/article.news_tag")}}</div>
      <div class="sidebar-list">
        <ul>
          @foreach($tags as $tag)
            <li><a href="{{ $tag->url }}">{{ $tag->translation?->name ?? '' }}</a></li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif
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
