# PayPal 支付插件

## 概述

PayPal 支付插件旨在将 PayPal 支付网关集成到 InnoShop 平台中，允许客户使用他们的 PayPal 账户支付订单。该插件为商家和客户提供了一种安全可靠的支付方式。

## 功能特性

- 与 PayPal 支付网关无缝集成
- 支持多种 PayPal 支付方式（例如 PayPal Checkout、PayPal Express Checkout）
- 安全处理支付交易，支持 PayPal IPN 验证
- 通过 InnoShop 管理面板轻松配置
- 支持多种货币
- 详细的支付交易日志记录
- 支持沙盒测试环境，便于开发和测试
- 自动处理支付回调和状态更新

## 安装

1. 从 InnoShop 插件市场或 GitHub 仓库下载 PayPal 支付插件
2. 将插件文件上传到 InnoShop 安装目录下的 `/plugins/Paypal` 目录
3. 确保插件目录结构正确，包含必要的配置文件和代码文件
4. 导航到 InnoShop 管理面板，进入 `插件` > `支付方式`
5. 找到 PayPal 支付插件并点击 `安装`
6. 配置插件设置，包括您的 PayPal API 凭据

### 插件目录结构

PayPal 支付插件具有以下目录结构：

```
/plugins/Paypal
├── Boot.php
├── Controllers/
│   └── PaypalController.php
├── Lang/
│   ├── en/
│   │   └── common.php
│   └── zh-cn/
│       └── common.php
├── Public/
│   └── images/
│       └── logo.png
├── Routes/
│   └── front.php
├── Services/
│   └── PaypalService.php
├── Views/
│   └── payment.blade.php
├── composer.json
├── config.json
├── fields.php
├── README.md
└── README.zh-cn.md
```

每个文件都有特定的用途：
- `Boot.php`：主插件类，用于初始化插件与 InnoShop 的集成
- `Controllers/PaypalController.php`：处理支付处理和回调
- `Lang/en/common.php` 和 `Lang/zh-cn/common.php`：包含插件设置的语言翻译
- `Public/images/logo.png`：插件标志图片
- `Routes/front.php`：定义插件的前端路由
- `Services/PaypalService.php`：实现 PayPal API 集成
- `Views/payment.blade.php`：提供支付表单视图
- `composer.json`：定义插件的依赖关系
- `config.json`：包含插件的基本配置信息
- `fields.php`：定义在管理面板中显示的插件配置字段

`config.json` 文件包含基本插件信息，例如：

```json
{
    "code": "paypal",
    "name": {
        "zh-cn": "PayPal",
        "en": "PayPal"
    },
    "description": {
        "zh-cn": "PayPal是一个全球领先的在线支付平台，支持多种货币和支付方式。",
        "en": "PayPal is a global leader in online payment solutions, supporting multiple currencies and payment methods."
    },
    "type": "billing",
    "version": "v1.1.1",
    "icon": "/images/logo.png",
    "author": {
        "name": "InnoShop",
        "email": "team@innoshop.com"
    }
}
```

`fields.php` 文件定义了将在管理面板中显示的配置字段，例如：

```php
return [
    [
        'name'      => 'sandbox_client_id',
        'label_key' => 'common.paypal.sandbox_client_id',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'sandbox_secret',
        'label_key' => 'common.paypal.sandbox_secret',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'live_client_id',
        'label_key' => 'common.paypal.live_client_id',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'live_secret',
        'label_key' => 'common.paypal.live_secret',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'currency',
        'label_key' => 'common.paypal.currency',
        'type'      => 'select',
        'required'  => true,
        'rules'     => 'required|size:3',
        'options'   => $currencies,
    ],
    [
        'name'      => 'sandbox_mode',
        'label_key' => 'common.paypal.sandbox_mode',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.paypal.enabled'],
            ['value' => '0', 'label_key' => 'common.paypal.disabled'],
        ],
        'required' => true,
    ],
];
```

这些配置字段将通过 InnoShop 管理面板的插件配置页面进行设置和管理。

## 多语言支持

该插件使用 `label_key` 属性支持多语言标签。语言文件位于以下目录中：

- 全局语言文件：`/lang/en/common/paypal.php` 和 `/lang/zh-cn/common/paypal.php`
- 插件特定语言文件：`/plugins/Paypal/Lang/en/common.php` 和 `/plugins/Paypal/Lang/zh-cn/common.php`

要添加对其他语言的支持，请在 `/plugins/Paypal/Lang/` 下创建一个新目录，使用语言代码（例如 `fr` 表示法语）并添加一个包含翻译标签的 `common.php` 文件。

## 配置

该插件需要以下配置设置：

- `sandbox_client_id`：PayPal 沙盒环境 API 客户端 ID
- `sandbox_secret`：PayPal 沙盒环境 API 客户端密钥
- `live_client_id`：PayPal 生产环境 API 客户端 ID
- `live_secret`：PayPal 生产环境 API 客户端密钥
- `currency`：用于交易的货币
- `sandbox_mode`：设置为 `true` 以使用 PayPal 沙盒环境，或设置为 `false` 用于生产环境

这些设置可以通过 InnoShop 管理面板的插件配置页面进行配置。配置字段在 `fields.php` 文件中定义，插件的基本信息在 `config.json` 文件中定义。

### 获取 PayPal API 凭据

要获取 PayPal API 凭据，请按照以下步骤操作：

1. 访问 [PayPal Developer Portal](https://developer.paypal.com/)
2. 登录您的 PayPal 账户或创建一个新账户（如果您没有）
3. 导航到 "My Apps & Credentials" 部分
4. 创建一个新应用或选择现有应用
5. 在应用详情下，您将找到沙盒环境和生产环境的客户端 ID 和密钥
6. 复制这些凭据并粘贴到 InnoShop 管理面板的插件配置页面中
7. 将 `sandbox_mode` 设置为 `true` 用于测试，或设置为 `false` 用于生产环境

### 配置说明

插件的配置字段在 `fields.php` 文件中定义，该文件返回配置字段定义数组。这些字段将自动显示在 InnoShop 管理面板的插件配置页面中。

`fields.php` 示例：

```php
return [
    [
        'name'      => 'sandbox_client_id',
        'label_key' => 'common.paypal.sandbox_client_id',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'sandbox_secret',
        'label_key' => 'common.paypal.sandbox_secret',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'live_client_id',
        'label_key' => 'common.paypal.live_client_id',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'live_secret',
        'label_key' => 'common.paypal.live_secret',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'currency',
        'label_key' => 'common.paypal.currency',
        'type'      => 'select',
        'required'  => true,
        'rules'     => 'required|size:3',
        'options'   => $currencies,
    ],
    [
        'name'      => 'sandbox_mode',
        'label_key' => 'common.paypal.sandbox_mode',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.paypal.enabled'],
            ['value' => '0', 'label_key' => 'common.paypal.disabled'],
        ],
        'required' => true,
    ],
];
```

插件的基本信息在 `config.json` 文件中定义，其中包含插件的代码、名称、描述、类型、版本、图标和作者信息。

`config.json` 示例：

```json
{
    "code": "paypal",
    "name": {
        "zh-cn": "PayPal",
        "en": "PayPal"
    },
    "description": {
        "zh-cn": "PayPal是一个全球领先的在线支付平台，支持多种货币和支付方式。",
        "en": "PayPal is a global leader in online payment solutions, supporting multiple currencies and payment methods."
    },
    "type": "billing",
    "version": "v1.1.1",
    "icon": "/images/logo.png",
    "author": {
        "name": "InnoShop",
        "email": "team@innoshop.com"
    }
}
```

通过管理面板设置这些值后，插件将能够通过 PayPal API 进行身份验证并处理支付。

## 使用方法

安装并配置 PayPal 支付插件后，客户将能够在结账过程中选择 PayPal 作为支付方式。插件将安全地处理支付交易并相应地更新订单状态。

## 优化建议

1. **增强安全性**：实施额外的安全措施，如 IPN（即时支付通知）验证，以确保 PayPal 支付通知的真实性
2. **改进错误处理**：添加更详细的错误消息和日志记录，以帮助商家诊断和解决支付问题
3. **支持订阅**：扩展插件以支持经常性支付和订阅，允许商家提供基于订阅的产品
4. **多语言支持**：添加对多种语言的支持，使插件对全球受众更具可访问性
5. **性能优化**：优化插件的代码和数据库查询，以提高性能并减少延迟
6. **全面的文档**：提供详细的文档和使用示例，以帮助商家更轻松地集成和配置插件

### 实现细节

要实现 PayPal 支付插件，以下组件协同工作：

1. **插件初始化**：`Boot.php` 文件初始化插件与 InnoShop 的集成，并设置支付处理所需的钩子
2. **路由定义**：`Routes/front.php` 文件定义插件的前端路由，如支付处理和回调 URL
3. **支付控制器**：`Controllers/PaypalController.php` 文件处理支付处理和回调。该控制器使用 `PaypalService` 与 PayPal API 交互
4. **PayPal 服务**：`Services/PaypalService.php` 文件实现 PayPal API 集成。该服务处理支付请求的创建、支付响应的处理以及 IPN 通知的验证
5. **插件配置**：`config.json` 文件包含基本插件信息，而 `fields.php` 文件定义在管理面板中显示的配置字段
6. **视图和翻译**：`Views/payment.blade.php` 文件提供支付表单视图，而 `Lang/` 目录中的语言文件提供插件设置的翻译

## 贡献

我们欢迎社区的贡献。如果您有任何建议或改进，请随时提交拉取请求或在我们的 GitHub 仓库上开启一个议题。

## 许可证

该插件根据 OSL-3.0 许可证授权。有关更多信息，请参见 [LICENSE](LICENSE) 文件。

## 支持

如需支持，请联系我们的团队 [support@innoshop.com](mailto:support@innoshop.com) 或访问我们的网站 [https://www.innoshop.com](https://www.innoshop.com)。