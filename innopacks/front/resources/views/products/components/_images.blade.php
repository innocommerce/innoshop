@if(is_array($product->images) || $product->video)
  <!-- Desktop thumbnails -->
  <div class="sub-product-img d-none d-lg-block">
    <div class="swiper" id="sub-product-img-swiper">
      <div class="swiper-wrapper">

        @if($product->video)
          <div class="swiper-slide">
            <div class="thumbnail-item video-thumbnail border border-2 border-transparent rounded d-flex align-items-center justify-content-center" 
                 data-large-image="{{ $product->image_url }}"
                 data-thumbnail-image="{{ image_resize($product->image, 100, 100) }}"
                 data-is-video="true"
                 style="width: 100px; height: 100px; overflow: hidden;">
              <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
                <img src="{{ image_resize($product->image, 100, 100) }}" class="img-fluid" style="object-fit: cover; max-width: 100%; max-height: 100%;">
                <div class="video-play-overlay position-absolute top-50 start-50 translate-middle text-white fs-5">
                  <i class="bi bi-play-circle-fill"></i>
                </div>
              </div>
            </div>
          </div>
        @endif
        
        @if(is_array($product->images))
          @foreach($product->images as $image)
            <div class="swiper-slide">
              <div class="thumbnail-item border border-2 border-transparent rounded d-flex align-items-center justify-content-center" 
                   data-large-image="{{ image_resize($image, 600, 600) }}"
                   data-thumbnail-image="{{ image_resize($image, 100, 100) }}"
                   style="width: 100px; height: 100px; overflow: hidden;">
                <img src="{{ image_resize($image, 100, 100) }}" class="img-fluid" style="object-fit: cover; max-width: 100%; max-height: 100%;">
              </div>
            </div>
          @endforeach
        @endif
      </div>
      <div class="sub-product-btn">
        <div class="sub-product-prev"><i class="bi bi-chevron-compact-up"></i></div>
        <div class="sub-product-next"><i class="bi bi-chevron-compact-down"></i></div>
      </div>
      <div class="swiper-pagination sub-product-pagination"></div>
    </div>
  </div>
@endif

<!-- Desktop main image -->
<div class="main-product-img d-none d-lg-block position-relative w-100 overflow-hidden bg-light" style="aspect-ratio: 1/1;">
  @hookinsert('front.product.show.image.before')
  <img src="{{ $product->image_url }}" class="img-fluid main-image w-100 h-100" style="object-fit: cover;">
  
  @if($product->video)
    <div class="video-play-overlay open-video position-absolute top-50 start-50 translate-middle text-white d-none" style="font-size: 3rem; cursor: pointer; z-index: 999;">
      <i class="bi bi-play-circle-fill"></i>
    </div>
  @endif
  
  @include('products.components._video')
</div>

<!-- Mobile slideshow -->
@if(is_array($product->images) || $product->video)
  <div class="mobile-product-slideshow d-lg-none position-relative w-100 overflow-hidden bg-light" style="aspect-ratio: 1/1;">
    <div class="swiper" id="mobile-product-swiper">
      <div class="swiper-wrapper">
        @if($product->video)
          <div class="swiper-slide">
            <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center" data-is-video="true">
              <img src="{{ $product->image_url }}" class="img-fluid w-100 h-100" style="object-fit: cover;">
              <div class="video-play-overlay open-video position-absolute top-50 start-50 translate-middle text-white" style="font-size: 3rem; cursor: pointer;">
                <i class="bi bi-play-circle-fill"></i>
              </div>
            </div>
          </div>
        @endif
        
        @if(is_array($product->images))
          @foreach($product->images as $image)
            <div class="swiper-slide">
              <div class="position-relative w-100 h-100">
                <img src="{{ image_resize($image, 600, 600) }}" class="img-fluid w-100 h-100" style="object-fit: cover;">
              </div>
            </div>
          @endforeach
        @endif
      </div>
      <div class="swiper-pagination mobile-product-pagination"></div>
    </div>
    
    @include('products.components._video')
  </div>
@endif

@push('footer')
  <script>
    // 全局变量定义
    let swiper, mobileSwiper, isMobile = window.innerWidth < 992, autoScrollTimer, scrollDirection = 0, resizeTimer;
    
    /**
     * 根据设备类型更新缩略图图片源
     */
    function updateThumbnailImages() {
      $('.sub-product-img .swiper-slide').each(function() {
        const $item = $(this).find('.thumbnail-item');
        const src = isMobile ? $item.data('large-image') : 
                   ($item.data('thumbnail-image') || $item.data('large-image').replace('600x600', '100x100'));
        $(this).find('img').attr('src', src);
      });
    }
    
    /**
     * 处理视频播放器状态 - 暂停并隐藏视频
     */
    function pauseAndHideVideo() {
      if (window.pVideo) {
        window.pVideo.pause();
        $('#product-video').fadeOut();
      }
      $('.video-wrap').addClass('d-none');
      $('.close-video').addClass('d-none');
    }
    
    /**
     * 处理视频控制按钮的显示/隐藏
     * @param {boolean} isVideo - 是否为视频内容
     */
    function handleVideoControls(isVideo) {
      const $playButton = $('.main-product-img .video-play-overlay');
      
      if (isVideo) {
        pauseAndHideVideo();
        $playButton.removeClass('d-none');
      } else {
        $('.video-wrap').addClass('d-none');
        $playButton.addClass('d-none');
      }
    }
    
    /**
     * 初始化桌面端Swiper轮播组件
     */
    function initDesktopSwiper() {
      if (swiper) swiper.destroy(true, true);
      
      if (!isMobile && $('#sub-product-img-swiper').length) {
        swiper = new Swiper('#sub-product-img-swiper', {
          direction: 'vertical',
          autoHeight: false,
          slidesPerView: 5,
          spaceBetween: 15,
          centeredSlides: false,
          freeMode: false,
          navigation: { 
            nextEl: '.sub-product-next', 
            prevEl: '.sub-product-prev' 
          },
          pagination: { 
            el: '.sub-product-pagination', 
            clickable: true 
          },
          observer: true, 
          observeParents: true,
          watchOverflow: true
        });
      }
    }
    
    /**
     * 处理移动端滑动切换事件
     */
    function handleMobileSlideChange() {
      const activeSlide = this.slides[this.activeIndex];
      const isVideo = $(activeSlide).find('[data-is-video="true"]').length > 0;
      handleVideoControls(isVideo);
    }
    
    /**
     * 初始化移动端Swiper轮播组件
     */
    function initMobileSwiper() {
      if (mobileSwiper) mobileSwiper.destroy(true, true);
      
      if (isMobile && $('#mobile-product-swiper').length) {
        mobileSwiper = new Swiper('#mobile-product-swiper', {
          direction: 'horizontal',
          slidesPerView: 1,
          spaceBetween: 0,
          allowTouchMove: true,
          touchRatio: 1,
          touchAngle: 45,
          grabCursor: true,
          pagination: { 
            el: '.mobile-product-pagination', 
            clickable: true,
            bulletClass: 'swiper-pagination-bullet',
            bulletActiveClass: 'swiper-pagination-bullet-active',
            renderBullet: function (index, className) {
              return '<span class="' + className + '"></span>';
            }
          },
          observer: true, 
          observeParents: true,
          on: {
            slideChange: handleMobileSlideChange
          }
        });
        
        // 设置移动端默认状态
        setTimeout(setDefaultThumbnail, 100);
      }
    }
    
    /**
     * 开始自动滚动
     */
    function startAutoScroll() {
      if (autoScrollTimer) return;
      
      autoScrollTimer = setInterval(() => {
        if (!swiper) return;
        
        if (scrollDirection === 1 && !swiper.isBeginning) {
          swiper.slidePrev();
        } else if (scrollDirection === -1 && !swiper.isEnd) {
          swiper.slideNext();
        } else {
          stopAutoScroll();
        }
      }, 200);
    }
    
    /**
     * 停止自动滚动
     */
    function stopAutoScroll() {
      if (autoScrollTimer) {
        clearInterval(autoScrollTimer);
        autoScrollTimer = null;
      }
      scrollDirection = 0;
    }
    
    /**
     * 处理鼠标移动事件以控制自动滚动
     * @param {Event} e - 鼠标事件对象
     */
    function handleMouseMove(e) {
      const rect = e.currentTarget.getBoundingClientRect();
      const mouseY = e.clientY - rect.top;
      const threshold = 30;
      
      let newDirection = 0;
      if (mouseY < threshold) {
        newDirection = 1;
      } else if (mouseY > rect.height - threshold) {
        newDirection = -1;
      }
      
      if (newDirection !== scrollDirection) {
        scrollDirection = newDirection;
        newDirection !== 0 ? startAutoScroll() : stopAutoScroll();
      }
    }
    
    /**
     * 更新缩略图选中状态和视频控制
     * @param {jQuery} $thumbnail - 缩略图jQuery对象
     * @param {boolean} updateMainImage - 是否更新主图
     */
    function updateThumbnailSelection($thumbnail, updateMainImage = true) {
      const isVideo = $thumbnail.data('is-video');
      
      // 更新主图
      if (updateMainImage) {
        $('.main-image').attr('src', $thumbnail.data('large-image'));
      }
      
      // 更新缩略图选中样式
      $('.thumbnail-item')
        .removeClass('active border-danger')
        .addClass('border-transparent');
      $thumbnail
        .addClass('active border-danger')
        .removeClass('border-transparent');
      
      // 处理视频控制
      handleVideoControls(isVideo);
    }
    
    /**
     * 设置默认选中的缩略图
     */
    function setDefaultThumbnail() {
      // 优先选择视频缩略图，否则选择第一张图片
      let $defaultThumbnail = $('.thumbnail-item[data-is-video="true"]').first();
      if ($defaultThumbnail.length === 0) {
        $defaultThumbnail = $('.thumbnail-item').first();
      }
      
      if ($defaultThumbnail.length > 0) {
        updateThumbnailSelection($defaultThumbnail, true);
      }
    }
    
    /**
     * 处理缩略图鼠标悬停事件
     */
    function handleThumbnailHover() {
      const $this = $(this);
      
      // 更新主图和播放按钮显示状态
      updateThumbnailSelection($this, true);
      
      // 防止图片缩放问题
      $this.find('img').css('transform', 'scale(1)');
    }
    
    /**
     * 绑定缩略图和滚动事件
     */
    function bindThumbnailEvents() {
      // 清除之前的事件绑定
      $('.thumbnail-item').off('click mouseenter');
      $('.sub-product-img').off('mouseenter mousemove mouseleave');
      $('#mobile-product-swiper .swiper-slide').off('click');
      
      if (!isMobile) {
        // 桌面端事件绑定
        $('.thumbnail-item')
          .on('click', function() {
            updateThumbnailSelection($(this), true);
          })
          .on('mouseenter', handleThumbnailHover);
        
        // 绑定自动滚动事件
        $('.sub-product-img')
          .on('mouseenter mousemove', handleMouseMove)
          .on('mouseleave', stopAutoScroll);
          
        // 设置默认选中状态
        setTimeout(setDefaultThumbnail, 100);
      } else {
        // 移动端幻灯片点击事件
        $('#mobile-product-swiper .swiper-slide').on('click', function() {
          const $thumbnail = $(this).find('.thumbnail-item');
          if ($thumbnail.length > 0) {
            updateThumbnailSelection($thumbnail, false);
          }
        });
      }
    }
    
    /**
     * 处理响应式布局变化
     */
    function handleResponsiveChange() {
      const wasMobile = isMobile;
      isMobile = window.innerWidth < 992;
      
      if (wasMobile !== isMobile) {
        updateThumbnailImages();
        initDesktopSwiper();
        initMobileSwiper();
        bindThumbnailEvents();
      }
    }
    
    /**
     * 初始化所有组件
     */
    function initializeComponents() {
      updateThumbnailImages();
      initDesktopSwiper();
      initMobileSwiper();
      bindThumbnailEvents();
    }

    // 页面加载时初始化
    initializeComponents();

    // 窗口大小变化时的防抖处理
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(handleResponsiveChange, 250);
    });
  </script>
@endpush