# PageBuilder 开发文档

## 开发环境

### 系统要求
- PHP >=8.1
- Laravel >= 10.0
- Node.js >= 16.0
- Composer >= 2

### 开发工具
- **IDE**: PhpStorm / VS Code
- **调试**: Laravel Debugbar
- **版本控制**: Git
- **包管理**: Composer / NPM

## 项目结构

```
PageBuilder/
├── Controllers/                    # 控制器层
│   └── Panel/
│       └── PageBuilderController.php
├── Services/                      # 服务层
│   ├── PageBuilderService.php    # 页面构建服务
│   ├── ModulePreviewService.php  # 模块预览服务
│   └── DesignService.php         # 设计服务
├── Views/                         # 视图层（详见前端结构）
├── Public/                       # 静态资源
│   ├── css/                     # 样式文件
│   ├── js/                      # JavaScript文件
│   └── images/                  # 图片资源
├── Routes/                       # 路由定义
│   └── panel.php                # 后台路由
├── config.json                  # 插件配置
└── README.md                    # 插件说明
```

## 前端结构

```
Views/                            # 视图层
├── design/                       # 设计器界面
│   ├── index.blade.php          # 主页面
│   ├── layouts/                 # 布局组件
│   │   ├── header.blade.php     # 头部布局
│   │   └── sidebar.blade.php    # 侧边栏布局
│   ├── scripts/                 # JavaScript脚本
│   │   ├── app.blade.php        # 应用主脚本
│   │   ├── vue-app.blade.php    # Vue应用脚本
│   │   └── iframe-events.blade.php # iframe事件处理
│   ├── editors/                 # 模块编辑器
│   │   ├── slideshow.blade.php      # 幻灯片编辑器
│   │   ├── rich-text.blade.php      # 富文本编辑器
│   │   ├── left-image-right-text.blade.php # 左图右文编辑器
│   │   ├── grid-square.blade.php    # 网格方块编辑器
│   │   ├── card-slider.blade.php    # 卡片滑块编辑器
│   │   ├── four-image.blade.php     # 四图编辑器
│   │   ├── four-image-plus.blade.php # 四图增强编辑器
│   │   ├── image-100.blade.php      # 单图编辑器
│   │   ├── latest.blade.php         # 最新产品编辑器
│   │   ├── product.blade.php        # 产品编辑器
│   │   ├── category.blade.php       # 分类编辑器
│   │   └── article.blade.php        # 文章编辑器
│   └── components/              # 通用组件
│       ├── multi-image-selector.blade.php  # 多图选择器
│       ├── single-image-selector.blade.php # 单图选择器
│       ├── i18n.blade.php            # 多语言组件
│       └── link-selector.blade.php   # 链接选择器
└── front/                        # 前台展示
    ├── home.blade.php           # 首页模板
    ├── page.blade.php           # 页面模板
    ├── modules/                 # 模块模板
    │   ├── slideshow.blade.php      # 幻灯片模块
    │   ├── rich_text.blade.php      # 富文本模块
    │   ├── left_image_right_text.blade.php # 左图右文模块
    │   ├── grid_square.blade.php    # 网格方块模块
    │   ├── card_slider.blade.php    # 卡片滑块模块
    │   ├── four_image.blade.php     # 四图模块
    │   ├── four_image-plus.blade.php # 四图增强模块
    │   ├── image10.blade.php       # 单图模块
    │   ├── image20.blade.php       # 双图模块
    │   ├── image41.blade.php       # 四图模块1
    │   ├── image42.blade.php       # 四图模块2
    │   ├── product.blade.php        # 产品模块
    │   └── article.blade.php        # 文章模块
    └── partials/                 # 前台组件
        └── module-edit-buttons.blade.php # 模块编辑按钮
```

## 核心概念

### 🧩 模块系统

PageBuilder 采用模块化设计，每个模块都是一个独立的功能单元：

```
┌─────────────────────────────────────────────────────────────────┐
│                           模块系统架构                            │
├─────────────────┬─────────────────┬─────────────────────────────┤
│   模块定义       │   模块编辑器     │   模块模板                   │
│  (ModuleRepo)   │  (Vue组件)      │  (Blade模板)                │
│                 │                 │                             │
│ • 模块配置       │ • 参数编辑       │ • 前台展示                   │
│ • 默认数据       │ • 样式设置       │ • 响应式布局                 │
│ • 图标标识       │ • 实时预览       │ • 编辑工具栏                 │
└─────────────────┴─────────────────┴─────────────────────────────┘
```

**核心概念说明**：

- **模块定义**：在 `ModuleRepo.php` 中定义模块的基本信息、默认配置和数据结构
- **模块编辑器**：Vue组件，提供可视化的参数编辑界面
- **模块模板**：Blade模板文件，负责前台展示和编辑工具栏

### 🎨 设计器界面

设计器是PageBuilder的核心操作界面，包含三个主要区域：

```
┌─────────────────────────────────────────────────────────────────┐
│                        设计器界面布局                            │
├─────────────────┬─────────────────┬─────────────────────────────┤
│   左侧边栏       │   预览区         │   顶部工具栏                 │
│  (切换显示)     │  (中央区域)     │  (顶部区域)                 │
│                 │                 │                             │
│ • 模块库         │ • 实时预览       │ • 页面选择                   │
│ • 编辑器面板     │ • 编辑工具栏     │ • 设备切换                   │
│ • 相互切换       │ • 响应式预览     │ • 保存发布                   │
└─────────────────┴─────────────────┴─────────────────────────────┘
```

**界面组件说明**：

- **左侧边栏**：包含模块库和编辑器面板，根据操作状态相互切换显示
  - **模块库**：显示所有可用模块，支持拖拽添加到预览区
  - **编辑器面板**：当前选中模块的参数编辑界面
- **预览区**：iframe嵌入的前台页面，实时显示设计效果
- **顶部工具栏**：页面选择、设备切换、保存发布等操作按钮

### 🔄 数据流转机制

PageBuilder的数据流转遵循以下路径：

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   模块定义   │───▶│   设计器     │───▶│   预览服务   │───▶│   前台展示   │
│  (硬编码)   │    │  (Vue App)  │    │  (Laravel)  │    │  (Blade)    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │                   │
       ▼                   ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   模块配置   │    │   模块数据   │    │   渲染数据   │    │   展示数据   │
│  (JSON)     │    │  (Array)    │    │  (Array)    │    │  (HTML)     │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
```

**数据流转说明**：

1. **模块定义** → **设计器**：加载模块配置到Vue应用2. **设计器** → **预览服务**：用户编辑时发送数据到后端
3 **预览服务** → **前台展示**：渲染模块HTML返回给前端
4. **前台展示** → **设计器**：更新预览区显示最新效果

### 🎯 关键机制

#### 1. 实时预览机制
- **iframe通信**：通过 `previewWindow` 操作预览区DOM
- **AJAX更新**：数据变化时发送请求获取新HTML
- **HTML替换**：直接替换预览区中的模块内容
- **防抖优化**：使用 `inno.debounce` 避免频繁请求

#### 2. 设计模式控制
- **design参数**：URL参数控制是否显示编辑工具栏
- **编辑工具栏**：hover时显示的编辑、删除、排序按钮
- **响应式预览**：支持PC、平板、手机三种设备预览

#### 3. 模块生命周期
- **创建**：从模块库拖拽到预览区
- **编辑**：点击模块进入编辑模式
- **更新**：参数变化触发实时预览
- **保存**：数据持久化到数据库
- **删除**：从页面中移除模块

#### 4. 组件通信机制

PageBuilder 采用 Vue.js 的组件通信机制，实现模块编辑器与主应用的数据同步：

**组件注册与绑定**：
```javascript
// 1. 模块编辑器组件注册 (slideshow.blade.php)
Vue.component('module-editor-slideshow', {
  template: '#module-editor-slideshow',
  props: ['module'],
  methods: {
    onChange() {
      // 防抖处理
      if (this.debounceTimer) {
        clearTimeout(this.debounceTimer);
      }
      this.debounceTimer = setTimeout(() => {
        // 关键：向父组件发射事件
        this.$emit('on-changed', this.module);
      }, 300);
    }
  }
});
```

**动态组件渲染**：
```html
<!-- 2. 动态组件渲染 (sidebar.blade.php) -->
<div class="module-edit" v-if="form.modules.length > 0 && design.editType == 'module'">
  <component
    :is="editingModuleComponent"           <!-- 动态决定渲染哪个编辑器 -->
    :key="design.editingModuleIndex"       <!-- 强制重新渲染 -->
    :module="form.modules[design.editingModuleIndex].content"  <!-- 传递数据 -->
    @on-changed="moduleUpdated"            <!-- 监听数据变化 -->
  ></component>
</div>
```

**组件名称计算**：
```javascript
// 3. 动态组件名称计算 (vue-app.blade.php)
computed: {
  editingModuleComponent() {
    const module = this.form.modules[this.design.editingModuleIndex];
    // 根据模块代码生成组件名，如：slideshow → module-editor-slideshow
    return 'module-editor-' + module.code.replace('_', '-');
  }
}
```

**事件处理与AJAX更新**：
```javascript
// 4. 事件处理与预览更新 (vue-app.blade.php)
moduleUpdated: inno.debounce(function(val) {
  // 更新模块数据
  this.form.modules[this.design.editingModuleIndex].content = val;
  const data = this.form.modules[this.design.editingModuleIndex];
  
  // 发起AJAX请求更新预览
  axios.post(url + '?design=1', data).then((res) => {
    // 替换iframe中对应的模块HTML
    $(previewWindow.document).find('#module-' + data.module_id).replaceWith(res);
  });
}, 300)
```

**完整数据流**：
```
用户修改模块内容
    ↓
onChange() 方法被调用
    ↓
setTimeout 防抖 300ms
    ↓
this.$emit('on-changed', this.module)  ← 事件发射
    ↓
父组件监听到事件
    ↓
moduleUpdated(this.module) 被调用
    ↓
inno.debounce 再次防抖 300ms
    ↓
发起 AJAX 请求到后端
    ↓
后端渲染模块HTML
    ↓
返回HTML替换iframe中的模块
    ↓
用户看到实时预览效果
```

**防抖机制**：
```javascript
// 防抖函数实现 (app.blade.php)
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const context = this; // 保存 this 上下文
    
    const later = () => {
      clearTimeout(timeout);
      func.apply(context, args); // 使用 apply 保持 this 上下文
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// 全局inno对象
window.inno = window.inno || {};
window.inno.debounce = debounce;
```

**组件映射关系**：
| 模块类型 | 组件名 | 对应文件 | 功能说明 |
|---------|--------|----------|----------|
| slideshow | module-editor-slideshow | slideshow.blade.php | 幻灯片编辑器 |
| product | module-editor-product | product.blade.php | 产品编辑器 |
| category | module-editor-category | category.blade.php | 分类编辑器 |
| article | module-editor-article | article.blade.php | 文章编辑器 |

**设计优势**：
- **动态切换**：一个区域可以显示不同类型的编辑器
- **代码复用**：不需要为每种模块写重复的容器代码
- **状态隔离**：不同模块的编辑器状态互不影响
- **统一接口**：所有编辑器都通过相同的 props 和 events 与父组件通信
- **双重防抖**：组件内部防抖 + Vue 实例防抖，避免频繁请求
- **上下文保持**：`inno.debounce` 确保 `this` 上下文正确

### 📋 核心数据结构

#### 模块数据结构
```php
$module =code' => 'slideshow,           // 模块代码
  module_id' =>unique_id,     // 模块唯一ID
    name幻灯片模块,           // 模块名称
   title幻灯片,             // 模块标题
content                   // 模块内容
      title' =>模块标题,
    images => [
           
              image' => path/to/image.jpg,
              link' => 'https://example.com,
                type' => 'product'
            ]
        ]
    ],
  view_path' => 'PageBuilder::front.modules.slideshow'
];
```

#### 页面数据结构
```php
$pageData = modules                   // 页面模块列表
        $module1,
        $module2
        // ...
    ],
   pageme',               // 页面标识
  designrue                // 是否设计模式
];
```

## 整体架构流程

### 🏗️ 系统框架概览

PageBuilder 是一个基于 Vue.js + Laravel 的可视化页面构建器，采用前后端分离的设计模式：

```
┌─────────────────────────────────────────────────────────────────┐
│                         PageBuilder 系统架构                      │
├─────────────────┬─────────────────┬─────────────────────────────┤
│   设计器界面     │   预览区        │   后台服务                   │
│  (Vue App)      │  (iframe)       │  (Laravel API)              │
│                 │                 │                             │
│ • 模块编辑器     │ • 实时预览       │ • 模块预览服务               │
│ • 拖拽排序       │ • 编辑工具栏     │ • 数据存储服务               │
│ • 样式设置       │ • 响应式预览     │ • 文件管理服务               │
└─────────────────┴─────────────────┴─────────────────────────────┘
         │                 │                       │
         ▼                 ▼                       ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────────────┐
│   前端组件       │ │   模块模板       │ │   数据存储               │
│  (Vue组件)      │ │  (Blade模板)     │ │  (数据库/配置)           │
└─────────────────┘ └─────────────────┘ └─────────────────────────┘
```

### 🔄 操作流程概览

用户使用 PageBuilder 的完整操作流程：

```
1 进入设计器 →2. 选择页面 →3. 拖拽模块 →4. 编辑内容 → 5时预览 → 6. 保存发布
     ↓              ↓              ↓              ↓              ↓              ↓
  加载模块库      获取页面数据     添加模块到      修改模块参数     更新预览区      保存到数据库
  初始化界面      设置编辑模式     预览区域        触发数据更新     替换HTML内容     清除缓存
```

### 📊 数据流程概览

系统内部的数据流转过程：

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   模块定义   │───▶│   设计器     │───▶│   预览服务   │───▶│   前台展示   │
│  (config)   │    │  (Vue App)  │    │  (Laravel)  │    │  (Blade)    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │                   │
       ▼                   ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   模块配置   │    │   模块数据   │    │   渲染数据   │    │   展示数据   │
│  (JSON)     │    │  (Array)    │    │  (Array)    │    │  (HTML)     │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
```

### 🎯 核心机制说明

####1 模块化设计
- **模块定义**: 每个模块在 `ModuleRepo.php` 中硬编码定义
- **模块模板**: 独立的 Blade 模板文件
- **模块编辑器**: 独立的 Vue 组件
- **模块数据**: 统一的数据结构格式

#### 2. 实时预览机制
- **iframe 通信**: 通过 `previewWindow` 操作预览区
- **AJAX 更新**: 数据变化时发送请求获取新HTML
- **HTML 替换**: 直接替换预览区中的模块内容
- **防抖优化**: 避免频繁请求，提升性能

#### 3. 设计模式控制
- **design 参数**: 通过 URL 参数控制显示模式
- **编辑工具栏**: 设计模式下显示操作按钮
- **前台展示**: 正常模式下隐藏编辑功能
- **响应式预览**: 支持不同设备尺寸预览

---

## 详细流程说明

### 📋1 模块定义与注册

**模块定义** (`ModuleRepo.php`):
```php
// 在 ModuleRepo.php 中硬编码定义模块
public static function getModules(): array
{
    return [
                title'   => '幻灯片模块',
        code  => 'slideshow',
          icon'    => '<i class=bi bi-images"></i>',
         content
            images                      image' => 'images/demo/banner/banner-1-en.jpg',
                       show              link             type'  => 'product',
                         value                   ],
                    ],
                ],
            ],
        ],
        // ... 更多模块定义
    ];
}
```

**模块注册流程**:
```php
// PageBuilderService 加载模块
public function getPageData(?string $page = null): array
{
    $data =    source=> [
      modules' => ModuleRepo::getModules(), // 从 ModuleRepo 获取模块
        ],
    ];
    return $data;
}
```

### 📋 2. 预览区机制

**iframe 加载流程**:
```javascript
// iframe 加载前台页面
<iframe src="{{ front_route('home.index) }}?design=1" id="preview-iframe">

// 前台页面检测 design 参数
if (request()->get('design'))[object Object]
    return view('front.home', ['design' => true]);  // 显示编辑工具栏
} else[object Object]
    return view('front.home', ['design' => false]); // 普通前台展示
}
```

### 📋3 Hover 操作按钮

**CSS 控制显示**:
```css
/* CSS 控制显示 */
.module-edit { display: none; }
.module-item:hover .module-edit { display: flex; }
```

**事件绑定**:
```javascript
// 事件绑定
$(previewWindow.document).on(click, .module-edit .edit,function(event) [object Object]   const module_id = $(this).parents('.module-item').prop('id).replace('module-', '');
    const editingModuleIndex = app.form.modules.findIndex(e => e.module_id == module_id);
    app.editModuleButtonClicked(editingModuleIndex);
});
```

### 📋4. 编辑器数据流

**Vue 组件数据变化**:
```javascript
// Vue 组件数据变化
Vue.component(slideshow-editor, {  props: ['content'],
    watch: [object Object]       content: {
            handler: function(val)[object Object]              this.$emit('update', val);  // 向父组件发送更新
            },
            deep: true
        }
    }
});

// 父组件接收更新
moduleUpdated: inno.debounce(function(val) {
    this.form.modules[this.design.editingModuleIndex].content = val;
    this.updatePreview(val);  // 发送 AJAX 更新预览
}, 300
```

### 📋5. 预览更新

**前端发送请求**:
```javascript
// 前端发送请求
axios.post(url +?design=1, data).then((res) => {
    $(previewWindow.document).find('#module-+ data.module_id).replaceWith(res);
});
```

**后端处理**:
```php
// 后端处理
public function previewModule(Request $request, ?string $page = null): View
{
    $module = json_decode($request->getContent(), true);
    $design = (bool) $request->get('design);    
    $viewData = $this->modulePreviewService->getPreviewData($module, $design);
    return view($viewData[view_path], $viewData);
}
```

### 📋6. 保存流程

**前端保存**:
```javascript
// 前端保存
saveButtonClicked() {
    axios.put(url, this.form).then((res) => {
        this.saveStatus =saved;    });
}
```

**后端保存**:
```php
// 后端保存
public function update(Request $request, ?string $page = null): JsonResponse
{
    $modules = $request->input('modules', []);
    $this->pageBuilderService->savePageModules($modules, $page);
    return json_success('保存成功);
}
```

### 📋7. 前台展示

**前台页面加载**:
```php
// 前台页面加载
public function index()[object Object]
    $designData = $pageBuilderService->getPageData('home');
    return view(front.home, 
    modules => $designData[modules'] ?? ],
        design' => false  // 前台模式
    ]);
}
```

**前台模板渲染**:
```blade
{{-- 前台模板渲染 --}}
@foreach($modules as $module)
    @include($module[view_path],      module_id' => $module['module_id'],
    content' => $module['content],
        design=> false  // 不显示编辑工具栏
    ])
@endforeach
```

### 🔑 关键技术点

| 技术点 | 说明 | 实现方式 |
|--------|------|----------|
| **iframe 通信** | 操作预览区内容 | `previewWindow.document` |
| **Vue 响应式** | 数据变化自动更新 | `v-model` + `watch` |
| **防抖处理** | 避免频繁请求 | `inno.debounce` |
| **设计模式** | 控制编辑工具栏 | `design` 参数 |
| **模块化** | 独立模板和编辑器 | 组件化开发 |
| **实时预览** | 所见即所得 | AJAX + HTML 替换 |

### 🎯 核心优势
1 **模块化设计**: 每个模块独立，易于扩展
2. **实时预览**: 编辑即预览，用户体验佳3. **响应式支持**: 多设备适配4. **可视化操作**: 拖拽式设计，无需编程5 **数据分离**: 设计数据与展示逻辑分离

## 核心架构

### MVC 架构
```
Controller (PageBuilderController)
    ↓
Service (PageBuilderService)
    ↓
Repository (ModuleRepository)
    ↓
Model (Module)
```

### 前端架构
```
Vue App (vue-app.blade.php)
    ↓
Component System
    ├── Module Editors
    ├── Image Selectors
    └── Layout Components
    ↓
AJAX Communication
    ↓
Backend API
```

## API 文档

### 路由定义

#### 页面构建器主页面
```php
Route::get(/pbuilder', [PageBuilderController::class, 'index])  ->name('pbuilder.index');
Route::get('/pbuilder/{page}', [PageBuilderController::class, 'index])  ->name('pbuilder.page.index);
```

#### 模块预览
```php
Route::post(/pbuilder/{page}/modules/preview', [PageBuilderController::class, previewModule])  ->name('pbuilder.modules.preview);
```

#### 保存页面数据
```php
Route::put(/pbuilder/{page}/modules', [PageBuilderController::class,update])  ->name('pbuilder.modules.update);
```

### 控制器方法

#### PageBuilderController

```php
/**
 * 页面编辑主页面
 * @param string|null $page 页面标识
 * @return mixed
 */
public function index(?string $page = null): mixed

/**
 * 预览模块HTML
 * @param Request $request
 * @param string|null $page 页面标识
 * @return View
 */
public function previewModule(Request $request, ?string $page = null): View

/**
 * 保存页面模块数据
 * @param Request $request
 * @param string|null $page 页面标识
 * @return JsonResponse
 */
public function update(Request $request, ?string $page = null): JsonResponse
```

### 服务层接口

#### PageBuilderService

```php
/**
 * 获取页面数据
 * @param string|null $page 页面标识
 * @return array
 */
public function getPageData(?string $page = null): array

/**
 * 保存页面模块
 * @param array $modules 模块数据
 * @param string|null $page 页面标识
 * @return void
 */
public function savePageModules(array $modules, ?string $page = null): void

/**
 * 导入演示数据
 * @param string|null $page 页面标识
 * @return array
 */
public function importDemoData(?string $page = null): array
```

## 模块开发指南

### 创建新模块

#### 1. 定义模块配置
在 `ModuleRepo.php` 中添加模块定义：

```php
// 在 ModuleRepo::getModules() 方法中添加新模块
   title'   => 自定义模块',
code'    =>custom_module',
  icon'    => '<i class="bi bi-grid></i>',
 content =>        title'    => self::languagesFill(模块标题'),
        subtitle' => self::languagesFill('模块副标题'),
        // 其他自定义字段
    ],
],
```

#### 2 创建模块模板
在 `Views/front/modules/` 目录下创建模块模板：

```blade
{{-- custom_module.blade.php --}}
<div id=module-{{ $module_id }}" class="module-item custom-module">
  <div class=module-content">
    {{-- 模块内容 --}}
    <div class=custom-content">
      @if($content['title])
        <h2>{{ $content['title] }}</h2>
      @endif
      @if($content['description'])
        <p>{{ $content['description'] }}</p>
      @endif
    </div>
  </div>
  
  @if($design)
    <div class="module-edit">
      <div class="edit"><i class=bi bi-pencil></i></div>
      <div class="delete"><i class="bi bi-trash></i></div>
      <div class="up><iclass="bi bi-arrow-up></i></div>
      <div class="down><iclass="bi bi-arrow-down"></i></div>
    </div>
  @endif
</div>
```

#### 3. 创建模块编辑器
在 `Views/design/editors/` 目录下创建编辑器：

```blade
{{-- custom_module.blade.php --}}
<script type="text/x-template" id="custom-module-editor">
  <div class="module-editor">
    <div class="editor-header>
      <h5定义模块设置</h5>
    </div>
    
    <div class=editor-content">
      <div class="form-group>
        <label>标题</label>
        <input type="text v-model="content.title" class="form-control">
      </div>
      
      <div class="form-group>
        <label>描述</label>
        <textarea v-model="content.description" class="form-control"></textarea>
      </div>
    </div>
  </div>
</script>

<script>
Vue.component('custom-module-editor', {
  template: #custom-module-editor',
  props: ['content],
  watch:[object Object]  content: {
      handler: function(val) {
        this.$emit('update, val);
      },
      deep: true
    }
  }
});
</script>
```

#### 4注册模块
在主页面中注册新模块：

```blade
{{-- 在 index.blade.php 中添加 --}}
@include('PageBuilder::design.editors.custom_module)
```

### 模块数据结构

```php
$module =code => 'custom_module,           // 模块代码
  module_id' => 'unique_id',          // 模块唯一ID
    name义模块,              // 模块名称
   title' => '自定义模块,             // 模块标题
content                   // 模块内容
      title' =>模块标题      description' => 模块描述',
        // 其他自定义字段
    ],
  view_path' => 'PageBuilder::front.modules.custom_module'
];
```

## 前端开发

### Vue 组件系统

#### 全局组件
- `module-editor`: 模块编辑器容器
- `single-image-selector`: 单图选择器
- `multi-image-selector`: 多图选择器
- `link-selector`: 链接选择器

#### 组件通信
```javascript
// 子组件向父组件发送更新
this.$emit('update', newContent);

// 父组件监听更新
<module-editor @update="moduleUpdated />
```

### AJAX 通信

#### 模块更新
```javascript
// 发送模块数据到后端
axios.post(url + '?design=1, moduleData)
  .then((res) => [object Object]   // 更新预览区
    $(previewWindow.document).find('#module-' + moduleId).replaceWith(res);
  });
```

#### 防抖处理
```javascript
// 使用 inno.debounce 防止频繁请求
moduleUpdated: inno.debounce(function(val) [object Object]  // 更新逻辑
}, 300``

### 样式开发

#### CSS 架构
```scss
// 设计器样式
.design-box [object Object]
  .sidebar[object Object] /* 侧边栏样式 */ }
  .preview-iframe { /* 预览区样式 */ }
}

// 模块样式
.module-item {
  .module-content { /* 模块内容 */ }
  .module-edit { /* 编辑工具栏 */ }
}

// 响应式设计
.device-mobile { /* 移动端样式 */ }
.device-pc { /* 桌面端样式 */ }
```

## 模块开发指南

### 🚀 新增自定义模块全流程

本指南将详细介绍如何从零开始创建一个完整的自定义模块，包含所有必要的文件和配置。

#### 1 确定模块需求

在开始开发前，需要明确模块的功能需求：

- **模块类型**：媒体模块、产品模块、内容模块、布局模块
- **功能描述**：模块的主要功能和展示效果
- **数据结构**：需要哪些字段和配置项
- **交互方式**：是否需要用户交互、动画效果等

#### 2. 建立模块文件结构

```
Views/
├── design/
│   └── editors/
│       └── custom_module.blade.php    # 模块编辑器
└── front/
    └── modules/
        └── custom_module.blade.php    # 前台模块模板
```

#### 3. 定义模块配置

在 `ModuleRepo.php` 中添加模块定义：

```php
// 在 ModuleRepo::getModules() 方法中添加
   title'   => 自定义模块',
code'    =>custom_module',
  icon'    => '<i class="bi bi-grid"></i>',
 content' =>        title'    => self::languagesFill(模块标题'),
        subtitle' => self::languagesFill('模块副标题'),
      images  => [
           
                image' => 'images/demo/custom/custom-1.jpg,
             link,
          type'  => 'product'
            ]
        ],
      settings=> [
            show_title'    => true,
            show_subtitle' => true,
            layout'        =>grid'
        ]
    ],
],
```

#### 4. 创建模块编辑器

创建 `Views/design/editors/custom_module.blade.php`：

```blade
<script type="text/x-template" id="custom-module-editor">
    <div class="module-editor">
        <div class="editor-header>
            <h5>自定义模块设置</h5  </div>
        
        <div class=editor-content">
            <!-- 基础设置 -->
            <div class=editor-section>
                <h6>基础设置</h6>
                
                <div class="form-group">
                    <label>模块标题</label>
                    <input type="text v-model="content.title" class="form-control>                </div>
                
                <div class="form-group">
                    <label>模块副标题</label>
                    <input type="text v-model=content.subtitle" class="form-control>                </div>
            </div>
            
            <!-- 图片设置 -->
            <div class=editor-section>
                <h6>图片设置</h6>
                
                <multi-image-selector 
                    v-model="content.images"
                    :max="4"
                    :show-link="true"
                    :show-type="true>                </multi-image-selector>
            </div>
            
            <!-- 样式设置 -->
            <div class=editor-section>
                <h6>样式设置</h6>
                
                <div class="form-group">
                    <label>显示标题</label>
                    <div class=btn-group" role="group">
                        <button type="button" 
                                class="btn btn-sm" 
                                :class="content.settings.show_title ?btn-primary' : 'btn-outline-primary'"
                                @click="content.settings.show_title = true">
                            显示
                        </button>
                        <button type="button" 
                                class="btn btn-sm" 
                                :class=!content.settings.show_title ?btn-primary' : 'btn-outline-primary'"
                                @click="content.settings.show_title = false">
                            隐藏
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>布局方式</label>
                    <select v-model="content.settings.layout" class="form-control">
                        <option value="grid">网格布局</option>
                        <option value="list">列表布局</option>
                        <option value="slider">轮播布局</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</script>

<script>
Vue.component('custom-module-editor,[object Object]template: #custom-module-editor',
    props: ['content'],
    watch: [object Object]       content: {
            handler: function(val)[object Object]              this.$emit('update', val);
            },
            deep: true
        }
    },
    mounted() [object Object]
        // 初始化默认值
        if (!this.content.settings) [object Object]            this.$set(this.content, 'settings',[object Object]              show_title: true,
                show_subtitle: true,
                layout: grid       });
        }
    }
});
</script>
```

#### 5. 创建前台模块模板

创建 `Views/front/modules/custom_module.blade.php`：

```blade
<div id=module-{{ $module_id }}" class="module-item custom-module">
    <div class=module-content">
        @if($content['settings'][show_title'] && $content['title'])
            <div class="module-title>
                <h2>{{ $contenttitle2
                @if($content['subtitle'])
                    <p class="subtitle>{{$content['subtitle'] }}</p>
                @endif
            </div>
        @endif
        
        @if(!empty($content['images]))
            <div class="custom-content layout-{{ $content['settings'][>
                @foreach($content['images'] as $image)
                    <div class="custom-item">
                        <div class="image-wrapper">
                            @if($image['link'])
                                <a href={{$image['link'] }}" 
                                   @if($image['type'] ==product) target="_blank" @endif>
                                    <img src="{{ $image[image'] }}" alt="自定义图片">
                                </a>
                            @else
                                <img src="{{ $image[image'] }}" alt="自定义图片">
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    
    @if($design)
        <div class="module-edit">
            <div class="edit"><i class=bi bi-pencil"></i></div>
            <div class="delete"><i class="bi bi-trash"></i></div>
            <div class="up><iclass="bi bi-arrow-up"></i></div>
            <div class="down><iclass="bi bi-arrow-down"></i></div>
        </div>
    @endif
</div>

<style>
.custom-module[object Object]
    padding: 200
}

.custom-module .module-title[object Object]text-align: center;
    margin-bottom: 30px;
}

.custom-module .module-title h2[object Object]
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 10px;
}

.custom-module .subtitle[object Object]
    font-size: 16x;
    color: #666;
}

.custom-content.layout-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1r));
    gap: 20px;
}

.custom-content.layout-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.custom-content.layout-slider {
    position: relative;
    overflow: hidden;
}

.custom-item {
    border-radius: 8  overflow: hidden;
    box-shadow: 0 2px 8 rgba(0,0,0);
}

.custom-item .image-wrapper img {
    width:100;
    height: auto;
    display: block;
}

/* 响应式设计 */
@media (max-width: 768   .custom-content.layout-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap:15px;
    }
    
    .custom-module .module-title h2 {
        font-size: 24px;
    }
}
</style>
```

#### 6. 注册模块到主页面

在 `Views/design/index.blade.php` 中引入模块编辑器：

```blade
{{-- 在其他模块编辑器引入后添加 --}}
@include('PageBuilder::design.editors.custom_module)
```

#### 7 测试模块功能
1. **访问设计器**：后台管理 → 设计 → 页面构建器
2. **添加模块**：从左侧模块库拖拽"自定义模块到预览区
3 **编辑模块**：点击模块进入编辑模式，测试各项功能
4 **预览效果**：检查前台展示效果和响应式布局5. **保存测试**：保存页面并检查数据是否正确存储

#### 8. 模块数据结构说明

```php
// 完整的模块数据结构
$module =   code => 'custom_module,           // 模块代码
   module_id'  => 'custom_123456,           // 模块唯一ID
   name'       => '自定义模块',               // 模块名称
   title      => 模块',               // 模块标题
   content                   // 模块内容
       title'    => '模块标题,              // 多语言标题
        subtitle' => '模块副标题,            // 多语言副标题
     images                   // 图片数组
           
              image' => path/to/image.jpg', // 图片路径
               link'  => 'https://example.com', // 链接地址
          type'  => 'product           // 链接类型
            ]
        ],
     settings                   // 设置选项
            show_title'    => true,           // 是否显示标题
            show_subtitle' => true,           // 是否显示副标题
            layout=> grid          // 布局方式
        ]
    ],
   view_path => 'PageBuilder::front.modules.custom_module // 模板路径
];
```

#### 9 开发注意事项

1 **命名规范**：
   - 模块代码使用小写字母和下划线
   - 文件名使用小写字母和下划线
   - Vue组件名使用连字符分隔

2. **数据验证**：
   - 在编辑器中添加必要的数据验证
   - 设置合理的默认值
   - 处理空数据的情况

3 **样式设计**：
   - 使用响应式设计
   - 遵循设计规范
   - 考虑不同设备的显示效果

4 **性能优化**：
   - 合理使用Vue的computed和watch
   - 避免不必要的DOM操作
   - 优化图片加载

#### 10. 常见问题解决

**Q: 模块编辑器不显示？**
A: 检查Vue组件是否正确注册，确认模板ID是否匹配。

**Q: 前台模板不渲染？**
A: 检查模板路径是否正确，确认数据格式是否匹配。

**Q: 样式不生效？**
A: 检查CSS选择器是否正确，确认样式文件是否加载。

**Q: 多语言不显示？**
A: 确认使用了`self::languagesFill()`方法，检查语言包配置。

## 扩展开发

### Hook 系统

#### 数据钩子
```php
// 注册数据钩子
listen_hook_filter('admin.design.preview.data, function ($viewData) {
    // 修改预览数据
    return $viewData;
});
```

#### 流程钩子
```php
// 注册流程钩子
listen_hook_action('admin.design.module.saved', function ($module) [object Object]  // 模块保存后的处理
});
```

### 自定义服务

#### 创建服务类
```php
<?php
namespace Plugin\PageBuilder\Services;

class CustomService
{
    public function processModule($module)
    [object Object]        // 自定义处理逻辑
        return $module;
    }
}
```

#### 注册服务
```php
// 在 Boot.php 中注册
$this->app->singleton(CustomService::class);
```

## 调试指南

### 前端调试
```javascript
// 开启 Vue 调试
Vue.config.devtools = true;

// 调试模块更新
console.log(Module updated:, val);

// 调试 AJAX 请求
axios.interceptors.request.use(config =>[object Object]
    console.log(Request:', config);
    return config;
});
```

### 后端调试
```php
// 调试模块数据
Log::info('Module data:', $module);

// 调试预览数据
dd($viewData);
```

### 性能优化

#### 前端优化
- 使用 `v-show` 替代 `v-if` 减少DOM操作
- 合理使用 `computed` 和 `watch`
- 图片懒加载和压缩

#### 后端优化
- 数据库查询优化
- 缓存机制
- 异步处理

## 测试指南

### 单元测试
```php
<?php
namespace Tests\Unit\PageBuilder;

use Tests\TestCase;
use Plugin\PageBuilder\Services\PageBuilderService;

class PageBuilderServiceTest extends TestCase
{
    public function test_get_page_data()
    [object Object]        $service = new PageBuilderService();
        $data = $service->getPageData(home;
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('modules', $data);
    }
}
```

### 功能测试
```php
<?php
namespace Tests\Feature\PageBuilder;

use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    public function test_preview_module()
    {
        $response = $this->post('/panel/pbuilder/home/modules/preview, [
           code => show',
          module_id' => test_123'
        ]);
        
        $response->assertStatus(20);
    }
}
```

## 部署指南

### 生产环境配置
```php
// 关闭调试模式
APP_DEBUG=false

// 启用缓存
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 静态资源编译
```bash
# 编译前端资源
npm run build

# 压缩CSS和JS
npm run production
```

## 常见问题

### Q: 模块预览不更新？
A: 检查 AJAX 请求是否成功，确认 `previewWindow` 对象存在。

### Q: 拖拽功能不工作？
A: 确认 Sortable.js 已正确加载，检查 DOM 元素是否存在。

### Q: 样式不生效？
A: 检查 CSS 文件是否正确加载，确认选择器优先级。

### Q: 多语言不显示？
A: 确认语言包文件存在，检查语言切换逻辑。

## 贡献指南

### 代码规范
- 遵循 PSR-12 编码规范
- 使用类型提示和返回值类型
- 编写完整的注释文档

### 提交规范
```
feat: 添加新功能
fix: 修复bug
docs: 更新文档
style: 代码格式调整
refactor: 代码重构
test: 添加测试
chore: 构建过程或辅助工具的变动
```

### 分支管理
- `main`: 主分支，稳定版本
- `develop`: 开发分支
- `feature/*`: 功能分支
- `hotfix/*`: 热修复分支

---

**PageBuilder 开发团队** - 让开发更高效！ 