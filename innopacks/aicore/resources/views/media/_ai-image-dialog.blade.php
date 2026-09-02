<!-- AI Image Generation Dialog -->
<el-dialog v-model="aiImageDialog.visible" width="960px" :close-on-click-modal="false" class="ai-image-dialog">
  <template #header>
    <div class="ai-dialog-header">
      <div class="ai-dialog-title">
        <span class="ai-dialog-icon"><el-icon><component :is="'MagicStick'"></component></el-icon></span>
        <span>{{ __('aicore::media.ai_image') }}</span>
      </div>
      <a class="ai-model-pill"
         :href="aiSettingsUrl"
         target="_blank"
         :title="aiLabelOpenSettings">
        <span class="ai-model-name" :title="aiImageDialog.modelInfo || ''">@{{ aiImageDialog.modelInfo || aiLabelNoModel }}</span>
        <span class="ai-model-divider"></span>
        <el-icon class="ai-model-icon"><component :is="'Setting'"></component></el-icon>
      </a>
    </div>
  </template>
  <div class="ai-dialog-body">
    <div class="ai-pane-left">
      <!-- Reference Image -->
      <div v-if="aiImageDialog.referenceImage" class="ai-ref-section">
        <div class="ai-section-label">{{ __('aicore::media.ai_reference_image') }}</div>
        <div class="ai-ref-card">
          <el-image :src="aiImageDialog.referencePreviewUrl" class="ai-ref-thumb" fit="cover"></el-image>
          <div class="ai-ref-info">
            <span class="ai-ref-name">@{{ aiImageDialog.referenceImage?.name || '' }}</span>
          </div>
          <button type="button" class="ai-ref-remove" @click="aiImageDialog.referenceImage = ''; aiImageDialog.referencePreviewUrl = '';" aria-label="Remove">
            <i class="ai-ref-remove-icon"></i>
          </button>
        </div>
      </div>

      <!-- Template -->
      <div class="ai-template-section">
        <div class="ai-section-label">{{ __('aicore::media.ai_template') }}</div>
        <el-select v-model="aiImageDialog.template" @change="applyTemplate" popper-class="ai-select-popper" style="width:100%">
          <el-option label="{{ __('aicore::media.ai_tpl_none') }}" value=""></el-option>
          <el-option label="{{ __('aicore::media.ai_tpl_product_white') }}" value="product_white"></el-option>
          <el-option label="{{ __('aicore::media.ai_tpl_portrait') }}" value="portrait"></el-option>
          <el-option label="{{ __('aicore::media.ai_tpl_social_square') }}" value="social_square"></el-option>
          <el-option label="{{ __('aicore::media.ai_tpl_abstract_bg') }}" value="abstract_bg"></el-option>
        </el-select>
      </div>

      <!-- Prompt -->
      <div class="ai-prompt-section">
        <div class="ai-section-label">@{{ aiLabelPrompt }}</div>
        <el-input v-model="aiImageDialog.prompt" type="textarea" :rows="4"
          :placeholder="aiLabelPromptPlaceholder" resize="none"></el-input>
      </div>

      <!-- Negative Prompt -->
      <div class="ai-negative-section">
        <div class="ai-section-label">{{ __('aicore::media.ai_negative_prompt') }}</div>
        <el-input v-model="aiImageDialog.negativePrompt" type="textarea" :rows="2"
          :placeholder="aiLabelNegativePlaceholder" resize="none"></el-input>
      </div>

      <!-- Options: Size / Quality / Count -->
      <el-row :gutter="10" class="ai-options-row">
        <el-col :span="11">
          <div class="ai-section-label">@{{ aiLabelSize }}</div>
          <el-select v-model="aiImageDialog.size" popper-class="ai-select-popper" style="width:100%">
            <el-option label="1:1 (1024×1024)" value="1:1"></el-option>
            <el-option label="3:2 (1536×1024)" value="3:2"></el-option>
            <el-option label="2:3 (1024×1536)" value="2:3"></el-option>
            <el-option label="16:9 (1792×1024)" value="16:9"></el-option>
            <el-option label="9:16 (1024×1792)" value="9:16"></el-option>
          </el-select>
        </el-col>
        <el-col :span="8">
          <div class="ai-section-label">@{{ aiLabelQuality }}</div>
          <el-select v-model="aiImageDialog.quality" popper-class="ai-select-popper" style="width:100%">
            <el-option :label="aiLabelLow" value="low"></el-option>
            <el-option :label="aiLabelMedium" value="medium"></el-option>
            <el-option :label="aiLabelHigh" value="high"></el-option>
          </el-select>
        </el-col>
        <el-col :span="5">
          <div class="ai-section-label">{{ __('aicore::media.ai_count') }}</div>
          <el-select v-model="aiImageDialog.count" popper-class="ai-select-popper" style="width:100%">
            <el-option label="1" :value="1"></el-option>
            <el-option label="2" :value="2"></el-option>
            <el-option label="4" :value="4"></el-option>
          </el-select>
        </el-col>
      </el-row>
    </div>

    <div class="ai-pane-right">
      <div class="ai-section-label">{{ __('aicore::media.ai_preview') }}</div>
      <div class="ai-preview-card" :class="{ 'is-loading': aiImageDialog.loading }">
        <div v-if="aiImageDialog.loading" class="ai-preview-loading">
          <el-icon class="is-loading"><component :is="'Loading'"></component></el-icon>
          <span class="ai-preview-loading-text">@{{ aiLabelGenerating }}</span>
        </div>
        <div v-else-if="aiImageDialog.previewUrl" class="ai-preview-result">
          <el-image :src="aiImageDialog.previewUrl" fit="contain" class="ai-preview-image"></el-image>
        </div>
        <div v-else class="ai-placeholder">
          <el-icon class="ai-placeholder-icon"><component :is="'Picture'"></component></el-icon>
          <div class="ai-placeholder-title">{{ __('aicore::media.ai_placeholder_title') }}</div>
          <div class="ai-placeholder-hint">{{ __('aicore::media.ai_placeholder_hint') }}</div>
        </div>
      </div>
    </div>
  </div>
  <template #footer>
    <div class="ai-dialog-footer">
      <el-button @click="aiImageDialog.visible = false">{{ __('panel/media.cancel_btn') }}</el-button>
      <div class="ai-dialog-footer-actions">
        <el-button type="success" @click="generateAIImage" :loading="aiImageDialog.loading">
          <el-icon v-if="!aiImageDialog.loading"><component :is="'MagicStick'"></component></el-icon>
          @{{ aiImageDialog.loading ? aiLabelGenerating : aiLabelGenerate }}
        </el-button>
        <el-button v-if="aiImageDialog.previewUrl && !aiImageDialog.loading" type="success" @click="useAIImage">
          <el-icon><component :is="'Check'"></component></el-icon> {{ __('aicore::media.ai_use') }}
        </el-button>
      </div>
    </div>
  </template>
</el-dialog>
