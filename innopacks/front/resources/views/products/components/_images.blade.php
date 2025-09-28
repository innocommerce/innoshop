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
    // Global variable definitions
    let swiper, mobileSwiper, isMobile = window.innerWidth < 992, autoScrollTimer, scrollDirection = 0, resizeTimer;
    
    /**
     * Update thumbnail image sources based on device type
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
     * Handle video player state - pause and hide video
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
     * Handle video control button show/hide
     * @param {boolean} isVideo - Whether it's video content
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
     * Initialize desktop Swiper carousel component
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
     * Handle mobile slide change events
     */
    function handleMobileSlideChange() {
      const activeSlide = this.slides[this.activeIndex];
      const isVideo = $(activeSlide).find('[data-is-video="true"]').length > 0;
      handleVideoControls(isVideo);
    }
    
    /**
     * Initialize mobile Swiper carousel component
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
        
        // Set mobile default state
        setTimeout(setDefaultThumbnail, 100);
      }
    }
    
    /**
     * Start auto scroll
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
     * Stop auto scroll
     */
    function stopAutoScroll() {
      if (autoScrollTimer) {
        clearInterval(autoScrollTimer);
        autoScrollTimer = null;
      }
      scrollDirection = 0;
    }
    
    /**
     * Handle mouse move events to control auto scroll
     * @param {Event} e - Mouse event object
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
     * Update thumbnail selection state and video controls
     * @param {jQuery} $thumbnail - Thumbnail jQuery object
     * @param {boolean} updateMainImage - Whether to update main image
     */
    function updateThumbnailSelection($thumbnail, updateMainImage = true) {
      const isVideo = $thumbnail.data('is-video');
      
      // Update main image
      if (updateMainImage) {
        $('.main-image').attr('src', $thumbnail.data('large-image'));
      }
      
      // Update thumbnail selection styles
      $('.thumbnail-item')
        .removeClass('active border-danger')
        .addClass('border-transparent');
      $thumbnail
        .addClass('active border-danger')
        .removeClass('border-transparent');
      
      // Handle video controls
      handleVideoControls(isVideo);
    }
    
    /**
     * Set default selected thumbnail
     */
    function setDefaultThumbnail() {
      // Prioritize video thumbnails, otherwise select the first image
      let $defaultThumbnail = $('.thumbnail-item[data-is-video="true"]').first();
      if ($defaultThumbnail.length === 0) {
        $defaultThumbnail = $('.thumbnail-item').first();
      }
      
      if ($defaultThumbnail.length > 0) {
        updateThumbnailSelection($defaultThumbnail, true);
      }
    }
    
    /**
     * Handle thumbnail mouse hover events
     */
    function handleThumbnailHover() {
      const $this = $(this);
      
      // Update main image and play button display state
      updateThumbnailSelection($this, true);
      
      // Prevent image scaling issues
      $this.find('img').css('transform', 'scale(1)');
    }
    
    /**
     * Bind thumbnail and scroll events
     */
    function bindThumbnailEvents() {
      // Clear previous event bindings
      $('.thumbnail-item').off('click mouseenter');
      $('.sub-product-img').off('mouseenter mousemove mouseleave');
      $('#mobile-product-swiper .swiper-slide').off('click');
      
      if (!isMobile) {
        // Desktop event bindings
        $('.thumbnail-item')
          .on('click', function() {
            updateThumbnailSelection($(this), true);
          })
          .on('mouseenter', handleThumbnailHover);
        
        // Bind auto scroll events
        $('.sub-product-img')
          .on('mouseenter mousemove', handleMouseMove)
          .on('mouseleave', stopAutoScroll);
          
        // Set default selected state
        setTimeout(setDefaultThumbnail, 100);
      } else {
        // Mobile slide click events
        $('#mobile-product-swiper .swiper-slide').on('click', function() {
          const $thumbnail = $(this).find('.thumbnail-item');
          if ($thumbnail.length > 0) {
            updateThumbnailSelection($thumbnail, false);
          }
        });
      }
    }
    
    /**
     * Handle responsive layout changes
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
     * Initialize all components
     */
    function initializeComponents() {
      updateThumbnailImages();
      initDesktopSwiper();
      initMobileSwiper();
      bindThumbnailEvents();
    }

    // Initialize on page load
    initializeComponents();

    // Debounced handling for window resize
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(handleResponsiveChange, 250);
    });
  </script>
@endpush