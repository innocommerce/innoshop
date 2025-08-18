# Stripe支付插件

Stripe是一个全球领先的在线支付处理平台，支持多种货币和支付方式。它提供了强大的API和工具，帮助商家轻松集成支付功能。

## 概述

该插件为InnoShop和Stripe支付网关之间提供无缝集成。它支持Stripe Elements（站内支付）和Stripe Checkout（跳转支付）两种模式，确保满足不同业务需求的灵活性。

## 功能特性

- **多种支付方式**：支持信用卡、借记卡和各种本地支付方式
- **多货币支持**：支持多种货币支付并自动转换
- **PCI DSS合规**：采用安全最佳实践并符合PCI DSS标准
- **实时支付处理**：即时支付确认和订单状态更新
- **Webhook集成**：自动处理支付事件和通知
- **测试模式支持**：完整的沙盒环境用于上线前测试
- **安全支付表单**：现代化、响应式的Stripe Elements支付表单
- **详细错误处理**：全面的错误信息和日志记录
- **Webhook安全**：带签名验证的安全Webhook端点

## 系统要求

- InnoShop 0.5.0或更高版本
- PHP 8.2或更高版本
- SSL证书（用于生产环境交易）
- Stripe账户

## 安装

### 手动安装

1. 下载插件包
2. 将内容解压到您的InnoShop `plugins/Stripe` 目录
3. 确保目录结构如下：
   ```
   plugins/Stripe/
   ├── Boot.php
   ├── Controllers/
   ├── Lang/
   ├── Public/
   ├── Routes/
   ├── Services/
   ├── Views/
   ├── composer.json
   ├── config.json
   ├── fields.php
   ├── README.md
   └── README.zh-cn.md
   ```

### 启用插件

1. 登录您的InnoShop管理面板
2. 导航到 **扩展 > 插件**
3. 在插件列表中找到 **Stripe**
4. 点击 **安装** 然后点击 **启用**

## 目录结构

```
plugins/Stripe/
├── Boot.php                    # 主插件引导文件
├── Controllers/
│   └── StripeController.php    # 处理支付处理和回调
├── Lang/
│   ├── en/
│   │   └── common.php         # 英文语言翻译
│   └── zh-cn/
│       └── common.php         # 中文语言翻译
├── Public/
│   └── images/
│       └── logo.png            # 插件标志
├── Routes/
│   └── front.php              # 前端路由定义
├── Services/
│   └── StripeService.php      # Stripe API集成服务
├── Views/
│   └── payment.blade.php      # 支付表单视图
├── composer.json              # 插件依赖
├── config.json                # 插件配置
├── fields.php                 # 管理配置字段
├── README.md                  # 英文文档
└── README.zh-cn.md            # 本文档
```

## 配置

### 1. 基本配置 (`config.json`)

插件配置在 `config.json` 中定义：

```json
{
    "code": "stripe",
    "name": {
        "zh-cn": "Stripe 支付",
        "en": "Stripe"
    },
    "description": {
        "zh-cn": "Stripe 是一个全球领先的在线支付处理平台，支持多种货币和支付方式。",
        "en": "Stripe is a leading global online payment processing platform that supports multiple currencies and payment methods."
    },
    "type": "billing",
    "version": "1.0.0",
    "icon": "icon.png",
    "author": {
        "name": "InnoShop Team",
        "email": "team@innoshop.com",
        "homepage": "https://www.innoshop.com"
    }
}
```

### 2. 管理配置字段 (`fields.php`)

在管理面板中显示的配置字段：

```php
return [
    [
        'name'      => 'publishable_key',
        'label_key' => 'common.stripe.publishable_key',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|min:32',
    ],
    [
        'name'      => 'secret_key',
        'label_key' => 'common.stripe.secret_key',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|min:32',
    ],
    [
        'name'      => 'webhook_secret',
        'label_key' => 'common.stripe.webhook_secret',
        'type'      => 'string',
        'required'  => false,
    ],
    [
        'name'      => 'test_mode',
        'label_key' => 'common.stripe.test_mode',
        'type'      => 'select',
        'options' => [
            ['value' => '1', 'label_key' => 'common.stripe.enabled'],
            ['value' => '0', 'label_key' => 'common.stripe.disabled'],
        ],
        'required' => true,
    ],
    [
        'name'      => 'payment_mode',
        'label_key' => 'common.stripe.payment_mode',
        'type'      => 'select',
        'options' => [
            ['value' => 'elements', 'label_key' => 'common.stripe.on_site_payment'],
            ['value' => 'checkout', 'label_key' => 'common.stripe.redirect_payment'],
        ],
        'required' => true,
    ],
];
```

### 3. 多语言支持

该插件使用`label_key`而不是`label`来支持多语言。语言文件位于：

- 英文：`/lang/en/common/stripe.php`
- 中文：`/lang/zh-cn/common/stripe.php`
- 插件内部英文：`/plugins/Stripe/Lang/en/common.php`
- 插件内部中文：`/plugins/Stripe/Lang/zh-cn/common.php`

要添加其他语言的支持，请在相应语言目录下创建对应的文件，遵循相同的结构。

### 3. 必需配置项

| 设置项 | 描述 | 必需 |
|--------|------|------|
| `publishable_key` | 您的Stripe发布密钥（以`pk_test_`或`pk_live_`开头） | 是 |
| `secret_key` | 您的Stripe密钥（以`sk_test_`或`sk_live_`开头） | 是 |
| `webhook_secret` | 您的Stripe webhook端点密钥用于签名验证 | 推荐 |
| `test_mode` | 启用测试模式进行沙盒测试 | 是 |
| `payment_mode` | 在站内支付和跳转支付之间选择 | 是 |

### 4. 获取Stripe API凭证

获取您的Stripe API凭证：

1. **创建Stripe账户**
   - 访问 [Stripe.com](https://stripe.com)
   - 注册免费账户或登录现有账户

2. **获取您的API密钥**
   - 导航到 **开发者 > API密钥**
   - 复制您的 **发布密钥**（以`pk_test_`或`pk_live_`开头）
   - 复制您的 **密钥**（以`sk_test_`或`sk_live_`开头）

3. **设置Webhooks**（推荐）
   - 转到 **开发者 > Webhooks**
   - 添加新的webhook端点
   - 将端点URL设置为：`https://yourstore.com/plugins/stripe/webhook`
   - 选择以下事件：
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `invoice.payment_succeeded`（如果使用订阅）
   - 复制 **Webhook密钥** 用于签名验证

4. **在InnoShop中配置**
   - 转到 **扩展 > 插件 > Stripe > 设置**
   - 输入您的API密钥和webhook密钥
   - 将 **测试模式** 设置为 **启用** 用于测试
   - 选择您首选的 **支付模式**
   - 保存配置

## 使用方法

### 对于客户

1. 将商品添加到购物车并进入结账流程
2. 选择 **Stripe** 作为支付方式
3. 输入支付详情：
   - **Stripe Elements**：直接在结账页面输入卡信息
   - **Stripe Checkout**：您将被重定向到Stripe的安全结账页面
4. 完成支付
5. 您将被重定向回商店并显示订单确认

### 对于店主

1. **监控支付**：在管理面板中查看所有Stripe交易
2. **处理退款**：直接从订单详情页面处理退款
3. **查看报告**：访问详细的支付报告和分析
4. **管理争议**：通过Stripe仪表板处理拒付和争议

## Webhook配置

Webhooks对于处理以下事件至关重要：
- 支付确认
- 支付失败
- 退款
- 争议

### 要配置的Webhook事件

| 事件 | 描述 |
|------|------|
| `payment_intent.succeeded` | 支付成功完成 |
| `payment_intent.payment_failed` | 支付失败 |
| `invoice.payment_succeeded` | 订阅支付成功 |
| `customer.subscription.created` | 创建新订阅 |
| `customer.subscription.deleted` | 订阅取消 |

## 测试

### 测试模式

上线前：

1. 在插件设置中启用 **测试模式**
2. 使用Stripe的测试卡号：
   - **成功**：`4242424242424242`（Visa）
   - **拒绝**：`4000000000000002`（通用拒绝）
   - **3D安全**：`4000002760003184`（需要身份验证）

3. 测试各种场景：
   - 成功支付
   - 失败支付
   - 退款
   - Webhook处理

### 测试Webhooks

1. 使用Stripe CLI进行本地webhook测试：
   ```bash
   stripe login
   stripe listen --forward-to localhost:8000/plugins/stripe/webhook
   ```

2. 在本地测试webhook事件

## 安全最佳实践

1. **仅使用HTTPS**：始终为生产环境交易使用HTTPS
2. **Webhook安全**：验证webhook签名以防止欺诈
3. **API密钥安全**：切勿在客户端代码中暴露密钥
4. **PCI合规**：使用Stripe Elements减少PCI合规范围
5. **定期更新**：保持插件更新以获取安全补丁

## 故障排除

### 常见问题

1. **"无效的API密钥"**
   - 验证您的API密钥是否正确
   - 检查测试/生产密钥是否与模式设置匹配

2. **"Webhook错误"**
   - 确保webhook URL可公开访问
   - 验证webhook密钥是否正确配置
   - 检查webhook事件是否正确选择

3. **"支付失败"**
   - 检查Stripe仪表板获取详细错误信息
   - 验证客户的卡信息
   - 审查欺诈预防设置

### 调试模式

在插件设置中启用调试日志以排除问题：

1. 转到 **扩展 > 插件 > Stripe > 设置**
2. 启用 **调试模式**
3. 在 **系统 > 错误日志** 中检查错误日志

## 技术支持

如需技术支持和帮助：

- **文档**：[InnoShop文档](https://docs.innoshop.com)
- **社区**：[InnoShop论坛](https://forum.innoshop.com)
- **邮箱**：[team@innoshop.com](mailto:team@innoshop.com)
- **GitHub**：[InnoShop GitHub](https://github.com/innoshop)

## 贡献

我们欢迎贡献！请查看我们的[贡献指南](CONTRIBUTING.md)了解详情。

## 更新日志

### 版本 1.0.0
- 初始版本
- Stripe Elements集成
- Stripe Checkout集成
- Webhook支持
- 多货币支持
- PCI DSS合规

## 许可证

该插件基于开放软件许可证3.0版（OSL-3.0）授权。有关详细信息，请参见[LICENSE](LICENSE)文件。

---

**注意**：该插件使用官方[Stripe PHP库](https://github.com/stripe/stripe-php)进行安全支付处理。