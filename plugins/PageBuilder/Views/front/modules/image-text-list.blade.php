{{-- 图文列表模块前台展示模板 --}}
@php
  $locale = locale_code();
@endphp
<div id="module-{{ $module_id }}" class="module-item module-image-text-list">
  <div class="module-content">
    <div class="{{ pb_get_width_class($content['width'] ?? 'wide') }}">
      
      {{-- 模块标题 --}}
      @if(!empty($content['title'][$locale] ?? $content['title']))
        <div class="module-title-wrap text-center">
          <div class="module-title">{{ $content['title'][$locale] ?? $content['title'] }}</div>
        </div>
      @endif

      {{-- 图文列表展示 --}}
      <div class="image-text-showcase">
        @if(!empty($content['imageTextItems']) && count($content['imageTextItems']) > 0)
          <div class="image-text-grid image-text-grid-{{ $content['columns'] ?? 4 }}" 
               @if($content['autoplay']) 
                 data-autoplay="true" 
                 data-autoplay-speed="{{ $content['autoplaySpeed'] ?? 3000 }}"
               @endif>
            @foreach($content['imageTextItems'] as $item)
              <div class="image-text-item">
                <div class="item-image-wrapper">
                  @if(!empty($item['link']['value']))
                    <a href="{{ $item['link']['value'] }}" class="item-link" target="_blank" rel="noopener">
                  @endif
                  
                  <img 
                    src="{{ $item['image'][$locale] ?? $item['image'] }}" 
                    alt="{{ $item['name'] }}"
                    class="item-image"
                    loading="lazy"
                  >
                  
                  @if(!empty($item['link']['value']))
                    </a>
                  @endif
                </div>
                
                @if($content['showNames'] ?? true)
                  <div class="item-name">{{ $item['name'] }}</div>
                @endif
              </div>
            @endforeach
          </div>
        @else
          {{-- 空状态 --}}
          <div class="image-text-empty">
            <i class="bi bi-grid-3x3-gap"></i>
            <p>暂无图文项</p>
            <span>请在后台添加图文项</span>
          </div>
        @endif
      </div>
    </div>
  </div>

  @if(request()->has('design'))
    <div class="module-edit">
      <div class="edit-wrap">
        <div class="edit" onclick="editModule('{{ $module_id }}')">
          <i class="bi bi-pencil"></i>
          <span>编辑</span>
        </div>
        <div class="delete" onclick="deleteModule('{{ $module_id }}')">
          <i class="bi bi-trash"></i>
          <span>删除</span>
        </div>
        <div class="up" onclick="moveModule('{{ $module_id }}', 'up')">
          <i class="bi bi-arrow-up"></i>
          <span>{{ __('PageBuilder::modules.move_up') }}</span>
        </div>
        <div class="down" onclick="moveModule('{{ $module_id }}', 'down')">
          <i class="bi bi-arrow-down"></i>
          <span>{{ __('PageBuilder::modules.move_down') }}</span>
        </div>
      </div>
    </div>
  @endif
</div>

<style>
.module-image-text-list {
  margin-bottom: 30px;
}



.image-text-showcase {
  position: relative;
}

.image-text-grid {
  display: grid;
  gap: 24px;
  align-items: stretch;
}

.image-text-grid-3 {
  grid-template-columns: repeat(3, 1fr);
}

.image-text-grid-4 {
  grid-template-columns: repeat(4, 1fr);
}

.image-text-grid-5 {
  grid-template-columns: repeat(5, 1fr);
}

.image-text-grid-6 {
  grid-template-columns: repeat(6, 1fr);
}

.image-text-item {
  text-align: center;
  padding: {{ $content['padding'] ?? 16 }}px;
  border-radius: {{ $content['borderRadius'] ?? 8 }}px;
  transition: all 0.3s ease;
  background: #fff;
  border: {{ $content['borderWidth'] ?? 1 }}px {{ $content['borderStyle'] ?? 'solid' }} {{ $content['borderColor'] ?? '#f0f0f0' }};
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.image-text-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border-color: #667eea;
}

.item-image-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: {{ $content['itemHeight'] ?? 120 }}px;
}

.item-image {
  width: 100%;
  height: {{ $content['itemHeight'] ?? 120 }}px;
  object-fit: cover;
  transition: all 0.3s ease;
  border-radius: {{ ($content['borderRadius'] ?? 8) - 2 }}px;
}

.image-text-item:hover .item-image {
  transform: scale(1.05);
}

.item-link {
  display: block;
  text-decoration: none;
  color: inherit;
}

.item-link:hover {
  text-decoration: none;
}

.item-name {
  font-size: 14px;
  color: #333;
  font-weight: 600;
  margin: 0;
  line-height: 1.4;
}

.image-text-empty {
  text-align: center;
  padding: 60px 20px;
  color: #999;
}

.image-text-empty i {
  font-size: 48px;
  margin-bottom: 16px;
  display: block;
  color: #ccc;
}

.image-text-empty p {
  margin: 0 0 8px 0;
  font-size: 16px;
}

.image-text-empty span {
  font-size: 14px;
  color: #bbb;
}

/* 轮播动画 */
@keyframes imageTextSlide {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-100%);
  }
}

.image-text-grid[data-autoplay="true"] {
  animation: imageTextSlide 20s linear infinite;
}

.image-text-grid[data-autoplay="true"]:hover {
  animation-play-state: paused;
}

/* 响应式设计 */
@media (max-width: 768px) {
  
  .image-text-title {
    font-size: 20px;
  }
  
  .image-text-grid-3,
  .image-text-grid-4,
  .image-text-grid-5,
  .image-text-grid-6 {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .image-text-item {
    padding: 12px !important;
  }
  
  .item-image {
    height: 80px !important;
  }
  
  .item-name {
    font-size: 12px;
  }
}

@media (max-width: 480px) {
  .image-text-grid-3,
  .image-text-grid-4,
  .image-text-grid-5,
  .image-text-grid-6 {
    grid-template-columns: 1fr;
  }
  
  .image-text-item {
    padding: 8px !important;
  }
  
  .item-image {
    height: 60px !important;
  }
}
</style>

<script>
// 图文列表轮播功能
document.addEventListener('DOMContentLoaded', function() {
  const imageTextGrids = document.querySelectorAll('.image-text-grid[data-autoplay="true"]');
  
  imageTextGrids.forEach(function(grid) {
    const autoplaySpeed = parseInt(grid.getAttribute('data-autoplay-speed')) || 3000;
    const items = grid.querySelectorAll('.image-text-item');
    
    if (items.length > 0) {
      // 克隆图文项以实现无缝轮播
      items.forEach(function(item) {
        const clone = item.cloneNode(true);
        grid.appendChild(clone);
      });
      
      // 设置轮播动画
      grid.style.animationDuration = (items.length * 2) + 's';
    }
  });
});
</script>