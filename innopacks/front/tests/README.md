# InnoShop Front Module Tests

This directory contains test cases for the InnoShop Front module, which handles the customer-facing store functionality.

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

- **HomeControllerTest**: Tests homepage controller
  - View homepage
  - Display featured products
  - Display categories
  - Display new products

- **ProductControllerTest**: Tests product browsing controller
  - View product list
  - View product detail
  - Filter by category
  - Search products
  - Sort products
  - Display product attributes
  - Display product reviews
  - Add to cart from product page

- **CartControllerTest**: Tests shopping cart controller
  - View cart
  - Add to cart
  - Update cart item
  - Remove cart item
  - Clear cart
  - Guest cart access
  - Authenticated customer cart
  - Display subtotal and total

- **CheckoutControllerTest**: Tests checkout process controller
  - Guest redirects to login
  - Customer can start checkout
  - Validate checkout data
  - Process payment
  - Create order successfully
  - Display shipping/billing address forms
  - Display payment methods
  - Display order summary

- **LoginControllerTest**: Tests customer login controller
  - View login page
  - Login with valid credentials
  - Cannot login with invalid credentials
  - Redirect authenticated customer
  - Logout
  - Validate email and password

- **RegisterControllerTest**: Tests customer registration controller
  - View register page
  - Register with valid data
  - Validate email uniqueness
  - Validate password confirmation
  - Validate password minimum length
  - Redirect authenticated customer
  - Create customer account
  - Login after registration

- **AccountControllerTest**: Tests customer account controller
  - Guest cannot access account
  - Customer can view account
  - Update profile
  - Change password
  - View orders
  - View order detail
  - View addresses
  - Add address
  - Delete account

### Middleware

- **CustomerAuthTest**: Tests customer authentication middleware
  - Allows authenticated customer
  - Redirects guest to login
  - Handles protected routes
  - Allows public routes

- **SetFrontLocaleTest**: Tests locale middleware
  - Sets locale from session
  - Sets locale from query parameter
  - Falls back to default locale
  - Persists locale in session
  - Validates locale

- **MaintenanceModeTest**: Tests maintenance mode middleware
  - Allows access when not in maintenance
  - Shows maintenance page when enabled
  - Bypasses maintenance for admins
  - Displays custom maintenance message

### Feature Tests

- **ShoppingCartTest**: End-to-end cart functionality
  - Add to cart from product page
  - Cart persists across requests
  - Update cart item quantity
  - Remove item from cart
  - Clear cart
  - Cart updates totals dynamically
  - Guest cart saved on login
  - Apply coupon to cart

- **CustomerLoginTest**: End-to-end authentication
  - User can register
  - User can login
  - User can logout
  - Authenticated user can access protected pages
  - Guest cannot access protected pages
  - User can view profile
  - User can update profile
  - Remember me functionality

- **GuestCheckoutTest**: Guest checkout flow
  - Guest can add to cart
  - Guest redirected to login on checkout
  - Cart items persist after login
  - Guest can view products
  - Guest can view product detail
  - Guest can search products
  - Guest can browse categories
  - Guest session preserves cart

- **ProductBrowsingTest**: Product browsing features
  - View homepage
  - View product list
  - View product detail
  - Filter by category
  - Search by keyword
  - Sort by price
  - Sort by date
  - Sort by popularity
  - Display product images
  - Display product price
  - Display product description
  - Display related products
  - Display product reviews
  - Pagination works
  - View category page

## Running Tests

### Run All Front Tests

```bash
php artisan test innopacks/front/tests
```

### Run Specific Test File

```bash
php artisan test innopacks/front/tests/Controllers/CartControllerTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_can_add_to_cart innopacks/front/tests/Controllers/CartControllerTest.php
```

### Run Tests by Directory

```bash
php artisan test innopacks/front/tests/Controllers/
php artisan test innopacks/front/tests/Middleware/
php artisan test innopacks/front/tests/Feature/
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

1. All tests extend from InnoShop\Front\Tests\TestCase
2. TestCase extends InnoShop\Common\Tests\TestCase
3. Chinese translator is initialized by default
4. Tests use mock objects for external dependencies
5. Tests follow naming conventions: PascalCase + Test suffix for classes, snake_case + test_ prefix for methods

## Contributing Guidelines

1. Extend the Front TestCase when writing new tests
2. Test method names should clearly express their purpose
3. Each test method should test only one functionality
4. Use Laravel's built-in testing helpers (get, post, assertResponseStatus, etc.)
5. Maintain test code readability and maintainability
6. Test both authenticated and guest user scenarios where applicable
