# PageBuilder 编辑器UI优化规范

## 概述

本文档定义了PageBuilder编辑器的统一UI风格规范，确保所有编辑器组件具有一致的用户体验。

## 文件结构重新整理

### 文件职责分工

```
Public/css/design/
├── base.css              # 基础样式和全局变量
├── header.css            # 头部导航栏样式
├── sidebar.css           # 侧边栏和编辑面板样式
├── preview.css           # 预览区域和设备切换样式
├── components.css        # 通用组件样式
├── editor-unified.css    # 统一编辑器样式规范
└── README.md            # 本文档
```

### 各文件详细职责

#### 1. base.css - 基础样式
- CSS变量定义（颜色、阴影、边框等）
- 全局页面布局样式
- 基础动画定义（spin、pulse、fadeIn）
- 响应式设计基础规则

#### 2. header.css - 头部样式
- 导航栏布局和样式
- 保存状态指示器
- 设备切换按钮（PC/平板/手机）
- 操作按钮（保存、预览等）
- 响应式头部适配

#### 3. sidebar.css - 侧边栏样式
- 侧边栏容器样式
- 模块库列表
- 模块编辑面板
- 模块导航和返回按钮
- 搜索和分类功能
- 响应式侧边栏适配

#### 4. preview.css - 预览区域样式
- 预览iframe样式
- 不同设备类型的预览效果
- 手机/平板/PC的设备框架
- 预览区域的响应式适配

#### 5. components.css - 通用组件样式
- 拖拽相关样式
- 模块编辑按钮
- 图片选择器
- 商品编辑器
- 幻灯片编辑器
- 各种表单组件
- 加载状态和动画
- 空状态样式
- 编辑器模板公共样式

#### 6. editor-unified.css - 统一编辑器样式规范
- 编辑器容器基础样式
- 区域标题统一样式
- 分段按钮和选项按钮
- 控制组和设置提示
- 输入框样式优化
- 预览元素样式
- 响应式设计规范
- 主题色彩变量

## 样式优先级和依赖关系

```
base.css (基础变量和布局)
    ↓
header.css (头部样式)
sidebar.css (侧边栏样式)  
preview.css (预览区域样式)
    ↓
components.css (通用组件)
editor-unified.css (编辑器规范)
    ↓
具体编辑器模板 (如 left-image-right-text.blade.php)
```

## 重复样式清理

### 已移除的重复定义

1. **动画定义** - 统一放在 `base.css` 中
   - `@keyframes spin` - 从 `components.css` 和 `editor-unified.css` 移除
   - `@keyframes fadeIn` - 从 `editor-unified.css` 移除

2. **编辑器样式** - 统一放在 `editor-unified.css` 中
   - `.section-title` - 从 `components.css` 移除重复定义
   - `.segmented-btn` - 从 `components.css` 移除重复定义
   - `.setting-tip` - 从 `components.css` 移除重复定义
   - `.el-input-number` - 从 `editor-unified.css` 移除重复定义

3. **通用组件样式** - 保留在 `components.css` 中
   - `.empty-state` - 保留在 `components.css`
   - `.loading-overlay` - 保留在 `components.css`
   - `.loading-spinner` - 保留在 `components.css`

## 优化内容

### 1. 统一编辑器结构

所有编辑器应遵循以下结构：

```html
<template id="module-editor-xxx-template">
  <div class="xxx-editor">
    <div class="top-spacing"></div>
    
    <!-- 模块宽度设置 -->
    <div class="editor-section">
      <div class="section-title">
        <i class="el-icon-monitor"></i>
        模块宽度
      </div>
      <div class="section-content">
        <!-- 内容 -->
      </div>
    </div>
    
    <!-- 其他设置区域 -->
  </div>
</template>
```

### 2. 样式规范

#### 2.1 区域标题
- 使用图标 + 文字的组合
- 左侧蓝色边框装饰
- 统一的字体大小和颜色

```css
.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 12px;
  padding-left: 12px;
  border-left: 3px solid #667eea;
  display: flex;
  align-items: center;
  gap: 8px;
}
```

#### 2.2 分段按钮
- 圆角设计
- 渐变背景效果
- 悬停和激活状态

```css
.segmented-btn {
  padding: 10px 12px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.segmented-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}
```

#### 2.3 选项按钮
- 卡片式设计
- 预览效果
- 统一的交互反馈

```css
.option-btn {
  padding: 10px;
  border: 2px solid #e1e5e9;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.option-btn.active {
  border-color: #667eea;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
}
```

### 3. 颜色规范

#### 3.1 主色调
- 主色：`#667eea`
- 渐变色：`linear-gradient(135deg, #667eea 0%, #764ba2 100%)`
- 文字主色：`#2c3e50`
- 文字次色：`#6c757d`

#### 3.2 边框颜色
- 默认：`#e1e5e9`
- 悬停：`#667eea`
- 分割线：`#f0f0f0`

### 4. 交互效果

#### 4.1 悬停效果
- 颜色变化
- 阴影效果
- 平滑过渡

#### 4.2 激活状态
- 渐变背景
- 阴影效果
- 颜色强调

#### 4.3 动画效果
- 淡入动画
- 平滑过渡
- 微交互反馈

### 5. 响应式设计

#### 5.1 移动端适配
- 垂直布局
- 更大的点击区域
- 简化的交互

#### 5.2 断点设置
- 768px 以下使用移动端样式
- 保持功能完整性
- 优化用户体验

## 使用指南

### 1. 引入样式文件

在编辑器模板中引入统一样式：

```html
<link rel="stylesheet" href="/plugin/PageBuilder/Public/css/design/editor-unified.css">
```

### 2. 使用标准类名

- `.editor-section` - 编辑器区域
- `.section-title` - 区域标题
- `.setting-group` - 设置组
- `.segmented-buttons` - 分段按钮
- `.option-buttons` - 选项按钮

### 3. 遵循命名规范

- 编辑器容器：`{module-name}-editor`
- 特定样式：使用模块前缀
- 避免全局样式污染

## 最佳实践

### 1. 结构清晰
- 按功能分组
- 逻辑顺序排列
- 清晰的层级关系

### 2. 交互友好
- 即时反馈
- 状态明确
- 操作简单

### 3. 视觉统一
- 一致的间距
- 统一的颜色
- 协调的字体

### 4. 性能优化
- 避免重复样式
- 合理使用CSS变量
- 优化选择器性能

## 更新日志

### v1.1.0 (2024-01-XX)
- 重新整理文件结构，消除重复样式
- 统一动画定义到 base.css
- 明确各文件职责分工
- 优化样式优先级和依赖关系

### v1.0.0 (2024-01-XX)
- 创建统一编辑器样式规范
- 优化左右图文编辑器UI
- 建立响应式设计标准
- 定义颜色和交互规范

## 维护说明

1. 所有编辑器样式变更应遵循本规范
2. 新增编辑器应参考现有实现
3. 定期检查和更新样式一致性
4. 收集用户反馈持续优化
5. 避免在不同文件中重复定义相同样式
6. 新增样式时应考虑放在合适的文件中 