@if ($item->translation)
  <div class="blog-item">
    <div class="image">
      <a href="{{ $item->url }}">
        <img src="{{ $item->translation->image }}" class="img-fluid">
      </a>
    </div>
    <div class="blog-item-info">
      <div class="blog-catalog"><a href="{{ $item->url }}">{{ $item->catalog->translation->title }}</a></div>
      <div class="blog-title">{{ $item->translation->title }}</div>
      <div class="author-wrap">
        @if($item->author)
          <div class="blog-author"><i class="bi bi-person"></i> {{ $item->author }}</div>
        @endif
        <div class="blog-created"><i class="bi bi-clock"></i> {{ $item->created_at->format('Y-m-d') }}</div>
      </div>
    </div>
  </div>
@endif