@extends('panel::layouts.app')

@section('title', __('MobileBuilder::route.title'))

@section('body-class', 'design-app-home')

<x-panel::form.right-btns/>

@push('header')
  <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
  <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
  <script src="{{ plugin_asset('mobile_builder', 'js/vuedraggable.js') }}"></script>
  <link rel="stylesheet" type="text/css" href="{{ plugin_asset('mobile_builder', 'css/design.css') }}">
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
          return 'image/placeholder.png';
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
    }
  </script>
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
              <div v-if="!module.content.products.length" class="hint-right-edit">请在右侧配置模块</div>
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
                <el-tooltip class="" effect="dark" content="删除" placement="left">
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
            image: languagesFill('catalog/demo/banner/banner-4-en.jpg'),
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

          axios.get('products/names?product_ids=' + this.form.products.map(e => e.id).join(','), {hload: true}).then((res) => {
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
          this.loading = true;

          inno.fileManagerIframe(images => {
            this.loading = false;

            if (this.isLanguage) {
              this.src[this.tabActiveId] = images[0].path;
            } else {
              this.src = images[0].path;
            }
          })
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
              <span>是否新窗口打开:</span>
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
              self.name = '数据不存在或已被删除';
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

    .design-app-home .main-content>#content{overflow:hidden}.design-app-home .tag{color:#777;font-size:12px;margin:8px 0}.design-app-home .hint-right-edit{color:#777;padding:10px;text-align:center;width:100%}.design-app-home .module-title{font-size:16px;font-weight:700;padding:10px 0;text-align:center}.design-app-home #app .card-body{display:flex;justify-content:center;padding:0}.design-app-home #app .card-body>div{flex:1}.design-app-home #app .card-body .component-wrap{height:calc(100% - 60px);overflow-y:auto;padding:0 14px}.design-app-home #app .card-body .c-title{font-size:16px;font-weight:700;padding:14px;text-align:center}.design-app-home #app .card-body .module-wrap{max-width:360px}.design-app-home #app .card-body .module-wrap .modules-list{overflow-y:auto;padding:0 14px}.design-app-home #app .card-body .module-wrap .modules-list .list-item{align-items:center;border:1px solid #eee;border-radius:2px;cursor:move;display:flex;margin-bottom:10px;padding:10px 24px 10px 16px;position:relative}.design-app-home #app .card-body .module-wrap .modules-list .list-item:after{color:#999;content:"\f3fe";font-family:bootstrap-icons;font-size:16px;position:absolute;right:8px}.design-app-home #app .card-body .module-wrap .modules-list .list-item:hover{border-color:#fd560f}.design-app-home #app .card-body .module-wrap .modules-list .list-item .icon{width:35px}.design-app-home #app .card-body .module-wrap .modules-list .list-item .icon i{color:#666;font-size:26px;line-height:1}.design-app-home #app .card-body .module-wrap .modules-list .list-item .name{font-size:12px;font-weight:700;overflow:hidden}.design-app-home #app .card-body .perview-wrap{align-items:center;border-left:1px solid #eee;border-right:1px solid #eee;display:flex;flex:0 0 40%;flex-direction:column;justify-content:flex-start;padding-bottom:20px}.design-app-home #app .card-body .perview-wrap .perview-content{background-color:#f6f6f6;border:2px solid #eee;border-radius:20px;box-shadow:0 13px 21px rgba(0,0,0,.07);height:100%;max-width:380px;overflow:hidden;position:relative;width:70%}.design-app-home #app .card-body .perview-wrap .perview-content .head{border-bottom:1px solid #eee;border-radius:20px 20px 0 0;overflow:hidden}.design-app-home #app .card-body .perview-wrap .hint{color:#888;font-size:15px;position:absolute;text-align:center;top:30%;width:100%}.design-app-home #app .card-body .perview-wrap .view-modules-list{height:100%;overflow-y:auto}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item{border:1px solid transparent;margin:7px 0;position:relative;width:100%}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:hover{border-color:#fd560f}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:hover .module-tool{display:flex}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:first-of-type{margin-top:0}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool{background-color:rgba(0,0,0,.5);display:none;height:26px;left:0;position:absolute;top:0;width:100%}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool>div{align-items:center;color:#fff;cursor:pointer;display:flex;height:100%;justify-content:center;width:36px}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool>div:hover{background-color:#333}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.sortable-ghost{align-items:center;border:1px dashed #aaa;display:flex;justify-content:center;padding:6px 10px;text-align:center}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.sortable-ghost .icon{margin-right:6px}.design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.active{border:2px solid #fd560f;box-shadow:0 0 10px 2px rgba(0,0,0,.1)}.design-app-home #app .card-body .module-edit{overflow:hidden;padding:0}.design-app-home .quick-icon-wrapper{background:#fff;display:flex;flex-flow:wrap;margin-bottom:20rpx;padding:30rpx 20rpx 0rpx}.design-app-home .quick-icon-wrapper .link-item{align-content:flex-start;align-items:center;display:flex;flex-direction:column;font-size:12px;justify-content:center;margin-bottom:10px;padding:5px;text-align:center;width:20%}.design-app-home .quick-icon-wrapper .link-item .img{max-height:120rpx}.design-app-home .quick-icon-wrapper .link-item span{display:block;font-size:12px;line-height:1.3;margin-top:7px}.design-app-home .quick-icon-wrapper.quick-icon-4 .link-item,.design-app-home .quick-icon-wrapper.quick-icon-8 .link-item{width:25%}.design-app-home .quick-icon-wrapper.quick-icon-3 .link-item{width:33.33%}.design-app-home .quick-icon-wrapper image{width:94rpx}.design-app-home .product-grid{display:flex;flex-wrap:wrap;justify-content:space-between}.design-app-home .product-grid .product-item{margin-bottom:10px;position:relative;width:calc(50% - 5px)}.design-app-home .product-grid .product-item:not(.video){background:#fff;border-radius:4px}.design-app-home .product-grid .product-item:before{border:1px solid rgba(0,0,0,.6);border-radius:4px;content:"";display:none;height:calc(100% + 2px);left:-1px;position:absolute;top:-1px;width:calc(100% + 2px)}.design-app-home .product-grid .product-item .name{-webkit-line-clamp:2;-webkit-box-orient:vertical;display:-webkit-box;font-weight:700;height:36px;margin-top:8px;overflow:hidden;padding:0 10px;text-overflow:ellipsis}.design-app-home .product-grid .product-item .tool-item>div{flex:1;padding-left:0;padding-right:0;text-align:center}.design-app-home .product-grid .product-item .product-price{margin:6px 0;padding:0 10px}
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
        form: {
          "modules": [{
            "title": "\u5e7b\u706f\u7247\u6a21\u5757",
            "code": "slideshow",
            "icon": "\u0026#xe663;",
            "content": {
              "images": [{
                "image": {
                  "zh_cn": "catalog\/demo\/banner\/banner-4-en.jpg",
                  "en": "catalog\/demo\/banner\/banner-4-en.jpg"
                }, "show": false, "link": {"type": "product", "value": 1, "link": ""}
              }, {
                "image": {
                  "zh_cn": "catalog\/demo\/banner\/banner-3-en.jpg",
                  "en": "catalog\/demo\/banner\/banner-3-en.jpg"
                }, "show": true, "link": {"type": "category", "value": 100003, "link": ""}
              }]
            }
          }, {
            "title": "\u670d\u52a1\u56fe\u6807\u6a21\u5757", "code": "icons", "icon": "\u0026#xe60e;", "content": {
              "style": {"background_color": ""},
              "floor": {"zh_cn": "", "en": ""},
              "images": [{
                "image": "catalog\/demo\/app-icon\/3.png",
                "link": {"type": "category", "value": 100003, "link": ""},
                "text": {"zh_cn": "\u7279\u60e0\u6d3b\u52a8", "en": "Special"},
                "show": false
              }, {
                "image": "catalog\/demo\/app-icon\/7.png",
                "link": {"type": "product", "value": 2, "link": ""},
                "text": {"zh_cn": "\u5168\u573a\u7206\u6b3e", "en": "Explosive"},
                "show": false
              }, {
                "image": "catalog\/demo\/app-icon\/1.png",
                "link": {"type": "brand", "value": 1, "link": ""},
                "text": {"zh_cn": "\u597d\u8d27\u63a8\u8350", "en": "Selling"},
                "show": true
              }, {
                "image": "catalog\/demo\/app-icon\/10.png",
                "link": {"type": "product", "value": "", "link": ""},
                "text": {"zh_cn": "\u5927\u724c\u7279\u4ef7", "en": "Bigname"},
                "show": false
              }, {
                "image": "catalog\/demo\/app-icon\/2.png",
                "link": {"type": "category", "value": 100010, "link": ""},
                "text": {"zh_cn": "\u7f8e\u597d\u5047\u65e5", "en": "Good"},
                "show": false
              }, {
                "image": "catalog\/demo\/app-icon\/4.png",
                "link": {"type": "category", "value": 100010, "link": ""},
                "text": {"zh_cn": "\u5168\u573a\u8d2d\u4e70", "en": "Shop all"},
                "show": false
              }, {
                "image": "catalog\/demo\/app-icon\/5.png",
                "link": {"type": "category", "value": 100007, "link": ""},
                "text": {"zh_cn": "\u65f6\u5c1a\u7279\u4ef7", "en": "Fashion"},
                "show": false
              }, {
                "image": "catalog\/demo\/app-icon\/6.png",
                "link": {"type": "category", "value": 100003, "link": ""},
                "text": {"zh_cn": "\u4f18\u60e0\u597d\u7269", "en": "Discount"},
                "show": false
              }, {
                "image": "catalog\/demo\/app-icon\/8.png",
                "link": {"type": "category", "value": 100008, "link": ""},
                "text": {"zh_cn": "\u51ac\u65e5\u65b0\u54c1", "en": "Newest"},
                "show": false
              }, {
                "image": "catalog\/demo\/app-icon\/9.png",
                "link": {"type": "product", "value": 5, "link": ""},
                "text": {"zh_cn": "\u590f\u5b63\u4e0a\u65b0", "en": "Summer"},
                "show": false
              }]
            }
          }, {
            "title": "\u56fe\u7247\u6a21\u5757",
            "code": "image100",
            "icon": "\u0026#xe663;",
            "content": {
              "style": {"background_color": ""},
              "images": [{
                "image": {
                  "zh_cn": "\/catalog\/demo\/banner\/banner-2.jpg",
                  "en": "\/catalog\/demo\/banner\/banner-2.jpg"
                }, "show": true, "link": {"type": "category", "value": 100006, "link": ""}
              }]
            }
          }, {
            "title": "\u5546\u54c1\u6a21\u5757", "code": "product", "icon": "\u0026#xe607;", "content": {
              "style": {"background_color": ""},
              "floor": {"zh_cn": "", "en": ""},
              "products": [{
                "id": 1,
                "name": "\u6b27\u6d32\u7ad9\u590f\u5b63\u65b0\u6b3e\u65f6\u5c1a\u4f11\u95f2\u77ed\u88e4\u70ed\u88e4\u5973\u88e4\u8fd0\u52a8\u5bb6\u5177\u7eaf\u68c9\u97e9\u7248\u5bbd\u677e\u767e\u642d\u88e4",
                "image": "catalog\/demo\/product\/1.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/1-100x100.jpg",
                "status": true
              }, {
                "id": 2,
                "name": "\u4e2d\u957f\u6b3e\u725b\u4ed4\u534a\u8eab\u88d9\u5973\u6625\u590f\u5b632021\u65b0\u6b3e\u8584\u6b3e\u9ad8\u8170\u5f00\u53c9\u5305\u81c0\u957f\u88d9A\u5b57\u88d9\u5b50",
                "image": "catalog\/demo\/product\/13.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/13-100x100.jpg",
                "status": true
              }, {
                "id": 3,
                "name": "\u53cc\u80a9\u5305\u4e66\u5305\u7537\u5973\u7b14\u8bb0\u672c\u7535\u8111\u5305\u65f6\u5c1a\u6f6e\u6d41\u65c5\u884c\u80cc\u5305",
                "image": "catalog\/demo\/product\/12.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/12-100x100.jpg",
                "status": true
              }, {
                "id": 4,
                "name": "\u7537\u5b50 \u4f11\u95f2\u978b TANJUN \u5929\u541b \u4f11\u95f2\u978b \u8fd0\u52a8\u978b 812654",
                "image": "catalog\/demo\/product\/3.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/3-100x100.jpg",
                "status": true
              }],
              "title": {"zh_cn": "\u63a8\u8350\u5546\u54c1", "en": "Hot Items"}
            }
          }, {
            "title": "\u5206\u7c7b\u5546\u54c1\u6a21\u5757", "code": "category", "icon": "\u0026#xe607;", "content": {
              "style": {"background_color": ""},
              "limit": "4",
              "order": "asc",
              "category_id": 100003,
              "category_name": "\u65f6\u5c1a\u6f6e\u6d41",
              "sort": "sales",
              "floor": {"zh_cn": "", "en": ""},
              "products": [{
                "id": 2,
                "name": "\u4e2d\u957f\u6b3e\u725b\u4ed4\u534a\u8eab\u88d9\u5973\u6625\u590f\u5b632021\u65b0\u6b3e\u8584\u6b3e\u9ad8\u8170\u5f00\u53c9\u5305\u81c0\u957f\u88d9A\u5b57\u88d9\u5b50",
                "image": "catalog\/demo\/product\/13.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/13-100x100.jpg",
                "status": true
              }, {
                "id": 3,
                "name": "\u53cc\u80a9\u5305\u4e66\u5305\u7537\u5973\u7b14\u8bb0\u672c\u7535\u8111\u5305\u65f6\u5c1a\u6f6e\u6d41\u65c5\u884c\u80cc\u5305",
                "image": "catalog\/demo\/product\/12.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/12-100x100.jpg",
                "status": true
              }, {
                "id": 4,
                "name": "\u7537\u5b50 \u4f11\u95f2\u978b TANJUN \u5929\u541b \u4f11\u95f2\u978b \u8fd0\u52a8\u978b 812654",
                "image": "catalog\/demo\/product\/3.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/3-100x100.jpg",
                "status": true
              }, {
                "id": 5,
                "name": "\u9ad8\u7ea7\u611f\u7537\u88c5\u590f\u5b63\u6f6e\u724c\u7f8e\u5f0f\u590d\u53e4\u77ed\u8896t\u6064\u7537\u58eb\u91cd\u78c5\u7eaf\u68c9\u5bbd\u677e\u534a\u8896\u7537\u4f53\u6064",
                "image": "catalog\/demo\/product\/4.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/4-100x100.jpg",
                "status": true
              }],
              "title": {"zh_cn": "\u5206\u7c7b\u5546\u54c1", "en": "New Summer"}
            }
          }, {
            "title": "\u6700\u65b0\u5546\u54c1\u6a21\u5757", "code": "latest", "icon": "\u0026#xe607;", "content": {
              "style": {"background_color": ""},
              "limit": "4",
              "floor": {"zh_cn": "", "en": ""},
              "products": [{
                "id": 39,
                "name": "\u590f\u5b63\u65b0\u6b3e\u5973\u88c5\u6cd5\u5f0f\u6c14\u8d28\u6d0b\u6c14\u9ad8\u7ea7\u611f\u6e29\u67d4\u98ce\u540a\u5e26\u4ed9\u5973\u8fde\u8863\u88d9",
                "image": "catalog\/demo\/product\/11.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/11-100x100.jpg",
                "status": true
              }, {
                "id": 35,
                "name": "\u6c14\u8d28\u901a\u52e4\u9ad8\u8857\u86cb\u9752\u8272\u633a\u62ec\u70df\u7ba1\u88e49\u5206\u88e4\u5957\u88c5\u4e0b\u88c522\u79cb\u5973",
                "image": "catalog\/demo\/product\/18.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/18-100x100.jpg",
                "status": true
              }, {
                "id": 15,
                "name": "\u7537\u978b2022\u590f\u5b63\u900f\u6c14\u51b2\u5b54\u65f6\u5c1a\u4f11\u95f2\u677f\u978b\u538b\u82b1\u8010\u78e8\u5c0f\u767d\u978b\u7537",
                "image": "catalog\/demo\/product\/15.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/15-100x100.jpg",
                "status": true
              }, {
                "id": 14,
                "name": "\u590f\u5b63\u5957\u88c5\u77ed\u8896T\u6064\u7537\u88c5\u4e00\u5957\u642d\u914d\u5e05\u6c14\u6f6e\u60c5\u4fa3\u7537\u751f\u534a\u8896\u4e0a\u8863\u670d",
                "image": "catalog\/demo\/product\/6.jpg",
                "image_format": "https:\/\/beike.gdemo.top\/cache\/catalog\/demo\/product\/6-100x100.jpg",
                "status": true
              }],
              "title": {"zh_cn": "\u6700\u65b0\u5546\u54c1", "en": "New Products"}
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
        }
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

    // bk.tableResponsive()

  </script>
  <script>
    // 定义模块的配置项
    app.source.modules.push({
      title: '图片模块',
      code: 'image100',
      icon: '&#xe663;',
      content: {
        style: {
          background_color: ''
        },
        images: [
          {
            image: languagesFill('catalog/demo/banner/banner-2-en.png'),
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
      icon: '&#xe663;',
      content: {
        images: [
          {
            image: languagesFill('catalog/demo/banner/banner-4-en.jpg'),
            show: true,
            link: {
              type: 'product',
              value:''
            }
          },
          {
            image: languagesFill('catalog/demo/banner/banner-3-en.jpg'),
            show: false,
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
    app.source.modules.push({
      title: '服务图标模块',
      code: 'icons',
      icon: '&#xe60e;',
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
      icon: '&#xe607;',
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
      icon: '&#xe607;',
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
      icon: '&#xe607;',
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