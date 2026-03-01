# InnoShop Panel Module Tests

This directory contains test cases for the InnoShop Panel module, which handles the admin panel functionality.

## Directory Structure

```
tests/
├── Controllers/       # Controller tests
├── Middleware/        # Middleware tests
├── Feature/           # Feature/Integration tests
├── TestCase.php       # Base test class (extends Common)
└── README.md
```

## Test Coverage

### Controllers

- **LoginControllerTest**: Tests admin login controller
  - View login page
  - Login with valid credentials
  - Cannot login with invalid credentials
  - Redirect authenticated admin
  - Logout clears session
  - Validate email and password
  - Remember me functionality

- **DashboardControllerTest**: Tests dashboard controller
  - Display dashboard
  - Show sales statistics
  - Show order count
  - Show product count
  - Show customer count
  - Show recent orders
  - Show revenue chart
  - Guest cannot access dashboard
  - Non-admin cannot access dashboard
  - Dashboard filters by date range

- **ProductControllerTest**: Tests product management controller
  - List products
  - Create product
  - Update product
  - Delete product
  - Validate product data
  - Handle product images
  - Clone product
  - Filter products
  - Search products
  - Bulk delete products
  - Bulk update status

- **CustomerControllerTest**: Tests customer management controller
  - List customers
  - View customer detail
  - Create customer
  - Update customer
  - Delete customer
  - View customer orders
  - View customer addresses
  - Search customers
  - Filter by status
  - Export customers

- **OrderControllerTest**: Tests order management controller
  - List orders
  - View order detail
  - Update order status
  - Add tracking number
  - Cancel order
  - Refund order
  - View order items
  - View order transactions
  - Filter by status
  - Filter by date
  - Search orders
  - Export orders
  - Print invoice
  - Send email to customer

- **SettingControllerTest**: Tests settings controller
  - View settings
  - Update general settings
  - Update store settings
  - Update SEO settings
  - Update email settings
  - Send test email
  - Update payment settings
  - Update shipping settings

- **ThemeControllerTest**: Tests theme management controller
  - View themes
  - Activate theme
  - Preview theme
  - Configure theme
  - Update theme settings
  - Upload theme
  - Delete theme

### Middleware

- **AdminAuthTest**: Tests admin authentication middleware
  - Allows authenticated admin
  - Redirects guest to login
  - Handles protected routes
  - Allows public admin routes
  - Requires admin role

- **SetPanelLocaleTest**: Tests locale middleware
  - Sets locale from session
  - Sets locale from query parameter
  - Falls back to default locale
  - Persists locale in session
  - Validates locale

### Feature Tests

- **ProductManagementTest**: End-to-end product management
  - Admin can create product
  - Admin can update product
  - Admin can delete product
  - Admin can upload product images
  - Admin can manage product variants
  - Admin can set product stock
  - Admin can set product pricing
  - Product appears on frontend
  - Admin can clone product
  - Admin can bulk update products

- **OrderManagementTest**: End-to-end order management
  - Admin can view order list
  - Admin can view order detail
  - Admin can update order status
  - Admin can add tracking
  - Admin can cancel order
  - Admin can refund order
  - Customer receives order status email
  - Admin can print invoice
  - Admin can add order notes
  - Admin can modify order items
  - Admin can view order statistics

- **CustomerManagementTest**: End-to-end customer management
  - Admin can view customer list
  - Admin can view customer detail
  - Admin can create customer
  - Admin can update customer
  - Admin can delete customer
  - Admin can view customer orders
  - Admin can view customer addresses
  - Admin can add address for customer
  - Admin can search customers
  - Admin can filter customers by status
  - Admin can export customers
  - Admin can reset customer password

## Running Tests

### Run All Panel Tests

```bash
php artisan test innopacks/panel/tests
```

### Run Specific Test File

```bash
php artisan test innopacks/panel/tests/Controllers/ProductControllerTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_can_create_product innopacks/panel/tests/Controllers/ProductControllerTest.php
```

### Run Tests by Directory

```bash
php artisan test innopacks/panel/tests/Controllers/
php artisan test innopacks/panel/tests/Middleware/
php artisan test innopacks/panel/tests/Feature/
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

1. All tests extend from InnoShop\Panel\Tests\TestCase
2. TestCase extends InnoShop\Common\Tests\TestCase
3. Chinese translator is initialized by default
4. Tests use mock objects for external dependencies
5. Tests follow naming conventions: PascalCase + Test suffix for classes, snake_case + test_ prefix for methods
6. Admin authentication is required for most routes

## Contributing Guidelines

1. Extend from Panel TestCase when writing new tests
2. Test method names should clearly express their purpose
3. Each test method should test only one functionality
4. Use Laravel's built-in testing helpers (get, post, put, delete, etc.)
5. Maintain test code readability and maintainability
6. Test both admin and non-admin scenarios where applicable
