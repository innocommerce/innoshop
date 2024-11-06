<template>
  <div class="category-editor">
    <!-- 基础设置 -->
    <div class="module-editor-row">设置</div>
    <div class="module-edit-group">
      <div class="module-edit-title">模块标题</div>
      <text-i18n v-model="form.title" />
    </div>

    <!-- 分类配置 -->
    <div class="module-editor-row">内容</div>
    <div class="module-edit-group">
      <div class="module-edit-title">搜索分类</div>
      <el-autocomplete
        v-model="keyword"
        :fetch-suggestions="queryCategories"
        placeholder="请输入分类名称搜索"
        :trigger-on-focus="false"
        @select="handleCategorySelect"
        class="category-search"
      />
    </div>

    <!-- 显示设置 -->
    <div class="module-edit-group">
      <div class="module-edit-title">显示数量</div>
      <el-input-number
        v-model="form.limit"
        :min="1"
        :max="20"
        size="small"
        @change="handleLimitChange"
      />
    </div>

    <!-- 商品预览 -->
    <div class="module-edit-group">
      <div class="module-edit-title">商品预览</div>
      <div class="products-preview" v-loading="loading">
        <div
          v-for="product in form.products"
          :key="product.id"
          class="product-item"
        >
          <img :src="product.image" class="thumb">
          <div class="name">{{ product.name }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TextI18n from '../common/TextI18n.vue'

export default {
  name: 'CategoryEditor',

  components: {
    TextI18n
  },

  props: {
    module: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      form: JSON.parse(JSON.stringify(this.module)),
      keyword: '',
      loading: false
    }
  },

  watch: {
    form: {
      handler(val) {
        this.$emit('on-changed', val)
      },
      deep: true
    }
  },

  methods: {
    // 搜索分类
    queryCategories(keyword, cb) {
      axios.get(`categories/autocomplete?name=${encodeURIComponent(keyword)}`)
        .then(response => {
          cb(response.data)
        })
    },

    // 选择分类
    handleCategorySelect(category) {
      this.form.category_id = category.id
      this.form.category_name = category.name
      this.loadCategoryProducts()
      this.keyword = ''
    },

    // 修改显示数量
    handleLimitChange() {
      if (this.form.category_id) {
        this.loadCategoryProducts()
      }
    },

    // 加载分类商品
    loadCategoryProducts() {
      this.loading = true
      axios.get(`categories/${this.form.category_id}/products`, {
        params: { limit: this.form.limit }
      })
      .then(response => {
        this.form.products = response.data
      })
      .finally(() => {
        this.loading = false
      })
    }
  },

  created() {
    if (this.form.category_id) {
      this.loadCategoryProducts()
    }
  }
}
</script>

<style scoped>
.category-search {
  width: 100%;
}

.products-preview {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  margin-top: 10px;
}

.product-item {
  background: #fff;
  border-radius: 4px;
  padding: 8px;
}

.product-item .thumb {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 4px;
}

.product-item .name {
  margin-top: 8px;
  font-size: 12px;
  line-height: 1.4;
  height: 34px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
</style>
