# Stripe Payment Plugin

Stripe is a leading global online payment processing platform that supports multiple currencies and payment methods. It provides powerful APIs and tools to help merchants easily integrate payment functionalities.

## Overview

This plugin provides seamless integration between InnoShop and Stripe payment gateway. It supports both Stripe Elements (on-site payment) and Stripe Checkout (redirect payment) modes, ensuring flexibility for different business needs.

## 功能

- **Multiple Payment Methods**: Support for credit cards, debit cards, and various local payment methods
- **Multi-Currency Support**: Process payments in multiple currencies with automatic conversion
- **PCI DSS Compliant**: Built with security best practices and PCI DSS compliance
- **Real-time Payment Processing**: Instant payment confirmation and order status updates
- **Webhook Integration**: Automatic handling of payment events and notifications
- **Test Mode Support**: Full sandbox environment for testing before going live
- **Secure Payment Forms**: Modern, responsive payment forms with Stripe Elements
- **Detailed Error Handling**: Comprehensive error messages and logging
- **Webhook Security**: Secure webhook endpoint with signature verification

## 要求

- InnoShop 0.5.0或更高版本
- PHP 8.2或更高版本
- SSL证书（在线交易时必需）
- 拥有API访问权限的Stripe账户

## Installation

### Manual Installation

1. Download the plugin package
2. Extract the contents to your InnoShop `plugins/Stripe` directory
3. Ensure the directory structure is as follows:
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

### Enable the Plugin

1. Log in to your InnoShop admin panel
2. Navigate to **Extensions > Plugins**
3. Find **Stripe** in the plugin list
4. Click **Install** and then **Enable**

## Directory Structure

```
plugins/Stripe/
├── Boot.php                    # Main plugin bootstrap file
├── Controllers/
│   └── StripeController.php    # Handles payment processing and callbacks
├── Lang/
│   ├── en/
│   │   └── common.php         # English language translations
│   └── zh-cn/
│       └── common.php         # Chinese language translations
├── Public/
│   └── images/
│       └── logo.png            # Plugin logo
├── Routes/
│   └── front.php              # Frontend route definitions
├── Services/
│   └── StripeService.php      # Stripe API integration service
├── Views/
│   └── payment.blade.php      # Payment form view
├── composer.json              # Plugin dependencies
├── config.json                # Plugin configuration
├── fields.php                 # Admin configuration fields
├── README.md                  # This file
└── README.zh-cn.md            # Chinese documentation
```

Additionally, the multilingual support files are located in the main language directories:

- English: `/lang/en/common/stripe.php`
- Chinese: `/lang/zh-cn/common/stripe.php`
- Plugin-specific English: `/plugins/Stripe/Lang/en/common.php`
- Plugin-specific Chinese: `/plugins/Stripe/Lang/zh-cn/common.php`

## Configuration

### 1. 基本配置 (`config.json`)

插件配置在`config.json`中定义:

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

### 2. Admin Configuration Fields (`fields.php`)

Configuration fields displayed in the admin panel:

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

### 3. Multilingual Support

This plugin uses `label_key` instead of `label` to support multiple languages. The language files are located in:

- English: `/lang/en/common/stripe.php`
- Chinese: `/lang/zh-cn/common/stripe.php`

To add support for additional languages, create corresponding files in the respective language directories following the same structure.

### 3. Required Configuration Settings

| Setting | Description | Required |
|---------|-------------|----------|
| `publishable_key` | Your Stripe publishable key (starts with `pk_test_` or `pk_live_`) | Yes |
| `secret_key` | Your Stripe secret key (starts with `sk_test_` or `sk_live_`) | Yes |
| `webhook_secret` | Your Stripe webhook endpoint secret for signature verification | Yes (recommended) |
| `test_mode` | 启用测试模式进行沙盒测试 | Yes |
| `payment_mode` | 在站内支付和跳转支付之间选择 | Yes |

### 4. 获取Stripe API凭证

To get your Stripe API credentials:

1. **Create a Stripe Account**
   - Visit [Stripe.com](https://stripe.com)
   - Sign up for a free account or log in to your existing account

2. **Get Your API Keys**
   - Navigate to **Developers > API keys**
   - Copy your **Publishable key** (starts with `pk_test_` or `pk_live_`)
   - Copy your **Secret key** (starts with `sk_test_` or `sk_live_`)

3. **Set Up Webhooks** (Recommended)
   - Go to **Developers > Webhooks**
   - Add a new webhook endpoint
   - Set the endpoint URL to: `https://yourstore.com/plugins/stripe/webhook`
   - Select the following events:
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `invoice.payment_succeeded` (if using subscriptions)
   - Copy the **Webhook secret** for signature verification

4. **Configure in InnoShop**
   - Go to **Extensions > Plugins > Stripe > Settings**
   - Enter your API keys and webhook secret
   - Set **Test Mode** to **Enabled** for testing
   - Choose your preferred **Payment Mode**
   - Save the configuration

## 使用方法

### 对于客户

1. Add products to cart and proceed to checkout
2. Select **Stripe** as the payment method
3. Enter payment details:
   - **Stripe Elements**: Enter card details directly on the checkout page
   - **Stripe Checkout**: You'll be redirected to Stripe's secure checkout page
4. Complete the payment
5. You'll be redirected back to the store with order confirmation

### 对于店主

1. **Monitor Payments**: View all Stripe transactions in the admin panel
2. **Handle Refunds**: Process refunds directly from the order details page
3. **View Reports**: Access detailed payment reports and analytics
4. **Manage Disputes**: Handle chargebacks and disputes through Stripe dashboard

## Webhook配置

Webhooks are essential for handling events like:
- Payment confirmations
- Payment failures
- Refunds
- Disputes

### Webhook Events to Configure

| Event | Description |
|-------|-------------|
| `payment_intent.succeeded` | Payment completed successfully |
| `payment_intent.payment_failed` | Payment failed |
| `invoice.payment_succeeded` | Subscription payment succeeded |
| `customer.subscription.created` | New subscription created |
| `customer.subscription.deleted` | Subscription cancelled |

## 测试

### 测试模式

Before going live:

1. Enable **Test Mode** in plugin settings
2. Use Stripe's test card numbers:
   - **Success**: `4242424242424242` (Visa)
   - **Decline**: `4000000000000002` (Generic decline)
   - **3D Secure**: `4000002760003184` (Requires authentication)

3. Test various scenarios:
   - Successful payments
   - Failed payments
   - Refunds
   - Webhook handling

### 测试Webhooks

1. Use Stripe CLI for local webhook testing:
   ```bash
   stripe login
   stripe listen --forward-to localhost:8000/plugins/stripe/webhook
   ```

2. Test webhook events locally

## 安全最佳实践

1. **HTTPS Only**: Always use HTTPS for live transactions
2. **Webhook Security**: Verify webhook signatures to prevent fraud
3. **API Key Security**: Never expose secret keys in client-side code
4. **PCI Compliance**: Use Stripe Elements to reduce PCI compliance scope
5. **Regular Updates**: Keep the plugin updated for security patches

## 故障排除

### 常见问题

1. **"Invalid API Key"**
   - Verify your API keys are correct
   - Check if test/live keys match the mode setting

2. **"Webhook Error"**
   - Ensure webhook URL is publicly accessible
   - Verify webhook secret is correctly configured
   - Check webhook events are properly selected

3. **"Payment Failed"**
   - Check Stripe dashboard for detailed error messages
   - Verify customer's card details
   - Review fraud prevention settings

### 调试模式

Enable debug logging in plugin settings to troubleshoot issues:

1. Go to **Extensions > Plugins > Stripe > Settings**
2. Enable **Debug Mode**
3. Check error logs in **System > Error Logs**

## 技术支持

For support and assistance:

- **Documentation**: [InnoShop Docs](https://docs.innoshop.com)
- **Community**: [InnoShop Forum](https://forum.innoshop.com)
- **Email**: [team@innoshop.com](mailto:team@innoshop.com)
- **GitHub**: [InnoShop GitHub](https://github.com/innoshop)

## 贡献

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

## 更新日志

### 版本 1.0.0
- Initial release
- Stripe Elements integration
- Stripe Checkout integration
- Webhook support
- Multi-currency support
- PCI DSS compliance

## 许可证

This plugin is licensed under the Open Software License 3.0 (OSL-3.0). See [LICENSE](LICENSE) file for details.

---

**注意**：该插件使用官方[Stripe PHP库](https://github.com/stripe/stripe-php)进行安全支付处理。