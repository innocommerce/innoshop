@php
  $hasImages = !empty($content['images']) && ($content['images_count'] ?? 0) > 0;
@endphp

@if ($hasImages || request('design'))
  <section class="module-line">
    <div class="module-four-image">
      <div class="{{ $content['width_class'] ?? pb_get_width_class($content['width'] ?? 'wide') }}">
        @include('PageBuilder::front.partials.module-title', [
          'title' => $content['title'] ?? '',
          'subtitle' => $content['subtitle'] ?? '',
        ])

        @if ($hasImages)
          <div class="image-grid">
            @foreach($content['images'] as $image)
              <div class="image-item">
                <a href="{{ $image['link']['link'] ?? 'javascript:void(0)' }}" class="d-block">
                  <div class="image-wrap">
                    <img src="{{ image_resize($image['image']) }}"
                         style="object-fit: {{ $image['object_fit'] ?? 'cover' }}"
                         alt="">
                  </div>
                  @if(!empty($image['text']))
                    <div class="image-text">{{ $image['text'] ?? '' }}</div>
                  @endif
                  @if(!empty($image['sub_text']))
                    <div class="image-sub-text">{{ $image['sub_text'] ?? '' }}</div>
                  @endif
                </a>
              </div>
            @endforeach
          </div>
        @elseif (request('design'))
          @include('PageBuilder::front.partials.module-empty', [
            'moduleClass' => 'four-image',
            'icon' => 'bi-layout-text-window-reverse',
            'message' => __('PageBuilder::modules.no_images'),
          ])
        @endif
      </div>
    </div>
  </section>

<style>
.module-four-image {
    padding: 30px 0;
}
.module-four-image .image-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}
.module-four-image .image-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background: #f8f8f8;
}
.module-four-image .image-wrap {
    position: relative;
    width: 100%;
    height: 300px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.module-four-image .image-wrap img {
    width: 100%;
    height: 100%;
    transition: transform 0.3s ease;
}
.module-four-image .image-item:hover img {
    transform: scale(1.05);
}
.module-four-image .image-text {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 30px;
    text-align: center;
    color: #fff;
    font-size: 1rem;
    font-weight: bold;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    z-index: 1;
    padding: 0 15px;
}
.module-four-image .image-sub-text {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 10px;
    text-align: center;
    color: #fff;
    font-size: 0.875rem;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    z-index: 1;
    padding: 0 15px;
}

@media (max-width: 768px) {
    .module-four-image .image-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .module-four-image .image-wrap {
        height: 150px; /* Mobile image height halved */
    }
}
</style>
@endif
