<div class="mb-3 col-12 col-md-8" id="product-video">
  <label class="form-label">{{ __('panel/product.video') }}</label>
  <div class="border rounded">
    <div class="nav nav-tabs video-nav-tabs" role="tablist">
      <button :class="['nav-link rounded-0', videoForm.videoType == 'local' ? 'active' : '']" 
              @click="videoTypeChange('local')" 
              data-bs-toggle="tab" 
              data-bs-target="#nav-v-local" 
              type="button">{{ __('panel/product.video_local') }}</button>
      <button :class="['nav-link rounded-0', videoForm.videoType == 'iframe' ? 'active' : '']" 
              @click="videoTypeChange('iframe')" 
              data-bs-toggle="tab" 
              data-bs-target="#nav-v-iframe" 
              type="button">{{ __('panel/product.video_iframe') }}</button>
      <button :class="['nav-link rounded-0', videoForm.videoType == 'custom' ? 'active' : '']" 
              @click="videoTypeChange('custom')" 
              data-bs-toggle="tab" 
              data-bs-target="#nav-v-custom" 
              type="button">{{ __('panel/product.video_custom') }}</button>
    </div>

    <div class="tab-content p-3" id="nav-tabContent">
      <div :class="['tab-pane fade', videoForm.videoType == 'local' ? 'show active' : '']" id="nav-v-local">
        <div class="d-flex align-items-end">
          <div class="set-product-img wh-80 rounded-2 me-2 border d-flex justify-content-center align-items-center cursor-pointer" 
               @click="addProductVideo"
               style="background-color: #f8f9fa;"
               title="Click to select video file">
            <i v-if="videoForm.url" class="bi bi-play-circle fs-1 text-primary"></i>
            <i v-else class="bi bi-plus fs-1 text-muted"></i>
          </div>
          <div v-if="videoForm.url" class="video-actions">
            <a target="_blank" :href="videoForm.url" class="btn btn-sm btn-outline-primary mb-2">
              <i class="bi bi-eye me-1"></i>{{ __('panel/common.preview') }}
            </a>
            <button type="button" @click="deleteVideo" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-trash me-1"></i>{{ __('panel/common.delete') }}
            </button>
          </div>
        </div>
        <div class="form-text mt-2">
          <i class="bi bi-info-circle me-1"></i>{{ __('panel/product.video_local_help') }}
        </div>
      </div>
      
      <div :class="['tab-pane fade', videoForm.videoType == 'iframe' ? 'show active' : '']" id="nav-v-iframe">
        <textarea class="form-control" 
                  rows="3" 
                  placeholder="{{ __('panel/product.video_iframe_placeholder') }}" 
                  v-model="videoForm.iframe"></textarea>
        <div class="form-text mt-2">
          <i class="bi bi-info-circle me-1"></i>{{ __('panel/product.video_iframe_help') }}
        </div>
      </div>
      
      <div :class="['tab-pane fade', videoForm.videoType == 'custom' ? 'show active' : '']" id="nav-v-custom">
        <input class="form-control" 
               placeholder="{{ __('panel/product.video_custom_placeholder') }}" 
               v-model="videoForm.custom">
        <div class="form-text mt-2">
          <i class="bi bi-info-circle me-1"></i>{{ __('panel/product.video_custom_help') }}
        </div>
      </div>
    </div>

    <input type="hidden" name="video" :value="videoForm.path">
  </div>
</div>

@push('footer')
<script>
  // Video module Vue application
  const videoApp = Vue.createApp({
    data() {
      return {
        videoForm: {
          videoType: 'local', // Default to local video
          url: '', // Local video URL
          iframe: '', // iframe code
          custom: '', // Custom path
          path: '' // Final submission path
        }
      };
    },
    
    mounted() {
      // Initialize video data
      this.initVideoData();
    },
    
    watch: {
      // Watch video form changes and update the final path
      'videoForm.videoType'() {
        this.updateVideoPath();
      },
      'videoForm.url'() {
        this.updateVideoPath();
      },
      'videoForm.iframe'() {
        this.updateVideoPath();
      },
      'videoForm.custom'() {
        this.updateVideoPath();
      }
    },
    
    methods: {
      /**
       * Initialize video data
       * Safely handle various data formats for video initialization
       */
      initVideoData() {
        // Get existing video data from backend (if any)
        const existingVideo = @json(old('video', $product->video ?? ''));
        
        // Reset all fields to default values
        this.resetVideoForm();
        
        if (existingVideo) {
          // If video data exists, try to parse it
          if (typeof existingVideo === 'string') {
            // If it's a string, try to parse as JSON
            try {
              const videoData = JSON.parse(existingVideo);
              this.setVideoDataFromObject(videoData);
            } catch (e) {
              // Parse failed, might be a simple URL string
              this.videoForm.url = this.sanitizeString(existingVideo);
              this.videoForm.videoType = 'local';
            }
          } else if (typeof existingVideo === 'object' && existingVideo !== null) {
            // If it's an object, set directly
            this.setVideoDataFromObject(existingVideo);
          }
        }
        
        this.updateVideoPath();
      },
      
      /**
       * Reset video form to default values
       * Reset video form fields to their default state
       */
      resetVideoForm() {
        this.videoForm.videoType = 'local';
        this.videoForm.url = '';
        this.videoForm.iframe = '';
        this.videoForm.custom = '';
        this.videoForm.path = '';
      },
      
      /**
       * Set video data from object
       * Set video data from a given object
       */
      setVideoDataFromObject(videoData) {
        if (videoData && typeof videoData === 'object') {
          this.videoForm.videoType = this.sanitizeVideoType(videoData.type);
          this.videoForm.url = this.sanitizeString(videoData.url);
          this.videoForm.iframe = this.sanitizeString(videoData.iframe);
          this.videoForm.custom = this.sanitizeString(videoData.custom);
        }
      },
      
      /**
       * Sanitize video type
       * Clean video type to ensure it's a valid value
       */
      sanitizeVideoType(type) {
        const validTypes = ['local', 'iframe', 'custom'];
        return validTypes.includes(type) ? type : 'local';
      },
      
      /**
       * Sanitize string value
       * Clean string value to ensure a valid string is returned
       */
      sanitizeString(value) {
        if (typeof value === 'string') {
          return value.trim();
        }
        return '';
      },
      
      /**
       * Handle video type switching
       */
      videoTypeChange(type) {
        this.videoForm.videoType = type;
      },
      
      /**
       * Add local video - call built-in file manager
       * Add local video by calling the built-in file manager
       */
      addProductVideo() {
        const self = this;
        // Use the built-in file manager to select video files
        inno.fileManagerIframe(function(file) {
          if (file) {
            // Get the original URL or path of the video file
            let videoPath = file.origin_url || file.url || file.path;
            
            // Clean video path, remove possible redundant characters
            if (videoPath) {
              videoPath = self.sanitizeString(videoPath);
              // Remove possible backticks and other special characters
              videoPath = videoPath.replace(/[`'"]/g, '').trim();
              self.videoForm.url = videoPath;
            }
          }
        }, {
          type: "video", // Specify file type as video
          multiple: false // Only allow selecting a single file
        });
      },
      
      /**
       * Delete video
       * Delete video data
       */
      deleteVideo() {
        // Clear all video-related fields
        this.videoForm.url = '';
        this.videoForm.iframe = '';
        this.videoForm.custom = '';
        // Update final submission path
        this.updateVideoPath();
      },
      
      /**
       * Update video path
       * Update video path and generate final submission data
       */
      updateVideoPath() {
        const currentValue = this.getCurrentVideoValue();
        
        // If current type has content, generate complete video data object
        if (currentValue && currentValue.trim()) {
          const videoData = this.buildVideoDataObject();
          this.videoForm.path = JSON.stringify(videoData);
        } else {
          this.videoForm.path = '';
        }
      },
      
      /**
       * Get current video value based on type
       * Get the corresponding video value based on current type
       */
      getCurrentVideoValue() {
        switch (this.videoForm.videoType) {
          case 'local':
            return this.videoForm.url;
          case 'iframe':
            return this.videoForm.iframe;
          case 'custom':
            return this.videoForm.custom;
          default:
            return '';
        }
      },
      
      /**
       * Build video data object for submission
       * Save all three types of video content, only type differs
       */
      buildVideoDataObject() {
        const videoData = {
          type: this.videoForm.videoType
        };
        
        // Save all three types of content simultaneously
        if (this.videoForm.url) {
          videoData.url = this.videoForm.url.trim();
        }
        
        if (this.videoForm.iframe) {
          videoData.iframe = this.videoForm.iframe.trim();
        }
        
        if (this.videoForm.custom) {
          videoData.custom = this.videoForm.custom.trim();
        }
        
        return videoData;
      }
    }
  });
  
  // Mount video application to specified element
  videoApp.mount('#product-video');
</script>
@endpush