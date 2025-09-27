@if (!empty($content['images']))
  @push('header')
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
    <style>
      .swiper-slide {
        height: 500px;
        overflow: hidden;
        position: relative;
      }

      /* 手机端响应式高度 */
      @media (max-width: 768px) {
        .swiper-slide {
          height: 300px;
        }
      }

      @media (max-width: 480px) {
        .swiper-slide {
          height: 200px;
        }
      }

      .slideshow-link {
        display: block;
        width: 100%;
        height: 100%;
        position: relative;
        text-decoration: none;
      }

      .swiper-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        position: absolute;
        top: 0;
        left: 0;
      }

      .slideshow-content {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: auto;
        height: auto;
        z-index: 1;
        display: flex;
        flex-direction: column;
        pointer-events: none;
        padding: 20px;
        align-items: center;
        text-align: center;
      }

      .slideshow-content.position-left {
        left: 10%;
        transform: translateY(-50%);
      }

      .slideshow-content.position-center {
        left: 50%;
        transform: translate(-50%, -50%);
      }

      .slideshow-content.position-right {
        right: 10%;
        transform: translateY(-50%);
      }

      .slideshow-title {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        pointer-events: none;
      }

      .slideshow-subtitle {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        pointer-events: none;
      }

      .slideshow-content .btn {
        pointer-events: auto;
      }

      /* 手机端响应式文字大小 */
      @media (max-width: 768px) {
        .slideshow-title {
          font-size: 24px !important;
          margin-bottom: 15px !important;
        }
        
        .slideshow-subtitle {
          font-size: 18px !important;
          margin-bottom: 15px !important;
        }
        
        .slideshow-content .btn {
          font-size: 14px !important;
          padding: 8px 16px !important;
        }
      }

      @media (max-width: 480px) {
        .slideshow-title {
          font-size: 20px !important;
          margin-bottom: 10px !important;
        }
        
        .slideshow-subtitle {
          font-size: 16px !important;
          margin-bottom: 10px !important;
        }
        
        .slideshow-content .btn {
          font-size: 12px !important;
          padding: 6px 12px !important;
        }
      }
    </style>
  @endpush
  <section class="module-line">
    <div class="{{ $content['width_class'] ?? 'container' }}">
      <div class="swiper" id="module-swiper-{{ $module_id }}">
        <div class="swiper-wrapper module-swiper">
          @foreach ($content['images'] as $image)
            <div class="swiper-slide">
              <a href="{{ $image['link']['link'] ?? 'javascript:void(0)' }}" class="slideshow-link">
                @php
                  $imageUrl = '';
                  if (isset($image['image'][front_locale_code()])) {
                      $imageUrl = $image['image'][front_locale_code()];
                  } elseif (isset($image['image'])) {
                      if (is_array($image['image'])) {
                          $imageUrl = reset($image['image']);
                      } else {
                          $imageUrl = $image['image'];
                      }
                  }
                @endphp
                <img src="{{ $imageUrl }}" class="img-fluid" alt="">
                
                <div class="slideshow-content position-{{ $image['title_align'] ?? 'center' }}"
                  style="margin: {{ $image['content_margin_top'] ?? 0 }}px {{ $image['content_margin_right'] ?? 0 }}px {{ $image['content_margin_bottom'] ?? 0 }}px {{ $image['content_margin_left'] ?? 0 }}px;">
                  @if ($image['title'] ?? false)
                    <h2 class="slideshow-title mb-4"
                      style="color: {{ $image['title_color'] ?? '#ffffff' }};
                             font-size: {{ $image['title_size'] ?? 32 }}px;">
                      {{ $image['title'][front_locale_code()] ?? ($image['title'][array_key_first($image['title'])] ?? '') }}
                    </h2>
                  @endif
                  @if ($image['subtitle'] ?? false)
                    <h3 class="slideshow-subtitle mb-4"
                      style="color: {{ $image['subtitle_color'] ?? '#ffffff' }};
                             font-size: {{ $image['subtitle_size'] ?? 24 }}px;">
                      {{ $image['subtitle'][front_locale_code()] ?? ($image['subtitle'][array_key_first($image['subtitle'])] ?? '') }}
                    </h3>
                  @endif
                  @if (
                      ($image['button_text'] ?? false) &&
                          !empty(trim(
                                  $image['button_text'][front_locale_code()] ??
                                      ($image['button_text'][array_key_first($image['button_text'])] ?? ''))
                          ))
                    @if (!empty($image['button_link']['link']) && $image['button_link']['link'] !== 'javascript:void(0)')
                      <a href="{{ $image['button_link']['link'] }}" class="btn"
                        style="background-color: {{ $image['button_color'] ?? '#409EFF' }};
                               color: {{ $image['button_text_color'] ?? '#ffffff' }};
                               font-size: {{ $image['button_text_size'] ?? 16 }}px;
                               margin-left: {{ $image['button_margin_left'] ?? 0 }}px;
                               margin-right: {{ $image['button_margin_right'] ?? 0 }}px;
                               display: inline-block;
                               padding: 10px 20px;
                               text-decoration: none;
                               border-radius: 4px;"
                        onclick="event.stopPropagation();">
                        {{ $image['button_text'][front_locale_code()] ?? ($image['button_text'][array_key_first($image['button_text'])] ?? '') }}
                      </a>
                    @else
                      <span class="btn"
                        style="background-color: {{ $image['button_color'] ?? '#409EFF' }};
                               color: {{ $image['button_text_color'] ?? '#ffffff' }};
                               font-size: {{ $image['button_text_size'] ?? 16 }}px;
                               margin-left: {{ $image['button_margin_left'] ?? 0 }}px;
                               margin-right: {{ $image['button_margin_right'] ?? 0 }}px;
                               display: inline-block;
                               padding: 10px 20px;
                               text-decoration: none;
                               border-radius: 4px;">
                        {{ $image['button_text'][front_locale_code()] ?? ($image['button_text'][array_key_first($image['button_text'])] ?? '') }}
                      </span>
                    @endif
                  @endif
                </div>
              </a>
            </div>
          @endforeach
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      new Swiper('#module-swiper-{{ $module_id }}', {
        loop: {{ count($content['images']) > 1 ? 'true' : 'false' }},
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        autoplay: {{ count($content['images']) > 1 ? '{delay: 3000, disableOnInteraction: false}' : 'false' }},
      });
    });
  </script>
@endif
