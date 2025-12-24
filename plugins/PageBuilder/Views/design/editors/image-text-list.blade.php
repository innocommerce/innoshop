{{-- 图文列表编辑模块 - 现代化风格 --}}
<template id="module-editor-image-text-list-template">
  <div class="image-text-list-editor">
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

    {{-- 显示设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-setting"></i>
        @{{ lang.display_settings }}
      </div>
      <div class="section-content">
        {{-- 每行显示数量设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.items_per_row }}</div>
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
              :class="['segmented-btn', { active: module.columns === 5 }]" 
              @click="setColumns(5)"
            >
              @{{ lang.items_5 }}
            </div>
            <div 
              :class="['segmented-btn', { active: module.columns === 6 }]" 
              @click="setColumns(6)"
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
              v-model="module.autoplay" 
              @change="onChange"
              :active-text="lang.enable" 
              :inactive-text="lang.disable"
              size="small"
            ></el-switch>
          </div>
        </div>

        {{-- 轮播间隔时间 --}}
        <div class="setting-group" v-if="module.autoplay">
          <div class="setting-label">@{{ lang.autoplay_interval_time }}</div>
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

        {{-- 显示标题 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.show_title }}</div>
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

        {{-- 图片高度设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.image_height }}</div>
          <el-input-number 
            v-model="module.itemHeight" 
            @change="onChange"
            :min="60" 
            :max="300" 
            :step="10"
            size="small"
            style="width: 100%;"
          ></el-input-number>
          <div class="setting-tip">
            <i class="el-icon-info"></i>
            @{{ lang.image_height_tip }}
          </div>
        </div>

        {{-- 内边距设置 --}}
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
            @{{ lang.padding_tip_image_text }}
          </div>
        </div>

        {{-- 边框圆角 --}}
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

        {{-- 边框宽度 --}}
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

        {{-- 边框颜色 --}}
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

        {{-- 边框样式 --}}
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

    {{-- 图文项管理 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-picture"></i>
        @{{ lang.image_text_item_management }}
      </div>
      <div class="section-content">
        {{-- 图文项列表 --}}
        <div class="image-text-list" v-loading="loading">
          <template v-if="module.imageTextItems && module.imageTextItems.length">
            <draggable 
              ghost-class="dragabble-ghost" 
              :list="module.imageTextItems" 
              @change="onChange"
              :options="{ animation: 330 }"
              class="image-text-draggable"
            >
                             <div v-for="(item, index) in module.imageTextItems" :key="index" class="image-text-item">
                 <div class="item-preview">
                   <img 
                     :src="getImageUrl(item.image)" 
                     :alt="item.name"
                     class="preview-img"
                   >
                 </div>
                 <div class="item-info">
                   <div class="item-name">@{{ item.name }}</div>
                   <div class="item-link" v-if="item.link && item.link.value && item.link.type">
                     <i class="el-icon-link"></i>
                     @{{ getLinkDisplayText(item.link) }}
                   </div>
                 </div>
                 <div class="item-actions">
                   <el-button 
                     type="primary" 
                     size="mini" 
                     icon="el-icon-edit" 
                     @click="editItem(index)"
                     style="padding: 6px; min-width: 28px;"
                   ></el-button>
                   <el-button 
                     type="danger" 
                     size="mini" 
                     icon="el-icon-delete" 
                     @click="removeItem(index)"
                     style="padding: 6px; min-width: 28px;"
                   ></el-button>
                 </div>
               </div>
            </draggable>
          </template>
          
          {{-- 空状态 --}}
          <div v-else class="empty-state">
            <i class="el-icon-picture-outline"></i>
            <p>@{{ lang.no_image_text_items }}</p>
            <span>@{{ lang.click_add_image_text_item }}</span>
          </div>
        </div>

        {{-- 添加图文项按钮 --}}
        <div class="add-item-section">
          <el-button 
            type="primary" 
            icon="el-icon-plus" 
            @click="addItem"
            size="small"
            style="width: 100%;"
          >
            @{{ lang.add_image_text_item }}
          </el-button>
        </div>
      </div>
    </div>

    {{-- 图文项编辑对话框 --}}
    <el-dialog 
      :title="editingItemIndex === -1 ? lang.add_image_text_item_dialog : lang.edit_image_text_item_dialog" 
      :visible.sync="showItemDialog" 
      width="500px"
      @close="closeItemDialog"
    >
      <div class="item-form">
        {{-- 标题 --}}
        <div class="form-group">
          <label>@{{ lang.item_title }}</label>
          <el-input 
            v-model="editingItem.name" 
            :placeholder="lang.enter_item_title"
            size="small"
          ></el-input>
        </div>

        {{-- 图片 --}}
        <div class="form-group">
          <label>@{{ lang.item_image }}</label>
          <single-image-selector 
            v-model="editingItem.image" 
            :aspectRatio="2/1" 
            :targetWidth="200"
            :targetHeight="100"
          ></single-image-selector>
          <div class="form-tip">
            <i class="el-icon-info"></i>
            @{{ lang.recommended_size_200_100 }}
          </div>
        </div>

        {{-- 链接 --}}
        <div class="form-group">
          <label>@{{ lang.item_link_optional }}</label>
          <link-selector 
            v-model="editingItem.link" 
            :placeholder="lang.select_link_type"
            :is-title="false"
          ></link-selector>
          <div class="form-tip" v-if="editingItem.link && editingItem.link.value">
            <i class="el-icon-info"></i>
            当前链接: @{{ getLinkDisplayText(editingItem.link) }}
          </div>
        </div>
      </div>
      
      <div slot="footer" class="dialog-footer">
        <el-button @click="closeItemDialog">@{{ lang.cancel }}</el-button>
        <el-button type="primary" @click="saveItem">@{{ lang.confirm_button }}</el-button>
      </div>
    </el-dialog>
  </div>
</template>

{{-- 图文列表编辑模块脚本 --}}
<script type="text/javascript">
  Vue.component('module-editor-image-text-list', {
    template: '#module-editor-image-text-list-template',
    props: ['module'],
    
    data: function() {
      return {
        debounceTimer: null,
        loading: false,
        showItemDialog: false,
        editingItemIndex: -1,
        editingItem: {
          name: '',
          image: '',
          link: {
            type: 'url',
            value: ''
          }
        },
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
        if (!this.module.title) {
          this.$set(this.module, 'title', this.languagesFill(''));
        }
      if (!this.module.imageTextItems) {
        this.$set(this.module, 'imageTextItems', []);
      }
      if (!this.module.columns) {
        this.$set(this.module, 'columns', 4);
      }
      if (!this.module.autoplay) {
        this.$set(this.module, 'autoplay', false);
      }
      if (!this.module.autoplaySpeed) {
        this.$set(this.module, 'autoplaySpeed', 3000);
      }
      if (!this.module.showNames) {
        this.$set(this.module, 'showNames', true);
      }
      if (!this.module.width) {
        this.$set(this.module, 'width', 'wide');
      }
      if (!this.module.itemHeight) {
        this.$set(this.module, 'itemHeight', 120);
      }
      if (!this.module.padding) {
        this.$set(this.module, 'padding', 16);
      }
      if (!this.module.borderRadius) {
        this.$set(this.module, 'borderRadius', 8);
      }
      if (!this.module.borderWidth) {
        this.$set(this.module, 'borderWidth', 1);
      }
      if (!this.module.borderColor) {
        this.$set(this.module, 'borderColor', '#f0f0f0');
      }
      if (!this.module.borderStyle) {
        this.$set(this.module, 'borderStyle', 'solid');
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

      setColumns(columns) {
        this.$set(this.module, 'columns', columns);
        this.onChange();
      },

      getImageUrl(image) {
        if (!image) {
          return PLACEHOLDER_IMAGE;
        }
        if (typeof image === 'string' && image.indexOf('http') === 0) {
          return image;
        }
        if (typeof image === 'object') {
          const locale = this.source.locale;
          return image[locale] || (Object.values(image)[0] || PLACEHOLDER_IMAGE);
        }
        return asset + image;
      },

      addItem() {
        this.editingItemIndex = -1;
        this.editingItem = {
          name: '',
          image: '',
          link: {
            type: 'url',
            value: ''
          }
        };
        this.showItemDialog = true;
      },

      editItem(index) {
        this.editingItemIndex = index;
        this.editingItem = JSON.parse(JSON.stringify(this.module.imageTextItems[index]));
        this.showItemDialog = true;
      },

      removeItem(index) {
        this.$confirm('确定要删除这个图文项吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.module.imageTextItems.splice(index, 1);
          this.onChange();
          this.$message.success('删除成功');
        }).catch(() => {
          // 用户取消删除
        });
      },

      saveItem() {
        if (!this.editingItem.name.trim()) {
          this.$message.error('请输入标题');
          return;
        }
        if (!this.editingItem.image) {
          this.$message.error('请选择图片');
          return;
        }

        if (this.editingItemIndex === -1) {
          // 添加新图文项
          this.module.imageTextItems.push(JSON.parse(JSON.stringify(this.editingItem)));
        } else {
          // 编辑现有图文项
          this.$set(this.module.imageTextItems, this.editingItemIndex, JSON.parse(JSON.stringify(this.editingItem)));
        }

        this.onChange();
        this.closeItemDialog();
        this.$message.success(this.editingItemIndex === -1 ? '添加成功' : '更新成功');
      },

      closeItemDialog() {
        this.showItemDialog = false;
        this.editingItemIndex = -1;
        this.editingItem = {
          name: '',
          image: '',
          link: {
            type: 'url',
            value: ''
          }
        };
      },

      languagesFill(text) {
        const obj = {};
        $languages.forEach(e => {
          obj[e.code] = text;
        });
        return obj;
      },

      getLinkDisplayText(link) {
        if (!link || !link.type) {
          return '';
        }

        switch (link.type) {
          case 'custom':
            return link.value || '自定义链接';
          case 'static':
            const staticLinks = {
              'account.index': '个人中心',
              'account.wishlist.index': '我的收藏',
              'account.order.index': '我的订单',
              'brands.index': '品牌列表'
            };
            return staticLinks[link.value] || link.value;
          case 'product':
            return link.value ? `商品 #${link.value}` : '商品链接';
          case 'category':
            return link.value ? `分类 #${link.value}` : '商品分类';
          case 'page':
            return link.value ? `页面 #${link.value}` : '页面链接';
          case 'catalog':
            return link.value ? `文章分类 #${link.value}` : '文章分类';
          case 'brand':
            return link.value ? `品牌 #${link.value}` : '品牌链接';
          default:
            return link.value || '未知链接';
        }
      }
    }
  });
</script> 