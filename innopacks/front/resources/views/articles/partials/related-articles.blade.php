@if($relatedArticles && $relatedArticles->count() > 0)
<div class="related-articles-section mb-4">
  <div class="sidebar-title">{{ __('front/article.related_articles') }}</div>
  <div class="sidebar-list">
    <ul class="list-unstyled">
      @foreach($relatedArticles as $relatedArticle)
        <li class="mb-3">
          <div class="related-article-item">
            @if($relatedArticle->image)
              <div class="related-article-image">
                <a href="{{ $relatedArticle->url }}">
                  <img src="{{ image_resize($relatedArticle->image, 60, 60) }}" alt="{{ $relatedArticle->translation->title }}" class="img-fluid rounded">
                </a>
              </div>
            @endif
            <div class="related-article-content">
              <h6 class="related-article-title">
                <a href="{{ $relatedArticle->url }}" class="text-decoration-none">
                  {{ $relatedArticle->translation->title }}
                </a>
              </h6>
              <div class="related-article-meta text-muted small">
                <i class="bi bi-clock me-1"></i>
                {{ $relatedArticle->created_at->format('Y-m-d') }}
                @if($relatedArticle->viewed > 0)
                  <span class="ms-2">
                    <i class="bi bi-eye me-1"></i>
                    {{ $relatedArticle->viewed }}
                  </span>
                @endif
              </div>
              @if($relatedArticle->translation->summary)
                <p class="related-article-summary text-muted small mt-1 mb-0">
                  {{ Str::limit($relatedArticle->translation->summary, 80) }}
                </p>
              @endif
            </div>
          </div>
        </li>
      @endforeach
    </ul>
  </div>
</div>
@endif

<style>
.related-articles-section {
  margin-bottom: 2rem;
}

.related-article-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid #e9ecef;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
}

.related-article-item:hover {
  border-color: #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.related-article-image {
  flex-shrink: 0;
  width: 60px;
  height: 60px;
}

.related-article-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.related-article-content {
  flex: 1;
  min-width: 0;
}

.related-article-title {
  font-size: 0.875rem;
  line-height: 1.3;
  margin-bottom: 0.25rem;
}

.related-article-title a {
  color: #212529;
}

.related-article-title a:hover {
  color: #0d6efd;
}

.related-article-meta {
  font-size: 0.75rem;
}

.related-article-summary {
  font-size: 0.75rem;
  line-height: 1.3;
}
</style>