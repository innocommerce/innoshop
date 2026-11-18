{{-- 文章列表 --}}
@if ($articles->count())
  <div class="newest-box">
    @foreach($articles as $article)
    <div class="newest-item">
      <div class="item-img">
        <a href="{{ $article->url }}">
          @php $cover = $article->coverUrl(200, 150); @endphp
          @if ($cover)
            {{-- Video covers use the auto-extracted poster frame so the list
                 page doesn't autoplay every video; images use a resized URL. --}}
            <div class="front-video-cover">
              <img src="{{ $cover }}"
                   class="img-fluid"
                   alt="{{ $article->title }}">
              @if ($article->is_video)
                <i class="bi bi-play-circle-fill front-video-cover__badge" aria-hidden="true"></i>
              @endif
            </div>
          @elseif ($article->is_video)
            {{-- Video cover but poster extraction failed (or media predates the
                 feature): show a play-icon placeholder instead of a broken <img>
                 pointing at the video URL. --}}
            <div class="ratio ratio-4x3 bg-light rounded">
              <div class="d-flex align-items-center justify-content-center">
                <i class="bi bi-play-circle text-primary fs-1"></i>
              </div>
            </div>
          @else
            {{-- No cover at all: keep the image placeholder the original list
                 rendered via image_resize('') so cards keep their height. --}}
            <div class="ratio ratio-4x3 bg-light rounded">
              <div class="d-flex align-items-center justify-content-center">
                <i class="bi bi-image text-muted fs-1"></i>
              </div>
            </div>
          @endif
        </a>
      </div>
      <div class="item-content d-flex flex-column justify-content-between">
        <div class="content-top">
          <div class="item-title mt-2"><a href="{{ $article->url }}">{{ $article->title }}</a></div>
          @if ($article->tags->count())
          <div class="newes-tags">
            <i class="bi bi-tags me-1"></i>
            <div class="d-flex flex-wrap">
              @foreach($article->tags as $tag)
                <a href="{{ $tag->url }}">{{ $tag->fallbackName() }}</a>
              @endforeach
            </div>
          </div>
          @endif
          <div class="item-summary">{{ sub_string($article->summary, 180) }}</div>
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
