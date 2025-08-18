{{-- 视频编辑模块 - 现代化风格 --}}
<template id="module-editor-video-template">
  <div class="video-editor">
    <div class="top-spacing"></div>
    
    {{-- 模块宽度设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-monitor"></i>
        模块宽度
      </div>
      <div class="section-content">
        <div class="segmented-buttons">
          <div 
            :class="['segmented-btn', { active: module.width === 'narrow' }]" 
            @click="setModuleWidth('narrow')"
          >
            窄屏
          </div>
          <div 
            :class="['segmented-btn', { active: module.width === 'wide' }]" 
            @click="setModuleWidth('wide')"
          >
            宽屏
          </div>
          <div 
            :class="['segmented-btn', { active: module.width === 'full' }]" 
            @click="setModuleWidth('full')"
          >
            全屏
          </div>
        </div>
      </div>
    </div>

    {{-- 视频类型选择 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-video-camera"></i>
        视频类型
      </div>
      <div class="section-content">
        <div class="segmented-buttons">
          <div 
            :class="['segmented-btn', { active: module.videoType === 'local' }]" 
            @click="setVideoType('local')"
          >
            本地视频
          </div>
          <div 
            :class="['segmented-btn', { active: module.videoType === 'youtube' }]" 
            @click="setVideoType('youtube')"
          >
            YouTube
          </div>
          <div 
            :class="['segmented-btn', { active: module.videoType === 'vimeo' }]" 
            @click="setVideoType('vimeo')"
          >
            Vimeo
          </div>
        </div>
      </div>
    </div>

    {{-- 本地视频设置 --}}
    <div class="editor-section" v-if="module.videoType === 'local'">
      <div class="section-title">
        <i class="el-icon-upload"></i>
        视频文件
      </div>
      <div class="section-content">
        <div class="video-upload-wrapper">
          <div class="upload-area" @click="openVideoSelector">
            <div v-if="!module.videoUrl" class="upload-placeholder">
              <i class="el-icon-video-camera"></i>
              <p>点击选择视频文件</p>
              <span class="upload-tip">支持 MP4, WebM, OGV 格式</span>
            </div>
            <div v-else class="video-preview">
              <video 
                :src="module.videoUrl" 
                controls 
                preload="metadata"
                class="preview-video"
              ></video>
              <div class="video-info">
                <span class="video-name">@{{ getVideoFileName(module.videoUrl) }}</span>
                <el-button 
                  type="danger" 
                  size="mini" 
                  icon="el-icon-delete" 
                  @click.stop="removeVideo"
                ></el-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 在线视频设置 --}}
    <div class="editor-section" v-if="module.videoType === 'youtube' || module.videoType === 'vimeo'">
      <div class="section-title">
        <i class="el-icon-link"></i>
        视频链接
      </div>
      <div class="section-content">
        <div class="video-url-wrapper">
          <el-input 
            v-model="module.videoUrl" 
            :placeholder="getVideoUrlPlaceholder()"
            @change="onChange"
            size="small"
          >
            <template slot="prepend">
              <i :class="getVideoIcon()"></i>
            </template>
          </el-input>
          <div class="url-tips">
            @{{ getVideoUrlTips() }}
          </div>
        </div>
      </div>
    </div>

    {{-- 视频封面设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-picture"></i>
        视频封面
      </div>
      <div class="section-content">
        <div class="cover-image-wrapper">
          <single-image-selector 
            v-model="module.coverImage" 
            :aspectRatio="16/9" 
            :targetWidth="1280"
            :targetHeight="720"
            @change="onChange"
          ></single-image-selector>
          <div class="cover-tips">
            <i class="el-icon-info"></i>
            建议尺寸: 1280 x 720 (16:9比例)
          </div>
        </div>
      </div>
    </div>

    {{-- 视频控制设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-setting"></i>
        播放控制
      </div>
      <div class="section-content">
        <div class="control-settings">
          {{-- 自动播放 --}}
          <div class="setting-item">
            <div class="setting-label">自动播放</div>
            <div class="setting-control">
              <el-switch 
                v-model="module.autoplay" 
                @change="onChange"
                active-text="启用" 
                inactive-text="禁用"
                size="small"
              ></el-switch>
            </div>
          </div>

          {{-- 循环播放 --}}
          <div class="setting-item">
            <div class="setting-label">循环播放</div>
            <div class="setting-control">
              <el-switch 
                v-model="module.loop" 
                @change="onChange"
                active-text="启用" 
                inactive-text="禁用"
                size="small"
              ></el-switch>
            </div>
          </div>

          {{-- 静音播放 --}}
          <div class="setting-item">
            <div class="setting-label">静音播放</div>
            <div class="setting-control">
              <el-switch 
                v-model="module.muted" 
                @change="onChange"
                active-text="启用" 
                inactive-text="禁用"
                size="small"
              ></el-switch>
            </div>
          </div>

          {{-- 显示控制栏 --}}
          <div class="setting-item">
            <div class="setting-label">显示控制栏</div>
            <div class="setting-control">
              <el-switch 
                v-model="module.controls" 
                @change="onChange"
                active-text="显示" 
                inactive-text="隐藏"
                size="small"
              ></el-switch>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 视频标题 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-edit"></i>
        视频标题
      </div>
      <div class="section-content">
        <text-i18n 
          v-model="module.title" 
          @change="onChange" 
          placeholder="请输入视频标题"
        ></text-i18n>
      </div>
    </div>

    {{-- 视频描述 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-document"></i>
        视频描述
      </div>
      <div class="section-content">
        <text-i18n 
          v-model="module.description" 
          @change="onChange" 
          placeholder="请输入视频描述"
          type="textarea"
          :rows="3"
        ></text-i18n>
      </div>
    </div>
  </div>
</template>

{{-- 视频编辑模块脚本 --}}
<script type="text/javascript">
  Vue.component('module-editor-video', {
    template: '#module-editor-video-template',
    props: ['module'],
    
    data: function() {
      return {
        debounceTimer: null,
        source: {
          locale: $locale
        }
      }
    },

    watch: {
      module: {
        handler: function(val) {
          this.onChange();
        },
        deep: true,
      }
    },

    created: function() {
      // 初始化默认值
      if (!this.module.videoType) {
        this.$set(this.module, 'videoType', 'local');
      }
      if (!this.module.videoUrl) {
        this.$set(this.module, 'videoUrl', '');
      }
      if (!this.module.coverImage) {
        this.$set(this.module, 'coverImage', this.languagesFill(''));
      }
      if (!this.module.title) {
        this.$set(this.module, 'title', this.languagesFill(''));
      }
      if (!this.module.description) {
        this.$set(this.module, 'description', this.languagesFill(''));
      }
      if (!this.module.autoplay) {
        this.$set(this.module, 'autoplay', false);
      }
      if (!this.module.loop) {
        this.$set(this.module, 'loop', false);
      }
      if (!this.module.muted) {
        this.$set(this.module, 'muted', false);
      }
      if (!this.module.controls) {
        this.$set(this.module, 'controls', true);
      }
      if (!this.module.width) {
        this.$set(this.module, 'width', 'wide');
      }
    },

    methods: {
      onChange() {
        // 清除之前的定时器
        if (this.debounceTimer) {
          clearTimeout(this.debounceTimer);
        }
        
        // 设置新的定时器
        this.debounceTimer = setTimeout(() => {
          this.$emit('on-changed', this.module);
        }, 300);
      },

      setModuleWidth(width) {
        this.$set(this.module, 'width', width);
        this.onChange();
      },

      setVideoType(type) {
        this.$set(this.module, 'videoType', type);
        this.$set(this.module, 'videoUrl', '');
        this.onChange();
      },

      openVideoSelector() {
        // 这里需要集成文件选择器
        // 暂时使用简单的文件输入
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'video/*';
        input.onchange = (e) => {
          const file = e.target.files[0];
          if (file) {
            // 这里应该上传文件并获取URL
            // 暂时使用本地URL
            this.$set(this.module, 'videoUrl', URL.createObjectURL(file));
            this.onChange();
          }
        };
        input.click();
      },

      removeVideo() {
        this.$set(this.module, 'videoUrl', '');
        this.onChange();
      },

      getVideoFileName(url) {
        if (!url) return '';
        const parts = url.split('/');
        return parts[parts.length - 1] || 'video.mp4';
      },

      getVideoUrlPlaceholder() {
        switch (this.module.videoType) {
          case 'youtube':
            return '请输入YouTube视频链接，例如: https://www.youtube.com/watch?v=VIDEO_ID';
          case 'vimeo':
            return '请输入Vimeo视频链接，例如: https://vimeo.com/VIDEO_ID';
          default:
            return '请输入视频链接';
        }
      },

      getVideoUrlTips() {
        switch (this.module.videoType) {
          case 'youtube':
            return '支持YouTube分享链接或嵌入链接';
          case 'vimeo':
            return '支持Vimeo分享链接或嵌入链接';
          default:
            return '';
        }
      },

      getVideoIcon() {
        switch (this.module.videoType) {
          case 'youtube':
            return 'el-icon-video-play';
          case 'vimeo':
            return 'el-icon-video-camera';
          default:
            return 'el-icon-video-camera';
        }
      },

      languagesFill(text) {
        const obj = {};
        $languages.forEach(e => {
          obj[e.code] = text;
        });
        return obj;
      }
    }
  });
</script> 