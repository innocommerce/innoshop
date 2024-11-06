@extends('panel::layouts.app')

@section('title', __('InnoMobileBuilder::route.title'))

@section('body-class', 'design-app-home')

<x-panel::form.right-btns/>

@push('header')
  {{-- <script src="{{ plugin_asset('inno_mobile_builder','js/common.js') }}"></script> --}}
  <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ plugin_asset('inno_mobile_builder', 'js/vuedraggable.js') }}"></script>
  <link rel="stylesheet" type="text/css" href="{{ plugin_asset('inno_mobile_builder', 'css/design.css') }}">
  <style scoped>
    .item {
      padding: 6px;
      background-color: #fdfdfd;
      border: solid 1px #eee;
      margin-bottom: 10px;
      cursor: move;
    }

    .item:hover {
      background-color: #f1f1f1;
      cursor: move;
    }

    .chosen {
      border: solid 2px #3089dc !important;
    }
  </style>
  <script>

    const $languages = @json(locales());
    const $locale = '{{ locale_code() }}';

    const asset = document.querySelector('meta[name="asset"]').content;
    if (typeof Vue != 'undefined') {
      Vue.prototype.thumbnail = function thumbnail(image) {
        if (!image) {
          return "{{ plugin_asset('inno_mobile_builder', 'images/placeholder.png') }}";
        }

        // 判断 image 是否以 http 开头
        if (image.indexOf('http') === 0) {
          return image;
        }

        return asset + image;
      };

      Vue.prototype.stringLengthInte = function stringLengthInte(text, length) {
        return inno.stringLengthInte(text, length)
      };
        Vue.prototype.fileManagerIframe = function stringLengthInte(text, length) {
            return inno.fileManagerIframe(text, length)
        };
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endpush

@section('page-title-right')
  <button type="button" class="btn w-min-100 btn-primary save-btn">{{ __('common.save') }}</button>
@endsection

@section('content')


  <div id="app">
  <div class="card-body">
    <div class="module-wrap">
      <div class="c-title">模块列表</div>
      <draggable class="modules-list dragArea list-group"
                 :options="{group:{ name: 'people', pull: 'clone', put: false }}" :list="source.modules"
                 :clone="cloneDefaultField" @end="perviewEnd">
        <div class="list-item" v-for="module, index in source.modules" :key="index">
          <div class="icon"><i class="ds-icon" v-html="module.icon"></i></div>
          <div class="name">@{{ module.title }}</div>
        </div>
      </draggable>
    </div>
    <div class="perview-wrap">
      <div class="c-title">效果预览</div>
      <div class="perview-content">
        <div class="head"><img src="https://demo.beikeshop.com/image/app-app/builder-mb-bg.png" class="img-fluid"></div>
        <div class="hint" v-if="!form.modules.length">
          <i class="bi bi-brightness-high fs-2"></i>
          <div class="mt-2">请从左边模块列表拖动模块到这里</div>
        </div>
        <draggable class="view-modules-list dragArea list-group" :options="{animation: 300, group:'people'}"
                   :list="form.modules" group="people">
          <div :class="['list-item', design.editingModuleIndex == index ? 'active' : '']"
               @click="design.editingModuleIndex = index"
               v-for="module, index in form.modules" :key="index">
            <div class="module-tool">
              <div class="module-delete" @click="deleteDodule(index)"><i class="bi bi-trash"></i></div>
            </div>
            <div v-if="module.code == 'slideshow'">
              <img :src="module.content.images[0].image[source.locale]" class="img-fluid">
            </div>
            <div v-if="module.code == 'image100'">
              <img :src="module.content.images[0].image[source.locale]" class="img-fluid">
            </div>
            <div v-if="module.code == 'icons'"
                 :class="['quick-icon-wrapper', 'quick-icon-' + module.content.images.length]">
              <div v-if="!module.content.images.length" class="hint-right-edit">请在右侧配置模块</div>
              <div class="link-item" v-for="item, icon_index in module.content.images" :key="icon_index">
                <img :src="item.image" class="img-fluid">
                <span>@{{ item.text[source.locale] }}</span>
              </div>
            </div>
            <div v-if="module.code == 'product' || module.code == 'category' || module.code == 'latest'">
              <div v-if="module.content.title[source.locale]" class="module-title">@{{
                module.content.title[source.locale] }}
              </div>
              <div v-if="!module.content.products.length" class="hint-right-edit">请在右配置模块</div>
              <div class="product-grid">
                <div class="product-item" v-for="item, product_index in module.content.products" :key="product_index">
                  <img :src="item.image" class="img-fluid">
                  <div class="name">@{{ item.name }}</div>
                  <div class="product-price">666</div>
                </div>
              </div>
            </div>
          </div>
        </draggable>
      </div>
    </div>
    <div class="module-edit">
      <div class="c-title">
        模块编辑 - <span v-if="form.modules.length">@{{ form.modules[design.editingModuleIndex].title }}</span>
      </div>
      <div v-if="form.modules.length > 0" class="component-wrap">
        <component :is="editingModuleComponent" :key="design.editingModuleIndex"
                   :module="form.modules[design.editingModuleIndex].content" @on-changed="moduleUpdated"></component>
      </div>
    </div>
  </div>
  </div>


  <template id="module-editor-image100-template">
    <div class="image-edit-wrapper">
      <div class="module-editor-row">内容</div>
      <div class="module-edit-group">
        <div class="module-edit-title">选择图片</div>
        <div class="">
          <div class="pb-images-top">
            <pb-image-selector v-model="form.images[0].image"></pb-image-selector>
            <div class="tag">建议尺寸: 1000 x 480</div>
          </div>
          <link-selector :hide-types="['page_category', 'static']" v-model="form.images[0].link"></link-selector>
        </div>
      </div>
    </div>
  </template>

  <script type="text/javascript">
    Vue.component('module-editor-image100', {
      template: '#module-editor-image100-template',

      props: ['module'],

      data: function () {
        return {
          form: null
        }
      },

      watch: {
        form: {
          handler: function (val) {
            this.$emit('on-changed', val);
          },
          deep: true
        }
      },

      created: function () {
        this.form = JSON.parse(JSON.stringify(this.module));
      },

      methods: {}
    });
  </script>

  <template id="module-editor-slideshow-template">
    <div>
      <div class="module-editor-row">内容</div>
      <div class="module-edit-group">
        <div class="module-edit-title">选择图片</div>
        <draggable
            ghost-class="dragabble-ghost"
            :list="form.images"
            :options="{animation: 330, handle: '.icon-rank'}"
        >
          <div class="pb-images-selector" v-for="(item, index) in form.images" :key="index">
            <div class="selector-head" @click="itemShow(index)">
              <div class="left">
                <el-tooltip class="icon-rank" effect="dark" content="拖动排序" placement="left">
                  <i class="el-icon-rank"></i>
                </el-tooltip>

                <img :src="thumbnail(item.image['zh_cn'], 40, 40)" class="img-responsive">
              </div>

              <div class="right">
                <el-tooltip class="" effect="dark" content="除" placement="left">
                  <div class="remove-item" @click.stop="removeImage(index)"><i class="el-icon-delete"></i></div>
                </el-tooltip>
                <i :class="'el-icon-arrow-'+(item.show ? 'up' : 'down')"></i>
              </div>
            </div>
            <div :class="'pb-images-list ' + (item.show ? 'active' : '')">
              <div class="pb-images-top">
                <pb-image-selector v-model="item.image"></pb-image-selector>
                <div class="tag">建议尺寸(宽x高): 1000 x 500</div>
              </div>
              <link-selector :hide-types="['page_category', 'static']" v-model="item.link"></link-selector>
            </div>
          </div>
        </draggable>

        <div class="add-item">
          <el-button type="primary" size="small" @click="addImage" icon="el-icon-circle-plus-outline">添加图片
          </el-button>
        </div>
      </div>
    </div>
  </template>

  <script type="text/javascript">

    Vue.component('module-editor-slideshow', {
      template: '#module-editor-slideshow-template',

      props: ['module'],

      data: function () {
        return {
          form: null
        }
      },

      watch: {
        form: {
          handler: function (val) {
            this.$emit('on-changed', val);
          },
          deep: true,
        }
      },

      created: function () {
        this.form = JSON.parse(JSON.stringify(this.module));
      },

      methods: {
        removeImage(index) {
          this.form.images.splice(index, 1);
        },

        itemShow(index) {
          this.form.images.find((e, key) => {
            if (index != key) return e.show = false
          });
          this.form.images[index].show = !this.form.images[index].show;
        },

        addImage() {
          this.form.images.find(e => e.show = false);
          this.form.images.push({
            image: languagesFill('images/demo/banner/banner-2-en.jpg'),
            show: true,
            link: {type: 'product', value: ''}
          });
        }
      }
    });

  </script>

  <template id="module-editor-icons-template">
    <div class="image-edit-wrapper">
      <div class="module-editor-row">设置</div>
      <div class="module-edit-group" style="margin-bottom: 200px;">
        <div class="module-edit-title">添加图片</div>
        <div class="pb-images-selector" v-for="(item, index) in form.images" :key="index">
          <div class="selector-head" @click="itemShow(index)">
            <div class="left">

              <img :src="thumbnail(item.image, 40, 40)" class="img-responsive">
            </div>

            <div class="right">
              <span @click="removeItem(index)" class="remove-item"><i class="el-icon-delete"></i></span>
              <i :class="'el-icon-arrow-'+(item.show ? 'up' : 'down')"></i>
            </div>
          </div>
          <div :class="'pb-images-list ' + (item.show ? 'active' : '')">
            <div class="pb-images-top">
              <pb-image-selector v-model="item.image" :is-language="false"></pb-image-selector>
              <div class="tag">建议尺寸(宽x高): : 200x200</div>
            </div>
            <div class="module-edit-title">配置标题</div>
            <text-i18n v-model="item.text" style="margin-bottom: 10px"></text-i18n>
            <link-selector :hide-types="['page_category', 'static']" v-model="item.link"></link-selector>
          </div>
        </div>
        <div class="add-items" style="margin-top: 20px">
          <el-button icon="el-icon-circle-plus-outline" type="primary" size="small" style="width: 100%"
                     @click="addItems" plain>添加
          </el-button>
        </div>
      </div>
    </div>
  </template>

  <script type="text/javascript">
    Vue.component('module-editor-icons', {
      template: '#module-editor-icons-template',

      props: ['module'],

      data: function () {
        return {
          form: null
        }
      },

      watch: {
        form: {
          handler: function (val) {
            this.$emit('on-changed', val);
          },
          deep: true
        }
      },

      created: function () {
        this.form = JSON.parse(JSON.stringify(this.module));
      },

      methods: {
        itemShow(index) {
          this.form.images.find((e, key) => {
            if (index != key) return e.show = false
          });
          this.form.images[index].show = !this.form.images[index].show;
        },

        addItems() {
          this.form.images.push({
            image: '',
            link: {
              type: 'product',
              value: ''
            },
            text: languagesFill(''),
            show: true
          })

          this.form.images.find((e, key) => {
            if (this.form.images.length - 1 != key) return e.show = false
          });
        },

        removeItem(index) {
          this.form.images.splice(index, 1);
        }
      }
    });

  </script>

  <template id="module-editor-product-template">
    <div class="module-editor-product-template">
      <div class="module-editor-row">设置</div>
      <div class="module-edit-group">
        <div class="module-edit-title">模块标题</div>
        <text-i18n v-model="form.title"></text-i18n>
      </div>

      <div class="module-editor-row">内容</div>
      <div class="module-edit-group">
        <div class="module-edit-title">配置商品</div>
        <div class="tab-info">
          <div class="module-edit-group">
            <div class="autocomplete-group-wrapper">
              <el-autocomplete
                  class="inline-input"
                  v-model="keyword"
                  value-key="name"
                  size="small"
                  :fetch-suggestions="querySearch"
                  placeholder="请输入关键字搜索"
                  :highlight-first-item="true"
                  @select="handleSelect"
              ></el-autocomplete>

              <div class="item-group-wrapper" v-loading="loading">
                <template v-if="productData.length">
                  <draggable
                      ghost-class="dragabble-ghost"
                      :list="productData"
                      @change="itemChange"
                      :options="{animation: 330}"
                  >
                    <div v-for="(item, index) in productData" :key="index" class="item">
                      <div>
                        <i class="el-icon-s-unfold"></i>
                        <span>${item.name}</span>
                      </div>
                      <i class="el-icon-delete right" @click="removeProduct(index)"></i>
                    </div>
                  </draggable>
                </template>
                <template v-else>请添加商品</template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script type="text/javascript">
    window.inno.randomString=(length = 32)=> {
    let str = '';
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for (let i = 0; i < length; i++) {
      str += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return str;
  }

    Vue.component('module-editor-product', {
      delimiters: ['${', '}'],
      template: '#module-editor-product-template',
      props: ['module'],
      data: function () {
        return {
          keyword: '',
          productData: [],
          loading: null,
          form: null
        }
      },

      watch: {
        form: {
          handler: function (val) {
            this.$emit('on-changed', val);
          },
          deep: true
        },
      },

      created: function () {
        this.form = JSON.parse(JSON.stringify(this.module));
        this.tabsValueProductData();
      },

      computed: {},

      methods: {
        tabTitleLanguage(titles) {
          return titles['zh_cn'];
        },

        tabsValueProductData() {
          var that = this;

          if (!this.form.products.length) return;
          this.loading = true;

          axios.get('api/products/names?product_ids=' + this.form.products.map(e => e.id).join(','), {hload: true}).then((res) => {
            this.loading = false;
            that.productData = res.data;
          })
        },

        querySearch(keyword, cb) {
          axios.get('products/autocomplete?name=' + encodeURIComponent(keyword), null, {hload: true}).then((res) => {
            cb(res.data);
          })
        },

        handleSelect(item) {
          if (!this.form.products.find(v => v == item.id)) {
            this.form.products.push(item);
            this.productData.push(item);
          }

          this.keyword = ""
        },

        itemChange(evt) {
          this.form.products = this.productData
        },

        addTabData(type) {
          console.log(type);
        },

        removeProduct(index) {
          this.productData.splice(index, 1)
          this.form.products.splice(index, 1);
        },
      }
    });
  </script>

  <template id="module-editor-category-template">
    <div class="module-editor-category-template">
      <div class="module-editor-row">设置</div>
      <div class="module-edit-group">
        <div class="module-edit-title">模块标题</div>
        <text-i18n v-model="form.title"></text-i18n>
      </div>

      <div class="module-editor-row">内容</div>
      <div class="module-edit-group">
        <div class="module-edit-title">搜索分类</div>
        <div class="tab-info">
          <div class="module-edit-group">
            <div class="autocomplete-group-wrapper">
              <el-autocomplete
                  class="inline-input"
                  v-model="keyword"
                  value-key="name"
                  size="small"
                  :fetch-suggestions="querySearch"
                  placeholder="请输入关键字搜索"
                  :highlight-first-item="true"
                  @select="handleSelect"
              ></el-autocomplete>
            </div>
          </div>
        </div>
      </div>
      <div class="module-edit-group">
        <div class="module-edit-title">数量</div>
        <el-input v-model="form.limit" type="muner" size="small" @input="limitChange"></el-input>
      </div>
    </div>
  </template>

  <script type="text/javascript">
    Vue.component('module-editor-category', {
      delimiters: ['${', '}'],
      template: '#module-editor-category-template',
      props: ['module'],
      data: function () {
        return {
          keyword: '',
          productData: [],
          loading: null,
          form: null
        }
      },

      watch: {
        form: {
          handler: function (val) {
            this.$emit('on-changed', val);
          },
          deep: true
        },
      },

      created: function () {
        this.form = JSON.parse(JSON.stringify(this.module));
        this.tabsValueProductData();
      },

      computed: {},

      methods: {
        tabTitleLanguage(titles) {
          return titles['zh_cn'];
        },

        tabsValueProductData() {
          var that = this;

          if (!this.form.products.length) return;
          this.loading = true;

          axios.get('products/names?product_ids=' + this.form.products.map(e => e.id).join(','), {hload: true}).then((res) => {
            this.loading = false;
            that.productData = res.data;
          })
        },

        querySearch(keyword, cb) {
          axios.get('categories/autocomplete?name=' + encodeURIComponent(keyword), null, {hload: true}).then((res) => {
            cb(res.data);
          })
        },

        handleSelect(item) {
          this.form.category_id = item.id;
          this.form.category_name = item.name;
          this.getCategories();
        },

        limitChange(e) {
          this.form.limit = e;
          this.getCategories();
        },

        getCategories() {
          axios.get(`categories/${this.form.category_id}/products`, {limit: this.form.limit}, {hload: true}).then((res) => {
            this.form.products = res.data
          })
        },

        itemChange(evt) {
          this.form.products = this.productData
        },

        addTabData(type) {
          console.log(type);
        },

        removeProduct(index) {
          this.productData.splice(index, 1)
          this.form.products.splice(index, 1);
        },
      }
    });
  </script>

  <template id="module-editor-latest-template">
    <div class="module-editor-latest-template">
      <div class="module-editor-row">设置</div>
      <div class="module-edit-group">
        <div class="module-edit-title">模块标题</div>
        <text-i18n v-model="form.title"></text-i18n>
      </div>

      <div class="module-editor-row">内容</div>
      <div class="module-edit-group">
        <div class="module-edit-title">数量</div>
        <el-input v-model="form.limit" type="muner" size="small" @input="limitChange"></el-input>
      </div>
    </div>
  </template>

  <script type="text/javascript">
    Vue.component('module-editor-latest', {
      delimiters: ['${', '}'],
      template: '#module-editor-latest-template',
      props: ['module'],
      data: function () {
        return {
          keyword: '',
          productData: [],
          loading: null,
          form: null
        }
      },

      watch: {
        form: {
          handler: function (val) {
            this.$emit('on-changed', val);
          },
          deep: true
        },
      },

      created: function () {
        this.form = JSON.parse(JSON.stringify(this.module));
        this.tabsValueProductData();
      },

      computed: {},

      methods: {
        tabTitleLanguage(titles) {
          return titles['zh_cn'];
        },

        tabsValueProductData() {
          this.loading = true;
          this.getLatest();
        },

        limitChange(e) {
          this.form.limit = e;
          this.getLatest();
        },

        getLatest() {
          axios.get('products/latest', {limit: this.form.limit}, {hload: true}).then((res) => {
            this.loading = false;
            this.form.products = res.data;
          })
        },

        removeProduct(index) {
          this.productData.splice(index, 1)
          this.form.products.splice(index, 1);
        },
      }
    });
  </script>


  <template id="pb-image-selector">
    <div class="pb-image-selector">
      <el-tabs v-if="isLanguage" @tab-click="tabClick" value="language-zh_cn"
               :stretch="languages.length > 5 ? true : false" type="card"
               :class="languages.length <= 1 ? 'languages-a' : ''">
        <el-tab-pane v-for="(item, index) in languages" :key="index" :label="item.name" :name="'language-' + item.code">
          <span slot="label" style="padding: 0 4px; font-size: 12px">@{{ item.name }}</span>

          <div class="i18n-inner">
            <div class="img">

              <el-image :src="type == 'image' ? thumbnail(value[item.code]) : 'image/video.png'" :id="'thumb-' + id"
                        @click="selectButtonClicked">
                <div slot="error" class="image-slot">
                  <i class="el-icon-picture-outline"></i>
                </div>
              </el-image>
            </div>
            <div class="btns">
              <el-button type="primary" size="mini" plain @click="selectButtonClicked">选择</el-button>
              <el-button size="mini" plain style="margin-left: 4px;" @click="removeImage">删除</el-button>
            </div>
            <input type="hidden" value="" v-model="src" :id="'input-' + id">
          </div>
        </el-tab-pane>
      </el-tabs>

      <div class="i18n-inner" v-else>
        <div class="img">
          <el-image :src="type == 'image' ? thumbnail(value) : 'image/video.png'" :id="'thumb-' + id"
                    @click="selectButtonClicked">
            <div slot="error" class="image-slot">
              <i class="el-icon-picture-outline"></i>
            </div>
          </el-image>
        </div>

        <div class="btns">
          <el-button type="primary" size="mini" plain @click="selectButtonClicked">选择</el-button>
          <el-button size="mini" plain style="margin-left: 4px;" @click="removeImage">删除</el-button>
        </div>
        <input type="hidden" value="" v-model="src">
      </div>
    </div>
  </template>

  <script type="text/javascript">
    Vue.component('pb-image-selector', {
      template: '#pb-image-selector',

      props: {
        value: {
          default: null
        },
        type: {
          default: 'image'
        },
        isLanguage: { // 是否需要多语言配置
          default: true
        },
      },

      data: function () {
        return {
          tabActiveId: 'zh_cn',
          languages: $languages,
          internalValues: {},
          id: 'image-selector-' + inno.randomString(4),
          loading: null
        }
      },

      created: function () {
        if (this.isLanguage) {
          this.languages.forEach(e => {
            let value = this.value;
            if (typeof (this.value) == 'object') {
              value = this.value[e.code];
            }

            Vue.set(this.internalValues, e.code, value || '');
          })
          this.$emit('input', this.internalValues);
        }
      },

      computed: {
        src: {
          get() {
            return this.value;
          },
          set(newValue) {
            this.$emit('input', newValue);
          }
        }
      },

      methods: {
        removeImage() {
          if (this.isLanguage) {
            this.src[this.tabActiveId] = '';
          } else {
            this.src = '';
          }
        },

        tabClick(e) {
          this.tabActiveId = this.languages[e.index * 1].code;
        },

        selectButtonClicked() {
          // 创建文件输入元素
          const input = document.createElement('input');
          input.type = 'file';
          input.accept = 'image/*';

          input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
              this.cropImage(file);
            }
          };

          input.click();
        },

        cropImage(file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            // 创建遮罩层
            const mask = document.createElement('div');
            mask.className = 'cropper-mask';
            document.body.appendChild(mask);

            // 创建裁剪对话框
            const dialog = document.createElement('div');
            dialog.className = 'cropper-dialog';
            dialog.innerHTML = `
              <div class="cropper-container">
                <img src="${e.target.result}">
              </div>
              <div class="cropper-controls">
                <button class="btn btn-default cancel">取消</button>
                <button class="btn btn-primary confirm">确认</button>
              </div>
            `;

            document.body.appendChild(dialog);

            // 初始化 cropper
            const image = dialog.querySelector('img');
            const cropper = new Cropper(image, {
              aspectRatio: 2, // 1000:500 = 2:1
              viewMode: 1,
              autoCropArea: 1,
              ready() {
                // 裁剪框准备就绪后调整大小
                cropper.setCropBoxData({
                  width: 800,
                  height: 400
                });
              }
            });

            // 确认裁剪
            dialog.querySelector('.confirm').onclick = () => {
              const canvas = cropper.getCroppedCanvas({
                width: 1000,
                height: 500
              });
              console.log("{{ panel_route('inno_mobile_builder.upload.images') }}");

              canvas.toBlob((blob) => {
                // 上传裁剪后的图片
                const formData = new FormData();
                formData.append('image', blob);
                formData.append('type', 'banners');
                formData.append('_token', '{{ csrf_token() }}');

                axios.post("{{ panel_route('inno_mobile_builder.upload.images') }}", formData).then(response => {
                  console.log(response);
                  if (this.isLanguage) {
                    this.src[this.tabActiveId] = response.data.path;
                  } else {
                    this.src = response.data.path;
                  }
                  document.body.removeChild(dialog);
                  document.body.removeChild(mask);
                });
              });
            };

            // 取消裁剪
            dialog.querySelector('.cancel').onclick = () => {
              document.body.removeChild(dialog);
              document.body.removeChild(mask);
            };
          };
          reader.readAsDataURL(file);
        }
      }
    });
  </script>

  <style scoped>
    .pb-image-selector {
    }

    .languages-a .el-tabs__header {
      display: none;
    }

    .pb-image-selector .btns {
      margin-left: 10px;
    }

    .pb-image-selector .btns .el-button {
      padding: 7px 10px;
    }


    .pb-image-selector .el-tabs__nav {
      display: flex;
      border-color: #ebecf5;
    }

    .pb-image-selector .el-tabs__nav > div {
      background: #ebecf5;
      border-left: 1px solid #d7dbf7 !important;
      padding: 0 !important;
      flex: 1;
      height: 30px;
      line-height: 30px;
      min-width: 50px;
      text-align: center;
    }

    .pb-image-selector .el-tabs__nav > div:first-of-type {
      border-left: none !important;
    }

    .pb-image-selector .el-tabs__nav > div.is-active {
      background: #fff !important;
    }

    .pb-image-selector .i18n-inner {
      margin-top: 5px;
      display: flex;
      align-items: center;
      background: whitesmoke;
      padding: 5px;
      border-radius: 4px;
    }

    .pb-image-selector .i18n-inner .img {
      width: 46px;
      height: 46px;
      border: 1px solid #eee;
      padding: 2px;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .image-slot {
      font-size: 26px;
      color: #939ab3;
    }


    .pb-image-selector .i18n-inner .img img {
      max-width: 100%;
      height: auto;
    }

    .pb-image-selector .el-tabs__header {
      margin-bottom: 0;
    }
  </style>
  <template id="link-selector">
    <div class="link-selector-wrap">
      <div class="title" v-if="isTitle"><i class="el-icon-link"></i>选择链接</div>
      <div class="selector-type" @blur="selectorContentShow = false" tabindex="1">
        <div class="title" v-if="link.type != 'custom' ? value.value == '' : ''"
             @click="selectorContentShow = !selectorContentShow">选择链接
        </div>
        <div class="title" @click="selectorContentShow = !selectorContentShow" v-else :title="name"
             v-loading="nameLoading">@{{ selectorTitle }}: @{{ name }}
        </div>
        <div :class="'selector-content ' + (selectorContentShow ? 'active' : '')">
          <div @click="selectorType()">无</div>
          <div v-for="(type, index) in types" :key="index" @click="selectorType(type.type)">@{{ type.label }}</div>
        </div>
      </div>

      <el-dialog
          :visible.sync="linkDialog.show"
          class="link-dialog-box"
          :append-to-body="true"
          :close-on-click-modal="false"
          @open="linkDialogOpen"
          @closed="linkDialogClose"
          width="460px">
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

            <a :href="linkTypeAdmin" target="_blank" v-if="link.type != 'custom' && link.type != 'static'">管理@{{
              dialogTitle }}</a>
          </div>

          <div class="link-text" v-if="isCustomName">
            <div class="module-edit-group" style="margin-bottom: 10px;">
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
                    <div class="left">
                      <span :class="'checkbox-plus ' + (link.value == product.value ? 'active':'')"></span>
                      <div>@{{ product.name }}</div>
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
                      @click="product.status ? link.value = product.id : false"
                      :class="!product.status ? 'no-status' : ''">
                    <div class="left">
                      <span
                          :class="'checkbox-plus ' + (link.value == product.id ? 'active':'') + (!product.status ? 'no-status':'')"></span>
                      <img :src="product.image" v-if="product.image" class="img-responsive">
                      <div>@{{ product.name }}</div>
                    </div>
                    <div :class="'right ' + (product.status ? 'ok' : 'no')">
                      <template v-if="product.status">启用</template>
                      <template v-else>禁用</template>
                    </div>
                  </li>
                </ul>
              </template>
              <div class="product-info-no" v-if="!linkDialog.data.length && loading === false">
                <div class="icon"><i class="iconfont">&#xe60c;</i></div>
                <div class="no-text">数据不存在或已被删除, <a :href="linkTypeAdmin" target="_blank">去添加@{{
                    dialogTitle }}</a></div>
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
  <script type="text/javascript">
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
          default: function () {
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

      data: function () {
        return {
          types: [
            {type: 'product', label: '商品'},
            {type: 'category', label: '商品分类'},
            {type: 'page', label: '特定页面'},
            {type: 'page_category', label: '文章分类'},
            {type: 'brand', label: '商品品牌'},
            {type: 'static', label: '固定连接'},
            {type: 'custom', label: '自定义'}
          ],
          static: [
            {name: '个人中心', value: 'account.index'},
            {name: '我的收藏', value: 'account.wishlist.index'},
            {name: '我的订单', value: 'account.order.index'},
            // {name: '最新商品', value: 'account.index'},
            {name: '品牌列表', value: 'brands.index'},
          ],
          link: null,
          keyword: '',
          name: '',
          locale: 'zh_cn',
          loading: null,
          nameLoading: null,
          selectorContentShow: false,
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

      watch: {
        value() {
          if (this.isUpdate) {
            this.updateData();
          }
        }
      },

      computed: {
        dialogTitle: function () {
          return this.types.find(e => e.type == this.link.type).label;
        },

        selectorTitle() {
          return this.types.find(e => e.type == this.value.type).label;
        },

        linkTypeAdmin: function () {
          let url = '';

          switch (this.link.type) {
            case 'product':
              url = 'https://demo.beikeshop.com/admin/products';
              break;
            case 'category':
              url = 'https://demo.beikeshop.com/admin/categories';
              break;
            case 'brand':
              url = 'https://demo.beikeshop.com/admin/brands';
              break;
            case 'page':
              url = 'https://demo.beikeshop.com/admin/pages';
              break;
            case 'page_category':
              url = 'https://demo.beikeshop.com/admin/page_categories';
              break;
            default:
              null;
          }
          return url;
        },
      },

      methods: {
        linkDialogConfirm() {
          this.isUpdate = false;
          if (this.link.type == 'custom') {
            this.name = this.link.value;
          } else if (this.link.type == 'static') {
            this.name = this.static.find(e => e.value == this.link.value).name;
          } else {
            this.name = this.linkDialog.data.find(e => e.id == this.link.value).name;
          }

          let links = JSON.parse(JSON.stringify(this.link)); // type 类型切换时，不需要更新视图
          this.$emit("input", links);
          this.linkDialog.show = false;
          this.$nextTick(() => {
            this.isUpdate = true;
          })
        },

        searchProduct() {
          const self = this;
          this.link.value = '';
          this.querySearch(this.keyword, null, function (data) {
            self.linkDialog.data = data.data;
          })
        },

        linkDialogClose() {
          this.linkDialog.data = [];
        },

        linkDialogOpen() {
          const self = this;
          this.keyword = '',
            this.selectorContentShow = false;
          if (this.link.type != 'custom' || this.value.type != 'custom') {
            this.link.value = ''
          }

          if (this.link.type == 'custom' || this.link.type == 'static') {
            return;
          }

          this.querySearch(this.keyword, 'all', function (data) {
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

          this.selectorContentShow = false;
          this.$emit("input", {link: '', type: 'category', value: ''});
        },

        querySearch(keyword, all, cb) {
          const self = this;
          let url = '';

          switch (this.link.type) {
            case 'product':
              url = 'products/autocomplete?name=';
              break;
            case 'category':
              url = 'categories/autocomplete?name=';
              break;
            case 'brand':
              url = 'brands/autocomplete?name=';
              break;
            case 'page':
              url = 'pages/autocomplete?name=';
              break;
            case 'page_category':
              url = 'page_categories/autocomplete?name=';
              break;
            default:
              null;
          }

          this.loading = true;

          axios.get(url + encodeURIComponent(keyword), null, {hload: true}).then((res) => {
            if (res) {
              cb(res)
            }
            ;
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
          this.value.type = this.value?.type || 'category';
          this.value.link = this.value?.link || '';
          this.link = JSON.parse(JSON.stringify(this.value));
          if (this.type) {
            this.types = this.types.filter(e => e.type == this.type);
          }

          if (this.link.type == 'custom') return this.name = this.link.value || this.link.text[this.locale] || '';

          if (!this.link.value) return;
          if (this.link.type == 'static') {
            if (this.static.find(e => e.value == this.link.value)) {
              this.name = this.static.find(e => e.value == this.link.value).name;
            }

            return;
          }

          this.nameLoading = true;

          let self = this, url = '', data = {};

          switch (this.link.type) {
            case 'product':
              url = `products/${this.link.value}/name`;
              break;
            case 'category':
              url = `categories/${this.link.value}/name`;
              break;
            case 'brand':
              url = `brands/${this.link.value}/name`;
              break;
            case 'page':
              url = `pages/${this.link.value}/name`;
              break;
            case 'page_category':
              url = `page_categories/${this.link.value}/name`;
              break;
            default:
              null;
          }

          axios.get(url, null, {hload: true, hmsg: true}).then((res) => {
            if (res.data) {
              self.name = res.data;
            } else {
              self.name = '数据不存或已被删除';
            }
          }).catch(() => {
            self.name = '数据不存在或已被删除';
          }).finally(() => {
            self.nameLoading = false;
          });
        },
      }
    });
  </script>
  <style>
    .link-text {
      margin-bottom: 5px;
    }

    .link-text .text-i18n-template .el-tabs__nav > div {
      height: 26px;
      line-height: 26px;
    }

    .link-text .el-tabs {
      margin-top: 10px;
    }

    .link-text .el-tabs__header {
      margin: 0;
    }

    .el-collapse-item__wrap {
      overflow: initial;
    }
  </style>
  <template id="text-i18n-template">
    <div class="text-i18n-template">
      <el-tabs v-if="languages.length > 1" value="language-zh_cn"
               :stretch="languages.length > 5 ? true : false" type="card">
        <el-tab-pane v-for="(item, index) in languages" :key="index" :label="item.name"
                     :name="'language-' + item.code">
          <span slot="label" style="padding: 0 8px; font-size: 12px">@{{ item.name }}</span>

          <div class="i18n-inner">
            <el-input :type="type" :rows="4" :placeholder="item.name" :key="index"
                      :size="size" v-model="value[item.code]" @input="(val) => {valueChanged(val, item.code)}">
            </el-input>
          </div>
        </el-tab-pane>
      </el-tabs>

      <div class="i18n-inner" v-else>
        <el-input :type="type" :rows="4" :placeholder="languages[0].name" :size="size"
                  :value="value[languages[0].code]" @input="(val) => {valueChanged(val, languages[0].code)}"></el-input>
      </div>
    </div>
  </template>

  <script type="text/javascript">
    Vue.component('text-i18n', {
      template: '#text-i18n-template',
      props: {
        value: {
          default: null
        },
        size: {
          default: 'small'
        },
        type: {
          type: String,
          default: 'text'
        },
      },
      data: function () {
        return {
          languages: $languages,
          internalValues: {}
        }
      },

      created: function () {
        this.initData()
      },

      methods: {
        valueChanged(val, code) {
          this.internalValues[code] = val;
          // this.$emit('input', JSON.parse(JSON.stringify(this.internalValues)));
          this.$emit('input', this.internalValues);
        },

        initData() {
          this.languages.forEach(e => {
            Vue.set(this.internalValues, e.code, this.value[e.code] || '');
          })
          // this.$emit('input', JSON.parse(JSON.stringify(this.internalValues)));
          this.$emit('input', this.internalValues);
        }
      }
    });
  </script>

  <style>
    .text-i18n-template .el-tabs__nav {
      display: flex;
      border-color: #ebecf5;
    }

    .text-i18n-template .el-tabs__nav > div {
      background: #ebecf5;
      border-left: 1px solid #d7dbf7 !important;
      padding: 0 !important;
      flex: 1;
      height: 30px;
      line-height: 30px;
      text-align: center;
    }

    .text-i18n-template .el-tabs__nav > div:first-of-type {
      border-left: none !important;
    }

    .text-i18n-template .el-tabs__nav > div.is-active {
      background: #fff !important;
    }

    .text-i18n-template .i18n-inner {
      margin-top: 5px;
    }

    .text-i18n-template .el-tabs__header {
      margin-bottom: 0;
    }

    .design-app-home .main-content>#content{overflow:hidden}.design-app-home .tag{color:#777;font-size:12px;margin:8px 0}.design-app-home .hint-right-edit{color:#777;padding:10px;text-align:center;width:100%}.design-app-home .module-title{font-size:16px;font-weight:700;padding:10px 0;text-align:center}.design-app-home #app .card-body{display:flex;justify-content:center;padding:0}.design-app-home #app .card-body>div{flex:1}.design-app-home #app .card-body .component-wrap{height:calc(100% - 60px);overflow-y:auto;padding:0 14px}.design-app-home #app .card-body .c-title{font-size:16px;font-weight:700;padding:14px;text-align:center}.design-app-home #app .card-body .module-wrap{max-width:360px}.design-app-home #app .card-body .module-wrap .modules-list{overflow-y:auto;padding:0 14px}.design-app-home #app .card-body .module-wrap .modules-list .list-item{align-items:center;border:1px solid #eee;border-radius:2px;cursor:move;display:flex;margin-bottom:10px;padding:10px 24px 10px 16px;position:relative}.design-app-home #app .card-body .module-wrap .modules-list .list-item:after{color:#999;content:"\f3fe";font-family:bootstrap-icons;font-size:16px;position:absolute;right:8px}.design-app-home #app .card-body .module-wrap .modules-list .list-item:hover{border-color:#8446df}.design-app-home #app .card-body .module-wrap .modules-list .list-item .icon{width:35px}.design-app-home #app .card-body .module-wrap .modules-list .list-item .icon i{color:#666;font-size:26px;line-height:1}.design-app-home #app .card-body .module-wrap .modules-list .list-item .name{font-size:12px;font-weight:700;overflow:hidden}.design-app-home #app .card-body .perview-wrap{align-items:center;border-left:1px solid #eee;border-right:1px solid #eee;display:flex;flex:0 0 40%;flex-direction:column;justify-content:flex-start;padding-bottom:20px}.design-app-home #app .card-body .perview-wrap .perview-content{background-color:#f6f6f6;border:2px solid #eee;border-radius:20px;box-shadow:0 13px 21px rgba(0,0,0,.07);height:100%;max-width:380px;overflow:hidden;position:relative;width:70%}.design-app-home #app .card-body .perview-wrap .perview-content .head{border-bottom:1px solid #eee;border-radius:20px 20px 0 0;overflow:hidden}.design-app-home #app .card-body .perview-wrap .hint{color:#888;font-size:15px;position:absolute;text-align:center;top:30%;width:100%}.design-app-home #app .card-body .perview-wrap .view-modules-list{height:100%;overflow-y:auto}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item{border:1px solid transparent;margin:7px 0;position:relative;width:100%}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:hover{border-color:#8446df}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:hover .module-tool{display:flex}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:first-of-type{margin-top:0}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool{background-color:rgba(0,0,0,.5);display:none;height:26px;left:0;position:absolute;top:0;width:100%}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool>div{align-items:center;color:#fff;cursor:pointer;display:flex;height:100%;justify-content:center;width:36px}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool>div:hover{background-color:#333}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.sortable-ghost{align-items:center;border:1px dashed #aaa;display:flex;justify-content:center;padding:6px 10px;text-align:center}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.sortable-ghost .icon{margin-right:6px}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.active{border:2px solid #8446df;box-shadow:0 0 10px 2px rgba(132, 70, 223, 0.1)}.design-app-home #app .card-body .module-edit{overflow:hidden;padding:0}.design-app-home .quick-icon-wrapper{background:#fff;display:flex;flex-flow:wrap;margin-bottom:20rpx;padding:30rpx 20rpx 0rpx}.design-app-home .quick-icon-wrapper .link-item{align-content:flex-start;align-items:center;display:flex;flex-direction:column;font-size:12px;justify-content:center;margin-bottom:10px;padding:5px;text-align:center;width:20%}.design-app-home .quick-icon-wrapper .link-item .img{max-height:120rpx}.design-app-home .quick-icon-wrapper .link-item span{display:block;font-size:12px;line-height:1.3;margin-top:7px}.design-app-home .quick-icon-wrapper.quick-icon-4 .link-item,.design-app-home .quick-icon-wrapper.quick-icon-8 .link-item{width:25%}.design-app-home .quick-icon-wrapper.quick-icon-3 .link-item{width:33.33%}.design-app-home .quick-icon-wrapper image{width:94rpx}.design-app-home .product-grid{display:flex;flex-wrap:wrap;justify-content:space-between}.design-app-home .product-grid .product-item{margin-bottom:10px;position:relative;width:calc(50% - 5px)}.design-app-home .product-grid .product-item:not(.video){background:#fff;border-radius:4px}.design-app-home .product-grid .product-item:before{border:1px solid rgba(0,0,0,.6);border-radius:4px;content:"";display:none;height:calc(100% + 2px);left:-1px;position:absolute;top:-1px;width:calc(100% + 2px)}.design-app-home .product-grid .product-item .name{-webkit-line-clamp:2;-webkit-box-orient:vertical;display:-webkit-box;font-weight:700;height:36px;margin-top:8px;overflow:hidden;padding:0 10px;text-overflow:ellipsis}.design-app-home .product-grid .product-item .tool-item>div{flex:1;padding-left:0;padding-right:0;text-align:center}.design-app-home .product-grid .product-item .product-price{margin:6px 0;padding:0 10px}
  </style>
  <script>
    $(document).ready(function ($) {
      const wh = window.innerHeight - 140;
      const perviewHead = $('.perview-content .head').height();
      $('#app').height(wh);
      $('.perview-content').height(wh - 70);
      $('.view-modules-list').height(wh - 74 - perviewHead);
      $('.modules-list, .component-wrap').height(wh - 70);
    })

    let app = new Vue({
      el: '#app',
      data: {
        form: { //初始化表单数据
          "modules": [{
            "title": "幻灯片模块",
            "code": "slideshow",
            "icon": '<i class="bi bi-images"></i>',
            "content": {
              "images": [{
                "image": {
                  "zh_cn": "images/demo/banner/banner-1-en.jpg",
                  "en": "images/demo/banner/banner-1-en.jpg"
                }, "show": false, "link": {"type": "product", "value": 1, "link": ""}
              }, {
                "image": {
                  "zh_cn": "images/demo/banner/banner-2-en.jpg",
                  "en": "images/demo/banner/banner-2-en.jpg"
                }, "show": true, "link": {"type": "category", "value": 100003, "link": ""}
              }]
            }
          }, {
            "title": "服务图标模块",
            "code": "icons",
            "icon": '<i class="bi bi-grid"></i>',
            "content": {
              "style": {"background_color": ""},
              "floor": {"zh_cn": "", "en": ""},
              "images": [{
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/1.png') }}",
                "link": {"type": "category", "value": 100003},
                "text": {"zh_cn": "特惠活动", "en": "Special"},
                "show": false
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/7.png') }}",
                "link": {"type": "product", "value": 2},
                "text": {"zh_cn": "全场爆款", "en": "Explosive"},
                "show": false
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/1.png') }}",
                "link": {"type": "brand", "value": 1},
                "text": {"zh_cn": "好货推荐", "en": "Selling"},
                "show": true
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/10.png') }}",
                "link": {"type": "product", "value": ""},
                "text": {"zh_cn": "大牌特价", "en": "Bigname"},
                "show": false
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/2.png') }}",
                "link": {"type": "category", "value": 100010},
                "text": {"zh_cn": "美好假日", "en": "Good"},
                "show": false
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/4.png') }}",
                "link": {"type": "category", "value": 100010},
                "text": {"zh_cn": "全场购买", "en": "Shop all"},
                "show": false
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/5.png') }}",
                "link": {"type": "category", "value": 100007},
                "text": {"zh_cn": "时尚特价", "en": "Fashion"},
                "show": false
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/6.png') }}",
                "link": {"type": "category", "value": 100003},
                "text": {"zh_cn": "优惠好物", "en": "Discount"},
                "show": false
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/8.png') }}",
                "link": {"type": "category", "value": 100008},
                "text": {"zh_cn": "冬日新品", "en": "Newest"},
                "show": false
              }, {
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/app-icons/9.png') }}",
                "link": {"type": "product", "value": 5},
                "text": {"zh_cn": "夏季上新", "en": "Summer"},
                "show": false
              }]
            }
          }, {
            "title": "图片模块",
            "code": "image100",
            "icon": '<i class="bi bi-image"></i>',
            "content": {
              "style": {"background_color": ""},
              "images": [{
                "image": {
                  "zh_cn": "images\/demo\/banner\/banner-2-en.jpg",
                  "en": "images\/demo\/banner\/banner-2-en.jpg"
                }, "show": true, "link": {"type": "category", "value": 100006, "link": ""}
              }]
            }
          }, { //商品模块初始demo数据
            "title": "商品模块",
            "code": "product",
            "icon": '<i class="bi bi-box"></i>',
            "content": {
              "style": {"background_color": ""},
              "floor": {"zh_cn": "", "en": ""},
              "products": [{
                "id": 1,
                "name": "都市精英风尚西装外套经典剪裁",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/1-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/1-100x100.jpg",
                "status": true
              }, {
                "id": 2,
                "name": "银河流光璀璨晚礼服闪耀全场",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/2-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/2-100x100.jpg",
                "status": true
              }, {
                "id": 3,
                "name": "晨曦漫步轻盈薄款风衣春意盎然",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/3-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/3-100x100.jpg",
                "status": true
              }, {
                "id": 4,
                "name": "极简风格主义经典衬衫简约不简单",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/4-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/4-100x100.jpg",
                "status": true
              }],
              "title": {"zh_cn": "推荐商品", "en": "Hot Items"}
            }
          }, {
            "title": "分类商品模块",
            "code": "category",
            "icon": '<i class="bi bi-collection"></i>',
            "content": {
              "style": {"background_color": ""},
              "limit": "4",
              "order": "asc",
              "category_id": 100003,
              "category_name": "时尚潮流",
              "sort": "sales",
              "floor": {"zh_cn": "", "en": ""},
              "products": [{
                "id": 1,
                "name": "摩登复古风高腰牛仔裤经典再现",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/5-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/1-100x100.jpg",
                "status": true
              }, {
                "id": 2,
                "name": "幻彩流苏时尚个性围巾绚丽多彩",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/6-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/2-100x100.jpg",
                "status": true
              }, {
                "id": 3,
                "name": "男士白色卫衣套装",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/7-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/3-100x100.jpg",
                "status": true
              }, {
                "id": 4,
                "name": "优雅蕾丝边透视性感上衣女性魅力",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/8-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/4-100x100.jpg",
                "status": true
              }],
              "title": {"zh_cn": "分类商品", "en": "New Summer"}
            }
          }, {
            "title": "最新商品模块",
            "code": "latest",
            "icon": '<i class="bi bi-star"></i>',
            "content": {
              "style": {"background_color": ""},
              "limit": "4",
              "floor": {"zh_cn": "", "en": ""},
              "products": [{
                "id": 1,
                "name": "都市精英风尚西装外套经典剪裁",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/1-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/1-100x100.jpg",
                "status": true
              }, {
                "id": 2,
                "name": "银河流光璀璨晚礼服闪耀全场",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/2-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/2-100x100.jpg",
                "status": true
              }, {
                "id": 3,
                "name": "晨曦漫步轻盈薄款风衣春意盎然",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/3-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/3-100x100.jpg",
                "status": true
              }, {
                "id": 4,
                "name": "极简风格主义经典衬衫简约不简单",
                "image": "{{ plugin_asset('InnoMobileBuilder','images/demo/product/4-600x600.png') }}",
                "image_format": "https://beike.gdemo.top/cache/catalog/demo/product/4-100x100.jpg",
                "status": true
              }],
              "title": {"zh_cn": "最新商品", "en": "New Products"}
            }
          }]
        },
        source: {
          locale: 'zh_cn',
          modules: []
        },
        design: {
          editingModuleIndex: 0,
        }
      },

      computed: {
        // 编辑中的模块编辑组件
        editingModuleComponent() {
          if (!this.editingModuleCode) {
            return false;
          }

          return 'module-editor-' + this.editingModuleCode.replace('_', '-');
        },

        // 编辑中的模块 code
        editingModuleCode() {
          if (this.form.modules.length === 0) {
            return false;
          }

          return this.form.modules[this.design.editingModuleIndex].code;
        },
      },

      watch: {},

      methods: {
        saveButtonClicked() {
          axios.put('design_app_home/builder', this.form).then((res) => {
            layer.msg(res.message)
          })
        },

        perviewEnd(e) {
          this.design.editingModuleIndex = e.newIndex;
        },

        cloneDefaultField(e) {
          return JSON.parse(JSON.stringify(e));
        },

        deleteDodule(index) {
          this.form.modules.splice(index, 1);
          if (this.design.editingModuleIndex == index) {
            if (index - 1 < 0) {
              this.design.editingModuleIndex = 0;
              return;
            }
            this.design.editingModuleIndex = index - 1;
          }

          if (this.design.editingModuleIndex >= this.form.modules.length) {
            this.design.editingModuleIndex = this.form.modules.length - 1;
          }
        },

        moduleUpdated(e) {
          this.form.modules[this.design.editingModuleIndex].content = e;
        },
      },

      created() {
      },
      mounted() {
      },
    })

    // let saveBtn = document.querySelector('.save-btn')
    // saveBtn.addEventListener('click', () => {
    //   app.saveButtonClicked()
    // })
  </script>

@endsection

@push('footer')
  <script>

    function languagesFill(text) {
      var obj = {};
      $languages.map(e => {
        obj[e.code] = text
      })

      return obj;
    }

  </script>
  <script>
    // 定义模块的配置项
    app.source.modules.push({
      title: '图片模块',
      code: 'image100',
      icon: '<i class="bi bi-image"></i>', // 替换原来的 &#xe663;
      content: {
        style: {
          background_color: ''
        },
        images: [
          {
            image: languagesFill('images/demo/banner/banner-2-en.png'),
            show: true,
            link: {
              type: 'product',
              value:''
            }
          }
        ]
      }
    })
  </script>
  <script>
    // 定义模块的配置项

    app.source.modules.push({
      title: '幻灯片模块',
      code: 'slideshow',
      icon: '<i class="bi bi-images"></i>',
      content: {
        images: [
          {
            image: languagesFill('images/demo/banner/banner-1-en.jpg'), // 修改图片路径
            show: true,
            link: {
              type: 'product',
              value: ''
            }
          },
          {
            image: {  // 为不同语言设置不同图片
              zh_cn: 'images/demo/banner/banner-1-cn.jpg',
              en: 'images/demo/banner/banner-1-en.jpg'
            },
            show: false,
            link: {
              type: 'product',
              value: ''
            }
          },
          {
            image: {
              zh_cn: 'images/demo/banner/banner-2-cn.jpg',
              en: 'images/demo/banner/banner-2-en.jpg'
            },
            show: false,
            link: {
              type: 'product',
              value: ''
            }
          }
        ]
      }
    })
  </script>
  <script>
    app.source.modules.push({
      title: '服务图标模块',
      code: 'icons',
      icon: '<i class="bi bi-grid"></i>',
      content: {
        style: {
          background_color: ''
        },
        floor: languagesFill(''),
        images: []
      }
    })
  </script>
  <script>
    app.source.modules.push({
      title: '商品模块',
      code: 'product',
      icon: '<i class="bi bi-box"></i>',
      content: {
        style: {
          background_color: ''
        },
        floor: languagesFill(''),
        products: [],
        title: languagesFill('模块标题'),
      }
    });
  </script>
  <script>
    app.source.modules.push({
      title: '分类商品模块',
      code: 'category',
      icon: '<i class="bi bi-collection"></i>',
      content: {
        style: {
          background_color: ''
        },
        limit: 10,
        order: 'asc',
        category_id: '',
        category_name: '',
        sort: 'sales',
        floor: languagesFill(''),
        products: [],
        title: languagesFill('模块标题'),
      }
    });
  </script>
  <script>
    app.source.modules.push({
      title: '最新商品模块',
      code: 'latest',
      icon: '<i class="bi bi-star"></i>',
      content: {
        style: {
          background_color: ''
        },
        limit: 10,
        floor: languagesFill(''),
        products: [],
        title: languagesFill('模块标题'),
      }
    });
  </script>
@endpush

<style>
.cropper-dialog {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.2);
  z-index: 1050; /* 确保在其他元素之上 */
  width: 900px; /* 设置合适的宽度 */
}

.cropper-container {
  width: 100%;
  height: 500px;
  margin-bottom: 20px;
  overflow: hidden; /* 确保裁剪区域不会溢出 */
}

.cropper-container img {
  max-width: 100%;
  display: block;
}

.cropper-controls {
  text-align: right;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.cropper-controls button {
  margin-left: 10px;
  padding: 6px 20px;
}

/* 添加遮罩层 */
.cropper-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}
</style>
