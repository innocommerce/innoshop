@if (!empty($content['images']))
  @push('header')
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
    <style>
      .slideshow-link {
        display: block;
        width: 100%;
        position: relative;
        text-decoration: none;
      }

      .swiper-slide {
        position: relative;
        overflow: hidden;
      }

      .swiper-slide img {
        width: 100%;
        height: auto;
        display: block;
        max-height: 600px;
        object-fit: cover;
        object-position: center;
      }

      .slideshow-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        z-index: 10;
        width: 90%;
        max-width: 800px;
      }

      .slideshow-content.position-left {
        left: 10%;
        transform: translate(0, -50%);
        text-align: left;
      }

      .slideshow-content.position-right {
        left: 90%;
        transform: translate(-100%, -50%);
        text-align: right;
      }

      .slideshow-content.position-center {
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
      }

      .slideshow-title {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        font-weight: bold;
        line-height: 1.2;
        margin-bottom: 1rem;
      }

      .slideshow-subtitle {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        font-weight: 500;
        line-height: 1.3;
        margin-bottom: 1.5rem;
      }

      .slideshow-content .btn {
        text-shadow: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
      }

      .slideshow-content .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
      }

      /* 响应式设计 - 移动端优化 */
      @media (max-width: 768px) {
        .swiper-slide img {
          max-height: 300px;
        }

        .slideshow-content {
          width: 95%;
          padding: 0 10px;
        }

        .slideshow-title {
          font-size: 24px !important;
          margin-bottom: 0.8rem;
        }

        .slideshow-subtitle {
          font-size: 18px !important;
          margin-bottom: 1rem;
        }

        .slideshow-content .btn {
          font-size: 14px !important;
          padding: 8px 16px !important;
        }
      }

      @media (max-width: 480px) {
        .swiper-slide img {
          max-height: 200px;
        }

        .slideshow-title {
          font-size: 20px !important;
          margin-bottom: 0.5rem;
        }

        .slideshow-subtitle {
          font-size: 16px !important;
          margin-bottom: 0.8rem;
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
