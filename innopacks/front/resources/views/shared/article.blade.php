@if ($item->translation)
  <div class="blog-item">
    <div class="image">
      <a href="{{ $item->url }}">
        @php $cover = $item->coverUrl(300, 300); @endphp
        @if ($cover)
          <div class="front-video-cover">
            <img src="{{ $cover }}"
                 class="img-fluid"
                 alt="{{ $item->title }}"
                 width="300" height="300">
            @if ($item->is_video)
              <i class="bi bi-play-circle-fill front-video-cover__badge" aria-hidden="true"></i>
            @endif
          </div>
        @elseif ($item->is_video)
          <div class="ratio ratio-3x2 bg-light">
            <div class="d-flex align-items-center justify-content-center">
              <i class="bi bi-play-circle text-primary fs-1"></i>
            </div>
          </div>
        @else
          <div class="ratio ratio-3x2 bg-light">
            <div class="d-flex align-items-center justify-content-center">
              <i class="bi bi-image text-muted fs-1"></i>
            </div>
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
