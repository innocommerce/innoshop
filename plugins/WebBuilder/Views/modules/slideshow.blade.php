@if (!empty($content['images']))
  <section class="module-line">
    <div class="swiper" id="module-swiper-{{ $module_id }}">
      <div class="swiper-wrapper">
        @foreach ($content['images'] as $image)
          <div class="swiper-slide">
            <a href="{{ $image['link']['link'] ?? 'javascript:void(0)' }}">
              <img src="{{ $image['image'][front_locale_code()] ?? '' }}" class="img-fluid" alt="">
            </a>
          </div>
        @endforeach
      </div>
      <div class="swiper-pagination"></div>
    </div>
  </section>
  <script>
    new Swiper('#module-swiper-{{ $module_id }}', {
      loop: true,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      autoplay: {
        delay: 3000,
      },
    });
  </script>
@endif
