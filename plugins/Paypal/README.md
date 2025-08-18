# PayPal Payment Plugin

## Overview

The PayPal Payment Plugin is designed to integrate PayPal payment gateway into the InnoShop platform, allowing customers to pay for their orders using their PayPal accounts. This plugin provides a secure and reliable payment method for both merchants and customers.

## Features

- Seamless integration with PayPal's payment gateway.
- Support for multiple PayPal payment methods (e.g., PayPal Checkout, PayPal Express Checkout).
- Secure handling of payment transactions with PayPal IPN validation.
- Easy configuration through the InnoShop admin panel.
- Support for multiple currencies.
- Detailed logging for payment transactions.
- Support for sandbox testing environment for development and testing.
- Automatic handling of payment callbacks and status updates.

## Installation

1. Download the PayPal Payment Plugin from the InnoShop plugin marketplace or GitHub repository.
2. Upload the plugin files to the `/plugins/Paypal` directory in your InnoShop installation.
3. Ensure the plugin directory structure is correct with all necessary configuration and code files.
4. Navigate to the InnoShop admin panel and go to `Plugins` > `Payment Methods`.
5. Find the PayPal Payment Plugin and click `Install`.
6. Configure the plugin settings, including your PayPal API credentials.

### Plugin Directory Structure

The PayPal Payment Plugin has the following directory structure:

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

Each file serves a specific purpose:
- `Boot.php`: The main plugin class that initializes the plugin with InnoShop.
- `Controllers/PaypalController.php`: Handles payment processing and callbacks.
- `Lang/en/common.php` and `Lang/zh-cn/common.php`: Contains language translations for the plugin settings.
- `Public/images/logo.png`: The plugin logo image.
- `Routes/front.php`: Defines the plugin's frontend routes.
- `Services/PaypalService.php`: Implements the PayPal API integration.
- `Views/payment.blade.php`: Provides the payment form view.
- `composer.json`: Defines the plugin's dependencies.
- `config.json`: Contains the plugin's basic configuration information.
- `fields.php`: Defines the plugin's configuration fields that appear in the admin panel.

The `config.json` file contains basic plugin information such as:

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

The `fields.php` file defines the configuration fields that will appear in the admin panel, such as:

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

These configuration fields will be set and managed through the plugin configuration page in the InnoShop admin panel.

## Multilingual Support

The plugin supports multilingual labels using the `label_key` property. The language files are located in the following directories:

- Global language files: `/lang/en/common/paypal.php` and `/lang/zh-cn/common/paypal.php`
- Plugin-specific language files: `/plugins/Paypal/Lang/en/common.php` and `/plugins/Paypal/Lang/zh-cn/common.php`

To add support for additional languages, create a new directory under `/plugins/Paypal/Lang/` with the language code (e.g., `fr` for French) and add a `common.php` file with the translated labels.

## Configuration

The plugin requires the following configuration settings:

- `sandbox_client_id`: Your PayPal Sandbox API client ID.
- `sandbox_secret`: Your PayPal Sandbox API client secret.
- `live_client_id`: Your PayPal Live API client ID.
- `live_secret`: Your PayPal Live API client secret.
- `currency`: The currency to use for transactions.
- `sandbox_mode`: Set to `true` to use the PayPal sandbox environment, or `false` for production.

These settings can be configured through the plugin configuration page in the InnoShop admin panel. The configuration fields are defined in the `fields.php` file, and the basic plugin information is defined in the `config.json` file.

### Obtaining PayPal API Credentials

To obtain your PayPal API credentials, follow these steps:

1. Go to the [PayPal Developer Portal](https://developer.paypal.com/).
2. Log in to your PayPal account or create a new account if you don't have one.
3. Navigate to the "My Apps & Credentials" section.
4. Create a new app or select an existing app.
5. Under the app details, you will find your Client ID and Secret for both sandbox and live environments.
6. Copy these credentials and paste them into the plugin configuration page in the InnoShop admin panel.
7. Set the `sandbox_mode` to `true` for testing or `false` for production.

### Configuration Instructions

The plugin's configuration fields are defined in the `fields.php` file, which returns an array of configuration field definitions. These fields will be automatically displayed in the plugin configuration page in the InnoShop admin panel.

Example `fields.php`:

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

The plugin's basic information is defined in the `config.json` file, which contains the plugin's code, name, description, type, version, icon, and author information.

Example `config.json`:

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

After setting these values through the admin panel, the plugin will be able to authenticate with the PayPal API and process payments accordingly.

## Usage

Once the PayPal Payment Plugin is installed and configured, customers will be able to select PayPal as a payment method during the checkout process. The plugin will handle the payment transaction securely and update the order status accordingly.

## Optimization Suggestions

1. **Enhanced Security**: Implement additional security measures, such as IPN (Instant Payment Notification) validation, to ensure the authenticity of payment notifications from PayPal.
2. **Improved Error Handling**: Add more detailed error messages and logging to help merchants diagnose and resolve payment issues.
3. **Support for Subscriptions**: Extend the plugin to support recurring payments and subscriptions, allowing merchants to offer subscription-based products.
4. **Multi-Language Support**: Add support for multiple languages to make the plugin more accessible to a global audience.
5. **Performance Optimization**: Optimize the plugin's code and database queries to improve performance and reduce latency.
6. **Comprehensive Documentation**: Provide detailed documentation and usage examples to help merchants integrate and configure the plugin more easily.

### Implementation Details

To implement the PayPal Payment Plugin, the following components work together:

1. **Plugin Initialization**: The `Boot.php` file initializes the plugin with InnoShop and sets up necessary hooks for payment processing.
2. **Route Definition**: The `Routes/front.php` file defines the plugin's frontend routes, such as the payment processing and callback URLs.
3. **Payment Controller**: The `Controllers/PaypalController.php` file handles payment processing and callbacks. This controller uses the `PaypalService` to interact with the PayPal API.
4. **PayPal Service**: The `Services/PaypalService.php` file implements the PayPal API integration. This service handles the creation of payment requests, processing of payment responses, and validation of IPN notifications.
5. **Plugin Configuration**: The `config.json` file contains basic plugin information, while the `fields.php` file defines the configuration fields that appear in the admin panel.
6. **Views and Translations**: The `Views/payment.blade.php` file provides the payment form view, while the language files in the `Lang/` directory provide translations for the plugin settings.

## Contributing

We welcome contributions from the community. If you have any suggestions or improvements, please feel free to submit a pull request or open an issue on our GitHub repository.

## License

This plugin is licensed under the OSL-3.0 License. See the [LICENSE](LICENSE) file for more information.

## Support

For support, please contact our team at [support@innoshop.com](mailto:support@innoshop.com) or visit our website at [https://www.innoshop.com](https://www.innoshop.com).