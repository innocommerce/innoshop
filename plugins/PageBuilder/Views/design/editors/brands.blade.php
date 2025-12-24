{{-- 品牌模块编辑器 --}}
<script type="text/x-template" id="module-editor-brands-template">
  <div class="module-editor">
    <div class="top-spacing"></div>
    
    {{-- 模块宽度设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-monitor"></i>
        @{{ lang.module_width }}
      </div>
      <div class="section-content">
        <div class="segmented-buttons">
          <div 
            :class="['segmented-btn', { active: module.width === 'narrow' }]" 
            @click="setModuleWidth('narrow')"
          >
            @{{ lang.narrow_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: module.width === 'wide' }]" 
            @click="setModuleWidth('wide')"
          >
            @{{ lang.wide_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: module.width === 'full' }]" 
            @click="setModuleWidth('full')"
          >
            @{{ lang.full_screen }}
          </div>
        </div>
      </div>
    </div>

    {{-- 模块标题 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-edit"></i>
        @{{ lang.module_title }}
      </div>
      <div class="section-content">
        <text-i18n 
          v-model="module.title" 
          @change="onChange" 
          :placeholder="lang.enter_module_title"
        ></text-i18n>
      </div>
    </div>

    {{-- 内容设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-picture"></i>
        @{{ lang.content_settings }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">@{{ lang.select_brands }}</div>
          <div class="search-section">
            <el-autocomplete 
              class="search-input" 
              v-model="keyword" 
              value-key="name" 
              size="small"
              :fetch-suggestions="querySearch" 
              :placeholder="lang.search_brand_placeholder" 
              :highlight-first-item="true"
              @select="handleSelect"
              style="width: 100%;"
            ></el-autocomplete>
          </div>
          <div class="products-section">
            <div class="section-subtitle">@{{ lang.selected_brands }}</div>
            <div class="products-list" v-loading="loading">
              <template v-if="module.brands.length">
                <div v-for="(brand, index) in module.brands" :key="brand.id" class="product-item">
                  <div class="product-info">
                    <div class="product-preview">
                      <img :src="brand.logo_url" :alt="brand.name" class="preview-img">
                    </div>
                    <div class="product-details">
                      <div class="product-name">@{{ brand.name }}</div>
                    </div>
                  </div>
                  <div class="product-actions">
                    <el-button 
                      type="danger" 
                      size="mini" 
                      icon="el-icon-delete" 
                      circle
                      @click="removeBrand(index)"
                    ></el-button>
                  </div>
                </div>
              </template>
              <div v-else class="empty-state">
                <i class="el-icon-award"></i>
                <p>@{{ lang.no_brands_search }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 样式设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-brush"></i>
        @{{ lang.style_settings }}
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">@{{ lang.display_columns }}</div>
          <div class="segmented-buttons">
            <div 
              :class="['segmented-btn', { active: module.columns === 3 }]" 
              @click="setColumns(3)"
            >
              @{{ lang.items_3 }}
            </div>
            <div 
              :class="['segmented-btn', { active: module.columns === 4 }]" 
              @click="setColumns(4)"
            >
              @{{ lang.items_4 }}
            </div>
            <div 
              :class="['segmented-btn', { active: module.columns === 6 }]" 
              @click="setColumns(6)"
            >
              @{{ lang.items_6 }}
            </div>
          </div>
        </div>

        <div class="setting-group">
          <div class="setting-label">@{{ lang.autoplay }}</div>
          <div class="switch-wrapper">
            <el-switch 
              v-model="module.autoplay" 
              @change="onChange"
              :active-text="lang.enable" 
              :inactive-text="lang.disable"
              size="small"
            ></el-switch>
          </div>
        </div>
        <div class="setting-group" v-if="module.autoplay">
          <div class="setting-label">@{{ lang.autoplay_interval }}</div>
          <el-input-number 
            v-model="module.autoplaySpeed" 
            @change="onChange"
            :min="1000" 
            :max="10000" 
            :step="500"
            size="small"
            style="width: 100%;"
          ></el-input-number>
          <div class="setting-tip">
            <i class="el-icon-info"></i>
            @{{ lang.autoplay_interval_tip }}
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">@{{ lang.show_brand_names }}</div>
          <div class="switch-wrapper">
            <el-switch 
              v-model="module.showNames" 
              @change="onChange"
              :active-text="lang.show" 
              :inactive-text="lang.hide"
              size="small"
            ></el-switch>
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">@{{ lang.image_height }}</div>
          <el-input-number 
            v-model="module.itemHeight" 
            @change="onChange"
            :min="40" 
            :max="200" 
            :step="10"
            size="small"
            style="width: 100%;"
          ></el-input-number>
          <div class="setting-tip">
            <i class="el-icon-info"></i>
            @{{ lang.image_height_tip }}
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">@{{ lang.padding }}</div>
          <el-input-number 
            v-model="module.padding" 
            @change="onChange"
            :min="0" 
            :max="40" 
            :step="2"
            size="small"
            style="width: 100%;"
          ></el-input-number>
          <div class="setting-tip">
            <i class="el-icon-info"></i>
            @{{ lang.padding_tip }}
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">@{{ lang.border_radius }}</div>
          <el-input-number 
            v-model="module.borderRadius" 
            @change="onChange"
            :min="0" 
            :max="50" 
            :step="1"
            size="small"
            style="width: 100%;"
          ></el-input-number>
          <div class="setting-tip">
            <i class="el-icon-info"></i>
            @{{ lang.border_radius_tip }}
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">@{{ lang.border_width }}</div>
          <el-input-number 
            v-model="module.borderWidth" 
            @change="onChange"
            :min="0" 
            :max="10" 
            :step="1"
            size="small"
            style="width: 100%;"
          ></el-input-number>
          <div class="setting-tip">
            <i class="el-icon-info"></i>
            @{{ lang.border_width_tip }}
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">@{{ lang.border_color }}</div>
          <el-color-picker 
            v-model="module.borderColor" 
            @change="onChange"
            size="small"
            style="width: 100%;"
            show-alpha
          ></el-color-picker>
        </div>
        <div class="setting-group">
          <div class="setting-label">@{{ lang.border_style }}</div>
          <el-select 
            v-model="module.borderStyle" 
            @change="onChange"
            size="small"
            style="width: 100%;"
          >
            <el-option :label="lang.solid" value="solid"></el-option>
            <el-option :label="lang.dashed" value="dashed"></el-option>
            <el-option :label="lang.dotted" value="dotted"></el-option>
            <el-option :label="lang.double" value="double"></el-option>
          </el-select>
        </div>
      </div>
    </div>
  </div>
</script>

<style scoped>
.responsive-settings {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.responsive-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.responsive-label {
  font-size: 12px;
  color: #666;
  font-weight: 500;
}
</style>

<script type="text/javascript">
  Vue.component('module-editor-brands', {
    template: '#module-editor-brands-template',
    props: ['module'],
    data: function() {
      return {
        keyword: '',
        loading: false
      }
    },

    watch: {
      module: {
        handler: function(val) {
          this.$emit('on-changed', val);
        },
        deep: true
      }
    },

    created: function() {
      // 初始化默认值 - 优化为更好看的样式
      if (!this.module.brands) {
        this.$set(this.module, 'brands', []);
      }
      if (!this.module.columns) {
        this.$set(this.module, 'columns', 4); // 默认4列，一行四个
      }
      if (!this.module.autoplay) {
        this.$set(this.module, 'autoplay', false);
      }
      if (!this.module.autoplaySpeed) {
        this.$set(this.module, 'autoplaySpeed', 4000); // 稍微慢一点
      }
      if (!this.module.showNames) {
        this.$set(this.module, 'showNames', true); // 默认显示品牌名称
      }
      if (!this.module.itemHeight) {
        this.$set(this.module, 'itemHeight', 100); // 增加高度，更突出
      }
      if (!this.module.padding) {
        this.$set(this.module, 'padding', 0); // 默认无内边距，用阴影效果
      }
      if (!this.module.borderRadius) {
        this.$set(this.module, 'borderRadius', 12); // 增加圆角，更现代
      }
      if (!this.module.borderWidth) {
        this.$set(this.module, 'borderWidth', 0); // 默认无边框，更简洁
      }
      if (!this.module.borderColor) {
        this.$set(this.module, 'borderColor', '#e8e8e8'); // 更淡的边框色
      }
      if (!this.module.borderStyle) {
        this.$set(this.module, 'borderStyle', 'solid');
      }

      // 模块宽度配置
      if (!this.module.width) {
        this.$set(this.module, 'width', 'wide'); // 默认宽屏
      }
    },

    methods: {
      onChange() {
        this.$emit('on-changed', this.module);
      },

      setColumns(columns) {
        this.module.columns = columns;
        this.onChange();
      },

      setModuleWidth(width) {
        this.module.width = width;
        this.onChange();
      },

      querySearch(keyword, cb) {
        axios.get('api/panel/brands/autocomplete?keyword=' + encodeURIComponent(keyword.trim()))
          .then((res) => {
            cb(res.data || []);
          })
          .catch((error) => {
            console.error('搜索品牌失败:', error);
            cb([]);
          });
      },

      handleSelect(item) {
        // 检查是否已经添加过
        const exists = this.module.brands.find(b => b.id === item.id);
        if (!exists) {
          this.module.brands.push(item);
          this.onChange();
        }
        this.keyword = '';
      },

      removeBrand(index) {
        this.module.brands.splice(index, 1);
        this.onChange();
      }
    }
  });
</script>

 