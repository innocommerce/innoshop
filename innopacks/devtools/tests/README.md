# InnoShop DevTools Module Tests

This directory contains test cases for the InnoShop DevTools module, which provides development utilities and scaffolding tools.

## Directory Structure

```
tests/
├── Console/            # Console command tests
├── Services/           # Service layer tests
├── TestCase.php        # Base test class (extends Common)
└── README.md
```

## Test Coverage

### Console

- **MakeControllerTest**: Tests controller generation command
  - Creates controller
  - Creates panel controller
  - Creates front controller
  - Validates controller name
  - Outputs success message
  - Handles existing controller
  - Creates controller with methods
  - Creates controller with request

- **MakeModelTest**: Tests model generation command
  - Creates model
  - Creates model with migration
  - Creates model with factory
  - Creates model with traits
  - Validates model name
  - Outputs success message
  - Handles existing model
  - Creates translatable model
  - Creates model with relationships

- **MakePluginTest**: Tests plugin generation command
  - Creates plugin directory
  - Creates plugin boot file
  - Creates plugin composer.json
  - Outputs success message
  - Handles existing plugin
  - Creates plugin with controllers
  - Creates plugin with models
  - Creates plugin with views
  - Validates plugin name

- **MakeThemeTest**: Tests theme generation command
  - Creates theme directory
  - Creates theme info file
  - Creates theme assets
  - Outputs success message
  - Handles existing theme
  - Creates theme with views
  - Creates theme with layouts
  - Validates theme name

- **ValidatePluginTest**: Tests plugin validation command
  - Validates plugin structure
  - Validates plugin manifest
  - Validates plugin dependencies
  - Validates plugin files
  - Outputs validation results
  - Returns exit code for errors
  - Validates plugin namespace
  - Validates plugin compatibility

- **ValidateThemeTest**: Tests theme validation command
  - Validates theme structure
  - Validates theme info
  - Validates theme assets
  - Validates theme views
  - Outputs validation results
  - Returns exit code for errors
  - Validates theme compatibility
  - Validates theme required files

### Services

- **ScaffoldServiceTest**: Tests scaffolding service
  - Creates plugin structure
  - Creates theme structure
  - Generates controller
  - Generates model
  - Generates repository
  - Generates service
  - Handles existing files
  - Uses templates
  - Replaces placeholders
  - Writes file

- **ValidationServiceTest**: Tests validation service
  - Validates plugin
  - Validates theme
  - Validates structure
  - Validates manifest
  - Validates dependencies
  - Validates files
  - Gets validation errors
  - Has validation errors
  - Outputs validation report

- **MarketplaceServiceTest**: Tests marketplace service
  - Gets available plugins
  - Gets available themes
  - Searches marketplace
  - Gets plugin details
  - Gets theme details
  - Downloads plugin
  - Downloads theme
  - Gets categories
  - Gets featured items
  - Has API endpoint

- **PackageServiceTest**: Tests package management service
  - Installs plugin
  - Installs theme
  - Uninstalls plugin
  - Uninstalls theme
  - Updates plugin
  - Updates theme
  - Runs migrations
  - Rolls back migrations
  - Clears cache
  - Publishes assets

## Running Tests

### Run All DevTools Tests

```bash
php artisan test innopacks/devtools/tests
```

### Run Specific Test File

```bash
php artisan test innopacks/devtools/tests/Console/MakePluginTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_creates_plugin_directory innopacks/devtools/tests/Console/MakePluginTest.php
```

### Run Tests by Directory

```bash
php artisan test innopacks/devtools/tests/Console/
php artisan test innopacks/devtools/tests/Services/
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

1. All tests extend from InnoShop\DevTools\Tests\TestCase
2. TestCase extends InnoShop\Common\Tests\TestCase
3. Chinese translator is initialized by default
4. Tests use mock objects for external dependencies
5. Tests follow naming conventions: PascalCase + Test suffix for classes, snake_case + test_ prefix for methods
6. Console command tests often use placeholder assertions (assertTrue(true)) as they test file generation
7. Scaffold service tests validate code generation without actual file I/O
8. Validation tests check structural requirements for plugins and themes

## Contributing Guidelines

1. Extend from DevTools TestCase when writing new tests
2. Test method names should clearly express their purpose
3. Each test method should test only one functionality
4. Use mock objects for external dependencies
5. Maintain test code readability and maintainability
6. Test both success and failure scenarios for scaffolding
7. Test validation rules thoroughly
8. Test error handling and edge cases
