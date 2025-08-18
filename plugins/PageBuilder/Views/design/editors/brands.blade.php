{{-- 品牌模块编辑器 --}}
<script type="text/x-template" id="module-editor-brands-template">
  <div class="module-editor">
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

    {{-- 模块标题 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-edit"></i>
        模块标题
      </div>
      <div class="section-content">
        <text-i18n 
          v-model="module.title" 
          @change="onChange" 
          placeholder="请输入模块标题"
        ></text-i18n>
      </div>
    </div>

    {{-- 内容设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-picture"></i>
        内容设置
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">选择品牌</div>
          <div class="search-section">
            <el-autocomplete 
              class="search-input" 
              v-model="keyword" 
              value-key="name" 
              size="small"
              :fetch-suggestions="querySearch" 
              placeholder="请输入关键字搜索品牌" 
              :highlight-first-item="true"
              @select="handleSelect"
              style="width: 100%;"
            ></el-autocomplete>
          </div>
          <div class="products-section">
            <div class="section-subtitle">已选品牌</div>
            <div class="products-list" v-loading="loading">
              <template v-if="module.brands.length">
                <div v-for="(brand, index) in module.brands" :key="brand.id" class="product-item">
                  <div class="product-info">
                    <div class="product-preview">
                      <img :src="brand.logo_url" :alt="brand.name" class="preview-img">
                    </div>
                    <div class="product-details">
                      <div class="product-name">${brand.name}</div>
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
                <p>暂无品牌，请在上方搜索并添加</p>
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
        样式设置
      </div>
      <div class="section-content">
        <div class="setting-group">
          <div class="setting-label">显示列数</div>
          <div class="segmented-buttons">
            <div 
              :class="['segmented-btn', { active: module.columns === 3 }]" 
              @click="setColumns(3)"
            >
              3个
            </div>
            <div 
              :class="['segmented-btn', { active: module.columns === 4 }]" 
              @click="setColumns(4)"
            >
              4个
            </div>
            <div 
              :class="['segmented-btn', { active: module.columns === 6 }]" 
              @click="setColumns(6)"
            >
              6个
            </div>
          </div>
        </div>

        <div class="setting-group">
          <div class="setting-label">自动轮播</div>
          <div class="switch-wrapper">
            <el-switch 
              v-model="module.autoplay" 
              @change="onChange"
              active-text="启用" 
              inactive-text="禁用"
              size="small"
            ></el-switch>
          </div>
        </div>
        <div class="setting-group" v-if="module.autoplay">
          <div class="setting-label">轮播间隔时间</div>
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
            单位：毫秒，建议设置 3000-5000
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">显示品牌名称</div>
          <div class="switch-wrapper">
            <el-switch 
              v-model="module.showNames" 
              @change="onChange"
              active-text="显示" 
              inactive-text="隐藏"
              size="small"
            ></el-switch>
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">图片高度</div>
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
            单位：像素，建议设置 60-120
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">内边距</div>
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
            单位：像素，0为无内边距，控制图片与卡片边缘的间距
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">边框圆角</div>
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
            单位：像素，0为直角，建议设置 4-16
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">边框宽度</div>
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
            单位：像素，0为无边框
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-label">边框颜色</div>
          <el-color-picker 
            v-model="module.borderColor" 
            @change="onChange"
            size="small"
            style="width: 100%;"
            show-alpha
          ></el-color-picker>
        </div>
        <div class="setting-group">
          <div class="setting-label">边框样式</div>
          <el-select 
            v-model="module.borderStyle" 
            @change="onChange"
            size="small"
            style="width: 100%;"
          >
            <el-option label="实线" value="solid"></el-option>
            <el-option label="虚线" value="dashed"></el-option>
            <el-option label="点线" value="dotted"></el-option>
            <el-option label="双线" value="double"></el-option>
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
    delimiters: ['${', '}'],
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
        if (!keyword.trim()) {
          cb([]);
          return;
        }

        axios.get('api/panel/brands/autocomplete?keyword=' + encodeURIComponent(keyword))
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

 