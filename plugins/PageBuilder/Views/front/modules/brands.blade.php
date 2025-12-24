<div class="brands-module">
  <div class="{{ pb_get_width_class($content['width'] ?? 'wide') }}">
  @if(!empty($content['title']))
    <div class="module-title-wrap text-center">
      <div class="module-title">{{ $content['title'] }}</div>
    </div>
  @endif

  <div class="brands-container">
    @if(!empty($content['brands']))
      @php
        $columns = $content['columns'] ?? 4;
        $colClass = pb_get_bootstrap_columns($columns);
      @endphp
      <div class="row gx-3 gx-lg-4">
        @foreach($content['brands'] as $brand)
          <div class="{{ $colClass }}">
            <div class="brand-item">
            <a href="{{ $brand['url'] ?? '#' }}" class="brand-link" @if(isset($brand['url']) && $brand['url'] !== '#') target="_blank" @endif>
              <div class="brand-logo-wrapper">
                <img 
                  src="{{ $brand['logo_medium'] ?? $brand['logo_url'] ?? '' }}" 
                  alt="{{ $brand['name'] ?? '' }}" 
                  class="brand-logo"
                  style="max-height: {{ $content['itemHeight'] ?? 80 }}px; max-width: 100%;"
                  loading="lazy"
                >
              </div>
            </a>
            @if($content['showNames'] ?? false)
              <div class="brand-name">{{ $brand['name'] ?? '' }}</div>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    @elseif(request('design'))
      @include('PageBuilder::front.partials.module-empty', [
          'moduleClass' => 'brands',
          'icon' => 'bi-award',
          'message' => __('PageBuilder::modules.no_brands'),
      ])
    @endif
  </div>
  </div>
</div>

<style>
.brands-module {
  margin: 20px 0;
}

.brands-container {
  overflow: hidden;
}

.brand-item {
  text-align: center;
  padding: {{ $content['padding'] ?? 0 }}px;
  border-radius: {{ $content['borderRadius'] ?? 12 }}px;
  transition: all 0.3s ease;
  background: transparent;
  border: none;
  box-shadow: none;
}

.brand-item:hover .brand-logo-wrapper {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.brand-link {
  display: block;
  text-decoration: none;
  color: inherit;
}

.brand-logo-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  margin-bottom: {{ ($content['showNames'] ?? false) ? '15px' : '0' }};
  min-height: {{ $content['itemHeight'] ?? 80 }}px;
  width: 100%;
  background: #fff;
  border-radius: {{ $content['borderRadius'] ?? 12 }}px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.brand-logo {
  max-width: 100%;
  max-height: 100%;
  width: auto;
  height: auto;
  object-fit: contain;
  transition: all 0.3s ease;
  filter: grayscale(20%);
  display: block;
}

.brand-item:hover .brand-logo {
  transform: scale(1.08);
  filter: grayscale(0%);
}

.brand-name {
  font-size: 14px;
  color: #333;
  margin-top: 0;
  font-weight: 600;
  line-height: 1.4;
  text-align: center;
}


/* 响应式设计 */
@media (max-width: 767px) {
  .brand-item {
    padding: 10px !important;
  }
  
  .brand-logo {
    height: 60px !important;
  }
}


@media (max-width: 480px) {
  .brand-item {
    padding: 6px !important;
  }
  
  .brand-logo {
    height: 40px !important;
  }
  
  .brand-name {
    font-size: 12px;
  }
}

/* 轮播功能 */
@if($content['autoplay'] ?? false)
.brands-container {
  position: relative;
}

.brands-container .row {
  display: flex;
  transition: transform 0.5s ease;
}
@endif

</style>

@if($content['autoplay'] ?? false)
<script>
document.addEventListener('DOMContentLoaded', function() {
  const brandsRow = document.querySelector('.brands-container .row');
  if (!brandsRow) return;
  
  const brandItems = brandsRow.querySelectorAll('.col-6, .col-4, .col-md-3, .col-md-4, .col-md-6, .col-lg-2, .col-lg-3, .col-lg-4');
  const totalItems = brandItems.length;
  const visibleItems = {{ $content['columns'] ?? 6 }};
  const autoplaySpeed = {{ $content['autoplaySpeed'] ?? 3000 }};
  
  if (totalItems <= visibleItems) return;
  
  let currentIndex = 0;
  
  function nextSlide() {
    currentIndex = (currentIndex + 1) % (totalItems - visibleItems + 1);
    const translateX = -(currentIndex * (100 / visibleItems));
    brandsRow.style.transform = `translateX(${translateX}%)`;
  }
  
  // 自动轮播
  setInterval(nextSlide, autoplaySpeed);
  
  // 鼠标悬停时暂停轮播
  let autoplayInterval;
  
  function startAutoplay() {
    autoplayInterval = setInterval(nextSlide, autoplaySpeed);
  }
  
  function stopAutoplay() {
    clearInterval(autoplayInterval);
  }
  
  brandsRow.addEventListener('mouseenter', stopAutoplay);
  brandsRow.addEventListener('mouseleave', startAutoplay);
  
  // 初始化自动轮播
  startAutoplay();
  });
  </script>
  

  @endif 