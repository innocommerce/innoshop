<template>
  <div class="product-editor">
    <!-- 基础设置 -->
    <div class="module-editor-row">设置</div>
    <div class="module-edit-group">
      <div class="module-edit-title">模块标题</div>
      <text-i18n v-model="form.title" />
    </div>

    <!-- 商品配置 -->
    <div class="module-editor-row">内容</div>
    <div class="module-edit-group">
      <div class="module-edit-title">配置商品</div>
      <div class="product-selector">
        <!-- 搜索输入框 -->
        <el-autocomplete
          v-model="keyword"
          :fetch-suggestions="querySearch"
          placeholder="请输入关键字搜索"
          :trigger-on-focus="false"
          @select="handleSelect"
          class="search-input"
        >
          <template slot-scope="{ item }">
            <div class="suggestion-item">
              <img :src="item.image" class="thumb">
              <span class="name">{{ item.name }}</span>
            </div>
          </template>
        </el-autocomplete>

        <!-- 已选商品列表 -->
        <div class="selected-products" v-loading="loading">
          <draggable
            v-model="form.products"
            :options="dragOptions"
            @change="handleProductsChange"
          >
            <div
              v-for="(item, index) in form.products"
              :key="item.id"
              class="product-item"
            >
              <div class="item-content">
                <i class="el-icon-rank handle"></i>
                <span class="name">{{ item.name }}</span>
              </div>
              <i class="el-icon-delete" @click="removeProduct(index)"></i>
            </div>
          </draggable>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TextI18n from '../common/TextI18n.vue'

export default {
  name: 'ProductEditor',

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
      loading: false,
      dragOptions: {
        animation: 300,
        handle: '.handle'
      }
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
    // 搜索商品
    querySearch(keyword, cb) {
      axios.get(`products/autocomplete?name=${encodeURIComponent(keyword)}`)
        .then(response => {
          cb(response.data)
        })
    },

    // 选择商品
    handleSelect(item) {
      if (!this.form.products.find(p => p.id === item.id)) {
        this.form.products.push(item)
      }
      this.keyword = ''
    },

    // 移除商品
    removeProduct(index) {
      this.form.products.splice(index, 1)
    },

    // 处理商品排序
    handleProductsChange() {
      // 可以在这里添加额外的处理逻辑
    },

    // 加载商品数据
    loadProducts() {
      if (!this.form.products.length) return

      this.loading = true
      const productIds = this.form.products.map(p => p.id).join(',')

      axios.get(`products/names?product_ids=${productIds}`)
        .then(response => {
          this.form.products = response.data
        })
        .finally(() => {
          this.loading = false
        })
    }
  },

  created() {
    this.loadProducts()
  }
}
</script>

<style scoped>
.product-selector {
  margin-top: 10px;
}

.search-input {
  width: 100%;
}

.suggestion-item {
  display: flex;
  align-items: center;
  padding: 4px;
}

.suggestion-item .thumb {
  width: 30px;
  height: 30px;
  margin-right: 8px;
  object-fit: cover;
}

.selected-products {
  margin-top: 10px;
  min-height: 100px;
  border: 1px solid #eee;
  border-radius: 4px;
  padding: 8px;
}

.product-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px;
  background: #f8f8f8;
  margin-bottom: 8px;
  border-radius: 4px;
}

.product-item .item-content {
  display: flex;
  align-items: center;
}

.product-item .handle {
  cursor: move;
  margin-right: 8px;
  color: #999;
}

.product-item .el-icon-delete {
  cursor: pointer;
  color: #999;
}

.product-item .el-icon-delete:hover {
  color: #f56c6c;
}
</style>
