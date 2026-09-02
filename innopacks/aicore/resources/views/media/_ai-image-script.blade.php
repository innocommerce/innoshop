const aiImageMixin = {
  data() {
    return {
      aiImageDialog: {
        visible: false,
        prompt: '',
        negativePrompt: '',
        template: '',
        size: '1:1',
        quality: 'medium',
        count: 1,
        loading: false,
        previewUrl: '',
        resultPath: '',
        modelInfo: '',
        referenceImage: '',
        referencePreviewUrl: '',
      },
      aiSettingsUrl: '{{ panel_route("settings.index") }}?tab=tab-setting-tools',
      aiLabelPrompt: "{{ __('aicore::media.ai_prompt') }}",
      aiLabelPromptPlaceholder: "{{ __('aicore::media.ai_prompt_placeholder') }}",
      aiLabelNegativePlaceholder: "{{ __('aicore::media.ai_negative_prompt_placeholder') }}",
      aiLabelSize: "{{ __('aicore::media.ai_size') }}",
      aiLabelQuality: "{{ __('aicore::media.ai_quality') }}",
      aiLabelLow: "{{ __('aicore::media.ai_low') }}",
      aiLabelMedium: "{{ __('aicore::media.ai_medium') }}",
      aiLabelHigh: "{{ __('aicore::media.ai_high') }}",
      aiLabelGenerate: "{{ __('aicore::media.ai_generate') }}",
      aiLabelGenerating: "{{ __('aicore::media.ai_generating') }}",
      aiLabelNoModel: "{{ __('aicore::media.ai_no_model') }}",
      aiLabelOpenSettings: "{{ __('aicore::media.ai_open_settings') }}",
      aiTemplates: {
        product_white: "{{ __('aicore::media.ai_tpl_product_white_prompt') }}",
        portrait: "{{ __('aicore::media.ai_tpl_portrait_prompt') }}",
        social_square: "{{ __('aicore::media.ai_tpl_social_square_prompt') }}",
        abstract_bg: "{{ __('aicore::media.ai_tpl_abstract_bg_prompt') }}",
      },
    }
  },
  methods: {
    applyTemplate(value) {
      if (!value) return;
      this.aiImageDialog.prompt = this.aiTemplates[value] || '';
    },

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
        count: this.aiImageDialog.count,
        save_path: savePath,
      };
      if (this.aiImageDialog.negativePrompt.trim()) {
        params.negative_prompt = this.aiImageDialog.negativePrompt.trim();
      }
      if (this.aiImageDialog.referenceImage) {
        params.reference_image = this.aiImageDialog.referenceImage;
      }
      http.post('ai/generate_image', params).then(function(res) {
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
      }).catch(function() {
        // Error toast is already shown by the global http interceptor in _header.blade.php.
      }).finally(function() {
        self.aiImageDialog.loading = false;
      });
    },

    useAIImage() {
      if (this.aiImageDialog.previewUrl && this.aiImageDialog.resultPath) {
        this.loadFiles(this.currentFolder ? this.currentFolder.id : '/');
        this.aiImageDialog.visible = false;
        this.aiImageDialog.prompt = '';
        this.aiImageDialog.negativePrompt = '';
        this.aiImageDialog.template = '';
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
      this.aiImageDialog.negativePrompt = '';
      this.aiImageDialog.template = '';
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
      this.aiImageDialog.negativePrompt = '';
      this.aiImageDialog.template = '';
      this.aiImageDialog.previewUrl = '';
      this.aiImageDialog.resultPath = '';
      this.aiImageDialog.visible = true;
      this.loadAIModelInfo();
    },
  }
};
__fmApp.mixin(aiImageMixin);
