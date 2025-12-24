{{-- 分类商品编辑模块 --}}
<template id="module-editor-category-products-template">
  <div class="editor-container">
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
            :class="['segmented-btn', { active: form.width === 'narrow' }]" 
            @click="form.width = 'narrow'"
          >
            <i class="el-icon-copy-document"></i>
            @{{ lang.narrow_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'wide' }]" 
            @click="form.width = 'wide'"
          >
            <i class="el-icon-copy-document"></i>
            @{{ lang.wide_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'full' }]" 
            @click="form.width = 'full'"
          >
            <i class="el-icon-full-screen"></i>
            @{{ lang.full_screen }}
          </div>
        </div>
      </div>
    </div>

    {{-- 模块标题设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-edit"></i>
        @{{ lang.module_title }}
      </div>
      <div class="section-content">
        <text-i18n v-model="form.title"></text-i18n>
      </div>
    </div>

    {{-- 显示设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-setting"></i>
        @{{ lang.display_settings }}
      </div>
      <div class="section-content">
        {{-- 显示数量设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.items_per_row }}</div>
          <div class="segmented-buttons">
            <div 
              :class="['segmented-btn', { active: form.columns === 3 }]" 
              @click="form.columns = 3"
            >
              <i class="el-icon-grid"></i>
              @{{ lang.items_3 }}
            </div>
            <div 
              :class="['segmented-btn', { active: form.columns === 4 }]" 
              @click="form.columns = 4"
            >
              <i class="el-icon-grid"></i>
              @{{ lang.items_4 }}
            </div>
            <div 
              :class="['segmented-btn', { active: form.columns === 6 }]" 
              @click="form.columns = 6"
            >
              <i class="el-icon-grid"></i>
              @{{ lang.items_6 }}
            </div>
          </div>
        </div>

        {{-- 商品数量设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.product_quantity }}</div>
          <el-input 
            v-model="form.limit" 
            type="number" 
            size="small" 
            :placeholder="lang.enter_product_quantity"
            @input="limitChange"
            style="width: 100%;"
          ></el-input>
        </div>
      </div>
    </div>

    {{-- 分类设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-folder"></i>
        @{{ lang.category_settings }}
      </div>
      <div class="section-content">
        {{-- 当前选择的分类 --}}
        <div class="setting-group" v-if="form.category_name">
          <div class="setting-label">@{{ lang.current_category }}</div>
          <div class="selected-category">
            <div class="category-info">
              <i class="el-icon-folder"></i>
              <span class="category-name">@{{ form.category_name }}</span>
            </div>
            <el-button 
              type="text" 
              size="mini" 
              @click="clearCategory"
              style="color: #f56c6c;"
            >
              <i class="el-icon-delete"></i>
              @{{ lang.clear }}
            </el-button>
          </div>
        </div>

        {{-- 搜索分类 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.search_category }}</div>
          <div class="autocomplete-group-wrapper">
            <el-autocomplete 
              class="inline-input" 
              v-model="keyword" 
              value-key="name" 
              size="small"
              :fetch-suggestions="querySearch" 
              :placeholder="lang.search_category_placeholder" 
              :highlight-first-item="true"
              @select="handleSelect"
              style="width: 100%;"
            ></el-autocomplete>
          </div>
        </div>

        {{-- 排序设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.sort_method }}</div>
          <el-select v-model="form.sort" size="small" style="width: 100%;" @change="onSortChange">
            <el-option :label="lang.sort_sales_desc" value="sales_desc"></el-option>
            <el-option :label="lang.sort_price_desc" value="price_desc"></el-option>
            <el-option :label="lang.sort_price_asc" value="price_asc"></el-option>
            <el-option :label="lang.sort_created_desc" value="created_desc"></el-option>
            <el-option :label="lang.sort_viewed_desc" value="viewed_desc"></el-option>
            <el-option :label="lang.sort_updated_desc" value="updated_desc"></el-option>
            <el-option :label="lang.sort_position_asc" value="position_asc"></el-option>
          </el-select>
        </div>
      </div>
    </div>
  </div>
</template>

{{-- 分类商品编辑模块脚本 --}}
<script type="text/javascript">
  Vue.component('module-editor-category-products', {
    template: '#module-editor-category-products-template',
    props: ['module'],
    data: function() {
      return {
        keyword: '',
        form: null
      }
    },

    watch: {
      form: {
        handler: function(val) {
          this.$emit('on-changed', val);
        },
        deep: true
      }
    },

    created: function() {
      this.form = JSON.parse(JSON.stringify(this.module));
      if (!this.form.width) {
        this.$set(this.form, 'width', 'wide');
      }
      if (!this.form.columns) {
        this.$set(this.form, 'columns', 4);
      }
      if (!this.form.sort) {
        this.$set(this.form, 'sort', 'sales_desc');
      }

      // 设置已保存的分类名称到搜索框中
      if (this.form.category_name) {
        this.keyword = this.form.category_name;
      }
    },

    computed: {},

    methods: {
      querySearch(keyword, cb) {
        let url = 'api/panel/categories/autocomplete';
        if (keyword && keyword.length > 0) {
          url += '?keyword=' + encodeURIComponent(keyword);
        }
        
        axios.get(url, {
          hload: true
        }).then((res) => {
          cb(res.data || []);
        }).catch(() => {
          cb([]);
        });
      },

      handleSelect(item) {
        console.log('选择分类:', item);
        this.form.category_id = item.id;
        this.form.category_name = item.name;
        this.keyword = item.name;

        // 触发表单更新
        this.$emit('on-changed', this.form);
      },

      clearCategory() {
        this.form.category_id = '';
        this.form.category_name = '';
        this.keyword = '';
        this.$emit('on-changed', this.form);
      },

      onSortChange() {
        // 排序方式修改时，只触发表单更新
        console.log('排序方式已修改为:', this.form.sort);
        this.$emit('on-changed', this.form);
      },

      limitChange(e) {
        this.form.limit = e;
        // 商品数量修改时，只触发表单更新
        console.log('商品数量已修改为:', this.form.limit);
        this.$emit('on-changed', this.form);
      },


    }
  });
</script> 