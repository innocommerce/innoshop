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
      aiLabelPrompt: "{{ __('aicore::media.ai_prompt') }}",
      aiLabelPromptPlaceholder: "{{ __('aicore::media.ai_prompt_placeholder') }}",
      aiLabelSize: "{{ __('aicore::media.ai_size') }}",
      aiLabelQuality: "{{ __('aicore::media.ai_quality') }}",
      aiLabelLow: "{{ __('aicore::media.ai_low') }}",
      aiLabelMedium: "{{ __('aicore::media.ai_medium') }}",
      aiLabelHigh: "{{ __('aicore::media.ai_high') }}",
      aiLabelGenerate: "{{ __('aicore::media.ai_generate') }}",
      aiLabelGenerating: "{{ __('aicore::media.ai_generating') }}",
    }
  },
  methods: {
    generateAIImage() {
      var self = this;
      if (!this.aiImageDialog.prompt.trim()) {
        ElementPlus.ElMessage.warning('{{ __("aicore::media.ai_enter_prompt") }}');
        return;
      }
      this.aiImageDialog.loading = true;
      this.aiImageDialog.previewUrl = '';
      this.aiImageDialog.resultPath = '';
      var savePath = this.currentFolder ? this.currentFolder.id.replace(/^\//, '') : '';
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
        // axios response interceptor unwraps to response.data, so `res` is the body
        var body = res || {};
        if (body.success || body.data) {
          var result = body.data || body;
          self.aiImageDialog.previewUrl = result.url || result.origin_url;
          self.aiImageDialog.resultPath = result.path;
          ElementPlus.ElMessage.success('{{ __("aicore::media.ai_success") }}');
          if (result.notice) {
            ElementPlus.ElMessage.warning({message: result.notice, duration: 8000, showClose: true});
          }
        } else {
          ElementPlus.ElMessage.error(body.message || '{{ __("aicore::media.ai_failed") }}');
        }
      }).catch(function(err) {
        ElementPlus.ElMessage.error(err.response?.data?.message || '{{ __("aicore::media.ai_failed") }}');
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
