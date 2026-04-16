{{-- 文章列表 --}}
@if ($articles->count())
  <div class="newest-box">
    @foreach($articles as $article)
    <div class="newest-item">
      <div class="item-img">
        <a href="{{ $article->url }}">
          <img src="{{ image_resize($article->image, 200, 150) }}" class="img-fluid">
        </a>
      </div>
      <div class="item-content d-flex flex-column justify-content-between">
        <div class="content-top">
          <div class="item-title mt-2"><a href="{{ $article->url }}">{{ $article->translation?->title ?? '' }}</a></div>
          @if ($article->tags->count())
          <div class="newes-tags">
            <i class="bi bi-tags me-1"></i>
            <div class="d-flex flex-wrap">
              @foreach($article->tags as $tag)
                <a href="{{ $tag->url }}">{{ $tag->translation?->name ?? '' }}</a>
              @endforeach
            </div>
          </div>
          @endif
          <div class="item-summary">{{ sub_string($article->translation?->summary ?? '', 180) }}</div>
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
