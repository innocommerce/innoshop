@if ($product->video)
  @php
    $videoData = $product->video ?? [];
    $videoType = $videoData['type'] ?? 'custom';
    $videoUrl = '';
    $isIframe = false;
    
    switch($videoType) {
      case 'custom':
        $videoUrl = $videoData['custom'] ?? '';
        break;
      case 'iframe':
        $videoUrl = $videoData['iframe'] ?? '';
        $isIframe = true;
        break;
      case 'local':
        $videoUrl = $videoData['url'] ?? '';
        break;
      default:
        $videoUrl = $videoData['url'] ?? '';
        break;
    }
  @endphp

  @if($videoUrl)
    <div class="video-wrap position-absolute top-0 start-0 w-100 h-100 bg-dark d-none" style="z-index: 1000;">
      @if (!$isIframe)
      <video
        id="product-video"
        class="video-js vjs-big-play-centered w-100 h-100"
        controls loop muted
      >
        <source src="{{ $videoUrl }}" type="video/mp4" />
      </video>
      @else
      <div id="product-video" class="w-100 h-100 d-flex align-items-center justify-content-center"></div>
      @endif
      <div class="close-video position-absolute top-0 end-0 m-3 d-none">
        <i class="bi bi-x-circle fs-3 text-white bg-dark bg-opacity-50 rounded-circle p-2"></i>
      </div>

    </div>

    @push('footer')
    @if (!$isIframe)
      <script>
        // Video.js player instance
        if (typeof window.pVideo === 'undefined') {
          window.pVideo = null;
        }

        // Initialize video player and bind events
        $(function () {
          initVideoPlayer();
        });

        /**
         * Initialize video player and setup events
         */
        function initVideoPlayer() {
          if (!$('#product-video').length) {
            return;
          }
          
          window.pVideo = videojs("product-video");
          
          // Show play button when video metadata is loaded
          window.pVideo.on('loadedmetadata', function() {
            showPlayButton();
          });
          
          // Listen for pause event - keep video visible when paused
          window.pVideo.on('pause', function() {
            showPlayButton();
            // Keep close button visible
          });
          
          bindVideoEvents();
        }

        /**
         * Bind video control events
         */
        function bindVideoEvents() {
          $(document)
            .on('click', '.open-video', function(e) {
              playVideo();
            })
            .on('click', '.close-video', closeVideo);
        }

        /**
         * Start video playback
         */
        function playVideo() {
          // Show video container
          $('.video-wrap').removeClass('d-none');
          
          if (!window.pVideo) {
            initVideoPlayer();
            // Delay playback to wait for initialization
            setTimeout(function() {
              if (window.pVideo) {
                const playPromise = window.pVideo.play();
                
                if (playPromise !== undefined) {
                  playPromise.then(() => {
                    window.pVideo.currentTime(0);
                    hidePlayButton();
                    $('#product-video').fadeIn();
                    showCloseButton();
                  }).catch(error => {
                    showPlayButton();
                  });
                } else {
                  window.pVideo.currentTime(0);
                  hidePlayButton();
                  $('#product-video').fadeIn();
                  showCloseButton();
                }
              }
            }, 500);
            return;
          }
          
          // Try to play video and handle possible errors
          const playPromise = window.pVideo.play();
          
          if (playPromise !== undefined) {
            playPromise.then(() => {
              window.pVideo.currentTime(0);
              hidePlayButton();
              $('#product-video').fadeIn();
              showCloseButton();
            }).catch(error => {
              // If autoplay fails, show play button for manual click
              showPlayButton();
            });
          } else {
            // For older browsers that don't return a Promise from play()
            window.pVideo.currentTime(0);
            hidePlayButton();
            $('#product-video').fadeIn();
            showCloseButton();
          }
        }

        /**
         * Stop video playback and hide player
         */
        function closeVideo() {
          if (!window.pVideo) return;
          
          window.pVideo.pause();
          $('#product-video').fadeOut();
          $('.video-wrap').addClass('d-none');
          hideCloseButton();
          
          const isVideoActive = $('.thumbnail-item.active[data-is-video="true"]').length > 0;
          if (isVideoActive) {
            $('.main-product-img .video-play-overlay').removeClass('d-none');
          }
        }

        /**
         * Show video play button
         */
        function showPlayButton() {
          // Show mobile play button
          $('.video-wrap .open-video').removeClass('d-none');
          // Show desktop play button only when video is active
          const isVideoActive = $('.thumbnail-item.active[data-is-video="true"]').length > 0 || 
                               ($('#mobile-product-swiper .swiper-slide-active').find('[data-is-video="true"]').length > 0);
          
          if (isVideoActive) {
            $('.main-product-img .video-play-overlay').removeClass('d-none');
          }
          
          // Ensure desktop play button is visible
          const $desktopPlayButton = $('.main-product-img .video-play-overlay');
          if (isVideoActive && $desktopPlayButton.hasClass('d-none')) {
            $desktopPlayButton.removeClass('d-none');
          }
        }

        /**
         * Hide video play button
         */
        function hidePlayButton() {
          // Hide mobile play button
          $('.video-wrap .open-video').addClass('d-none');
          // Hide desktop play button
          $('.main-product-img .video-play-overlay').addClass('d-none');
        }

        /**
         * Show video close button
         */
        function showCloseButton() {
          $('.close-video').removeClass('d-none');
        }

        /**
         * Hide video close button
         */
        function hideCloseButton() {
          $('.close-video').addClass('d-none');
        }
      </script>
    @else
      <script>
        // Iframe video content - check if already declared
        if (typeof window.iframeVideoContent === 'undefined') {
          window.iframeVideoContent = '{!! $videoUrl !!}';
        }
        
        // Initialize iframe video player
        $(function() {
          initIframeVideo();
        });

        /**
         * Initialize iframe video player and setup events
         */
        function initIframeVideo() {
          $('#product-video').html(iframeVideoContent);
          
          // Auto-configure iframe styles and display
          const $iframe = $('#product-video iframe');
          $iframe.attr({
            width: '100%',
            height: '100%'
          }).css({
            'max-width': '100%',
            'max-height': '100%',
            'object-fit': 'contain'
          });
          
          // Show close button
          showCloseButton();
          bindVideoEvents();
        }

        /**
         * Bind video control events
         */
        function bindVideoEvents() {
          $(document)
            .on('click', '.open-video', function(e) {
              initIframeVideo();
              $('.video-wrap').removeClass('d-none');
              hidePlayButton();
              showCloseButton();
            })
            .on('click', '.close-video', closeIframeVideo);
        }

        /**
         * Stop iframe video playback and hide player
         */
        function closeIframeVideo() {
          $('#product-video').fadeOut();
          $('#product-video').html('');
          $('.video-wrap').addClass('d-none');
          hideCloseButton();
          showPlayButton();
        }

        /**
         * Show video play button
         */
        function showPlayButton() {
          // Show mobile play button
          $('.video-wrap .open-video').removeClass('d-none');
          // Show desktop play button only when video is active
          const isVideoActive = $('.thumbnail-item.active[data-is-video="true"]').length > 0 || 
                               ($('#mobile-product-swiper .swiper-slide-active').find('[data-is-video="true"]').length > 0);
          
          if (isVideoActive) {
            $('.main-product-img .video-play-overlay').removeClass('d-none');
          }
          
          // Ensure desktop play button is visible
          const $desktopPlayButton = $('.main-product-img .video-play-overlay');
          if (isVideoActive && $desktopPlayButton.hasClass('d-none')) {
            $desktopPlayButton.removeClass('d-none');
          }
        }

        /**
         * Hide video play button
         */
        function hidePlayButton() {
          // Hide mobile play button
          $('.video-wrap .open-video').addClass('d-none');
          // Hide desktop play button
          $('.main-product-img .video-play-overlay').addClass('d-none');
        }

        /**
         * Show video close button
         */
        function showCloseButton() {
          $('.close-video').removeClass('d-none');
        }

        /**
         * Hide video close button
         */
        function hideCloseButton() {
          $('.close-video').addClass('d-none');
        }
      </script>
    @endif
    @endpush
  @endif
@endif