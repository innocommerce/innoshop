# InnoShop 开发者工具包 (DevTools)

InnoShop 开发者工具包是一个强大的命令行工具集，用于快速开发、验证和发布 InnoShop 插件和主题。它提供了完整的脚手架生成、规范验证和市场发布功能。

## 📋 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [快速开始](#快速开始)
- [命令详解](#命令详解)
- [使用示例](#使用示例)
- [最佳实践](#最佳实践)
- [常见问题](#常见问题)

## ✨ 功能特性

### 代码生成
- **插件脚手架生成**：一键生成完整的插件目录结构和基础文件
- **主题脚手架生成**：快速创建主题基础结构
- **组件生成**：支持生成控制器、模型、服务、仓储、迁移文件等

### 规范验证
- **插件验证**：检查插件是否符合 InnoShop 开发规范
- **主题验证**：验证主题配置和结构
- **详细报告**：提供清晰的错误和警告信息

### 市场发布
- **自动打包**：基于 `config.json` 自动创建发布包
- **规范检查**：发布前自动验证插件/主题规范
- **一键上传**：直接发布到 InnoShop 官方市场

## 🚀 安装

DevTools 已集成到 InnoShop 系统中，无需额外安装。如果作为独立 Composer 包使用：

```bash
composer require innoshop/devtools
```

## 📖 快速开始

### 创建你的第一个插件

```bash
# 生成基础插件
php artisan dev:make-plugin MyFirstPlugin --type=feature

# 生成包含控制器和模型的插件
php artisan dev:make-plugin PaymentGateway --type=billing --with-controller --with-model --with-migration
```

### 创建主题

```bash
php artisan dev:make-theme modern_shop
```

### 验证插件

```bash
php artisan dev:validate-plugin plugins/MyFirstPlugin
```

### 发布到市场

```bash
php artisan dev:publish-plugin plugins/MyFirstPlugin
```

## 📚 命令详解

### 代码生成命令

#### `dev:make-plugin` / `devtools:make-plugin`

生成完整的插件脚手架。

**参数：**
- `name` (必需): 插件名称，使用 PascalCase（如：`StripePayment`）

**选项：**
- `--type`: 插件类型，可选值：
  - `feature` - 功能模块（默认）
  - `marketing` - 营销工具
  - `billing` - 支付方式
  - `shipping` - 物流方式
  - `fee` - 订单费用
  - `social` - 社交登录
  - `language` - 语言包
  - `translator` - 翻译工具
  - `intelli` - AI模型
- `--with-controller`: 生成控制器文件
- `--with-model`: 生成模型文件
- `--with-migration`: 生成数据库迁移文件（需要配合 `--with-model`）
- `--name-zh`: 中文名称
- `--name-en`: 英文名称
- `--description-zh`: 中文描述
- `--description-en`: 英文描述

**示例：**

```bash
# 基础插件
php artisan dev:make-plugin MyPlugin

# 完整插件（包含控制器、模型和迁移）
php artisan dev:make-plugin PaymentPlugin \
    --type=billing \
    --with-controller \
    --with-model \
    --with-migration \
    --name-zh="支付插件" \
    --name-en="Payment Plugin" \
    --description-zh="支持多种支付方式" \
    --description-en="Support multiple payment methods"

# 使用完整命令名
php artisan devtools:make-plugin MyPlugin
```

**生成的文件结构：**

```
MyPlugin/
├── Boot.php                    # 插件启动类
├── config.json                 # 插件配置
├── fields.php                  # 配置字段定义
├── Controllers/                # 控制器目录
├── Models/                     # 模型目录
├── Services/                   # 服务类目录
├── Repositories/               # 仓储类目录
├── Routes/                     # 路由文件
│   ├── panel.php
│   └── front.php
├── Views/                      # 视图文件
├── Lang/                       # 语言包
│   ├── en/
│   │   ├── common.php
│   │   ├── panel.php
│   │   └── front.php
│   └── zh-cn/
│       ├── common.php
│       ├── panel.php
│       └── front.php
└── Database/
    └── Migrations/             # 数据库迁移
```

#### `dev:make-theme` / `devtools:make-theme`

生成主题脚手架。

**参数：**
- `name` (必需): 主题名称，使用 snake_case（如：`modern_shop`）

**选项：**
- `--name-zh`: 中文名称
- `--name-en`: 英文名称
- `--description-zh`: 中文描述
- `--description-en`: 英文描述

**示例：**

```bash
php artisan dev:make-theme modern_shop \
    --name-zh="现代商店" \
    --name-en="Modern Shop" \
    --description-zh="现代化的电商主题" \
    --description-en="Modern e-commerce theme"
```

**生成的文件结构：**

```
modern_shop/
├── config.json                 # 主题配置
├── views/                      # 视图文件
│   └── layout.blade.php
└── public/                     # 公共资源
    ├── css/
    ├── js/
    └── images/
```

#### `dev:make-controller` / `devtools:make-controller`

为现有插件生成控制器。

**参数：**
- `name` (必需): 控制器名称，格式：`PluginName/ControllerName`

**选项：**
- `--plugin`: 插件路径（如果不在 `plugins` 目录下）

**示例：**

```bash
php artisan dev:make-controller MyPlugin/ProductController
php artisan dev:make-controller MyPlugin/Admin/UserController --plugin=/path/to/plugin
```

#### `dev:make-model` / `devtools:make-model`

为现有插件生成模型。

**参数：**
- `name` (必需): 模型名称，格式：`PluginName/ModelName`

**选项：**
- `--plugin`: 插件路径

**示例：**

```bash
php artisan dev:make-model MyPlugin/Product
```

#### `dev:make-service` / `devtools:make-service`

为现有插件生成服务类。

**参数：**
- `name` (必需): 服务名称，格式：`PluginName/ServiceName`

**示例：**

```bash
php artisan dev:make-service MyPlugin/PaymentService
```

#### `dev:make-repository` / `devtools:make-repository`

为现有插件生成仓储类。

**参数：**
- `name` (必需): 仓储名称，格式：`PluginName/RepositoryName`

**选项：**
- `--model` (必需): 关联的模型名称

**示例：**

```bash
php artisan dev:make-repository MyPlugin/ProductRepo --model=Product
```

#### `dev:make-migration` / `devtools:make-migration`

为现有插件生成数据库迁移文件。

**参数：**
- `name` (必需): 迁移名称（如：`create_users_table`）

**选项：**
- `--table`: 表名（如果不从迁移名称中提取）
- `--plugin`: 插件路径（如果不在插件目录中运行）

**示例：**

```bash
# 从插件目录运行
cd plugins/MyPlugin
php artisan dev:make-migration create_products_table

# 指定插件路径
php artisan dev:make-migration create_products_table \
    --table=products \
    --plugin=plugins/MyPlugin
```

### 验证命令

#### `dev:validate-plugin` / `devtools:validate-plugin`

验证插件是否符合 InnoShop 开发规范。

**参数：**
- `path` (必需): 插件路径（相对或绝对路径）

**验证项：**
- ✅ `config.json` 格式和必需字段
- ✅ `Boot.php` 存在性和格式
- ✅ 目录结构是否符合规范
- ✅ 命名规范（PascalCase）
- ✅ 语言包完整性

**示例：**

```bash
# 使用相对路径
php artisan dev:validate-plugin plugins/MyPlugin

# 使用绝对路径
php artisan dev:validate-plugin /path/to/MyPlugin
```

**输出示例：**

```
Validating plugin: /path/to/plugins/MyPlugin...
✓ Plugin validation passed!

Warnings:
  - Missing recommended directory: Services
```

#### `dev:validate-theme` / `devtools:validate-theme`

验证主题是否符合规范。

**参数：**
- `path` (必需): 主题路径

**示例：**

```bash
php artisan dev:validate-theme themes/modern_shop
```

### Git 初始化命令

#### `dev:set-gitea-token` / `devtools:set-gitea-token`

保存 Gitea API token 以便后续使用，避免每次都需要输入 token。

**参数：**
- `token` (可选): Gitea API token（如果不提供，会提示输入）

**选项：**
- `--gitea-url`: Gitea 服务器地址（默认：`https://innoshop.work`）
- `--storage`: 保存到 `storage/app/.gitea_token` 文件而不是 `.env` 文件
- `--clear`: 清除已保存的 token

**功能：**
- 默认将 token 保存到 `.env` 文件（`GITEA_TOKEN` 和 `GITEA_URL`）
- 使用 `--storage` 选项可保存到 `storage/app/.gitea_token` 文件（JSON 格式）
- 后续使用 `dev:init-git` 命令时会自动从 `.env` 或 `storage` 读取保存的 token
- `.env` 文件已在 `.gitignore` 中，不会被提交到版本控制

**示例：**

```bash
# 保存 token 到 .env 文件（推荐）
php artisan dev:set-gitea-token your_gitea_token

# 保存 token（交互式输入，更安全）
php artisan dev:set-gitea-token

# 保存 token 到 storage 目录
php artisan dev:set-gitea-token your_gitea_token --storage

# 清除已保存的 token
php artisan dev:set-gitea-token --clear
```

**获取 Gitea Token：**
1. 登录 https://innoshop.work
2. 进入 设置 → 应用 → 生成新令牌
3. 选择权限：`write:repository` 和 `read:repository`
4. 复制生成的 token

#### `dev:init-git` / `devtools:init-git`

初始化插件的 Git 仓库并推送到 innoshop.work。

**参数：**
- `plugin` (必需): 插件文件夹名称

**选项：**
- `--gitea-url`: Gitea 服务器地址（默认：从 `.env` 或 `storage` 读取，或 `https://innoshop.work`）
- `--gitea-token`: Gitea API Token（默认：从 `.env` 或 `storage` 读取）
- `--org`: 组织名称（默认：`splugins`）
- `--private`: 创建私有仓库（默认：true）
- `--commit-message`: 提交信息（默认：`Initial commit`）
- `--force`: 强制重新初始化已存在的 Git 仓库

**功能：**
1. 初始化 Git 仓库（如果不存在）
2. 设置远程仓库地址：`git@innoshop.work:splugins/{plugin}.git`
3. 通过 API 创建远程仓库（如果提供了 token 或已保存 token）
4. 添加所有文件并创建初始提交
5. 推送到远程仓库的 `main` 分支

**示例：**

```bash
# 使用已保存的 token（推荐）
php artisan dev:init-git Wintopay

# 使用命令行参数指定 token
php artisan dev:init-git Wintopay \
    --gitea-token=your_gitea_token

# 自定义提交信息
php artisan dev:init-git Wintopay \
    --commit-message="Initial commit: Wintopay plugin"

# 强制重新初始化
php artisan dev:init-git Wintopay --force
```

**提示：**
- 如果已使用 `dev:set-gitea-token` 保存了 token，可以直接运行 `dev:init-git` 而无需提供 `--gitea-token` 参数
- Token 会自动从 `.env` 文件（`GITEA_TOKEN`）或 `storage/app/.gitea_token` 文件读取
- 读取优先级：命令行参数 > `.env` 文件 > `storage/app/.gitea_token` 文件

### 发布命令

#### `dev:publish-plugin` / `devtools:publish-plugin`

打包并发布插件到 InnoShop 官方市场。

**参数：**
- `path` (可选): 插件路径，默认为当前目录

**选项：**
- `--dry-run`: 仅创建包，不上传到市场
- `--skip-validation`: 跳过验证步骤

**发布流程：**
1. 验证插件规范（除非使用 `--skip-validation`）
2. 读取 `config.json` 获取插件信息
3. 创建 ZIP 包（排除 `.git`、`node_modules` 等）
4. 上传到市场 API
5. 显示发布结果

**示例：**

```bash
# 从插件目录运行
cd plugins/MyPlugin
php artisan dev:publish-plugin

# 指定插件路径
php artisan dev:publish-plugin plugins/MyPlugin

# 仅打包，不上传（用于测试）
php artisan dev:publish-plugin --dry-run

# 跳过验证（不推荐）
php artisan dev:publish-plugin --skip-validation
```

**输出示例：**

```
Validating plugin...
✓ Plugin validation passed!
Package: my_plugin v1.0.0
Creating package...
Package created: /path/to/storage/app/temp_packages/my_plugin-v1.0.0.zip
Uploading to marketplace...
✓ Plugin published successfully!
```

#### `dev:publish-theme` / `devtools:publish-theme`

打包并发布主题到市场。

**参数和选项：** 与 `publish-plugin` 相同

**示例：**

```bash
php artisan dev:publish-theme themes/modern_shop
```

#### `dev:init-git` / `devtools:init-git`

初始化插件的 Git 仓库并推送到 innoshop.work。

**参数：**
- `plugin` (必需): 插件文件夹名称

**选项：**
- `--gitea-url`: Gitea 服务器地址（默认：`https://innoshop.work`）
- `--gitea-token`: Gitea API Token（创建仓库时需要）
- `--org`: 组织名称（默认：`splugins`）
- `--private`: 创建私有仓库（默认：true）
- `--commit-message`: 提交信息（默认：`Initial commit`）
- `--force`: 强制重新初始化已存在的 Git 仓库

**功能：**
1. 初始化 Git 仓库（如果不存在）
2. 设置远程仓库地址：`git@innoshop.work:splugins/{plugin}.git`
3. 通过 API 创建远程仓库（如果提供了 token）
4. 添加所有文件并创建初始提交
5. 推送到远程仓库的 `main` 分支

**示例：**

```bash
# 基础用法（需要手动创建远程仓库）
php artisan dev:init-git Wintopay

# 自动创建远程仓库
php artisan dev:init-git Wintopay \
    --gitea-token=your_gitea_token

# 自定义提交信息
php artisan dev:init-git Wintopay \
    --gitea-token=your_gitea_token \
    --commit-message="Initial commit: Wintopay plugin"

# 强制重新初始化
php artisan dev:init-git Wintopay \
    --gitea-token=your_gitea_token \
    --force
```

**获取 Gitea Token：**
1. 登录 https://innoshop.work
2. 进入 设置 → 应用 → 生成新令牌
3. 选择权限：`write:repository` 和 `read:repository`
4. 复制生成的 token

## 💡 使用示例

### 完整开发流程

#### 1. 创建插件

```bash
php artisan dev:make-plugin BlogSystem \
    --type=feature \
    --with-controller \
    --with-model \
    --with-migration \
    --name-zh="博客系统" \
    --name-en="Blog System"
```

#### 2. 添加更多组件

```bash
# 生成服务类
php artisan dev:make-service BlogSystem/BlogService

# 生成仓储类
php artisan dev:make-repository BlogSystem/BlogRepo --model=Blog

# 生成额外的控制器
php artisan dev:make-controller BlogSystem/Admin/BlogController
```

#### 3. 验证插件

```bash
php artisan dev:validate-plugin plugins/BlogSystem
```

#### 4. 初始化 Git 并推送到远程

```bash
# 初始化 Git 并推送到 innoshop.work
php artisan dev:init-git BlogSystem \
    --gitea-token=your_gitea_token \
    --commit-message="Initial commit: Blog System plugin"
```

#### 5. 发布插件

```bash
# 先测试打包
php artisan dev:publish-plugin plugins/BlogSystem --dry-run

# 确认无误后正式发布
php artisan dev:publish-plugin plugins/BlogSystem
```

### 主题开发流程

```bash
# 1. 创建主题
php artisan dev:make-theme elegant_store \
    --name-zh="优雅商店" \
    --name-en="Elegant Store"

# 2. 验证主题
php artisan dev:validate-theme themes/elegant_store

# 3. 发布主题
php artisan dev:publish-theme themes/elegant_store
```

### 完整开发流程（包含 Git）

```bash
# 1. 创建插件
php artisan dev:make-plugin MyPlugin --type=feature

# 2. 开发插件功能...

# 3. 初始化 Git 并推送
php artisan dev:init-git MyPlugin \
    --gitea-token=your_token \
    --commit-message="Initial commit"

# 4. 验证插件
php artisan dev:validate-plugin plugins/MyPlugin

# 5. 发布到市场
php artisan dev:publish-plugin plugins/MyPlugin
```

## 🎯 最佳实践

### 插件开发

1. **命名规范**
   - 插件名称使用 PascalCase：`StripePayment` ✅
   - 避免使用下划线或连字符：`stripe_payment` ❌

2. **目录结构**
   - 遵循标准目录结构
   - 使用推荐的目录名称（Controllers, Models, Services 等）

3. **配置文件**
   - 确保 `config.json` 包含所有必需字段
   - 提供完整的多语言名称和描述

4. **代码组织**
   - 使用 Service 层处理业务逻辑
   - 使用 Repository 层处理数据访问
   - Controller 保持轻量，只负责请求处理

5. **语言包**
   - 至少提供 `en` 和 `zh-cn` 语言包
   - 使用语言包而不是硬编码文本

### 发布前检查清单

- [ ] 运行 `dev:validate-plugin` 确保没有错误
- [ ] 检查 `config.json` 中的版本号
- [ ] 确保所有必需文件都存在
- [ ] 使用 `--dry-run` 测试打包
- [ ] 检查 ZIP 包内容是否正确

### 版本管理

发布前更新 `config.json` 中的版本号：

```json
{
    "version": "v1.0.1"
}
```

## ❓ 常见问题

### Q: 命令找不到？

**A:** 确保 ServiceProvider 已正确注册。运行：

```bash
php artisan package:discover
```

### Q: 如何自定义模板？

**A:** 模板文件位于 `innopacks/devtools/src/Templates/`，你可以修改这些模板文件来自定义生成的内容。

### Q: 发布失败怎么办？

**A:** 检查以下几点：
1. 确保已配置 `domain_token`（在系统设置中）
2. 检查网络连接
3. 使用 `--dry-run` 先测试打包
4. 查看错误信息，通常会有具体提示

### Q: 如何排除特定文件不被打包？

**A:** 在 `config/devtools.php` 中的 `exclude_patterns` 数组添加排除规则。

### Q: 支持哪些插件类型？

**A:** 支持的插件类型：
- `feature` - 功能模块
- `marketing` - 营销工具
- `billing` - 支付方式
- `shipping` - 物流方式
- `fee` - 订单费用
- `social` - 社交登录
- `language` - 语言包
- `translator` - 翻译工具
- `intelli` - AI模型

### Q: 可以在现有插件中使用这些命令吗？

**A:** 可以！所有 `make-*` 命令都支持为现有插件生成新文件。只需指定插件名称即可。

### Q: 命令前缀 `dev:` 和 `devtools:` 有什么区别？

**A:** 没有区别，两者完全等价。`dev:` 是简写形式，推荐使用；`devtools:` 是完整形式，用于向后兼容。

## 📝 配置文件

配置文件位于 `config/devtools.php`，可以自定义：

- **模板路径**：自定义模板文件位置
- **插件类型**：添加或修改支持的插件类型
- **排除模式**：自定义打包时排除的文件和目录

## 🔗 相关资源

- [InnoShop 开发规范](../docs/dev_standard.md)
- [插件开发指南](../docs/plugin_development.md)
- [主题开发指南](../docs/theme_development.md)

## 📄 许可证

本项目采用 [OSL 3.0](https://opensource.org/licenses/OSL-3.0) 许可证。

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

---

**InnoShop DevTools** - 让插件和主题开发更简单！🚀

