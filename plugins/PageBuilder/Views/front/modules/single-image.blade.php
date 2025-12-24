@php
  $hasImages = !empty($content['images']) && ($content['images_count'] ?? 0) > 0;
@endphp

@if ($hasImages || request('design'))
  <section class="module-line">
    <div class="module-single-image">
      <div class="{{ $content['width_class'] ?? pb_get_width_class($content['width'] ?? 'wide') }}">
        @include('PageBuilder::front.partials.module-title', [
          'title' => $content['title'] ?? '',
          'subtitle' => $content['subtitle'] ?? '',
        ])

        @if ($hasImages)
          <div class="image-wrap">
            @foreach($content['images'] as $image)
              <a href="{{ $image['link']['link'] ?? 'javascript:void(0)' }}">
                <img src="{{ $image['image'] ?? '' }}" class="img-fluid w-100" alt="">
              </a>
            @endforeach
          </div>
        @elseif (request('design'))
          @include('PageBuilder::front.partials.module-empty', [
            'moduleClass' => 'single-image',
            'icon' => 'bi-image',
            'message' => __('PageBuilder::modules.no_image'),
          ])
        @endif
      </div>
    </div>
  </section>
@endif
