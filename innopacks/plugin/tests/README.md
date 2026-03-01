# InnoShop Plugin Module Tests

This directory contains test cases for the InnoShop Plugin module, which handles the plugin system for extending functionality.

## Directory Structure

```
tests/
├── Core/
│   └── Blade/           # Blade directives tests
├── Models/              # Model tests
├── Repositories/        # Repository tests
├── Services/            # Service tests
├── Feature/             # Feature/Integration tests
├── TestCase.php         # Base test class (extends Common)
└── README.md
```

## Test Coverage

### Core

- **PluginManagerTest**: Tests plugin manager
  - Discovers plugins
  - Loads plugins
  - Enables plugin
  - Disables plugin
  - Handles plugin dependencies
  - Gets enabled plugins
  - Gets disabled plugins
  - Gets all plugins
  - Gets plugin info
  - Validates plugin

- **PluginTest**: Tests plugin base class
  - Has boot method
  - Has routes method
  - Has views method
  - Has config method
  - Has migrations method
  - Has install method
  - Has uninstall method
  - Has settings
  - Has hooks
  - Gets name
  - Gets version
  - Gets description
  - Gets author

- **Blade/HookTest**: Tests Blade hook system
  - hookinsert directive
  - hookupdate directive
  - Registers hooks
  - Executes hooks
  - Adds hook
  - Removes hook
  - Gets hooks
  - Clears hooks
  - Has hook
  - Registers directives

### Models

- **PluginModelTest**: Tests plugin model
  - Has name attribute
  - Has version attribute
  - Has status attribute
  - Has is_enabled scope
  - Has is_disabled scope
  - Can be enabled
  - Can be disabled
  - Has settings relationship
  - Has dependencies
  - Has configuration

- **SettingTest**: Tests setting model
  - Belongs to plugin
  - Has key attribute
  - Has value attribute
  - Has type attribute
  - Has label attribute
  - Supports various types (string, boolean, integer, array)
  - Can be casted
  - Has validation rules

### Repositories

- **PluginRepoTest**: Tests plugin repository
  - Get all plugins
  - Get enabled plugins
  - Get disabled plugins
  - Find by name
  - Enable plugin
  - Disable plugin
  - Install plugin
  - Uninstall plugin
  - Search plugins
  - Get plugin by slug

- **SettingRepoTest**: Tests setting repository
  - Get plugin settings
  - Get setting by key
  - Update setting
  - Create setting
  - Delete setting
  - Batch update settings
  - Get setting value
  - Set setting value

### Services

- **PluginServiceTest**: Tests plugin service
  - Installs plugin
  - Uninstalls plugin
  - Enables plugin
  - Disables plugin
  - Runs plugin migrations
  - Rolls back migrations
  - Registers plugin routes
  - Registers plugin views
  - Registers plugin config
  - Validates plugin
  - Gets plugin dependencies
  - Checks dependencies

- **MarketplaceServiceTest**: Tests marketplace service
  - Gets available plugins
  - Searches marketplace
  - Gets plugin details
  - Downloads plugin
  - Installs from marketplace
  - Gets plugin version
  - Check for updates
  - Updates plugin
  - Gets categories
  - Gets featured plugins

### Feature Tests

- **PluginInstallationTest**: Plugin installation feature tests
  - Can install plugin
  - Plugin runs migrations
  - Plugin registers routes
  - Plugin registers views
  - Plugin can be enabled
  - Plugin can be disabled
  - Plugin can be uninstalled
  - Migrations rollback on uninstall
  - Plugin dependencies are installed
  - Validates plugin structure

- **PluginActivationTest**: Plugin activation feature tests
  - Enabled plugins load
  - Disabled plugins do not load
  - Plugin can be re-enabled
  - Plugin status is persisted
  - Multiple plugins can be enabled
  - Plugin order is respected
  - Plugin hooks fire on enable
  - Plugin hooks fire on disable
  - Cannot enable plugin with missing dependencies
  - Enabled plugin registers service provider

- **PluginSettingsTest**: Plugin settings feature tests
  - Plugin has settings
  - Can retrieve plugin settings
  - Can update plugin settings
  - Can create new setting
  - Can delete setting
  - Settings are validated
  - Settings support various types
  - Can batch update settings
  - Settings are persisted
  - Settings have default values

## Running Tests

### Run All Plugin Tests

```bash
php artisan test innopacks/plugin/tests
```

### Run Specific Test File

```bash
php artisan test innopacks/plugin/tests/Core/PluginManagerTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_disovers_plugins innopacks/plugin/tests/Core/PluginManagerTest.php
```

### Run Tests by Directory

```bash
php artisan test innopacks/plugin/tests/Core/
php artisan test innopacks/plugin/tests/Models/
php artisan test innopacks/plugin/tests/Repositories/
php artisan test innopacks/plugin/tests/Services/
php artisan test innopacks/plugin/tests/Feature/
```

## Test Environment Requirements

1. PHP Version Requirements
   - PHP >= 8.2

2. Required PHP Extensions
   - PDO
   - PDO_MySQL
   - OpenSSL
   - Mbstring
   - Tokenizer
   - XML
   - Ctype
   - JSON

3. Database
   - Test database configured in phpunit.xml
   - Migrations run for test environment

## Important Notes

1. All tests extend from InnoShop\Plugin\Tests\TestCase
2. TestCase extends InnoShop\Common\Tests\TestCase
3. Chinese translator is initialized by default
4. Tests use mock objects for external dependencies
5. Tests follow naming conventions: PascalCase + Test suffix for classes, snake_case + test_ prefix for methods
6. Plugin system tests are designed to work with the plugin architecture
7. Some feature tests are placeholders marked as true for structural purposes

## Contributing Guidelines

1. Extend from Plugin TestCase when writing new tests
2. Test method names should clearly express their purpose
3. Each test method should test only one functionality
4. Use mock objects for external dependencies
5. Maintain test code readability and maintainability
6. Test both success and failure scenarios
7. Test plugin lifecycle operations (install, enable, disable, uninstall)
8. Test plugin settings and configuration
