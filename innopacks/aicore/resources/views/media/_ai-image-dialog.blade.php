<!-- AI Image Generation Dialog -->
<el-dialog v-model="aiImageDialog.visible" width="640px" :close-on-click-modal="false" class="ai-image-dialog">
  <template #header>
    <div class="ai-dialog-header">
      <div class="ai-dialog-title">
        <span class="ai-dialog-icon"><el-icon><component :is="'MagicStick'"></component></el-icon></span>
        <span>{{ __('aicore::media.ai_image') }}</span>
      </div>
      <el-tag size="small" type="info" effect="plain">@{{ aiImageDialog.modelInfo }}</el-tag>
    </div>
  </template>
  <div class="ai-dialog-body">
    <!-- Reference Image -->
    <div v-if="aiImageDialog.referenceImage" class="ai-ref-section">
      <div class="ai-ref-label">{{ __('aicore::media.ai_reference_image') }}</div>
      <div class="ai-ref-card">
        <el-image :src="aiImageDialog.referencePreviewUrl" class="ai-ref-thumb" fit="cover"></el-image>
        <div class="ai-ref-info">
          <span class="ai-ref-name">@{{ aiImageDialog.referenceImage?.name || '' }}</span>
        </div>
        <el-button class="ai-ref-remove" size="small" circle @click="aiImageDialog.referenceImage = ''; aiImageDialog.referencePreviewUrl = '';">
          <el-icon><component :is="'Close'"></component></el-icon>
        </el-button>
      </div>
    </div>

    <!-- Prompt -->
    <div class="ai-prompt-section">
      <div class="ai-section-label">@{{ aiLabelPrompt }}</div>
      <el-input v-model="aiImageDialog.prompt" type="textarea" :rows="5"
        :placeholder="aiLabelPromptPlaceholder" resize="none"></el-input>
    </div>

    <!-- Options -->
    <el-row :gutter="16" class="ai-options-row">
      <el-col :span="12">
        <div class="ai-section-label">@{{ aiLabelSize }}</div>
        <el-select v-model="aiImageDialog.size" style="width:100%">
          <el-option label="1:1 (1024x1024)" value="1:1"></el-option>
          <el-option label="3:2 (Landscape)" value="3:2"></el-option>
          <el-option label="2:3 (Portrait)" value="2:3"></el-option>
        </el-select>
      </el-col>
      <el-col :span="12">
        <div class="ai-section-label">@{{ aiLabelQuality }}</div>
        <el-select v-model="aiImageDialog.quality" style="width:100%">
          <el-option :label="aiLabelLow" value="low"></el-option>
          <el-option :label="aiLabelMedium" value="medium"></el-option>
          <el-option :label="aiLabelHigh" value="high"></el-option>
        </el-select>
      </el-col>
    </el-row>

    <!-- Preview -->
    <div v-if="aiImageDialog.previewUrl" class="ai-preview-section">
      <div class="ai-preview-card">
        <el-image :src="aiImageDialog.previewUrl" fit="contain" class="ai-preview-image"></el-image>
      </div>
    </div>
  </div>
  <template #footer>
    <div class="ai-dialog-footer">
      <el-button @click="aiImageDialog.visible = false">{{ __('panel/media.cancel_btn') }}</el-button>
      <div class="ai-dialog-footer-actions">
        <el-button type="primary" @click="generateAIImage" :loading="aiImageDialog.loading">
          <el-icon v-if="!aiImageDialog.loading"><component :is="'MagicStick'"></component></el-icon>
          @{{ aiImageDialog.loading ? aiLabelGenerating : aiLabelGenerate }}
        </el-button>
        <el-button v-if="aiImageDialog.previewUrl" type="success" @click="useAIImage">
          <el-icon><component :is="'Check'"></component></el-icon> {{ __('aicore::media.ai_use') }}
        </el-button>
      </div>
    </div>
  </template>
</el-dialog>
