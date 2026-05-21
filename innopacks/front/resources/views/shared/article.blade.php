@if ($item->translation)
  <div class="blog-item">
    <div class="image">
      <a href="{{ $item->url }}">
        @if ($item->image)
          <img src="{{ image_resize($item->image, 300, 300) }}" class="img-fluid" alt="{{ $item->title }}">
        @else
          <div class="bg-light d-flex align-items-center justify-content-center" style="aspect-ratio: 3/2;">
            <i class="bi bi-image text-muted" style="font-size: 48px;"></i>
          </div>
        @endif
      </a>
    </div>
    <div class="blog-item-info">
      @if($item->catalog->translation ?? '')
        <div class="blog-catalog"><a href="{{ $item->url }}">{{ $item->catalog?->title }}</a></div>
      @endif
      <div class="blog-title"><a href="{{ $item->url }}">{{ $item->title }}</a></div>
      <div class="author-wrap">
        @if($item->author)
          <div class="blog-author"><i class="bi bi-person"></i> {{ $item->author }}</div>
        @endif
        <div class="blog-created"><i class="bi bi-clock"></i> {{ $item->created_at->format('Y-m-d') }}</div>
      </div>
    </div>
  </div>
@endif
