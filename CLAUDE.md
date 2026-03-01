# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

InnoShop is an open-source e-commerce system built on Laravel 12 with PHP 8.2+. It features a modular "Innopacks" architecture, plugin system, multi-language/currency support, and AI integration.

## Development Commands

### PHP/Laravel
```bash
# Run the application
php artisan serve

# Run tests
php artisan test
php artisan test --filter=TestName

# Code quality
composer pint              # Run Laravel Pint (PHP CS Fixer)
composer phpstan           # Run PHPStan on plugins and innopacks
composer phpstan:plugins   # Run PHPStan on plugins only
composer phpstan:innopacks # Run PHPStan on innopacks only

# Database
php artisan migrate
php artisan db:seed

# Cache clearing (important after route/config changes)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Frontend Assets (Laravel Mix)
```bash
# Install dependencies
npm install

# Development builds
npm run dev          # Build once
npm run watch        # Watch for changes
npm run hot          # Hot module replacement

# Production build
npm run production

# Build specific theme
THEME=petnow npm run dev
```

## Architecture Overview

### Innopacks Modular System

The codebase is organized into modular packages under `/innopacks/`:

- **common/** - Shared models, repositories, services, and traits used across all modules
- **front/** - Frontend controllers, views, and routes for the customer-facing store
- **panel/** - Admin panel controllers, views, and routes for backend management
- **restapi/** - RESTful API endpoints (front-api.php, panel-api.php)
- **plugin/** - Plugin management system core
- **install/** - Installation wizard
- **enterprise/** - Enterprise-specific features
- **devtools/** - Development tools and utilities

### Key Architectural Patterns

**Repository Pattern**: All data access goes through repository classes in `innopacks/common/src/Repositories/`. Example: `ProductRepo::getInstance()->getFrontList($filters)`.

**Multi-language Support**: Models use the `Translatable` trait. Translation tables (e.g., `product_translations`) store localized content with locale columns.

**Database Prefix**: All tables use the `inno_` prefix (configured in `.env` as `DB_PREFIX=inno_`).

**Custom Route Groups**: The application defines custom middleware groups in `bootstrap/app.php`:
- `front` - Frontend web requests
- `panel` - Admin panel web requests
- `front_api` - Frontend API requests (with Sanctum)
- `panel_api` - Admin panel API requests (with Sanctum)

### Plugin System

Plugins are located in `/plugins/` and are auto-discovered. Each plugin:
- Has its own directory with `composer.json` (merged via wikimedia/composer-merge-plugin)
- Uses the `Plugin\` namespace
- Can define routes, views, controllers, and service providers
- Can be enabled/disabled through the admin panel

### Theme System

Themes are in `/themes/` (e.g., `petnow`, `thangka`). The build system (`webpack.mix.js`):
- Compiles theme assets from `themes/{name}/css/` and `themes/{name}/js/`
- Outputs to `public/static/themes/{name}/`
- Copies assets to theme's `public/` directory for distribution
- Set `THEME` environment variable to build a specific theme

### Code Quality Standards

- **Laravel Pint**: Uses Laravel preset with custom array alignment rules (see `pint.json`)
- **PHPStan**: Level 1 analysis for `plugins/` and `innopacks/` directories
- **Testing**: PHPUnit with separate Unit and Feature test suites
- **File Headers**: All PHP files (including test files) MUST include the standard copyright header:
```php
<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
```
- **Test File Organization**: Test files should only contain tests relevant to their module. For example, `innopacks/install/tests/` should only contain installation-related tests.

### Helper Functions

Helper functions are defined in multiple locations and are auto-loaded via composer.json:
- `innopacks/install/helpers.php` - Installation-related helpers
- `innopacks/common/helpers.php` - Common helpers: `front_route()`, `panel_route()`, `is_admin()`, `current_locale()`
- `innopacks/panel/helpers.php` - Admin panel helpers
- `innopacks/plugin/helpers.php` - Plugin system helpers

### Model Conventions

- All models extend `InnoShop\Common\Models\BaseModel`
- Use `HasPackageFactory` trait for factory support
- Use `Translatable` trait for multi-language models
- Relationships use explicit foreign keys
- JSON/array columns use Laravel casts

### Repository Conventions

- All repositories extend `InnoShop\Common\Repositories\BaseRepo`
- Use singleton pattern: `ProductRepo::getInstance()`
- Methods return models, collections, or paginators
- Filter parsing via `RequestFilterParser` service

---

# Plugin Development Guide

## Correct Plugin Directory Structure

```
plugins/PluginName/
├── Boot.php                  # Required: Plugin entry class
├── config.json               # Required: Plugin configuration (NOT composer.json)
├── fields.php                # Optional: Configuration field definitions
├── README.md                 # Recommended: Plugin documentation
├── Controllers/              # Optional: Controllers (Front/Panel subdirectories)
│   ├── Front/                # Frontend controllers
│   └── Panel/                # Admin panel controllers
├── Services/                 # Optional: Service classes
├── Models/                   # Optional: Eloquent models
├── Repositories/             # Optional: Data repositories
├── Routes/                   # Optional: Route definitions (PascalCase "Routes", NOT "routes")
│   ├── front.php             # Frontend routes
│   └── panel.php             # Admin panel routes
├── Migrations/               # Optional: Database migrations (PascalCase "Migrations", NOT "database/migrations")
├── Lang/                     # Required: Language files
│   ├── en/
│   │   └── common.php        # English translations
│   └── zh-cn/
│       └── common.php        # Simplified Chinese translations
├── Views/                    # Optional: Blade templates
│   └── front/                # Frontend views
└── Public/                   # Optional: Public assets (css, js, images)
```

## Common Mistakes to Avoid

### 0. README file naming (IMPORTANT!)
**⚠️ CRITICAL**: Use DOT notation for localized README files!

The plugin system expects `README.{localeCode}.md` format (dot, not underscore):

```
✅ CORRECT:
- README.md        (default, English)
- README.zh-cn.md  (Simplified Chinese)
- README.en.md     (English)

❌ WRONG:
- README_zh-cn.md (underscore won't work!)
- README_CN.md    (underscore won't work!)
```

The system automatically detects the current locale and loads the appropriate README file.

### 1. DO NOT use `composer.json` for plugins
- **Wrong**: Creating `composer.json` in plugin root
- **Correct**: Use `config.json` for plugin metadata
- **Exception**: Only create `composer.json` if the plugin has external PHP dependencies

### 2. Directory naming conventions
- **Wrong**: `routes/`, `database/migrations/`, `lang/`
- **Correct**: `Routes/`, `Migrations/`, `Lang/` (PascalCase)

### 3. Required files
Every plugin MUST have:
- `Boot.php` - Plugin entry class
- `config.json` - Plugin configuration
- `Lang/en/common.php` - English translations
- `Lang/zh-cn/common.php` - Chinese translations

### 4. Route registration
Routes are defined in `Routes/front.php` or `Routes/panel.php`, NOT in `Boot.php`. The plugin system auto-loads these files.

### 5. Language file access
```php
// In plugin files
trans('PluginName::common.key_name')

// In views
{{ trans('PluginName::common/key_name') }}
```

### 6. View naming
- Use PascalCase for plugin namespace: `PluginName::view.name`
- NOT lowercase: `plugin_name::view.name`

## Plugin Boot Class Template

```php
<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PluginName;

class Boot
{
    public function init(): void
    {
        // Register hooks, blade inserts, etc.
        listen_blade_insert('front.layout.app.head.bottom', function ($data) {
            // Your code here
        });
    }
}
```

## config.json Template

```json
{
    "code": "plugin_code",
    "name": {
        "zh-cn": "插件中文名称",
        "en": "Plugin English Name"
    },
    "description": {
        "zh-cn": "插件中文描述",
        "en": "Plugin English Description"
    },
    "type": "marketing",
    "version": "v1.0.0",
    "author": {
        "name": "InnoShop",
        "email": "team@innoshop.com"
    }
}
```

Plugin types: `feature`, `marketing`, `billing`, `shipping`, `fee`, `social`, `language`, `translator`, `intelli`

## fields.php Template (IMPORTANT: Multi-language support)

**⚠️ CRITICAL**: Use `label_key` instead of `label` for multi-language support!

The plugin system handles field labels with priority: `label` > `label_key`.
- If `label` exists, it's used directly (hardcoded, not translatable)
- If `label_key` exists, it's translated from language files

```php
// ❌ WRONG - Hardcoded English label (won't translate)
return [
    [
        'name'  => 'api_key',
        'label' => 'API Key',  // Always shows "API Key" regardless of language
        'type'  => 'string',
    ],
];

// ✅ CORRECT - Uses label_key for translation
return [
    [
        'name'      => 'api_key',
        'label_key' => 'api_key',  // Translates to Lang/en/common.php['api_key']
        'type'      => 'string',
    ],
];
```

**Corresponding language files:**

`Lang/en/common.php`:
```php
return [
    'api_key' => 'API Key',
];
```

`Lang/zh-cn/common.php`:
```php
return [
    'api_key' => 'API 密钥',
];
```

**Complete fields.php example:**

```php
<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    [
        'name'      => 'conversion_id',
        'label_key' => 'conversion_id',
        'type'      => 'string',
        'required'  => false,
        'rules'     => 'nullable|string',
    ],
    [
        'name'      => 'enabled',
        'label_key' => 'enabled',
        'type'      => 'bool',
        'default'   => true,
    ],
];
```

## Quick Reference Checklist

When creating a new plugin:

- [ ] Created plugin directory in `/plugins/PluginName/`
- [ ] Created `Boot.php` with proper namespace and copyright header
- [ ] Created `config.json` (NOT `composer.json`)
- [ ] Created `Lang/en/common.php` and `Lang/zh-cn/common.php`
- [ ] Created `fields.php` if plugin needs configuration
- [ ] Created `Routes/front.php` for frontend routes (if needed)
- [ ] Used PascalCase for directories: `Routes/`, `Migrations/`, `Lang/`, `Views/`
- [ ] Added copyright header to all PHP files
- [ ] Ran `composer pint` to check code style
- [ ] Ran `composer phpstan:plugins` to check for issues
