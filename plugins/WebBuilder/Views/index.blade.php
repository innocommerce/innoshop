<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ panel_locale_direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="{{ panel_route('home.index') }}">
    <title>{{ __('WebBuilder::route.title') }} - InnoShop</title>
    <meta name="keywords" content="@yield('keywords', 'InnoShop, 创新, 开源, CMS, Laravel 11, 多语言, 多货币, Hook, 插件架构, 灵活, 强大')">
    <meta name="generator" content="InnoShop {{ innoshop_version() }}">
    <meta name="asset" content="{{ asset('/') }}">
    <meta name="description" content="@yield('description', 'InnoShop')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ session('api_token') }}">
    <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">
    <link rel="stylesheet" href="{{ asset('vendor/element-plus/index.css') }}">
    <link rel="stylesheet" href="{{ mix('build/panel/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ mix('build/panel/css/app.css') }}">
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/vue/3.5/vue.global.prod.js')}}"></script>
    <script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
    <script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
    <script src="{{ mix('build/panel/js/app.js') }}"></script>
    <script>
        let urls = {
            base_url: '{{ panel_route('home.index') }}',
            upload_images: '{{ panel_route('upload.images') }}',
            ai_generate: '{{ panel_route('content_ai.generate') }}',
        }

        const lang = {
            hint: '{{ __('panel/common.hint') }}',
            delete_confirm: '{{ __('panel/common.delete_confirm') }}',
            confirm: '{{ __('panel/common.confirm') }}',
            cancel: '{{ __('panel/common.cancel') }}',
        }
    </script>
    <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
    <script src="{{ asset('vendor/vuedraggable/sortable.min.js') }}"></script>
    <script src="{{ plugin_asset('mobile_builder', 'js/vuedraggable.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ plugin_asset('mobile_builder', 'css/design.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <script>
        const apiToken = "{{ session('panel_api_token') }}";
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + apiToken;
        console.log('apiToken:'+apiToken);
    </script>
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
            border: solid 2px #8446df !important;
            /*border: solid 2px #3089dc !important;*/
        }
    </style>
    <script>
        //获取语言信息
        const $languages = @json(locales());
        //获取当前语言
        const $locale = '{{ locale_code() }}';

        const asset = document.querySelector('meta[name="asset"]').content;
        if (typeof Vue != 'undefined') {
            //定义默认缩略图
            Vue.prototype.thumbnail = function thumbnail(image) {
                if (!image) {
                    return "{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}";
                }

                if (typeof image === 'string' && image.indexOf('http') === 0) {
                    return image;
                }
                if (typeof image === 'object') {
                    const locale = this.source.locale;
                    return image[locale] || image['zh_cn'] || Object.values(image)[0] || "{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}";
                }
                return asset + image;
            };

            //挂载stringLengthInte
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
</head>

<body class="@yield('body-class')">
<div id="app" class="bg-light">
    <div class="card-body d-flex">
        {{-- 左侧栏 --}}
        <div class="module-wrap" :class="{'collapsed': isCollapsed}">
            {{--返回和保存按钮--}}
            <div class="d-flex w-100 gap-2 p-2">
                <button type="button" class="btn btn-secondary flex-fill" onclick="history.back()">
                    <i class="bi bi-arrow-left"></i> {{ __('WebBuilder::common.back') }}
                </button>
                <button type="button" class="btn btn-primary flex-fill" id="saveBtn">
                    <i class="bi bi-save"></i> {{ __('WebBuilder::common.save') }}
                </button>
            </div>
            <!-- tab切换 -->
            <div class="module-tabs">
                <div class="tab-item"
                     :class="{'active': !showPropertyPanel}"
                     @click="showPropertyPanel = false">
                    <i class="bi bi-grid"></i> 模块列表
                </div>
                <div class="tab-item"
                     :class="{'active': showPropertyPanel}"
                     @click="showPropertyPanel = true"
                     v-if="form.modules.length">
                    <i class="bi bi-gear"></i> 属性设置
                </div>
            </div>

            <!-- 模块列表面板 -->
            <div class="panel-content" v-show="!showPropertyPanel">
                <draggable class="modules-list dragArea list-group"
                           :options="{
                                       group: {
                                           name: 'people',
                                           pull: 'clone',
                                           put: false
                                       },
                                       sort: false
                                   }"
                           :list="source.modules"
                           :clone="cloneDefaultField">
                    <div class="list-item" v-for="module in source.modules" :key="module.code">
                        <div class="icon">
                            <i class="ds-icon" v-html="module.icon"></i>
                        </div>
                        <div class="name">@{{ module.title }}</div>
                    </div>
                </draggable>
            </div>

            <!-- 属性设置面板 -->
            <div class="panel-content" v-show="showPropertyPanel">
                <div v-if="form.modules.length > 0 && design.editingModuleIndex >= 0" class="component-wrap">
                    <component
                        v-if="editingModuleComponent"
                        :is="editingModuleComponent"
                        :key="design.editingModuleIndex"
                        :module="form.modules[design.editingModuleIndex].content"
                        @on-changed="moduleUpdated">
                    </component>
                </div>
            </div>
        </div>

        {{-- 中间预览区 --}}
        <div class="preview-wrap flex-grow-1 mx-3">
            <div class="position-absolute top-50">
                <button class="collapse-btn" @click="toggleCollapse">
                    <i class="bi" :class="isCollapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
                </button>
            </div>

            <div class="preview-container">
                <img src="{{ plugin_asset('WebBuilder','images/header.png') }}" class="w-100" alt="">
                <div class="editable-area">
                    <!-- 预览区域的拖拽配置 -->
                    <draggable
                        class="view-modules-list dragArea list-group"
                        :list="form.modules"
                        :options="{
                                    group: {
                                      name: 'people',
                                      pull: false,
                                      put: true
                                    },
                                    animation: 300,
                                    sort: true
                                  }"
                        @change="handleDragChange"
                    >
                        <!-- 占位符节点：modules.length == 0显示 -->
                        <template v-if="form.modules.length === 0">
                            <div class="hint text-center">
                                <i class="bi bi-brightness-high fs-2"></i>
                                <div class="mt-2">请从左边模块列表拖动模块到这里</div>
                            </div>
                        </template>
                        <div
                            v-else
                            v-for="(module, index) in form.modules"
                            :key="index"
                            :class="['list-item', {'active': design.editingModuleIndex === index}]"
                            @click="handleModuleSelect(index)">
                            <!-- 操作按钮组 -->
                            <div class="module-actions" v-show="design.editingModuleIndex === index">
                                <button class="btn-action" @click.stop="moveModule(index, 'up')"
                                        :disabled="index === 0" title="上移">
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button class="btn-action" @click.stop="moveModule(index, 'down')"
                                        :disabled="index === form.modules.length - 1" title="下移">
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button class="btn-action" @click.stop="editModule(index)" title="编辑">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-action delete" @click.stop="deleteDodule(index)" title="删除">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            {{------预览组建-------}}
                            <!-- slideshow 轮播图 -->
                            <div v-if="module && module.code == 'slideshow' && module.content && module.content.images && module.content.images.length">
                                <img :src="module.content.images[0].image[source.locale]" class="img-fluid">
                            </div>

                            <!-- image100 图片模块 -->
                            <div v-if="module && module.code == 'image100' && module.content && module.content.images && module.content.images.length">
                                <img :src="module.content.images[0].image[source.locale]" class="img-fluid">
                            </div>

                            <!-- product 商品模块 -->
                            <div v-if="module && module.code == 'product' && module.content">
                                <div v-if="module.content.title && module.content.title[source.locale]"
                                     class="module-title-wrap">
                                    <div class="module-title">@{{ module.content.title[source.locale] }}</div>
                                    <div v-if="module.content.subtitle && module.content.subtitle[source.locale]"
                                         class="module-sub-title">@{{ module.content.subtitle[source.locale] }}</div>
                                </div>
                                <div v-if="!module.content.products || !module.content.products.length"
                                     class="hint-right-edit">
                                    请在左侧配置商品
                                </div>
                                <div class="row gx-3 gx-lg-4" v-else>
                                    <div class="col-6 col-md-4 col-lg-3" v-for="item in module.content.products" :key="item.id">
                                        <div class="product-grid-item">
                                            <div class="image">
                                                <img :src="item.image_big|| '{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}'" class="img-fluid">
                                            </div>
                                            <div class="product-item-info">
                                                <div class="product-name">
                                                    @{{ item.name || '' }}
                                                </div>
                                                <div class="product-bottom">
                                                    <div class="product-price">
                                                        <div class="price-old" v-if="item.origin_price">
                                                            @{{ item.origin_price_format }}
                                                        </div>
                                                        <div class="price-new">@{{ item.price_format }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- four_image 一行四图 -->
                            <div v-if="module && module.code == 'four_image' && module.content">
                                <!-- 添加标题和副标题 -->
                                <div v-if="module.content.title && module.content.title[source.locale]"
                                     class="module-title-wrap">
                                    <div class="module-title">@{{ module.content.title[source.locale] }}</div>
                                    <div v-if="module.content.subtitle && module.content.subtitle[source.locale]"
                                         class="module-sub-title">@{{ module.content.subtitle[source.locale] }}</div>
                                </div>

                                <!-- 图片内容 -->
                                <div v-if="module && module.content.images && module.content.images.length"
                                     class="four-image-grid">
                                    <div class="image-row">
                                        <div v-for="item in module.content.images" :key="item.id" class="image-item">
                                            <img :src="item.image && (item.image[source.locale] || item.image['zh_cn'] || Object.values(item.image)[0]) || '{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}'" class="img-fluid">
                                            <div v-if="item.description && item.description[source.locale]" class="image-description">
                                                @{{ item.description[source.locale] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="hint-right-edit">
                                    请在左侧添加图片
                                </div>
                            </div>

                            {{--four_image-plus--}}
                            <div v-if="module && module.code == 'four_image-plus' && module.content">
                                <!-- 添加标题和副标题 -->
                                <div v-if="module.content.title && module.content.title[source.locale]"
                                     class="module-title-wrap">
                                    <div class="module-title">@{{ module.content.title[source.locale] }}</div>
                                    <div v-if="module.content.subtitle && module.content.subtitle[source.locale]"
                                         class="module-sub-title">@{{ module.content.subtitle[source.locale] }}</div>
                                </div>

                                <!-- 图片内容 -->
                                <div v-if="module.content.images && module.content.images.length" class="container d-flex justify-content-center">
                                    <div class="row col-md-10">
                                        <!-- 第一列，大图片 -->
                                        <div class="col-lg-6 col-md-12 mb-0 d-flex align-items-center justify-content-center">
                                            <div class="image-item h-100">
                                                <a :href="module.content.images[0].link.value || 'javascript:;'">
                                                    <img :src="module.content.images[0].image[source.locale] || module.content.images[0].image['zh_cn'] || Object.values(module.content.images[0].image)[0] || '{{ plugin_asset('web_builder', 'images/demo/product/1-500x400.png') }}'" class="img-fluid rounded image-large h-100">
                                                </a>
                                            </div>
                                        </div>

                                        <!-- 第二列，小图片和宽图片 -->
                                        <div class="col-lg-6 col-md-12">
                                            <div class="row">
                                                <!-- 小图片 1 -->
                                                <div class="col-6 mb-3 d-flex align-items-center justify-content-center">
                                                    <div class="image-item w-100">
                                                        <a :href="module.content.images[1].link.value || 'javascript:;'">
                                                            <img :src="module.content.images[1].image[source.locale] || module.content.images[1].image['zh_cn'] || Object.values(module.content.images[1].image)[0] || '{{ plugin_asset('web_builder', 'images/demo/product/2-200x150.png') }}'" class="img-fluid rounded image-small w-100" style="object-fit: contain">
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- 小图片 2 -->
                                                <div class="col-6 mb-3 d-flex align-items-center justify-content-center">
                                                    <div class="image-item w-100">
                                                        <a :href="module.content.images[2].link.value || 'javascript:;'">
                                                            <img :src="module.content.images[2].image[source.locale] || module.content.images[2].image['zh_cn'] || Object.values(module.content.images[2].image)[0] || '{{ plugin_asset('web_builder', 'images/demo/product/3-200x150.png') }}'" class="img-fluid rounded image-small w-100" style="object-fit: contain">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 宽图片 -->
                                            <div class="row">
                                                <div class="col-12 d-flex align-items-center justify-content-center">
                                                    <div class="image-item">
                                                        <a :href="module.content.images[3].link.value || 'javascript:;'">
                                                            <img :src="module.content.images[3].image[source.locale] || module.content.images[3].image['zh_cn'] || Object.values(module.content.images[3].image)[0] || '{{ plugin_asset('web_builder', 'images/demo/product/4-500x200.png') }}'" class="img-fluid rounded image-wide">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="hint-right-edit">
                                    请在左侧添加图片
                                </div>
                            </div>
                            {{--一行四图plus样式--}}
                            <style scoped>
                                .image-large {
                                    width: 100%;
                                    max-width: 500px;
                                    height: 300px;
                                    object-fit: cover;
                                    border-radius: 20px;
                                }

                                .image-small {
                                    width: 100%;
                                    max-width: 200px;
                                    height: 150px;
                                    object-fit: cover;
                                    border-radius: 20px;
                                }

                                .image-wide {
                                    width: 100%;
                                    max-width: 500px;
                                    height: 200px;
                                    object-fit: cover;
                                    border-radius: 20px;
                                }

                                /* 居中对齐 */
                                .d-flex {
                                    display: flex !important;
                                }

                                .justify-content-center {
                                    justify-content: center !important;
                                }

                                .align-items-center {
                                    align-items: center !important;
                                }
                            </style>

                            <!-- article 文章模块 -->
                            <div v-if="module && module.code == 'article' && module.content">
                                <div v-if="module.content.title && module.content.title[source.locale]"
                                     class="module-title-wrap">
                                    <div class="module-title">@{{ module.content.title[source.locale] }}</div>
                                    <div class="module-sub-title">@{{ module.content.subtitle[source.locale] }}</div>
                                </div>
                                <div v-if="!module.content.articles || !module.content.articles.length"
                                     class="hint-right-edit">
                                    请在左侧配置文章
                                </div>
                                <div class="row gx-3 gx-lg-4" v-else>
                                    <div class="col-6 col-md-4 col-lg-3" v-for="item in module.content.articles.slice(0, 4)" :key="item.id">
                                        <div class="blog-item">
                                            <div class="image">
                                                <a :href="item.url">
                                                    @{{console.log(item)}}
                                                    <img :src="item.image" class="img-fluid">
                                                </a>
                                            </div>
                                            <div class="blog-item-info">
                                                <div class="blog-catalog" v-if="item.catalog">
                                                    <a :href="item.url">@{{ item.catalog }}</a>
                                                </div>
                                                <div class="blog-title">@{{ item.name }}</div>
                                                <div class="author-wrap">
                                                    <div class="blog-author" v-if="item.author">
                                                        <i class="bi bi-person"></i> @{{ item.author }}
                                                    </div>
                                                    <div class="blog-created">
                                                        <i class="bi bi-clock"></i> @{{ item.created_at }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- category 分类商品模块 -->
                            <div v-if="module && module.code == 'category' && module.content">
                                <div v-if="module.content.title && module.content.title[source.locale]"
                                     class="module-title-wrap">
                                    <div class="module-title">@{{ module.content.title[source.locale] }}</div>
                                    <div v-if="module.content.subtitle && module.content.subtitle[source.locale]"
                                         class="module-sub-title">@{{ module.content.subtitle[source.locale] }}</div>
                                </div>
                                <div v-if="!module.content.products || !module.content.products.length"
                                     class="hint-right-edit">
                                    请在左侧选择分类
                                </div>
                                <div class="row gx-3 gx-lg-4" v-else>
                                    <div class="col-6 col-md-4 col-lg-3" v-for="item in module.content.products" :key="item.id">
                                        <div class="product-grid-item">
                                            <div class="image">
                                                <img :src="item.image_big || '{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}'" class="img-fluid">
                                            </div>
                                            <div class="product-item-info">
                                                <div class="product-name">
                                                    @{{ item.name || '' }}
                                                </div>
                                                <div class="product-bottom">
                                                    <div class="product-price">
                                                        <div class="price-old" v-if="item.origin_price">
                                                            @{{ item.origin_price_format }}
                                                        </div>
                                                        <div class="price-new">@{{ item.price_format }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- latest 最新商品模块 -->
                            <div v-if="module && module.code == 'latest' && module.content">
                                <div v-if="module.content.title && module.content.title[source.locale]"
                                     class="module-title-wrap">
                                    <div class="module-title">@{{ module.content.title[source.locale] }}</div>
                                    <div v-if="module.content.subtitle && module.content.subtitle[source.locale]"
                                         class="module-sub-title">@{{ module.content.subtitle[source.locale] }}</div>
                                </div>
                                <div v-if="!module.content.products || !module.content.products.length"
                                     class="hint-right-edit">
                                    请在左侧设置数量
                                </div>
                                <div class="row gx-3 gx-lg-4" v-else>
                                    <div class="col-6 col-md-4 col-lg-3" v-for="item in module.content.products" :key="item.id">
                                        <div class="product-grid-item">
                                            <div class="image">
                                                <img :src="item.image_big || '{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}'" class="img-fluid">
                                            </div>
                                            <div class="product-item-info">
                                                <div class="product-name">
                                                    @{{ item.name || '' }}
                                                </div>
                                                <div class="product-bottom">
                                                    <div class="product-bottom-btns">
                                                        <div class="product-price">
                                                            <div class="price-old" v-if="item.origin_price">
                                                                @{{ item.origin_price_format }}
                                                            </div>
                                                            <div class="price-new">@{{ item.price_format }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </draggable>
                </div>
                {{--页脚--}}
                <img src="{{ plugin_asset('WebBuilder','images/footer.png') }}" class="w-100" alt="">
            </div>
        </div>
    </div>
</div>

{{----- 模块组建区域 -----}}
{{--幻灯片编辑模块--}}
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
                            <pb-image-selector v-model="item.image"
                                               :aspectRatio="2"
                                               :targetWidth="1000"
                                               :targetHeight="500"></pb-image-selector>
                            <div class="tag">建议尺寸(宽x高): 1000 x 500</div>
                        </div>
                        <link-selector :hide-types="['catalog', 'static']" v-model="item.link"></link-selector>
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
{{--幻灯片编辑模块脚本--}}
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

{{--图片编辑模块--}}
<template id="module-editor-image100-template">
    <div class="image-edit-wrapper">
        <div class="module-editor-row">内容</div>
        <div class="module-edit-group">
            <div class="module-edit-title">选择图片</div>
            <div class="">
                <div class="pb-images-top">
                    <pb-image-selector v-model="form.images[0].image"
                                       :aspectRatio="2.0833"
                                       :targetWidth="1000"
                                       :targetHeight="480"></pb-image-selector>
                    <div class="tag">建议尺寸: 1000 x 480</div>
                </div>
                <link-selector :hide-types="['catalog', 'static']" v-model="form.images[0].link"></link-selector>
            </div>
        </div>
    </div>
</template>
{{--图片编辑模块脚本--}}
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

{{--商品编辑模块--}}
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
                                            <span>@{{ item.name }}</span>
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
{{--商品编辑模块脚本--}}
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

                    axios.get('api/panel/products/names?product_ids=' + this.form.products.map(e => e.id).join(','), {
                        headers: {
                            'Authorization': 'Bearer ' + apiToken
                        },
                        hload: true
                    }).then((res) => {
                        this.loading = false;
                        that.productData = res.data;
                    })
                },

                querySearch(keyword, cb) {
                    axios.get('api/panel/products/autocomplete?keyword=' + encodeURIComponent(keyword), null, {
                        headers: {
                            'Authorization': 'Bearer ' + apiToken
                        },
                        hload: true
                    }).then((res) => {
                        console.log('获取商品：');
                        console.log(res)
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
                    console.log('itemChange:')
                    console.log(this.productData)
                    this.form.products = this.productData
                },

                addTabData(type) {
                    console.log(type);
                },

                removeProduct(index) {
                    this.productData.splice(index, 1)
                    this.form.products.splice(index, 1);
                },
            },
        }
    })
</script>

{{--分类商品编辑模块--}}
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
{{--分类商品编辑模块脚本--}}
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
            }
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

                axios.get('api/panel/products/names?product_ids=' + this.form.products.map(e => e.id).join(','), {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
                    this.loading = false;
                    console.log('选择弹窗品列表：');
                    console.log(res.data);
                    that.productData = res.data;
                });
            },

            querySearch(keyword, cb) {
                axios.get('api/panel/categories/autocomplete?keyword=' + encodeURIComponent(keyword), {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
                    cb(res.data);
                });
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
                axios.get(`api/panel/products?category=${this.form.category_id}&per_page=${this.form.limit ?? ''}&page=1`, {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
                    console.log(res.data);
                    this.form.products = res.data;
                });
            },

            itemChange(evt) {
                this.form.products = this.productData;
            },

            addTabData(type) {
                console.log(type);
            },

            removeProduct(index) {
                this.productData.splice(index, 1);
                this.form.products.splice(index, 1);
            }
        }
    });
</script>

{{--最新编辑模块--}}
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
{{--最新编辑模块脚本--}}
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
                axios.get('api/panel/products?per_page='+ this.form.limit, {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
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

{{--多语言图片选择器模板--}}
<template id="pb-image-selector">
    <div class="pb-image-selector">
        <el-tabs v-if="isLanguage" @tab-click="tabClick" value="language-zh_cn"
                 :stretch="languages.length > 5 ? true : false" type="card"
                 :class="languages.length <= 1 ? 'languages-a' : ''">
            {{--查询所有语言 languages--}}
            <el-tab-pane v-for="(item, index) in languages" :key="index" :label="item.name" :name="'language-' + item.code">
                <span slot="label" style="padding: 0 4px; font-size: 12px">@{{ item.name }}</span>
                <div class="i18n-inner">
                    <div class="img">
                        {{--缩略图(value[item.code])--}}
                        <el-image :src="type == 'image' ? thumbnail(src) : 'image/video.png'" :id="'thumb-' + id"
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
        {{--单语言--}}
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
{{--多语言选择器脚本--}}
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
            isLanguage: {
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
                    return this.isLanguage ? this.value[this.tabActiveId] : this.value;
                },
                set(newValue) {
                    if (this.isLanguage) {
                        this.$set(this.value, this.tabActiveId, newValue);
                        this.$emit('input', this.value);
                    } else {
                        this.$emit('input', newValue);
                    }
                }
            }
        },
        methods: {
            removeImage() {
                if (this.isLanguage) {
                    // this.src[this.tabActiveId] = '';
                    this.src = '';
                } else {
                    this.src = '';
                }
            },
            tabClick(e) {
                this.tabActiveId = this.languages[e.index * 1].code;
            },
            selectButtonClicked() {
                // 创建文件输入元素
                console.log(window.inno)
                window.inno.fileManagerIframe((file)=>{
                    console.log(file.url); //单个对象
                    this.src = file.url;
                    // this.$emit('input', this.src);
                });
            },
        }
    });
</script>
{{--多语言选择器样式--}}
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

{{--链接选择器--}}
<template id="link-selector">
    <div class="link-selector-wrap">
        <div class="title" v-if="isTitle"><i class="el-icon-link"></i>选择链接</div>
        <div class="selector-type" @blur="selectorContentShow = false" tabindex="1">
            <div class="title" v-if="link.type != 'custom' ? value.value == '' : ''"
                 @click="selectorContentShow = !selectorContentShow">选择链接
            </div>
            <div class="title" @click="selectorContentShow = !selectorContentShow" v-else :title="name"
                 v-loading="nameLoading">@{{ selectorTitle }}: @{{ name[0]?.name ?? '' }}
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
                        <el-input v-model="link.value" placeholder="请输入接地址"></el-input>
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
                                    @click="product.active ? link.value = product.id : false"
                                    :class="!product.active ? 'no-status' : ''">
                                    <div class="left">
                          <span
                              :class="'checkbox-plus ' + (link.value == product.id ? 'active':'') + (!product.active ? 'no-status':'')"></span>
                                        <img :src="product.image_small" v-if="product.image" class="img-responsive">
                                        <div>@{{ product.name }}</div>
                                    </div>
                                    <div :class="'right ' + (product.active ? 'ok' : 'no')">
                                        <template v-if="product.active">启用</template>
                                        <template v-else>禁用</template>
                                    </div>
                                </li>
                            </ul>
                        </template>
                        <div class="product-info-no" v-if="!linkDialog.data.length && loading === false">
                            <div class="icon"><svg t="1731182073387" class="icon" viewBox="0 0 1127 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1728" width="81" height="81"><path d="M917.28125 562.244375L802.593125 376.559375c-2.184375-4.36875-6.0075-7.100625-10.37625-7.100625h-474.590625c-4.36875 0-8.1909375 2.184375-10.37625 7.100625L192.5628125 562.244375c-1.093125 1.0921875-1.093125 2.184375-1.093125 4.36875v227.191875c0 7.0996875 4.36875 11.4684375 11.469375 11.4684375h704.5125c7.09875 0 11.4684375-4.36875 11.4684375-11.4684375V566.613125c-0.5465625-1.6378125-0.5465625-2.73-1.63875-4.36875zM324.726875 392.943125h460.9359375l103.21875 162.7471875H635.4771875c-7.0996875 0-11.4684375 4.36875-11.4684375 11.469375 0 37.6828125-31.1296875 68.8125-68.8125 68.8125-37.68375 0-68.8125-31.1296875-68.8125-68.8125 0-7.0996875-4.3696875-11.469375-11.469375-11.469375H220.9615625l103.7653125-162.7471875zM895.435625 782.88125h-681.028125V578.628125h250.1296875c6.0075 44.7825 44.7825 80.2809375 90.658125 80.2809375s85.19625-35.4984375 90.658125-80.281875H895.98125v204.253125zM138.4953125 674.748125v-12.5615625c0-3.2765625-2.7309375-6.0075-6.0075-6.0075-3.2765625 0-6.0075 2.7309375-6.0075 6.0075v12.5615625H113.91875c-3.2765625 0-6.0075 2.73-6.0075 6.0075 0 3.2765625 2.7309375 6.0075 6.0075 6.0075H126.48125v12.560625c0 3.2765625 2.7309375 6.0075 6.0075 6.0075 3.2765625 0 6.0075-2.7309375 6.0075-6.0075v-12.560625h12.560625c3.2775 0 6.0075-2.7309375 6.0075-6.0075 0-3.2775-2.73-6.0075-6.0075-6.0075H138.4953125zM962.0646875 426.25625h19.1146875c4.914375 0 9.2840625 4.36875 9.2840625 9.2840625 0 5.461875-3.823125 9.2840625-9.2840625 9.2840625h-19.115625v19.115625c0 4.914375-4.36875 9.2840625-9.2840625 9.2840625s-9.2840625-3.823125-9.2840625-9.285v-19.1146875h-19.1146875c-4.9153125 0-9.2840625-4.36875-9.2840625-9.2840625 0-5.4609375 3.823125-9.2840625 9.2840625-9.2840625h19.115625v-19.115625c0-4.914375 4.36875-9.283125 9.283125-9.283125 5.461875 0 9.285 3.8221875 9.285 9.2840625v19.1146875z m67.17375 81.3740625h12.015c3.2765625 0 6.0075 2.73 6.0075 6.0075 0 3.2765625-2.7309375 6.0075-6.0075 6.0075h-12.015v12.015c0 3.2765625-2.73 6.0065625-6.0075 6.0065625-3.2765625 0-6.0075-2.73-6.0075-5.4609375v-12.015H1005.209375c-3.2765625 0-6.0075-2.7309375-6.0075-6.0075 0-3.2765625 2.7309375-6.0075 6.0075-6.0075h12.015v-12.560625c0-3.2775 2.7309375-6.0075 6.0075-6.0075 3.2775 0 6.0075 2.73 6.0075 6.0075v12.015zM154.334375 410.965625v-19.115625c0-5.4609375-4.36875-9.2840625-9.285-9.2840625-5.4609375 0-9.2840625 4.36875-9.2840625 9.285v18.568125H117.19625c-5.461875 0-9.285 4.36875-9.285 9.2840625 0 5.461875 4.36875 9.285 9.285 9.285h18.568125v18.568125c0 5.4609375 4.36875 9.2840625 9.2840625 9.2840625 5.461875 0 9.285-4.36875 9.285-9.2840625v-18.568125h18.568125c5.4609375 0 9.2840625-4.36875 9.2840625-9.285 0-5.4609375-4.36875-9.2840625-9.2840625-9.2840625 0 0.5465625-18.568125 0.5465625-18.568125 0.5465625z m-84.650625 186.2315625c-20.7534375 0-37.68375-16.93125-37.68375-37.68375s16.9303125-37.6828125 37.68375-37.6828125c20.7525 0 37.6828125 16.9303125 37.6828125 37.6828125 0 21.3-16.9303125 37.68375-37.6828125 37.68375z m0-18.5690625c10.37625 0 18.568125-8.191875 18.568125-18.568125s-8.191875-18.5690625-18.568125-18.5690625-18.5690625 8.191875-18.5690625 18.5690625 8.191875 18.568125 18.5690625 18.568125zM1071.8375 474.3171875c-9.285 0-17.476875-7.64625-17.476875-17.476875s7.64625-17.4759375 17.476875-17.4759375c9.2840625 0 17.475 7.6453125 17.475 17.475 17.4759375 0 9.830625-7.6453125 17.476875-17.475 17.476875z m0-8.1928125c4.914375 0 8.7375-3.8221875 8.7375-8.7375s-3.823125-8.7375-8.7375-8.7375c-4.9153125 0-8.7384375 3.8221875-8.7384375 8.7375s4.36875 8.7375 8.7375 8.7375zM312.1653125 191.42c7.64625-7.64625 20.206875-7.64625 27.853125 0l69.9046875 69.3590625c7.64625 7.6453125 7.64625 20.206875 0 27.853125-7.6453125 7.6453125-7.6453125-7.6453125-7.6453125-20.206875-7.6453125-27.853125 0l-69.9046875-69.36c-7.6453125-7.6453125-7.6453125-20.206875 0-27.853125z m243.03-34.9528125c10.921875 0 19.6603125 8.7375 19.6603125 19.66125v98.3034375c0 10.9228125-8.7375 19.66125-19.6603125 19.66125s-19.66125-8.7384375-19.66125-19.66125V176.1284375c-0.545625-10.3771875 8.7375-19.66125 19.66125-19.66125-0.5465625 0 0 0 0 0z m243.5746875 30.0375c7.64625 7.6453125 7.64625 20.206875 0 27.853125l-69.358125 69.358125c-7.64625 7.64625-20.2078125 7.64625-27.853125 0-7.64625-7.6453125-7.64625-20.206875 0-27.853125l69.3590625-69.358125c7.6453125-7.64625 20.206875-7.64625 27.853125 0z" fill="#bbbbbb" p-id="1729"></path></svg></div>
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
                    {type: 'catalog', label: '文章分类'},
                    {type: 'brand', label: '商品品牌'},
                    {type: 'static', label: '固定连接'},
                    {type: 'custom', label: '自定义'}
                ],
                static: [
                    {name: '个人中心', value: 'account.index'},
                    {name: '我的收藏', value: 'account.wishlist.index'},
                    {name: '我的订单', value: 'account.order.index'},
                    {name: '最新商品', value: 'account.index'},
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

            // 模块数据管理地址
            linkTypeAdmin: function () {
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
                    console.log(data);
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

            // 搜索自动补
            querySearch(keyword, all, cb) {
                const self = this;
                let url = '';

                switch (this.link.type) {
                    case 'product':
                        url = all ? 'api/panel/products' : 'api/panel/products/autocomplete?keyword=';
                        break;
                    case 'category':
                        url = all ? 'api/panel/categories' : 'api/panel/categories/autocomplete?keyword=';
                        break;
                    case 'brand':
                        url = all ? 'api/panel/brands' : 'api/panel/brands/autocomplete?keyword=';
                        break;
                    case 'page':
                        url = all ? 'api/panel/pages' : 'api/panel/pages/autocomplete?keyword=';
                        break;
                    case 'catalog':
                        url = all ? 'api/panel/catalogs' : 'api/panel/catalogs/autocomplete?keyword=';
                        break;
                    default:
                        null;
                }

                this.loading = true;

                // 如果是获取所有数,不需要拼接关键字
                const apiUrl = all ? url : url + encodeURIComponent(keyword);
                axios.get(apiUrl, null, {hload: true}).then((res) => {
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
                        url = `api/panel/products/names?product_ids=${this.link.value}`;
                        break;
                    case 'category':
                        url = `api/panel/categories/names?category_ids=${this.link.value}`;
                        break;
                    case 'brand':
                        url = `api/panel/brands/names?brand_ids=${this.link.value}`;
                        break;
                    case 'page':
                        url = `api/panel/pages/names?page_ids=${this.link.value}`;
                        break;
                    case 'catalog':
                        url = `api/panel/catalogs/name?catalog_ids=${this.link.value}`;
                        break;
                    default:
                        null;
                }

                axios.get(url, null, {hload: true, hmsg: true}).then((res) => {
                    if (res.data) {
                        console.log(res.data)
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

            moveModule(index, direction) {
                if (direction === 'up' && index > 0) {
                    const temp = this.form.modules[index];
                    this.form.modules.splice(index, 1);
                    this.form.modules.splice(index - 1, 0, temp);
                    this.design.editingModuleIndex = index - 1;
                } else if (direction === 'down' && index < this.form.modules.length - 1) {
                    const temp = this.form.modules[index];
                    this.form.modules.splice(index, 1);
                    this.form.modules.splice(index + 1, 0, temp);
                    this.design.editingModuleIndex = index + 1;
                }
            },

            editModule(index) {
                this.design.editingModuleIndex = index;
                this.showPropertyPanel = true;
            }
        }
    });
</script>
<style lang="scss">
    .link-dialog-box {
        .link-dialog-header{
            background-color: #8446df !important;
        }
        .link-dialog-content{
            .product-info-no a{
                color: #8446df !important;
            }
        }
    }
    .el-button--primary {
        color: #FFF;
        background-color: #8446df;
        border-color: #8446df;
    }

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
{{--链接选择器结束--}}

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

    .design-app-home .main-content>#content{
        overflow:hidden
    }
    .design-app-home .tag{
        color:#777;
        font-size:12px;
        margin:8px 0
    }
    .design-app-home .hint-right-edit{
        color:#777;
        padding:10px;
        text-align:center;
        width:100%
    }
    .design-app-home .module-title{
        font-size:16px;
        font-weight:700;
        padding:10px 0;
        text-align:center
    }
    .design-app-home #app .card-body{
        display:flex;
        justify-content:center;
        padding:0
    }
    .design-app-home #app .card-body>div{
        flex:1
    }
    .design-app-home #app .card-body .component-wrap{
        height:calc(100% - 60px);
        overflow-y:auto;padding:0 14px
    }
    .design-app-home #app .card-body .c-title{
        font-size:16px;
        font-weight:700;
        padding:14px;
        text-align:center
    }
    .design-app-home #app .card-body .module-wrap{
        max-width:360px
    }
    .design-app-home #app .card-body .module-wrap .modules-list{
        overflow-y:auto;
        padding:0 14px
    }
    .design-app-home #app .card-body .module-wrap .modules-list .list-item{
        align-items:center;
        border:1px solid #eee;
        border-radius:2px;
        cursor:move;
        display:flex;
        margin-bottom:10px;
        padding:10px 24px 10px 16px;
        position:relative
    }
    .design-app-home #app .card-body .module-wrap .modules-list .list-item:after{
        color:#999;
        content:"\f3fe";
        font-family:bootstrap-icons;
        font-size:16px;
        position:absolute;
        right:8px
    }
    .design-app-home #app .card-body .module-wrap .modules-list .list-item:hover{
        border-color:#8446df
    }
    .design-app-home #app .card-body .module-wrap .modules-list .list-item .icon{
        width:35px
    }
    .design-app-home #app .card-body .module-wrap .modules-list .list-item .icon i{
        color:#666;
        font-size:26px;
        line-height:1
    }
    .design-app-home #app .card-body .module-wrap .modules-list .list-item .name{
        font-size:12px;
        font-weight:700;
        overflow:hidden
    }
    .design-app-home #app .card-body .perview-wrap{
        align-items:center;
        border-left:1px solid #eee;
        border-right:1px solid #eee;
        display:flex;
        flex:0 0 40%;
        flex-direction:column;
        justify-content:flex-start;
        padding-bottom:20px
    }
    .design-app-home #app .card-body .perview-wrap .perview-content{
        background-color:#f6f6f6;
        border:2px solid #eee;
        border-radius:20px;
        box-shadow:0 13px 21px rgba(0,0,0,.07);
        height:100%;
        max-width:380px;
        overflow:hidden;
        position:relative;
        width:70%
    }
    .design-app-home #app .card-body .perview-wrap .perview-content .head{
        border-bottom:1px solid #eee;
        border-radius:20px 20px 0 0;
        overflow:hidden
    }
    .design-app-home #app .card-body .perview-wrap .hint{
        color:#888;
        font-size:15px;
        position:absolute;
        text-align:center;
        top:30%;width:100%
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list{
        height:100%;
        overflow-y:auto
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item{
        border:1px solid transparent;
        margin:7px 0;
        position:relative;
        width:100%
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:hover{
        border-color:#8446df
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:hover .module-tool{
        display:flex
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item:first-of-type{
        margin-top:0
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool{
        background-color:rgba(0,0,0,.5);
        display:none;
        height:26px;
        left:0;
        position:absolute;
        top:0;
        width:100%
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool>div{
        align-items:center;
        color:#fff;
        cursor:pointer;
        display:flex;
        height:100%;
        justify-content:center;
        width:36px
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item .module-tool>div:hover{
        background-color:#333
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.sortable-ghost{
        align-items:center;
        border:1px dashed #aaa;
        display:flex;
        justify-content:center;
        padding:6px 10px;
        text-align:center
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.sortable-ghost .icon{
        margin-right:6px
    }
    .design-app-home #app .card-body .perview-wrap .view-modules-list .list-item.active{
        border:2px solid #8446df;
        box-shadow:0 0 10px 2px rgba(132, 70, 223, 0.1)
    }
    .design-app-home #app .card-body .module-edit{
        overflow:hidden;padding:0
    }
    .design-app-home .quick-icon-wrapper{
        background:#fff;
        display:flex;
        flex-flow:wrap;
        margin-bottom:20rpx;
        padding:30rpx 20rpx 0rpx
    }
    .design-app-home .quick-icon-wrapper .link-item{
        align-content:flex-start;
        align-items:center;
        display:flex;
        flex-direction:column;
        font-size:12px;
        justify-content:center;
        margin-bottom:10px;
        padding:5px;
        text-align:center;
        width:20%
    }
    .design-app-home .quick-icon-wrapper .link-item .img{
        max-height:120rpx
    }
    .design-app-home .quick-icon-wrapper .link-item span{
        display:block;
        font-size:12px;
        line-height:1.3;
        margin-top:7px
    }
    .design-app-home .quick-icon-wrapper.quick-icon-4 .link-item,.design-app-home .quick-icon-wrapper.quick-icon-8 .link-item{
        width:25%
    }
    .design-app-home .quick-icon-wrapper.quick-icon-3 .link-item{
        width:33.33%
    }
    .design-app-home .quick-icon-wrapper image{
        width:94rpx
    }
    .design-app-home .product-grid{
        display:flex;
        flex-wrap:wrap;
        justify-content:space-between
    }
    .design-app-home .product-grid .product-item{
        margin-bottom:10px;
        position:relative;
        width:calc(50% - 5px)
    }
    .design-app-home .product-grid .product-item:not(.video){
        background:#fff;
        border-radius:4px
    }
    .design-app-home .product-grid .product-item:before{
        border:1px solid rgba(0,0,0,.6);
        border-radius:4px;
        content:"";
        display:none;
        height:calc(100% + 2px);
        left:-1px;
        position:absolute;
        top:-1px;
        width:calc(100% + 2px)
    }
    .design-app-home .product-grid .product-item .name{
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        display:-webkit-box;
        font-weight:700;
        height:36px;
        margin-top:8px;
        overflow:hidden;
        padding:0 10px;
        text-overflow:ellipsis
    }
    .design-app-home .product-grid .product-item .tool-item>div{
        flex:1;
        padding-left:0;
        padding-right:0;
        text-align:center
    }
    .design-app-home .product-grid .product-item .product-price{
        margin:6px 0;
        padding:0 10px
    }
</style>

{{--全局总控脚本--}}
<script>
    $(document).ready(function ($) {
        const wh = window.innerHeight - 140;
        const perviewHead = $('.perview-content .head').height();
        $('#app').height(wh);
        $('.perview-content').height(wh - 70);
        $('.view-modules-list').height(wh - 74 - perviewHead);
        $('.modules-list, .component-wrap').height(wh - 70);
    })

    // 定义图片组件
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

    // 定义幻灯片组件
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

    // 定义商品组件
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

                axios.get('api/panel/products/names?product_ids=' + this.form.products.map(e => e.id).join(','), {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
                    this.loading = false;
                    that.productData = res.data;
                })
            },

            querySearch(keyword, cb) {
                axios.get('api/panel/products/autocomplete?keyword=' + encodeURIComponent(keyword), null, {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
                    console.log('获取商品：');
                    console.log(res)
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
                console.log('itemChange:')
                console.log(this.productData)
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

    // 定义分类组件
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

                axios.get('api/panel/products/names?product_ids=' + this.form.products.map(e => e.id).join(','), {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true}).then((res) => {
                    this.loading = false;
                    console.log('选择弹窗品列表：');
                    console.log(res.data);
                    that.productData = res.data;
                })
            },

            querySearch(keyword, cb) {
                axios.get('api/panel/categories/autocomplete?keyword=' + encodeURIComponent(keyword), null, {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
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
                axios.get(`api/panel/products?category=${this.form.category_id}&per_page=${this.form.limit ?? ''}&page=1`, {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
                    console.log(res.data);
                    this.form.products = res.data;
                });
            },

            itemChange(evt) {
                this.form.products = this.productData;
            },

            addTabData(type) {
                console.log(type);
            },

            removeProduct(index) {
                this.productData.splice(index, 1);
                this.form.products.splice(index, 1);
            }
        }
    });

    // 定义最新组件
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
                axios.get('api/panel/products?per_page='+ this.form.limit, {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    },
                    hload: true
                }).then((res) => {
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

    // 定义图片选择器组件
    Vue.component('pb-image-selector', {
        template: '#pb-image-selector',
        props: {
            value: {
                default: null
            },
            type: {
                default: 'image'
            },
            isLanguage: {
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
                    return this.isLanguage ? this.value[this.tabActiveId] : this.value;
                },
                set(newValue) {
                    if (this.isLanguage) {
                        // 使用 Vue.$set 确保响应式更新
                        this.$set(this.value, this.tabActiveId, newValue);
                        this.$emit('input', this.value);
                    } else {
                        this.$emit('input', newValue);
                    }
                }
            }
        },
        methods: {
            removeImage() {
                if (this.isLanguage) {
                    // this.src[this.tabActiveId] = '';
                    this.src = '';
                } else {
                    this.src = '';
                }
            },
            tabClick(e) {
                this.tabActiveId = this.languages[e.index * 1].code;
            },
            selectButtonClicked() {
                // 创建文件输入元素
                console.log(window.inno)
                window.inno.fileManagerIframe((file)=>{
                    console.log(file.url); //单个对象
                    this.src = file.url;
                    // this.$emit('input', this.src);
                });
            },
        }
    });

    // 定义链接选择器组件
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
                    {type: 'catalog', label: '文章分类'},
                    {type: 'brand', label: '商品品牌'},
                    {type: 'static', label: '固定连接'},
                    {type: 'custom', label: '自定义'}
                ],
                static: [
                    {name: '个人中心', value: 'account.index'},
                    {name: '我的收藏', value: 'account.wishlist.index'},
                    {name: '我的订单', value: 'account.order.index'},
                    {name: '最新商品', value: 'account.index'},
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

            // 模块数据管理地址
            linkTypeAdmin: function () {
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
                    console.log(data);
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

            // 搜索自动补
            querySearch(keyword, all, cb) {
                const self = this;
                let url = '';

                switch (this.link.type) {
                    case 'product':
                        url = all ? 'api/panel/products' : 'api/panel/products/autocomplete?keyword=';
                        break;
                    case 'category':
                        url = all ? 'api/panel/categories' : 'api/panel/categories/autocomplete?keyword=';
                        break;
                    case 'brand':
                        url = all ? 'api/panel/brands' : 'api/panel/brands/autocomplete?keyword=';
                        break;
                    case 'page':
                        url = all ? 'api/panel/pages' : 'api/panel/pages/autocomplete?keyword=';
                        break;
                    case 'catalog':
                        url = all ? 'api/panel/catalogs' : 'api/panel/catalogs/autocomplete?keyword=';
                        break;
                    default:
                        null;
                }

                this.loading = true;

                // 如果是获取所有数据,不需要拼接关键字
                const apiUrl = all ? url : url + encodeURIComponent(keyword);
                axios.get(apiUrl, null, {hload: true}).then((res) => {
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
                        url = `api/panel/products/names?product_ids=${this.link.value}`;
                        break;
                    case 'category':
                        url = `api/panel/categories/names?category_ids=${this.link.value}`;
                        break;
                    case 'brand':
                        url = `api/panel/brands/names?brand_ids=${this.link.value}`;
                        break;
                    case 'page':
                        url = `api/panel/pages/names?page_ids=${this.link.value}`;
                        break;
                    case 'catalog':
                        url = `api/panel/catalogs/name?catalog_ids=${this.link.value}`;
                        break;
                    default:
                        null;
                }

                axios.get(url, null, {hload: true, hmsg: true}).then((res) => {
                    if (res.data) {
                        console.log(res.data)
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

            moveModule(index, direction) {
                if (direction === 'up' && index > 0) {
                    const temp = this.form.modules[index];
                    this.form.modules.splice(index, 1);
                    this.form.modules.splice(index - 1, 0, temp);
                    this.design.editingModuleIndex = index - 1;
                } else if (direction === 'down' && index < this.form.modules.length - 1) {
                    const temp = this.form.modules[index];
                    this.form.modules.splice(index, 1);
                    this.form.modules.splice(index + 1, 0, temp);
                    this.design.editingModuleIndex = index + 1;
                }
            },

            editModule(index) {
                this.design.editingModuleIndex = index;
                this.showPropertyPanel = true;
            }
        }
    });

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

    // 创建 Vue 实例
    const app = new Vue({
        el: '#app',
        data: {
            isCollapsed: false,
            form: {
                modules: []
            },
            source: {
                locale: $locale || 'zh_cn',
                modules: []
            },
            design: {
                editingModuleIndex: -1,
            },
            showPropertyPanel: false,
            languages: $languages || [], // 添加 languages 到 data
        },

        computed: {
            editingModuleComponent() {
                if (!this.form.modules ||
                    !this.form.modules.length ||
                    this.design.editingModuleIndex < 0 ||
                    !this.form.modules[this.design.editingModuleIndex] ||
                    !this.form.modules[this.design.editingModuleIndex].code) {
                    return null;
                }

                const module = this.form.modules[this.design.editingModuleIndex];
                return 'module-editor-' + module.code;
            }
        },

        watch: {
            'design.editingModuleIndex': function(newVal) {
                if(newVal >= 0) {
                    this.showPropertyPanel = true;
                }
            },

            'form.modules': function(newVal) {
                if(newVal.length === 0) {
                    this.showPropertyPanel = false;
                    this.design.editingModuleIndex = -1;
                }
            }
        },

        methods: {
            handleModuleSelect(index) {
                this.design.editingModuleIndex = index;
                this.showPropertyPanel = true;
            },

            handleDragChange(evt) {
                console.log('drag change:', evt);

                // 如果是跨列表新增
                if (evt.added) {
                    const newIndex = evt.added.newIndex;
                    const newModule = evt.added.element;
                    console.log('==> 新增模块', newModule, '位置=', newIndex);

                    // 设置当前编辑下标，显示右侧属性面板
                    this.design.editingModuleIndex = newIndex;
                    this.showPropertyPanel = true;
                }
                // 如果是同列表内部的移动
                else if (evt.moved) {
                    console.log(
                        '==> 同列表移动 from',
                        evt.moved.oldIndex,
                        'to',
                        evt.moved.newIndex
                    );
                    // 更新当前编辑 index
                    this.design.editingModuleIndex = evt.moved.newIndex;
                }
                else if (evt.removed) {
                    console.log('==> 被移除 oldIndex=', evt.removed.oldIndex);
                }
            },

            toggleCollapse() {
                this.isCollapsed = !this.isCollapsed;
            },

            cloneDefaultField(e) {
                console.log('clone...')
                const clone = JSON.parse(JSON.stringify(e));

                if (!clone.content) {
                    clone.content = {};
                }

                // 根据不同模块类型初始化默认内容
                switch (clone.code) {
                    case 'slideshow':
                        if (!clone.content.images) {
                            clone.content.images = [{
                                image: this.languagesFill(''),
                                show: true,
                                link: {type: 'product', value: ''}
                            }];
                        }
                        break;
                    case 'image100':
                        if (!clone.content.images) {
                            clone.content.images = [{
                                image: this.languagesFill(''),
                                show: true,
                                link: {type: 'product', value: ''}
                            }];
                        }
                        break;
                    case 'product':
                        break;
                    case 'category':
                        break;
                    case 'latest':
                        if (!clone.content.products) {
                            clone.content.products = [];
                        }
                        if (!clone.content.style) {
                            clone.content.style = {background_color: ''};
                        }
                        if (!clone.content.floor) {
                            clone.content.floor = this.languagesFill('');
                        }
                        if (!clone.content.title) {
                            clone.content.title = this.languagesFill('模块标题');
                        }
                        break;
                    case 'four_image':
                    case 'four_image-plus':
                        if (!clone.content.images) {
                            clone.content.images = [];
                        }
                        if (!clone.content.style) {
                            clone.content.style = {background_color: ''};
                        }
                        break;
                    case 'article':
                        break;
                    default:
                        break;
                }

                return clone;
            },

            moveModule(index, direction) {
                if (direction === 'up' && index > 0) {
                    const temp = this.form.modules[index];
                    this.form.modules.splice(index, 1);
                    this.form.modules.splice(index - 1, 0, temp);
                    this.design.editingModuleIndex = index - 1;
                } else if (direction === 'down' && index < this.form.modules.length - 1) {
                    const temp = this.form.modules[index];
                    this.form.modules.splice(index, 1);
                    this.form.modules.splice(index + 1, 0, temp);
                    this.design.editingModuleIndex = index + 1;
                }
            },

            editModule(index) {
                this.design.editingModuleIndex = index;
                this.showPropertyPanel = true;
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

            getDesignData() {
                axios.get('{{ panel_route('web_builder.design') }}').then(res => {
                    if (res.success) {
                        this.form.modules = res.data.modules ? res.data.modules.filter(module => module != null) : [];
                        layer.msg(res.message);
                        if (this.form.modules.length > 0) {
                            this.design.editingModuleIndex = 0;
                        } else {
                            this.design.editingModuleIndex = -1;
                        }
                    } else {
                        this.$message.error(res.message || '获取数据失败');
                    }
                }).catch(err => {
                    this.$message.error('获取数据失败：' + err.message);
                });
            },

            saveButtonClicked() {
                axios.put('{{ panel_route('web_builder.design.update') }}', this.form).then((res) => {
                    if (res.success) {
                        this.$message.success('保存成功');
                    } else {
                        this.$message.error(res.message || '保存失败');
                    }
                }).catch(err => {
                    this.$message.error('保存失败：' + err.message);
                });
            },

            perviewEnd(e) {
                this.design.editingModuleIndex = e.newIndex;
            },

            importDemoData() {
                this.$confirm('导入演示数据将覆盖当前已有数据，是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    // 演示数据
                    const demoData = [
                        {
                            "title": "幻灯片模块",
                            "code": "slideshow",
                            "icon": '<i class="bi bi-images"></i>',
                            "content": {
                                "images": [{
                                    "image": {
                                        "zh_cn": "{{ plugin_asset('MobileBuilder','images/demo/banner/banner-1-cn.jpg') }}",
                                        "en": "{{ plugin_asset('MobileBuilder','images/demo/banner/banner-1-en.jpg') }}"
                                    },
                                    "show": false,
                                    "link": {"type": "product", "value": 1, "link": ""}
                                }, {
                                    "image": {
                                        "zh_cn": "{{ plugin_asset('MobileBuilder','images/demo/banner/banner-2-cn.jpg') }}",
                                        "en": "{{ plugin_asset('MobileBuilder','images/demo/banner/banner-2-en.jpg') }}"
                                    },
                                    "show": true,
                                    "link": {"type": "category", "value": 1, "link": ""}
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
                                    }, "show": true, "link": {"type": "category", "value": 5, "link": ""}
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
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/1-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 2,
                                    "name": "银河流光璀璨晚礼服闪耀全场",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/2-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 3,
                                    "name": "晨曦漫步轻盈薄款风衣春意盎然",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/3-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 4,
                                    "name": "极简风格主义经典衬衫简约不简单",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/4-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
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
                                "category_id": 1,
                                "category_name": "时尚潮流",
                                "sort": "sales",
                                "floor": {"zh_cn": "", "en": ""},
                                "products": [{
                                    "id": 1,
                                    "name": "摩登复风高腰牛仔裤经典再现",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/5-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 2,
                                    "name": "幻彩流苏时尚个性围巾绚丽多彩",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/6-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 3,
                                    "name": "男士白色卫衣套装",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/7-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 4,
                                    "name": "优雅蕾丝边透视性感上衣女性魅力",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/8-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
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
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/1-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 2,
                                    "name": "银河流光璀璨晚礼服闪耀全场",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/2-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 3,
                                    "name": "晨曦漫步轻盈薄款风衣春意盎然",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/3-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }, {
                                    "id": 4,
                                    "name": "极简风格主义经典衬衫简约不简单",
                                    "image_big": "{{ plugin_asset('MobileBuilder','images/demo/product/4-600x600.png') }}",
                                    "image_format": "",
                                    "price_format": "$123.50",
                                    "active": true
                                }],
                                "title": {"zh_cn": "最新商品", "en": "New Products"}
                            }
                        }
                    ];

                    // 更新数据
                    this.form.modules = demoData;

                    // 重置编辑索引
                    this.design.editingModuleIndex = 0;

                    this.$message({
                        type: 'success',
                        message: '演示数据导入成功'
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消导入'
                    });
                });
            },

            // 辅助函数：为多语言字段填充默认值
            languagesFill(defaultValue) {
                const result = {};
                this.languages.forEach(lang => {
                    result[lang.code] = defaultValue;
                });
                return result;
            },

            handleDragAdd(evt) {
                if (!evt.added) {
                    console.warn('handleDragAdd - evt.added is undefined, skip')
                    return
                }
                const newIndex = evt.newIndex
                const newModule = evt.added.element
                console.log('handleDragAdd, newModule =', newModule)
                if (!newModule) return
                this.design.editingModuleIndex = evt.added.newIndex
                this.showPropertyPanel = true
            },

            handleDragUpdate(evt) {
                // 在预览区内部拖拽排序时调用
                if (evt.moved) {
                    const oldIndex = evt.moved.oldIndex
                    const newIndex = evt.moved.newIndex
                    console.log('update: move from ' + oldIndex + ' to ' + newIndex)
                    // 设置编辑中的下标
                    this.design.editingModuleIndex = newIndex
                }
            }
        },

        created() {
            this.getDesignData();
        },

        mounted() {
            // 绑定保存按钮事件
            const saveBtn = document.querySelector('#saveBtn');
            if (saveBtn) {
                saveBtn.addEventListener('click', this.saveButtonClicked);
            }
        },

        beforeDestroy() {
            // 移除保存按钮事件
            const saveBtn = document.querySelector('#saveBtn');
            if (saveBtn) {
                saveBtn.removeEventListener('click', this.saveButtonClicked);
            }
        }
    });
</script>

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
    // 配置模块列表
    app.source.modules.push({
        title: '幻灯片模块',
        code: 'slideshow',
        icon: '<i class="bi bi-images"></i>',
        content: {
            images: [
                {
                    image: languagesFill('images/demo/banner/banner-1-en.jpg'),
                    show: true,
                    link: {
                        type: 'product',
                        value: ''
                    }
                },
                {
                    image: {
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

    app.source.modules.push({
        title: '图片模块',
        code: 'image100',
        icon: '<i class="bi bi-image"></i>',
        content: {
            style: {
                background_color: ''
            },
            images: [
                {
                    image: languagesFill("{{ plugin_asset('MobileBuilder','images/demo/banner/banner-2-en.jpg') }}"),
                    show: true,
                    link: {
                        type: 'product',
                        value:''
                    }
                }
            ]
        }
    })

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
            subtitle: languagesFill('') // 添加默认的subtitle
        }
    });

    app.source.modules.push({
        title: '分类商品模块',
        code: 'category',
        icon: '<i class="bi bi-collection"></i>',
        content: {
            style: {
                background_color: ''
            },
            limit: 8,
            order: 'asc',
            category_id: '',
            category_name: '',
            sort: 'sales',
            floor: languagesFill(''),
            products: [],
            title: languagesFill('模块标题'),
        }
    });

    app.source.modules.push({
        title: '最新商品模块',
        code: 'latest',
        icon: '<i class="bi bi-star"></i>',
        content: {
            style: {
                background_color: ''
            },
            limit: 8,
            floor: languagesFill(''),
            products: [],
            title: languagesFill('模块标题'),
        }
    });

    // 一行四图
    app.source.modules.push({
        title: '一行四图',
        code: 'four_image',
        icon: '<i class="bi bi-grid-3x3"></i>',
        content: {
            style: {
                background_color: ''
            },
            title: languagesFill('一行四图'),
            subtitle: languagesFill('您可以设置副标题'),
            images: [
                {
                    image: languagesFill(''),
                    description: languagesFill('此处为文字说明'),
                    show: true,
                    link: {type: 'product', value: ''}
                },
                {
                    image: languagesFill(''),
                    description: languagesFill('此处为文字说明'),
                    show: false,
                    link: {type: 'product', value: ''}
                },
                {
                    image: languagesFill(''),
                    description: languagesFill('此处为文字说明'),
                    show: false,
                    link: {type: 'product', value: ''}
                },
                {
                    image: languagesFill(''),
                    description: languagesFill('此处为文字说明'),
                    show: false,
                    link: {type: 'product', value: ''}
                }
            ]
        }
    });

    // 一行四图Plus
    app.source.modules.push({
        title: '一行四图Plus',
        code: 'four_image-plus',
        icon: '<i class="bi bi-grid-3x3"></i>',
        content: {
            style: {
                background_color: ''
            },
            title: languagesFill('一行四图Plus'),
            subtitle: languagesFill('您可以设置副标题'),
            images: [
                {
                    image: languagesFill(''),
                    description: languagesFill('此处为文字说明'),
                    show: true,
                    link: {type: 'product', value: ''}
                },
                {
                    image: languagesFill(''),
                    description: languagesFill('此处为文字说明'),
                    show: false,
                    link: {type: 'product', value: ''}
                },
                {
                    image: languagesFill(''),
                    description: languagesFill('此处为文字说明'),
                    show: false,
                    link: {type: 'product', value: ''}
                },
                {
                    image: languagesFill(''),
                    description: languagesFill('此处为文字说明'),
                    show: false,
                    link: {type: 'product', value: ''}
                }
            ]
        }
    });

    // 文章模块
    app.source.modules.push({
        title: '文章模块',
        code: 'article',
        icon: '<i class="bi bi-file-text"></i>',
        content: {
            style: {
                background_color: ''
            },
            title: languagesFill('模块标题'),
            subtitle: languagesFill('探索未来，引领创新，加入我们，一起见证最新科技的诞生。'),
            articles: []
        }
    });
</script>
<style>
    .design-web-home .card-body {
        padding: 15px;
        gap: 15px;
        height: calc(100vh - 60px);
    }

    /* 标题样式统一 */
    .c-title {
        padding: 15px;
        border-bottom: 1px solid #eee;
        font-weight: bold;
        background: #fafafa;
        border-radius: 4px 4px 0 0;
        flex-shrink: 0; /* 防止被压缩 */
    }

    /* 确保图片不会超出容器 */
    .list-item img {
        max-width: 100%;
        height: auto;
    }

    /* 调整拖拽时的样式 */
    .sortable-ghost {
        opacity: 0.5;
        background: #f5f5f5;
    }

    /* 添加响应式支持 */
    @media (max-width: 1200px) {
        .module-wrap {
            width: 240px;
        }

        .module-edit {
            width: 280px;
        }
    }

    /* 左侧面板 */
    .module-wrap {
        width: 280px;
        flex-shrink: 0; /* 防止宽度被压缩 */
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
    }

    .module-wrap.collapsed {
        width: 0;
        padding: 0;
        overflow: hidden;
    }

    /* 预览区域 */
    .preview-wrap {
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        position: relative;
        display: flex;
        flex-direction: column; /* 改为纵向排列 */
        height: 100%; /* 设置高度 */
    }

    .collapse-btn {
        position: absolute;
        left: -20px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 60px;
        border: none;
        background: #8446df;
        color: #fff;
        border-radius: 4px 0 0 4px;
        cursor: pointer;
        z-index: 100;
    }

    /* 编辑区域 */
    .module-edit {
        width: 420px;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden; /* 防止内容溢出 */
    }

    .c-title {
        padding: 15px;
        border-bottom: 1px solid #eee;
        font-weight: bold;
        background: #fafafa;
        border-radius: 4px 4px 0 0;
    }

    /* 继承原有的模样式 */
    .module-tool {
        background-color: rgba(0,0,0,.5);
        display: none;
        height: 26px;
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
        z-index: 10;
    }

    .list-item {
        position: relative;
        margin-bottom: 15px;
        border: 1px solid transparent;
    }

    .list-item:hover .module-tool {
        display: flex;
    }

    .list-item.active {
        border: 2px solid #8446df;
        box-shadow: 0 0 10px 2px rgba(132, 70, 223, 0.1);
    }

    .modules-list {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
    }

    .component-wrap {
        padding: 15px;
        height: calc(100% - 56px);
        overflow-y: auto;
    }

    .preview-container {
        flex: 1; /* 占据剩余空间 */
        overflow-y: auto; /* 允许内容滚动 */
        padding: 0;
    }

    .editable-area {
        min-height: 100%;
    }

    /* 调整整体布局 */
    .card-body {
        padding: 0px;
        gap: 15px;
        height: calc(100vh);
        display: flex;
        overflow: hidden; /* 防止窗体出现滚动条 */
    }

    /* 左侧模块列表样式 */
    .module-wrap {
        width: 280px;
        flex-shrink: 0;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
    }

    .modules-list .list-item {
        align-items: center;
        border: 1px solid #eee;
        border-radius: 4px;
        cursor: move;
        display: flex;
        margin-bottom: 10px;
        padding: 10px 24px 10px 16px;
        position: relative;
        background: #fff;
        transition: all 0.3s;
    }

    .modules-list .list-item:hover {
        border-color: #8446df;
        box-shadow: 0 2px 6px rgba(132, 70, 223, 0.1);
    }

    .modules-list .list-item .icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7f3ff;
        border-radius: 4px;
        margin-right: 10px;
    }

    .modules-list .list-item .icon i {
        color: #8446df;
        font-size: 18px;
    }

    .modules-list .list-item .name {
        font-size: 13px;
        color: #333;
    }

    .modules-list .list-item:after {
        content: "\F3FE";
        font-family: bootstrap-icons;
        position: absolute;
        right: 8px;
        color: #999;
        font-size: 16px;
    }
    /* 左侧面板tabs样式 */
    .module-tabs {
        display: flex;
        border-bottom: 1px solid #eee;
        background: #fafafa;
    }

    .module-tabs .tab-item {
        flex: 1;
        padding: 12px 15px;
        text-align: center;
        cursor: pointer;
        font-size: 13px;
        color: #666;
        transition: all 0.3s;
    }

    .module-tabs .tab-item:hover {
        color: #8446df;
    }

    .module-tabs .tab-item.active {
        color: #8446df;
        background: #fff;
        border-bottom: 2px solid #8446df;
    }

    .module-tabs .tab-item i {
        margin-right: 4px;
    }

    /* 面板内容区域 */
    .panel-content {
        flex: 1;
        overflow-y: auto;
    }

    /* 属性设置面板样式调整 */
    .component-wrap {
        padding: 15px;
    }

    /* 移除原右侧属性栏 */
    .module-edit {
        display: none;
    }

    /* 拖拽时的样式 */
    .ghost-class {
        background: #f7f3ff !important;
        border: 2px dashed #8446df !important;
        opacity: 0.5;
    }

    .chosen-class {
        background: #f7f3ff !important;
        border: 2px solid #8446df !important;
        box-shadow: 0 0 10px rgba(132, 70, 223, 0.2);
    }

    .drag-class {
        background: #f7f3ff !important;
        opacity: 0.8;
        transform: scale(1.05);
        transition: transform 0.2s;
    }

    /* 预览区的模样式 */
    .view-modules-list .list-item {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 4px;
        margin-bottom: 15px;
        position: relative;
        transition: all 0.3s;
    }

    .view-modules-list .list-item:hover {
        border-color: #8446df;
        box-shadow: 0 2px 8px rgba(132, 70, 223, 0.1);
    }

    .view-modules-list .list-item.active {
        border: 2px solid #8446df;
        box-shadow: 0 0 10px rgba(132, 70, 223, 0.15);
    }

    /* 拖拽提示区域 */
    .editable-area {
        min-height: 200px;
        padding: 5px;
        background: #f9f9f9;
        border: 2px dashed #ddd;
        border-radius: 4px;
        transition: all 0.3s;
    }

    .editable-area:empty::after {
        content: '拖拽模块到这里';
        display: block;
        text-align: center;
        color: #999;
        padding: 40px 0;
    }

    .editable-area.dragover {
        background: #f7f3ff;
        border-color: #8446df;
    }

    @keyframes moduleAdded {
        0% {
            transform: scale(0.8);
            opacity: 0;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .view-modules-list .list-item {
        transform-origin: center;
        will-change: transform, opacity;
    }
</style>

<!-- 一行四图编辑模块 -->
<template id="module-editor-four-image-template">
    <div class="image-edit-wrapper">
        <div class="module-editor-row">设置</div>
        <div class="module-edit-group">
            <div class="module-edit-title">模块标题</div>
            <text-i18n v-model="form.title"></text-i18n>

            <div class="module-edit-title mt-3">副标题</div>
            <text-i18n v-model="form.subtitle"></text-i18n>
        </div>

        <div class="module-editor-row">内容</div>
        <div class="module-edit-group">
            <div class="module-edit-title">图片设置</div>
            <div class="module-edit-subtitle">建议上传相同尺寸的图片，最佳尺寸400x400，支持拖拽排序</div>

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
                            <img :src="thumbnail(item.image[source.locale])" class="img-responsive">
                            <span class="image-index">图片 @{{ index + 1 }}</span>
                        </div>
                        <div class="right">
                            <el-tooltip effect="dark" content="删除" placement="left">
                                <div class="remove-item" @click.stop="removeImage(index)">
                                    <i class="el-icon-delete"></i>
                                </div>
                            </el-tooltip>
                            <i :class="'el-icon-arrow-'+(item.show ? 'up' : 'down')"></i>
                        </div>
                    </div>
                    <div :class="'pb-images-list ' + (item.show ? 'active' : '')">
                        <div class="pb-images-top">
                            <pb-image-selector v-model="item.image"
                                               :aspectRatio="1"
                                               :targetWidth="400"
                                               :targetHeight="400"></pb-image-selector>
                            <div class="tag">建议尺寸: 400 x 400，图片比例1:1</div>
                        </div>
                        <div class="link-section">
                            <div class="module-edit-subtitle">图片说明</div>
                            <text-i18n v-model="item.description"></text-i18n>

                            <div class="module-edit-subtitle mt-3">图片链接</div>
                            <link-selector :hide-types="['catalog', 'static']" v-model="item.link"></link-selector>
                        </div>
                    </div>
                </div>
            </draggable>

            <div class="add-item" v-if="form.images.length < 4">
                <el-button type="primary" size="small" @click="addImage" icon="el-icon-circle-plus-outline">
                    添加图片 (@{{ form.images.length }}/4)
                </el-button>
            </div>
        </div>
    </div>
</template>
<!-- 一行四图组件脚本 -->
<script type="text/javascript">
    Vue.component('module-editor-four_image', {
        template: '#module-editor-four-image-template',
        props: ['module'],
        data: function () {
            return {
                form: null,
                source: {
                    locale: $locale
                }
            }
        },
        watch: {
            form: {
                handler: function (val) {
                    // 深度监听 form 的变化，实时更新到父组件
                    this.$emit('on-changed', val);
                },
                deep: true
            }
        },
        created: function () {
            this.form = JSON.parse(JSON.stringify(this.module));
        },
        methods: {
            thumbnail(image) {
                if (!image) {
                    return "{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}";
                }
                if (typeof image === 'string' && image.indexOf('http') === 0) {
                    return image;
                }
                if (typeof image === 'object') {
                    const locale = this.source.locale;
                    return image[locale] || image['zh_cn'] || Object.values(image)[0] || "{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}";
                }
                return asset + image;
            },
            addImage() {
                if (this.form.images.length >= 4) {
                    this.$message.warning('最多只能添加4张图片');
                    return;
                }
                this.form.images.push({
                    image: languagesFill(''),
                    description: languagesFill(''),
                    link: {
                        type: 'product',
                        value: ''
                    },
                    show: true
                });
            },
            removeImage(index) {
                this.form.images.splice(index, 1);
            },
            itemShow(index) {
                this.form.images[index].show = !this.form.images[index].show;
            }
        }
    });
</script>

<!-- 添加样式 -->
<style>
    .pb-images-selector {
        background: #fff;
        border-radius: 4px;
        margin-bottom: 15px;
    }

    .pb-images-selector .selector-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 4px 4px 0 0;
        cursor: pointer;
    }

    .pb-images-selector .selector-head .left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pb-images-selector .selector-head .right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pb-images-selector .selector-head .icon-rank {
        cursor: move;
        color: #8446df;
    }

    .pb-images-selector .selector-head .remove-item {
        color: #999;
        cursor: pointer;
    }

    .pb-images-selector .selector-head .remove-item:hover {
        color: #ff4d4f;
    }

    .pb-images-list {
        display: none;
        padding: 15px;
        border: 1px solid #f5f5f5;
        border-top: none;
    }

    .pb-images-list.active {
        display: block;
    }

    .add-item {
        text-align: center;
        margin-top: 15px;
    }
</style>

<style>
    /* 一行四图模块样式 */
    .module-edit-subtitle {
        color: #666;
        font-size: 12px;
        margin: 5px 0 15px;
        padding-left: 2px;
    }

    .pb-images-selector .selector-head .image-index {
        color: #666;
        font-size: 12px;
        margin-left: 10px;
    }

    .pb-images-selector .link-section {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f5f5f5;
    }

    /* 预览区域的一行四图样式 */
    .four-image-grid {
        padding: 10px;
    }

    .four-image-grid .image-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .four-image-grid .image-item {
        position: relative;
        padding-bottom: 100%;
        overflow: hidden;
        border-radius: 4px;
    }

    .four-image-grid .image-item img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .four-image-grid .image-description {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 8px;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-size: 12px;
        text-align: center;
    }

    /* 标题样式 */
    .module-title-wrap {
        text-align: center;
        margin-bottom: 20px;
    }

    .module-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .module-sub-title {
        font-size: 14px;
        color: #666;
    }
</style>

<!-- 一行四图编辑模块 -->
<template id="module-editor-four-image-template-plus">
    <div class="image-edit-wrapper">
        <div class="module-editor-row">设置</div>
        <div class="module-edit-group">
            <div class="module-edit-title">模块标题</div>
            <text-i18n v-model="form.title"></text-i18n>

            <div class="module-edit-title mt-3">副标题</div>
            <text-i18n v-model="form.subtitle"></text-i18n>
        </div>

        <div class="module-editor-row">内容</div>
        <div class="module-edit-group">
            <div class="module-edit-title">图片设置</div>
            <div class="module-edit-subtitle">建议上传相同尺寸的图片，最佳尺寸400x400，支持拖拽排序</div>

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
                            <img :src="thumbnail(item.image[source.locale])" class="img-responsive">
                            <span class="image-index">图片 @{{ index + 1 }}</span>
                        </div>
                        <div class="right">
                            <el-tooltip effect="dark" content="删除" placement="left">
                                <div class="remove-item" @click.stop="removeImage(index)">
                                    <i class="el-icon-delete"></i>
                                </div>
                            </el-tooltip>
                            <i :class="'el-icon-arrow-'+(item.show ? 'up' : 'down')"></i>
                        </div>
                    </div>
                    <div :class="'pb-images-list ' + (item.show ? 'active' : '')">
                        <div class="pb-images-top">
                            <pb-image-selector v-model="item.image"
                                               :aspectRatio="1"
                                               :targetWidth="400"
                                               :targetHeight="400"></pb-image-selector>
                            <div class="tag">建议尺寸: 400 x 400，图片比例1:1</div>
                        </div>
                        <div class="link-section">
                            <div class="module-edit-subtitle">图片说明</div>
                            <text-i18n v-model="item.description"></text-i18n>

                            <div class="module-edit-subtitle mt-3">图片链接</div>
                            <link-selector :hide-types="['catalog', 'static']" v-model="item.link"></link-selector>
                        </div>
                    </div>
                </div>
            </draggable>

            <div class="add-item" v-if="form.images.length < 4">
                <el-button type="primary" size="small" @click="addImage" icon="el-icon-circle-plus-outline">
                    添加图片 (@{{ form.images.length }}/4)
                </el-button>
            </div>
        </div>
    </div>
</template>
<!-- 一行四图PLUS组件定义 -->
<script type="text/javascript">
    Vue.component('module-editor-four_image-plus', {
        template: '#module-editor-four-image-template-plus',
        props: ['module'],
        data: function () {
            return {
                form: null,
                source: {
                    locale: $locale
                }
            }
        },
        watch: {
            form: {
                handler: function (val) {
                    // 深度监听 form 的变化，实时更新到父组件
                    this.$emit('on-changed', val);
                },
                deep: true
            }
        },
        created: function () {
            this.form = JSON.parse(JSON.stringify(this.module));
        },
        methods: {
            thumbnail(image) {
                if (!image) {
                    return "{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}";
                }
                if (typeof image === 'string' && image.indexOf('http') === 0) {
                    return image;
                }
                if (typeof image === 'object') {
                    const locale = this.source.locale;
                    return image[locale] || image['zh_cn'] || Object.values(image)[0] || "{{ plugin_asset('mobile_builder', 'images/placeholder.png') }}";
                }
                return asset + image;
            },
            addImage() {
                if (this.form.images.length >= 4) {
                    this.$message.warning('最多只能添加4张图片');
                    return;
                }
                this.form.images.push({
                    image: languagesFill(''),
                    description: languagesFill(''),
                    link: {
                        type: 'product',
                        value: ''
                    },
                    show: true
                });
            },
            removeImage(index) {
                this.form.images.splice(index, 1);
            },
            itemShow(index) {
                this.form.images[index].show = !this.form.images[index].show;
            }
        }
    });
</script>

<!-- 文章辑模块模板 -->
<template id="module-editor-article-template">
    <div class="article-edit-wrapper">
        <div class="module-editor-row">设置</div>
        <div class="module-edit-group">
            <div class="module-edit-title">模块标题</div>
            <text-i18n v-model="form.title"></text-i18n>

            <div class="module-edit-title mt-3">副标题</div>
            <text-i18n v-model="form.subtitle"></text-i18n>
        </div>

        <div class="module-editor-row">内容</div>
        <div class="module-edit-group">
            <div class="module-edit-title">配置文章</div>
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
                            :disabled="articleData.length >= 4">
                        </el-autocomplete>

                        <div class="item-group-wrapper" v-loading="loading">
                            <template v-if="articleData.length">
                                <draggable
                                    ghost-class="dragabble-ghost"
                                    :list="articleData"
                                    @change="itemChange"
                                    :options="{animation: 330}">
                                    <div v-for="(item, index) in articleData"
                                         :key="index"
                                         class="item">
                                        <div>
                                            <i class="el-icon-s-unfold"></i>
                                            <span>@{{ item.name }}</span>
                                        </div>
                                        <i class="el-icon-delete right"
                                           @click="removeArticle(index)"></i>
                                    </div>
                                </draggable>
                            </template>
                            <template v-else>
                                <div class="empty-tip">请添加文章</div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- 添加组件定义 -->
<script type="text/javascript">
    Vue.component('module-editor-article', {
        template: '#module-editor-article-template',
        props: ['module'],
        data: function () {
            return {
                keyword: '',
                articleData: [],
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
            }
        },
        created: function () {
            this.form = JSON.parse(JSON.stringify(this.module));
            this.loadArticles();
        },
        methods: {
            loadArticles() {
                if (!this.form.articles.length) return;
                this.loading = true;

                axios.get('api/panel/articles/names?article_ids=' + this.form.articles.map(e => e.id).join(','), {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    }
                }).then((res) => {
                    this.loading = false;
                    this.articleData = res.data;
                });
            },

            querySearch(keyword, cb) {
                axios.get('api/panel/articles/autocomplete?keyword=' + encodeURIComponent(keyword), {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken
                    }
                }).then((res) => {
                    cb(res.data);
                });
            },

            handleSelect(item) {
                if (this.articleData.length >= 4) {
                    this.$message.warning('最多只能添加4篇文章');
                    return;
                }
                if (!this.form.articles.find(v => v.id === item.id)) {
                    this.form.articles.push(item);
                    this.articleData.push(item);
                }
                this.keyword = "";
            },

            itemChange(evt) {
                this.form.articles = this.articleData;
            },

            removeArticle(index) {
                this.articleData.splice(index, 1);
                this.form.articles.splice(index, 1);
            },

            limitChange(e) {
                this.form.limit = e;
                this.loadArticles();
            }
        }
    });
</script>

<!-- 添加样式 -->
<style>
    .pb-images-selector {
        background: #fff;
        border-radius: 4px;
        margin-bottom: 15px;
    }

    .pb-images-selector .selector-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 4px 4px 0 0;
        cursor: pointer;
    }

    .pb-images-selector .selector-head .left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pb-images-selector .selector-head .right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pb-images-selector .selector-head .icon-rank {
        cursor: move;
        color: #8446df;
    }

    .pb-images-selector .selector-head .remove-item {
        color: #999;
        cursor: pointer;
    }

    .pb-images-selector .selector-head .remove-item:hover {
        color: #ff4d4f;
    }

    .pb-images-list {
        display: none;
        padding: 15px;
        border: 1px solid #f5f5f5;
        border-top: none;
    }

    .pb-images-list.active {
        display: block;
    }

    .add-item {
        text-align: center;
        margin-top: 15px;
    }
</style>

<style>
    /* 一行四图模块样式 */
    .module-edit-subtitle {
        color: #666;
        font-size: 12px;
        margin: 5px 0 15px;
        padding-left: 2px;
    }

    .pb-images-selector .selector-head .image-index {
        color: #666;
        font-size: 12px;
        margin-left: 10px;
    }

    .pb-images-selector .link-section {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f5f5f5;
    }

    /* 预览区域的一行四图样式 */
    .four-image-grid {
        padding: 10px;
    }

    .four-image-grid .image-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .four-image-grid .image-item {
        position: relative;
        padding-bottom: 100%;
        overflow: hidden;
        border-radius: 4px;
    }

    .four-image-grid .image-item img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .four-image-grid .image-description {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 8px;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-size: 12px;
        text-align: center;
    }

    /* 拖拽排序时的样式 */
    .dragabble-ghost {
        opacity: 0.5;
        background: #f7f3ff !important;
        border: 2px dashed #8446df !important;
    }

    .pb-images-selector .selector-head:hover {
        background: #f0f0f0;
    }

    .add-item {
        text-align: center;
        margin-top: 20px;
        padding: 10px;
        background: #f9f9f9;
        border-radius: 4px;
    }
</style>

<style>
    /* 文章列表样式 */
    .article-grid {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 15px;
        background: #fff;
    }

    /* 文章项样式 */
    .article-item {
        display: flex;
        align-items: center;
        padding: 12px;
        background: #fff;
        border-radius: 4px;
        border: 1px solid #eee;
        transition: all 0.3s;
        cursor: pointer;
    }

    .article-item:hover {
        border-color: #8446df;
        box-shadow: 0 2px 8px rgba(132, 70, 223, 0.1);
    }

    /* 文章标题样式 */
    .article-title {
        font-size: 14px;
        color: #333;
        margin-bottom: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-right: 15px;
        position: relative;
    }

    /* 添加文章标题前的图标 */
    .article-title:before {
        content: "";
        display: inline-block;
        width: 4px;
        height: 4px;
        background: #8446df;
        border-radius: 50%;
        margin-right: 8px;
        vertical-align: middle;
        position: relative;
        top: -1px;
    }

    /* 文章搜索框样式 */
    .article-search {
        width: 100%;
        margin-bottom: 15px;
    }

    .article-search .el-input__inner {
        border-color: #e4e7ed;
    }

    .article-search .el-input__inner:focus {
        border-color: #8446df;
    }

    /* 文章列表容器 */
    .article-list-container {
        background: #f8f9fa;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
    }

    /* 拖拽排序样式 */
    .article-item.sortable-ghost {
        background: #f7f3ff;
        border: 2px dashed #8446df;
        opacity: 0.7;
    }

    /* 删除按钮样式 */
    .article-item .delete-btn {
        opacity: 0;
        transition: all 0.3s;
        color: #999;
        margin-left: auto;
        padding: 4px;
    }

    .article-item:hover .delete-btn {
        opacity: 1;
    }

    .article-item .delete-btn:hover {
        color: #ff4d4f;
    }

    /* 空状态提示 */
    .empty-tip {
        text-align: center;
        color: #999;
        padding: 30px 0;
        font-size: 13px;
    }

    /* 数量限制输入框 */
    .limit-input {
        width: 120px;
    }

    .limit-input .el-input__inner {
        text-align: center;
    }
</style>

<style>
    /* 文章模块样式 */
    .module-title-wrap {
        text-align: center;
        margin-bottom: 30px;
    }

    .module-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .module-sub-title {
        color: #666;
        font-size: 14px;
    }

    .blog-item {
        margin-bottom: 30px;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s;
    }

    .blog-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .blog-item .image {
        position: relative;
        padding-bottom: 75%;
        overflow: hidden;
    }

    .blog-item .image img {
        position: absolute;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.3s;
    }

    .blog-item:hover .image img {
        transform: scale(1.05);
    }

    .blog-item-info {
        padding: 15px;
    }

    .blog-catalog {
        margin-bottom: 8px;
    }

    .blog-catalog a {
        color: #8446df;
        font-size: 12px;
    }

    .blog-title {
        font-size: 14px;
        font-weight: 500;
        line-height: 1.4;
        margin-bottom: 10px;
        height: 40px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .author-wrap {
        display: flex;
        align-items: center;
        gap: 15px;
        color: #999;
        font-size: 12px;
    }

    .blog-author, .blog-created {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .blog-author i, .blog-created i {
        font-size: 14px;
    }
</style>

<style>
    /* 操作按钮组样式 */
    .module-actions {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translate(-50%, -100%);
        background: rgba(132, 70, 223, 0.9);
        padding: 6px;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(132, 70, 223, 0.2);
        display: flex;
        gap: 6px;
        z-index: 100;
        opacity: 0;
        transition: all 0.3s;
    }

    .module-actions::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid rgba(132, 70, 223, 0.9);
    }

    .list-item:hover .module-actions {
        opacity: 1;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        border: none;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        transition: all 0.3s;
        cursor: pointer;
    }

    .btn-action:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .btn-action:disabled {
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.4);
        cursor: not-allowed;
        transform: none;
    }

    .btn-action.delete:hover {
        background: #ff4d4f;
    }

    /* 添加遮罩效果 */
    .list-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.2);
        opacity: 0;
        transition: all 0.3s;
        pointer-events: none;
    }

    .list-item:hover::before {
        opacity: 1;
    }
</style>

<style>
    @keyframes moduleMove {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.02);
        }
        100% {
            transform: scale(1);
        }
    }
</style>

<style>
    /* 商品网格样式 */
    .product-grid-item {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s;
        margin-bottom: 30px;
    }

    .product-grid-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0.1);
        transform: translateY(-2px);
    }

    .product-grid-item .image {
        position: relative;
        padding-bottom: 100%;
        overflow: hidden;
    }

    .product-grid-item .image img {
        position: absolute;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.3s;
    }

    .product-grid-item:hover .image img {
        transform: scale(1.05);
    }

    .product-item-info {
        padding: 15px;
    }

    .product-name {
        margin-bottom: 10px;
        height: 40px;
        overflow: hidden;
    }

    .product-name a {
        color: #333;
        font-size: 14px;
        line-height: 1.4;
        text-decoration: none;
    }

    .product-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .product-bottom-btns {
        flex: 1;
    }

    .btn-add-cart {
        background: #8446df;
        color: #fff;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        transition: all 0.3s;
        text-align: center;
    }

    .btn-add-cart:hover {
        background: #6e35c3;
    }

    .product-price {
        text-align: right;
    }

    .price-old {
        color: #999;
        font-size: 12px;
        text-decoration: line-through;
    }

    .price-new {
        color: #ff4d4f;
        font-size: 16px;
        font-weight: 500;
    }

    /* 模块标题样式 */
    .module-title-wrap {
        text-align: center;
        margin-bottom: 30px;
    }

    .module-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .module-sub-title {
        color: #666;
        font-size: 14px;
    }
</style>

<style>
    /* 预览区的四图Plus模块样式 */
    .preview-container .module-four-image-plus {
        padding: 30px 0;
    }
    .preview-container .module-four-image-plus .row {
        min-height: 420px;
        display: flex;
    }
    .preview-container .module-four-image-plus .col-md-6 {
        flex: 1;
    }
    .preview-container .module-four-image-plus .image-item {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        background: #f8f8f8;
        height: 100%;
        width: 100%;
    }
    .preview-container .module-four-image-plus .image-wrap {
        position: relative;
        overflow: hidden;
        width: 100%;
        height: 100%;
        background: #f8f8f8;
    }
    .preview-container .module-four-image-plus .image-wrap img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    /* 右侧图片布局 */
    .preview-container .module-four-image-plus .right-images {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: calc(50% - 6px) calc(50% - 6px);
        gap: 12px;
        height: 100%;
        width: 100%;
    }
    .preview-container .module-four-image-plus .right-images .small-image {
        height: 100%;
        width: 100%;
    }
    .preview-container .module-four-image-plus .right-images .wide-image {
        grid-column: span 1;
        height: 100%;
        width: 100%;
    }
</style>
</body>
</html>
