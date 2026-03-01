# InnoShop RestAPI Module Tests

This directory contains test cases for the InnoShop RestAPI module, which handles RESTful API endpoints for both frontend customers and admin panel users.

## Directory Structure

```
tests/
├── FrontApiControllers/   # Frontend API controller tests
├── PanelApiControllers/   # Admin panel API controller tests
├── Middleware/            # API middleware tests
├── Feature/               # Feature/Integration tests
├── TestCase.php           # Base test class (extends Common + API helpers)
└── README.md
```

## Test Coverage

### Front API Controllers

- **AuthControllerTest**: Tests customer authentication API
  - Register customer
  - Login customer
  - Cannot login with invalid credentials
  - Logout customer
  - Refresh token
  - Get authenticated user
  - Validates registration data
  - Validates email format
  - Validates password confirmation

- **ProductControllerTest**: Tests product browsing API
  - List products
  - Show product
  - Filter products
  - Search products
  - Returns 404 for nonexistent product
  - Sort products
  - Paginate products
  - Filter by price range
  - Get featured products
  - Get new products
  - Get best-selling products
  - Include product images
  - Include product reviews
  - Filter by availability
  - Filter by brand

- **CartControllerTest**: Tests shopping cart API
  - Get cart
  - Add to cart
  - Update cart item
  - Remove cart item
  - Clear cart
  - Returns 401 for unauthenticated
  - Validates product_id
  - Validates quantity
  - Calculate cart totals
  - Apply coupon
  - Remove coupon

- **CheckoutControllerTest**: Tests checkout process API
  - Start checkout
  - Process checkout
  - Validates checkout data
  - Returns 401 for unauthenticated
  - Get available payment methods
  - Get available shipping methods
  - Calculate checkout totals
  - Validates cart not empty
  - Creates order on success

- **OrderControllerTest**: Tests order management API
  - List orders
  - Show order
  - Returns 401 for unauthenticated
  - Returns 404 for nonexistent order
  - Cannot view other customers' orders
  - Cancel order
  - Track order
  - Filter by status
  - Paginate orders
  - Reorder
  - Download invoice

- **AccountControllerTest**: Tests customer account API
  - Get profile
  - Update profile
  - Change password
  - Get addresses
  - Create address
  - Update address
  - Delete address
  - Set default address
  - Returns 401 for unauthenticated
  - Validates password change

### Panel API Controllers

- **AuthControllerTest**: Tests admin authentication API
  - Login admin
  - Cannot login with invalid credentials
  - Logout
  - Get authenticated admin
  - Returns 401 for unauthenticated
  - Validates email
  - Validates password

- **DashboardControllerTest**: Tests dashboard statistics API
  - Get statistics
  - Get sales data
  - Get orders data
  - Get products data
  - Get customers data
  - Get recent activities
  - Filter by date range
  - Returns 401 for unauthenticated
  - Returns 403 for non-admin

- **ProductControllerTest**: Tests product management API
  - List products
  - Show product
  - Create product
  - Update product
  - Delete product
  - Filter products
  - Search products
  - Paginate products
  - Returns 401 for unauthenticated
  - Validates product data
  - Bulk delete products

- **OrderControllerTest**: Tests order management API
  - List orders
  - Show order
  - Update order status
  - Add tracking
  - Cancel order
  - Refund order
  - Filter by status
  - Filter by date range
  - Search orders
  - Paginate orders
  - Returns 401 for unauthenticated
  - Export orders

- **CustomerControllerTest**: Tests customer management API
  - List customers
  - Show customer
  - Create customer
  - Update customer
  - Delete customer
  - View customer orders
  - Search customers
  - Filter by status
  - Paginate customers
  - Returns 401 for unauthenticated
  - Validates customer data
  - Export customers

### Middleware

- **ApiAuthTest**: Tests API authentication middleware
  - Allows authenticated customer
  - Allows authenticated admin
  - Returns 401 for unauthenticated
  - Returns 401 for invalid token
  - Requires customer token for front API
  - Requires admin token for panel API
  - Public endpoints do not require auth

- **RateLimitTest**: Tests API rate limiting middleware
  - Respects rate limit
  - Returns 429 when rate limit exceeded
  - Resets rate limit after timeout
  - Uses different limits for guest and authenticated
  - Headers include rate limit info

### Feature Tests

- **CustomerAuthenticationTest**: End-to-end customer auth flow
  - Customer can register via API
  - Customer can login via API
  - Returns token on successful login
  - Can use token to access protected routes
  - Can logout via API
  - Token is invalid after logout
  - Can refresh token
  - Can get current user

- **CheckoutFlowTest**: End-to-end checkout flow
  - Complete checkout flow
  - Checkout with new address
  - Checkout with coupon
  - Checkout creates order
  - Checkout clears cart

- **AdminManagementTest**: End-to-end admin management
  - Admin can manage products
  - Admin can manage orders
  - Admin can view dashboard
  - Admin can search products
  - Admin can filter orders
  - Admin can export data
  - Admin can manage customers
  - Customer cannot access panel API
  - Guest cannot access panel API

## Running Tests

### Run All RestAPI Tests

```bash
php artisan test innopacks/restapi/tests
```

### Run Specific Test File

```bash
php artisan test innopacks/restapi/tests/FrontApiControllers/AuthControllerTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_can_login_customer innopacks/restapi/tests/FrontApiControllers/AuthControllerTest.php
```

### Run Tests by Directory

```bash
php artisan test innopacks/restapi/tests/FrontApiControllers/
php artisan test innopacks/restapi/tests/PanelApiControllers/
php artisan test innopacks/restapi/tests/Middleware/
php artisan test innopacks/restapi/tests/Feature/
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

4. Laravel Sanctum
   - Sanctum configured for API authentication

## Important Notes

1. All tests extend from InnoShop\RestAPI\Tests\TestCase
2. TestCase extends InnoShop\Common\Tests\TestCase
3. Chinese translator is initialized by default
4. Tests use Sanctum for API authentication
5. Helper methods: `actingAsCustomer()`, `actingAsAdmin()`
6. Tests follow naming conventions: PascalCase + Test suffix for classes, snake_case + test_ prefix for methods
7. API routes are prefixed with `/front-api` for customers and `/panel-api` for admins
8. Authentication is required for most API endpoints except public endpoints like products list

## Contributing Guidelines

1. Extend from RestAPI TestCase when writing new tests
2. Test method names should clearly express their purpose
3. Each test method should test only one functionality
4. Use Laravel's built-in testing helpers (get, post, put, delete, etc.)
5. Use `actingAsCustomer()` or `actingAsAdmin()` helper methods for authentication
6. Test both authenticated and unauthenticated scenarios where applicable
7. Test API response codes (200, 201, 401, 403, 404, 422, etc.)
8. Maintain test code readability and maintainability
