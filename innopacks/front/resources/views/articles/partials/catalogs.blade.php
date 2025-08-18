<div class="mb-4">
  <div class="sidebar-title">{{ __('front/article.news_classification' )}}</div>
  <div class="sidebar-list">
    <ul>
      @foreach($catalogs as $catalog)
        <li><a href="{{ $catalog->url }}">{{ $catalog->translation->title ?? '' }}</a></li>
      @endforeach
    </ul>
  </div>
</div>