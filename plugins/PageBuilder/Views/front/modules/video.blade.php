{{-- 视频模块前台展示模板 --}}
@php
  $locale = locale_code();
@endphp
<div id="module-{{ $module_id }}" class="module-item module-video">
  <div class="module-content">
    <div class="video-container" style="width: {{ $content['width'] ?? 'wide' === 'narrow' ? '800px' : ($content['width'] === 'full' ? '100%' : '1200px') }}; margin: 0 auto;">
      
      {{-- 模块标题 --}}
      @if(!empty($content['title'][$locale] ?? $content['title']))
        <div class="video-header">
          <h2 class="video-title">{{ $content['title'][$locale] ?? $content['title'] }}</h2>
          @if(!empty($content['description'][$locale] ?? $content['description']))
            <p class="video-description">{{ $content['description'][$locale] ?? $content['description'] }}</p>
          @endif
        </div>
      @endif

      {{-- 视频播放器 --}}
      <div class="video-player-wrapper">
        @if($content['videoType'] === 'local' && !empty($content['videoUrl']))
          {{-- 本地视频 --}}
          <video 
            class="video-player"
            @if($content['autoplay']) autoplay @endif
            @if($content['loop']) loop @endif
            @if($content['muted']) muted @endif
            @if($content['controls']) controls @endif
            preload="metadata"
            style="width: 100%; height: auto; border-radius: 8px;"
          >
            <source src="{{ $content['videoUrl'] }}" type="video/mp4">
            <source src="{{ $content['videoUrl'] }}" type="video/webm">
            <source src="{{ $content['videoUrl'] }}" type="video/ogg">
            {{ __('PageBuilder::common.browser_not_support_video') }}
          </video>
          
        @elseif($content['videoType'] === 'youtube' && !empty($content['videoUrl']))
          {{-- YouTube视频 --}}
          @php
            $youtubeId = '';
            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/', $content['videoUrl'], $matches)) {
              $youtubeId = $matches[1];
            }
          @endphp
          @if($youtubeId)
            @php
              $youtubeParams = [];
              if ($content['autoplay']) $youtubeParams[] = 'autoplay=1';
              if ($content['muted']) $youtubeParams[] = 'mute=1';
              if ($content['loop']) {
                $youtubeParams[] = 'loop=1';
                $youtubeParams[] = 'playlist=' . $youtubeId;
              }
              if (!$content['controls']) $youtubeParams[] = 'controls=0';
              $youtubeQuery = !empty($youtubeParams) ? '?' . implode('&', $youtubeParams) : '';
            @endphp
            <div class="youtube-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
              <iframe 
                src="https://www.youtube.com/embed/{{ $youtubeId }}{{ $youtubeQuery }}"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                allowfullscreen
              ></iframe>
            </div>
          @else
            <div class="video-error">
              <p>{{ __('PageBuilder::common.youtube_url_invalid') }}</p>
            </div>
          @endif
          
        @elseif($content['videoType'] === 'vimeo' && !empty($content['videoUrl']))
          {{-- Vimeo视频 --}}
          @php
            $vimeoId = '';
            if (preg_match('/vimeo\.com\/(\d+)/', $content['videoUrl'], $matches)) {
              $vimeoId = $matches[1];
            }
          @endphp
          @if($vimeoId)
            @php
              $vimeoParams = [];
              if ($content['autoplay']) $vimeoParams[] = 'autoplay=1';
              if ($content['loop']) $vimeoParams[] = 'loop=1';
              if ($content['muted']) $vimeoParams[] = 'muted=1';
              if (!$content['controls']) $vimeoParams[] = 'controls=0';
              $vimeoQuery = !empty($vimeoParams) ? '?' . implode('&', $vimeoParams) : '';
            @endphp
            <div class="vimeo-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
              <iframe 
                src="https://player.vimeo.com/video/{{ $vimeoId }}{{ $vimeoQuery }}"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                allowfullscreen
              ></iframe>
            </div>
          @else
            <div class="video-error">
              <p>{{ __('PageBuilder::common.vimeo_url_invalid') }}</p>
            </div>
          @endif
          
        @else
          {{-- 无视频或封面图片 --}}
          @if(!empty($content['coverImage'][$locale] ?? $content['coverImage']))
            <div class="video-placeholder" style="position: relative; border-radius: 8px; overflow: hidden;">
              <img 
                src="{{ $content['coverImage'][$locale] ?? ($content['coverImage'] ?? image_resize()) }}" 
                alt="{{ __('PageBuilder::common.video_cover') }}"
                style="width: 100%; height: auto; display: block;"
              >
              <div class="play-button-overlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.7); border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="bi bi-play-fill" style="color: white; font-size: 32px; margin-left: 5px;"></i>
              </div>
            </div>
          @elseif(request('design'))
            @include('PageBuilder::front.partials.module-empty', [
                'moduleClass' => 'video',
                'icon' => 'bi-camera-video',
                'message' => __('PageBuilder::modules.add_video_content'),
            ])
          @endif
        @endif
      </div>
    </div>
  </div>

  @if(request()->has('design'))
    <div class="module-edit">
      <div class="edit-wrap">
        <div class="edit" onclick="editModule('{{ $module_id }}')">
          <i class="bi bi-pencil"></i>
          <span>{{ __('PageBuilder::modules.edit') }}</span>
        </div>
        <div class="delete" onclick="deleteModule('{{ $module_id }}')">
          <i class="bi bi-trash"></i>
          <span>{{ __('PageBuilder::modules.delete') }}</span>
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
.module-video {
  margin-bottom: 30px;
}

.video-header {
  text-align: center;
  margin-bottom: 20px;
}

.video-title {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
}

.video-description {
  font-size: 14px;
  color: #666;
  line-height: 1.6;
  margin: 0;
}

.video-player-wrapper {
  position: relative;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.video-player {
  display: block;
  width: 100%;
  height: auto;
}

.video-error {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 40px 20px;
  text-align: center;
  color: #6c757d;
}

.video-placeholder {
  background: #f8f9fa;
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.video-placeholder:hover {
  border-color: #667eea;
  background: #f0f4ff;
}

.play-button-overlay {
  transition: all 0.3s ease;
}

.play-button-overlay:hover {
  background: rgba(0,0,0,0.8) !important;
  transform: translate(-50%, -50%) scale(1.1) !important;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .video-container {
    width: 100% !important;
    padding: 0 15px;
  }
  
  .video-title {
    font-size: 20px;
  }
  
  .video-description {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .video-title {
    font-size: 18px;
  }
  
  .play-button-overlay {
    width: 60px !important;
    height: 60px !important;
  }
  
  .play-button-overlay i {
    font-size: 24px !important;
  }
}
</style>