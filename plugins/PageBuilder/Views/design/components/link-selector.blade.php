<template id="link-selector">
  <div class="link-selector-wrap">
    <div class="selector-type" @blur="selectorContentShow = false" tabindex="1">
      <div class="title" v-if="!link.type || link.type === '' || !value.value"
        @click="toggleSelector">请选择链接类型
      </div>
      <div class="title" @click="toggleSelector" v-else :title="name"
        v-loading="nameLoading">@{{ selectorTitle }}: @{{ name[0]?.name ?? '' }}
      </div>
      <div :class="'selector-content ' + (selectorContentShow ? 'active' : '') + (shouldShowUpward ? ' bottom-up' : '')">
        <div @click="selectorType()">
          <i class="el-icon-close"></i>
          无
        </div>
        <div v-for="(type, index) in types" :key="index" @click="selectorType(type.type)">
          <i :class="getTypeIcon(type.type)"></i>
          @{{ type.label }}
        </div>
      </div>
    </div>

    <el-dialog :visible.sync="linkDialog.show" class="link-dialog-box" :append-to-body="true"
      :close-on-click-modal="false" @open="linkDialogOpen" @closed="linkDialogClose" width="460px">
      <div slot="title" class="link-dialog-header">
        <div class="title">选择@{{ dialogTitle }}</div>
        <div class="input-with-select" v-if="link.type != 'custom'">
          <input type="text" placeholder="请输入关键字搜索" v-model="keyword" @keyup.enter="searchProduct"
            class="form-control">
          <el-button @click="searchProduct"><i class="el-icon-search"></i> 搜索</el-button>
        </div>
      </div>
      <div class="link-dialog-content">
        <div class="product-search">
          <div class="link-top-new">
            <span>是新窗口打开:</span>
            <el-switch :width="36" @change="linksNewBack" v-model="link.new_window"></el-switch>
          </div>

          <a :href="linkTypeAdmin" target="_blank"
            v-if="link.type != 'custom' && link.type != 'static'">管理@{{ dialogTitle }}</a>
        </div>

        <div class="link-text" v-if="isCustomName">
          <div class="module-edit-group edit-group-margin">
            <div class="module-edit-title">自定义名称</div>
            <text-i18n v-model="link.text"></text-i18n>
          </div>
        </div>
        <template v-if="link.type == 'custom'">
          <div class="linkDialog-custom">
            <el-input v-model="link.value" placeholder="请输入链接地址"></el-input>
          </div>
        </template>
        <template v-else-if="link.type == 'static'">
          <div class="">
            <div class="product-info">
              <ul class="product-list static">
                <li v-for="(product, index) in static" @click="link.value = product.value">
                  <div class="content-cell">
                    <span :class="'radio-plus ' + (link.value == product.value ? 'active' : '')"></span>
                    <div class="product-name">@{{ product.name }}</div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="product-info" v-loading="loading">
            <template v-if="linkDialog.data.length">
              <div class="product-info-title">
                <span>内容</span>
                <span>状态</span>
              </div>

              <ul class="product-list">
                <li v-for="(product, index) in linkDialog.data"
                  @click="product.active ? link.value = product.id : false" :class="!product.active ? 'no-status' : ''">
                  <div class="content-cell">
                    <span
                      :class="'radio-plus ' + (link.value == product.id ? 'active' : '') + (!product.active ? 'no-status' :
                          '')"></span>
                    <img v-if="getProductImage(product)" :src="getProductImage(product)" class="img-responsive">
                    <div class="product-name">@{{ product.name }}</div>
                  </div>
                  <div :class="'status-cell ' + (product.active ? 'ok' : 'no')">
                    <template v-if="product.active">启用</template>
                    <template v-else>禁用</template>
                  </div>
                </li>
              </ul>
            </template>
            <div class="product-info-no" v-if="!linkDialog.data.length && loading === false">
              <div class="icon"><i class="el-icon-warning"></i></div>
              <div class="no-text">数据不存在或已被删除, <a :href="linkTypeAdmin" target="_blank">去添加@{{ dialogTitle }}</a>
              </div>
            </div>
          </div>
        </template>
      </div>
      <div slot="footer" class="link-dialog-footer">
        <el-button type="primary" @click="linkDialogConfirm">确 定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
  Vue.component('link-selector', {
    template: '#link-selector',
    props: {
      value: {
        default: null
      },
      isTitle: {
        default: true,
        type: Boolean
      },
      isCustomName: {
        default: false,
        type: Boolean
      },
      showText: {
        default: false
      },
      hideTypes: {
        type: Array,
        default: function() {
          return [];
        }
      },
      type: {
        default: null
      },
      linkNew: {
        default: true
      },
    },
    data: function() {
      return {
        types: [{
            type: 'product',
            label: '商品链接'
          },
          {
            type: 'category',
            label: '商品分类'
          },
          {
            type: 'page',
            label: '特定页面'
          },
          {
            type: 'catalog',
            label: '文章分类'
          },
          {
            type: 'brand',
            label: '商品品牌'
          },
          {
            type: 'static',
            label: '固定连接'
          },
          {
            type: 'custom',
            label: '自定义'
          }
        ],
        static: [{
            name: '个人中心',
            value: 'account.index'
          },
          {
            name: '我的收藏',
            value: 'account.wishlist.index'
          },
          {
            name: '我的订单',
            value: 'account.order.index'
          },
          {
            name: '最新商品',
            value: 'account.index'
          },
          {
            name: '品牌列表',
            value: 'brands.index'
          },
        ],
        link: null,
        keyword: '',
        name: '',
        locale: 'zh_cn',
        loading: null,
        nameLoading: null,
        selectorContentShow: false,
        shouldShowUpward: false,
        isUpdate: true,
        linkDialog: {
          show: false,
          data: [],
        }
      }
    },
    beforeMount() {
      this.updateData();
      if (this.hideTypes.length) {
        this.types = this.types.filter((item) => {
          return this.hideTypes.indexOf(item.type) == -1;
        });
      }
    },
    
    mounted() {
      // 监听窗口大小改变，重新计算显示方向
      window.addEventListener('resize', this.handleResize);
    },
    
    beforeDestroy() {
      // 移除事件监听器
      window.removeEventListener('resize', this.handleResize);
    },
    watch: {
      value() {
        if (this.isUpdate) {
          this.updateData();
        }
      }
    },
    computed: {
      dialogTitle: function() {
        const foundType = this.types.find(e => e.type == this.link.type);
        return foundType ? foundType.label : '选择链接';
      },
      selectorTitle() {
        // 添加安全检查，防止value或value.type为undefined
        if (!this.value || !this.value.type) {
          return '请选择链接类型';
        }
        const foundType = this.types.find(e => e.type == this.value.type);
        return foundType ? foundType.label : '未知类型';
      },
      linkTypeAdmin: function() {
        let url = '';
        switch (this.link.type) {
          case 'product':
            url = '/panel/products';
            break;
          case 'category':
            url = '/panel/categories';
            break;
          case 'brand':
            url = '/panel/brands';
            break;
          case 'page':
            url = '/panel/pages';
            break;
          case 'catalog':
            url = '/panel/catalogs';
            break;
          default:
            url = '';
        }
        return url;
      },
    },
    methods: {
      // 获取产品图片
      getProductImage(product) {
        // 优先使用image_small（商品）
        if (product.image_small) {
          return product.image_small;
        }
        // 其次使用image（分类、品牌等）
        if (product.image) {
          return product.image;
        }
        return null;
      },

      // 获取类型图标
      getTypeIcon(type) {
        const iconMap = {
          'product': 'el-icon-goods',
          'category': 'el-icon-folder',
          'brand': 'el-icon-star-on',
          'page': 'el-icon-document',
          'catalog': 'el-icon-collection'
        };
        return iconMap[type] || 'el-icon-document';
      },

      // 计算是否应该向上显示
      calculateShowDirection() {
        this.$nextTick(() => {
          const selectorElement = this.$el.querySelector('.selector-type');
          if (!selectorElement) return;
          
          const rect = selectorElement.getBoundingClientRect();
          const viewportHeight = window.innerHeight;
          const dropdownHeight = 200; // 最大高度
          const margin = 20; // 预留边距
          
          // 计算下方可用空间
          const spaceBelow = viewportHeight - rect.bottom - margin;
          // 计算上方可用空间
          const spaceAbove = rect.top - margin;
          
          // 如果下方空间不足且上方空间足够，则向上显示
          this.shouldShowUpward = spaceBelow < dropdownHeight && spaceAbove >= dropdownHeight;
          
          // 调试信息（可选）
          console.log('Dropdown direction calculation:', {
            spaceBelow,
            spaceAbove,
            dropdownHeight,
            shouldShowUpward: this.shouldShowUpward
          });
        });
      },

      // 切换选择器显示
      toggleSelector() {
        this.selectorContentShow = !this.selectorContentShow;
        if (this.selectorContentShow) {
          this.calculateShowDirection();
        }
      },

      // 处理窗口大小改变
      handleResize() {
        if (this.selectorContentShow) {
          this.calculateShowDirection();
        }
      },

      linkDialogConfirm() {
        this.isUpdate = false;
        if (this.link.type == 'custom') {
          this.name = [{
            name: this.link.value
          }];
        } else if (this.link.type == 'static') {
          const staticItem = this.static.find(e => e.value == this.link.value);
          this.name = [{
            name: staticItem.name
          }]
        } else {
          const selectedItem = this.linkDialog.data.find(e => e.id == this.link.value);
          this.name = [{
            name: selectedItem.name
          }]
        }

        let links = JSON.parse(JSON.stringify(this.link));
        this.$emit("input", links);
        this.linkDialog.show = false;
        this.$nextTick(() => {
          this.isUpdate = true;
        })
      },

      searchProduct() {
        const self = this;
        this.link.value = '';
        this.querySearch(this.keyword, null, function(data) {
          self.linkDialog.data = data.data;
        })
      },

      linkDialogClose() {
        this.linkDialog.data = [];
      },

      linkDialogOpen() {
        const self = this;
        this.keyword = '';
        this.selectorContentShow = false;
        if (this.link.type != 'custom' || this.value.type != 'custom') {
          this.link.value = ''
        }

        if (this.link.type == 'custom' || this.link.type == 'static') {
          return;
        }

        this.querySearch(this.keyword, 'all', function(data) {
          self.linkDialog.data = data.data;
        })
      },

      selectorType(type) {
        if (type) {
          this.linkDialog.show = true;
          this.link.type = type;

          if (type == 'custom') {
            if (this.link.text) {
              this.link.text = this.link.text
            } else {
              this.link.text = languagesFill('')
            }
          }
          return;
        }

        // 选择"不要链接"时，清空所有链接信息
        this.selectorContentShow = false;
        this.$emit("input", {
          link: '',
          type: '',  // 设置为空字符串而不是'category'
          value: '',
          new_window: false
        });
      },

      querySearch(keyword, all, cb) {
        const self = this;
        let url = '';

        switch (this.link.type) {
          case 'product':
            url = 'api/panel/products/autocomplete';
            break;
          case 'category':
            url = 'api/panel/categories/autocomplete';
            break;
          case 'brand':
            url = 'api/panel/brands/autocomplete';
            break;
          case 'page':
            url = 'api/panel/pages/autocomplete';
            break;
          case 'catalog':
            url = 'api/panel/catalogs/autocomplete';
            break;
          default:
            null;
        }

        if (keyword) {
          url += '?keyword=' + encodeURIComponent(keyword);
        }

        this.loading = true;

        const apiUrl = url;
        axios.get(apiUrl, null, {
          hload: true
        }).then((res) => {
          if (res) {
            cb(res)
          }
          this.loading = false;
        }).finally(() => {
          this.loading = false
        });
      },

      linksNewBack() {
        let links = JSON.parse(JSON.stringify(this.link));
        this.$emit("input", links);
      },

      updateData() {
        // Initialize value if it's null or undefined
        if (!this.value) {
          this.value = {
            type: '',
            link: '',
            value: '',
            new_window: this.linkNew || false
          };
        }

        // Initialize link object with proper defaults
        this.link = {
          type: this.value?.type || '',
          link: this.value?.link || '',
          value: this.value?.value || '',
          new_window: this.value?.new_window !== undefined ? this.value.new_window : (this.linkNew || false),
          text: this.value?.text || languagesFill('')
        };

        // Filter types if specific type is requested
        if (this.type) {
          this.types = this.types.filter(e => e.type == this.type);
        }

        // Handle empty type (no link selected)
        if (!this.link.type) {
          this.name = '';
          return;
        }

        // Handle custom type
        if (this.link.type == 'custom') {
          this.name = this.link.value || (this.link.text && this.link.text[this.locale]) || '';
          return;
        }

        // Handle static type
        if (this.link.type == 'static') {
          if (!this.link.value) return;
          const staticItem = this.static.find(e => e.value == this.link.value);
          if (staticItem) {
            this.name = [{
              name: staticItem.name
            }];
          }
          return;
        }

        // Handle other types - only load name if value exists
        if (!this.link.value) {
          this.name = '';
          return;
        }

        this.nameLoading = true;

        let self = this,
          url = '';

        switch (this.link.type) {
          case 'product':
            url = `api/panel/products/names?ids=${this.link.value}`;
            break;
          case 'category':
            url = `api/panel/categories/names?ids=${this.link.value}`;
            break;
          case 'brand':
            url = `api/panel/brands/names?ids=${this.link.value}`;
            break;
          case 'page':
            url = `api/panel/pages/names?ids=${this.link.value}`;
            break;
          case 'catalog':
            url = `api/panel/catalogs/names?ids=${this.link.value}`;
            break;
          default:
            this.nameLoading = false;
            return;
        }

        axios.get(url, null, {
          hload: true,
          hmsg: true
        }).then((res) => {
          if (res && res.data) {
            self.name = res.data;
          } else {
            self.name = [{
              name: '数据不存在或已被删除'
            }];
          }
        }).catch((error) => {
          console.warn('Failed to load link name:', error);
          self.name = [{
            name: '数据不存在或已被删除'
          }];
        }).finally(() => {
          self.nameLoading = false;
        });
      }
    }
  });
</script>


