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

    $videoId = $videoId ?? 'product-video';
  @endphp

  @if($videoUrl)
    <div class="video-wrap position-absolute top-0 start-0 w-100 h-100 bg-dark d-none" style="z-index: 1000;">
      @if (!$isIframe)
      <video
        id="{{ $videoId }}"
        class="video-js vjs-big-play-centered w-100 h-100"
        controls loop muted
      >
        <source src="{{ $videoUrl }}" type="video/mp4" />
      </video>
      @else
      <div id="{{ $videoId }}" class="w-100 h-100 d-flex align-items-center justify-content-center"></div>
      @endif
      <div class="close-video position-absolute top-0 end-0 m-3 d-none">
        <i class="bi bi-x-circle fs-3 text-white bg-dark bg-opacity-50 rounded-circle p-2"></i>
      </div>
    </div>

    @push('footer')
    @if (!$isIframe)
      <script>
      (function() {
        var vid = '{{ $videoId }}';
        var player = null;

        $(function() {
          if (!$('#' + vid).length) return;
          player = videojs(vid);

          // Store in global map for cross-script access
          if (!window.pVideoPlayers) window.pVideoPlayers = {};
          window.pVideoPlayers[vid] = player;

          var $wrap = $('#' + vid).closest('.video-wrap');
          var $parent = $wrap.parent();

          // Find the play button in the same container
          var $playBtn = $parent.find('.video-play-overlay.open-video').first();
          var $closeBtn = $wrap.find('.close-video');

          // Play button click
          $playBtn.on('click', function(e) {
            e.stopPropagation();
            $wrap.removeClass('d-none');
            $playBtn.addClass('d-none');
            player.currentTime(0);
            var p = player.play();
            if (p) p.catch(function() {});
            $closeBtn.removeClass('d-none');
          });

          // Close button click
          $closeBtn.on('click', function(e) {
            e.stopPropagation();
            player.pause();
            $wrap.addClass('d-none');
            $closeBtn.addClass('d-none');
            $playBtn.removeClass('d-none');
          });

          // On pause, show close button so user can resume or close
          player.on('pause', function() {
            $closeBtn.removeClass('d-none');
          });
        });
      })();
      </script>
    @else
      <script>
      (function() {
        var vid = '{{ $videoId }}';
        var iframeContent = '{!! $videoUrl !!}';
        var initialized = false;

        $(function() {
          if (!$('#' + vid).length) return;

          var $wrap = $('#' + vid).closest('.video-wrap');
          var $parent = $wrap.parent();
          var $playBtn = $parent.find('.video-play-overlay.open-video').first();
          var $closeBtn = $wrap.find('.close-video');

          $playBtn.on('click', function(e) {
            e.stopPropagation();
            if (!initialized) {
              $('#' + vid).html(iframeContent);
              $('#' + vid + ' iframe').attr({width: '100%', height: '100%'}).css({
                'max-width': '100%', 'max-height': '100%', 'object-fit': 'contain'
              });
              initialized = true;
            }
            $wrap.removeClass('d-none');
            $playBtn.addClass('d-none');
            $closeBtn.removeClass('d-none');
          });

          $closeBtn.on('click', function(e) {
            e.stopPropagation();
            $('#' + vid).html('');
            initialized = false;
            $wrap.addClass('d-none');
            $closeBtn.addClass('d-none');
            $playBtn.removeClass('d-none');
          });
        });
      })();
      </script>
    @endif
    @endpush
  @endif
@endif
