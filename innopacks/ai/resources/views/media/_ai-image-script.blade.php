const aiImageMixin = {
  data() {
    return {
      aiImageDialog: {
        visible: false,
        prompt: '',
        size: '1:1',
        quality: 'medium',
        loading: false,
        previewUrl: '',
        resultPath: '',
        modelInfo: '',
        referenceImage: '',
        referencePreviewUrl: '',
      },
      aiLabelPrompt: "{{ __('ai::media.ai_prompt') }}",
      aiLabelPromptPlaceholder: "{{ __('ai::media.ai_prompt_placeholder') }}",
      aiLabelSize: "{{ __('ai::media.ai_size') }}",
      aiLabelQuality: "{{ __('ai::media.ai_quality') }}",
      aiLabelLow: "{{ __('ai::media.ai_low') }}",
      aiLabelMedium: "{{ __('ai::media.ai_medium') }}",
      aiLabelHigh: "{{ __('ai::media.ai_high') }}",
      aiLabelGenerate: "{{ __('ai::media.ai_generate') }}",
      aiLabelGenerating: "{{ __('ai::media.ai_generating') }}",
    }
  },
  methods: {
    generateAIImage() {
      var self = this;
      if (!this.aiImageDialog.prompt.trim()) {
        ElementPlus.ElMessage.warning('{{ __("ai::media.ai_enter_prompt") }}');
        return;
      }
      this.aiImageDialog.loading = true;
      this.aiImageDialog.previewUrl = '';
      this.aiImageDialog.resultPath = '';
      var savePath = this.currentFolder ? this.currentFolder.id.replace(/^\//, '') : '';
      if (!savePath) savePath = 'ai-images';
      var params = {
        prompt: this.aiImageDialog.prompt,
        size: this.aiImageDialog.size,
        quality: this.aiImageDialog.quality,
        save_path: savePath,
      };
      if (this.aiImageDialog.referenceImage) {
        params.reference_image = this.aiImageDialog.referenceImage;
      }
      http.post('ai/generate_image', params).then(function(res) {
        var data = res.data;
        if (data.status === 'success' || data.data) {
          var result = data.data || data;
          self.aiImageDialog.previewUrl = result.url || result.origin_url;
          self.aiImageDialog.resultPath = result.path;
          ElementPlus.ElMessage.success('{{ __("ai::media.ai_success") }}');
        } else {
          ElementPlus.ElMessage.error(data.message || '{{ __("ai::media.ai_failed") }}');
        }
      }).catch(function(err) {
        ElementPlus.ElMessage.error(err.response?.data?.message || '{{ __("ai::media.ai_failed") }}');
      }).finally(function() {
        self.aiImageDialog.loading = false;
      });
    },

    useAIImage() {
      if (this.aiImageDialog.previewUrl && this.aiImageDialog.resultPath) {
        this.loadFiles(this.currentFolder ? this.currentFolder.id : '/');
        this.aiImageDialog.visible = false;
        this.aiImageDialog.prompt = '';
        this.aiImageDialog.previewUrl = '';
        this.aiImageDialog.resultPath = '';
        this.aiImageDialog.referenceImage = '';
        this.aiImageDialog.referencePreviewUrl = '';
      }
    },

    imageToImage(file) {
      if (!file) return;
      this.aiImageDialog.referenceImage = file.path || '';
      this.aiImageDialog.referencePreviewUrl = file.origin_url || file.url || '';
      this.aiImageDialog.prompt = '';
      this.aiImageDialog.previewUrl = '';
      this.aiImageDialog.resultPath = '';
      this.aiImageDialog.visible = true;
      this.hideContextMenu();
      this.loadAIModelInfo();
    },

    loadAIModelInfo() {
      var self = this;
      http.get('ai/models_info').then(function(res) {
        var data = res.data || res;
        if (data && data.image_model) {
          self.aiImageDialog.modelInfo = data.image_model;
        }
      }).catch(function() {});
    },

    openAIDialog() {
      this.aiImageDialog.referenceImage = '';
      this.aiImageDialog.referencePreviewUrl = '';
      this.aiImageDialog.prompt = '';
      this.aiImageDialog.previewUrl = '';
      this.aiImageDialog.resultPath = '';
      this.aiImageDialog.visible = true;
      this.loadAIModelInfo();
    },
  }
};
__fmApp.mixin(aiImageMixin);
