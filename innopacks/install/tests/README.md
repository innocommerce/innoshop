# InnoShop Installer Tests

This directory contains test cases for the InnoShop installer.

## Directory Structure

```
tests/
├── Database/          # Database related tests
├── Environment/       # Environment check tests
├── InstallerTest.php  # Main installer tests
└── TestCase.php       # Base test class
```

## Test Coverage

### 1. Installer Tests (InstallerTest.php)

- MySQL Installation Tests
  - Database connection tests
  - Configuration generation tests
  - Environment check tests

- SQLite Installation Tests
  - Database connection tests
  - Configuration generation tests
  - Environment check tests

- Invalid Data Tests
  - Invalid database configuration tests
  - Invalid admin information tests

- Installation Status Checks
  - Installation status detection
  - File system operation tests

- Route Tests
  - Installer page route tests
  - Homepage route tests

### 2. Database Tests (Database/)

- MySQL database tests
- SQLite database tests

### 3. Environment Tests (Environment/)

- PHP version checks
- Required extension checks
- Directory permission checks

## Running Tests

### Run All Tests

```bash
php artisan test innopacks/install/tests
```

### Run Specific Test File

```bash
php artisan test innopacks/install/tests/InstallerTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_can_install_with_my_sql innopacks/install/tests/InstallerTest.php
```

## Test Environment Requirements

1. PHP Version Requirements
   - PHP >= 8.0

2. Required PHP Extensions
   - PDO
   - PDO_MySQL
   - PDO_SQLite
   - OpenSSL
   - Mbstring
   - Tokenizer
   - XML
   - Ctype
   - JSON

3. Directory Permissions
   - storage/ directory writable
   - bootstrap/cache/ directory writable
   - .env file writable

## Important Notes

1. Tests use mock objects and won't modify actual databases
2. Tests create temporary files and directories during execution
3. Temporary files are automatically cleaned up after tests
4. Ensure test environment is isolated from production

## Contributing Guidelines

1. Extend the `TestCase` class when writing new tests
2. Test method names should clearly express their purpose
3. Each test method should test only one functionality
4. Use `@dataProvider` for data-driven tests
5. Maintain test code readability and maintainability

## Language Support

- [English](README.md)
- [简体中文](README.zh-CN.md) 