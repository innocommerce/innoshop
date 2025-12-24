{{-- 卡片轮播编辑模块 - 现代化风格 --}}
<template id="module-editor-card-slider-template">
  <div class="card-slider-editor">
    <div class="top-spacing"></div>
    
    {{-- 模块宽度设置 --}}
    <div class="editor-section">
      <div class="section-title">@{{ lang.module_width }}</div>
      <div class="section-content">
        <div class="segmented-buttons">
          <div 
            :class="['segmented-btn', { active: form.width === 'narrow' }]" 
            @click="form.width = 'narrow'"
          >
            @{{ lang.narrow_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'wide' }]" 
            @click="form.width = 'wide'"
          >
            @{{ lang.wide_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'full' }]" 
            @click="form.width = 'full'"
          >
            @{{ lang.full_screen }}
          </div>
        </div>
      </div>
    </div>

    {{-- 模块标题 --}}
    <div class="editor-section">
      <div class="section-title">@{{ lang.module_title }}</div>
      <div class="section-content">
        <text-i18n v-model="form.title" @change="onChange" :placeholder="lang.enter_module_title"></text-i18n>
      </div>
    </div>

    {{-- 显示设置 --}}
    <div class="editor-section">
      <div class="section-title">@{{ lang.display_settings }}</div>
      <div class="section-content">
        {{-- 每行显示数量设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.items_per_row }}</div>
          <div class="segmented-buttons">
            <div 
              :class="['segmented-btn', { active: form.items_per_row === 2 }]" 
              @click="form.items_per_row = 2"
            >
              @{{ lang.items_2 }}
            </div>
            <div 
              :class="['segmented-btn', { active: form.items_per_row === 3 }]" 
              @click="form.items_per_row = 3"
            >
              @{{ lang.items_3 }}
            </div>
            <div 
              :class="['segmented-btn', { active: form.items_per_row === 4 }]" 
              @click="form.items_per_row = 4"
            >
              @{{ lang.items_4 }}
            </div>
            <div 
              :class="['segmented-btn', { active: form.items_per_row === 6 }]" 
              @click="form.items_per_row = 6"
            >
              @{{ lang.items_6 }}
            </div>
          </div>
        </div>

        {{-- 自动轮播设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.autoplay }}</div>
          <div class="switch-wrapper">
            <el-switch 
              v-model="form.autoplay" 
              @change="onChange"
              :disabled="form.screens.length > 1" 
              :active-text="lang.enable" 
              :inactive-text="lang.disable"
              size="small"
            ></el-switch>
          </div>
          <div v-if="form.screens.length > 1" class="form-tip">
            <i class="el-icon-info"></i>
            @{{ lang.delete_extra_screens_to_disable }}
          </div>
        </div>
      </div>
    </div>

    {{-- 商品内容 --}}
    <div class="editor-section">
      <div class="section-title">@{{ lang.product_content }}</div>
      <div class="section-content">
        <div class="tab-container">
          <el-tabs v-model="activeTab" type="card" @tab-click="handleTabClick" class="custom-tabs">
            <el-tab-pane 
              v-for="(screen, index) in form.screens" 
              :key="index" 
              :label="lang.screen + ' ' + (index + 1)"
              :name="index"
            >
              <div class="screen-content">
                {{-- 商品搜索 --}}
                <div class="search-section">
                  <div class="section-subtitle">@{{ lang.add_products }}</div>
                  <el-autocomplete 
                    class="search-input" 
                    v-model="keyword" 
                    value-key="name" 
                    size="small"
                    :fetch-suggestions="querySearch" 
                    :placeholder="lang.search_products_placeholder" 
                    :highlight-first-item="true"
                    @select="handleSelect"
                    style="width: 100%;"
                  ></el-autocomplete>
                </div>

                {{-- 商品列表 --}}
                <div class="products-section">
                  <div class="section-subtitle">@{{ lang.selected_products }}</div>
                  <div class="products-list" v-loading="loading">
                    <template v-if="screen.products.length">
                      <draggable 
                        ghost-class="dragabble-ghost" 
                        :list="screen.products" 
                        @change="itemChange"
                        :options="{ animation: 330 }"
                        class="products-draggable"
                      >
                        <div v-for="(item, index) in screen.products" :key="index" class="product-item">
                          <div class="product-info">
                            <div class="drag-handle">
                              <i class="el-icon-rank"></i>
                            </div>
                            <div class="product-preview">
                              <img :src="thumbnail(item.image_big)" class="preview-img">
                            </div>
                            <div class="product-details">
                              <div class="product-name">@{{ item.name }}</div>
                              <div class="product-price">@{{ item.price_format }}</div>
                            </div>
                          </div>
                          <div class="product-actions">
                            <el-button 
                              type="danger" 
                              size="mini" 
                              icon="el-icon-delete" 
                              circle
                              @click="removeProduct(index)"
                            ></el-button>
                          </div>
                        </div>
                      </draggable>
                    </template>
                    <div v-else class="empty-state">
                      <i class="el-icon-shopping-cart-2"></i>
                      <p>@{{ lang.no_products_search }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </el-tab-pane>
          </el-tabs>

          {{-- 屏幕操作按钮 --}}
          <div class="screen-actions">
            <el-button 
              type="primary" 
              size="small" 
              @click="addScreen" 
              :disabled="!form.autoplay"
              icon="el-icon-plus"
            >
              @{{ lang.add_screen }}
            </el-button>
            <el-button 
              type="danger" 
              size="small" 
              @click="removeScreen"
              :disabled="form.screens.length <= 1"
              icon="el-icon-delete"
            >
              @{{ lang.delete_current_screen }}
            </el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

{{-- 商品编辑模块脚本 --}}
<script type="text/javascript">
  Vue.component('module-editor-card-slider', {
    template: '#module-editor-card-slider-template',
    props: ['module'],
    data: function() {
      return {
        keyword: '',
        productData: [],
        loading: null,
        debounceTimer: null,
        form: {
          screens: [{
            products: []
          }],
          items_per_row: 4,
          activeTab: 0,
          autoplay: true,
          width: 'wide',
          title: {}
        },
        activeTab: 0
      }
    },
    watch: {
      form: {
        handler: function(val) {
          this.onChange();
        },
        deep: true
      }
    },
    created: function() {
      if (this.module) {
        this.form = JSON.parse(JSON.stringify(this.module));
      }

      // 确保 screens 数组存在且有效
      if (!this.form.screens || !Array.isArray(this.form.screens)) {
        this.$set(this.form, 'screens', [{
          products: []
        }]);
      }

      // 确保每个屏幕都有 products 数组
      this.form.screens.forEach(screen => {
        if (!screen.products || !Array.isArray(screen.products)) {
          this.$set(screen, 'products', []);
        }
      });

      if (!this.form.items_per_row) {
        this.$set(this.form, 'items_per_row', 4);
      }

      if (typeof this.form.activeTab === 'undefined') {
        this.$set(this.form, 'activeTab', 0);
      }

      if (!this.form.title) {
        this.$set(this.form, 'title', this.languagesFill(''));
      }

      if (!this.form.width) {
        this.$set(this.form, 'width', 'wide');
      }

      this.activeTab = this.form.activeTab;
      this.$emit('on-changed', this.form);
    },

    methods: {
      onChange() {
        // 清除之前的定时器
        if (this.debounceTimer) {
          clearTimeout(this.debounceTimer);
        }
        
        // 设置新的定时器
        this.debounceTimer = setTimeout(() => {
          this.$emit('on-changed', this.form);
        }, 300);
      },

      languagesFill(text) {
        const obj = {};
        $languages.forEach(e => {
          obj[e.code] = text;
        });
        return obj;
      },

      thumbnail(image) {
        if (!image) {
          return PLACEHOLDER_IMAGE;
        }
        if (typeof image === 'string' && image.indexOf('http') === 0) {
          return image;
        }
        if (typeof image === 'object') {
          const locale = $locale || 'zh_cn';
          return image[locale] || (Object.values(image)[0] || PLACEHOLDER_IMAGE);
        }
        return asset + image;
      },

      tabTitleLanguage(titles) {
        return titles['zh_cn'];
      },

      tabsValueProductData(tabIndex) {
        var that = this;
        if (!this.form.screens[tabIndex].products.length) return;
        this.loading = true;

        axios.get('api/panel/products/names?ids=' + this.form.screens[tabIndex].products.map(e => e.id).join(
          ','), {
          hload: true
        }).then((res) => {
          this.loading = false;
          that.productData = res.data;
          this.itemChange(that.productData);
        })
      },

      querySearch(keyword, cb) {
        axios.get('api/panel/products/autocomplete?keyword=' + encodeURIComponent(keyword), null, {
          hload: true
        }).then((res) => {
          cb(res.data);
        })
      },

      handleSelect(item) {
        const currentScreen = this.form.screens[this.activeTab];
        if (!currentScreen.products.find(v => v.id == item.id)) {
          currentScreen.products.push(item);
        }
        this.keyword = "";
      },

      itemChange(evt) {
        this.form.screens[this.activeTab].products = evt;
      },

      removeProduct(index) {
        if (this.form.screens[this.activeTab].products.length <= 1) {
          this.$message.warning(lang.keep_at_least_one_product);
          return;
        }
        this.form.screens[this.activeTab].products.splice(index, 1);
      },

      handleTabClick(tab) {
        this.activeTab = tab.index;
        this.form.activeTab = tab.index;
        this.tabsValueProductData(tab.index);
      },

      addScreen() {
        this.form.screens.push({
          products: []
        });
        this.activeTab = this.form.screens.length - 1;
      },

      removeScreen() {
        if (this.form.screens.length <= 1) {
          this.$message.warning(lang.keep_at_least_one_screen);
          return;
        }
        this.form.screens.splice(this.activeTab, 1);
        this.activeTab = Math.min(this.activeTab, this.form.screens.length - 1);
      }
    }
  });
</script>
