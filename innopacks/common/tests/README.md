# InnoShop Common Module Tests

This directory contains test cases for the InnoShop Common module, which provides the core foundation for all other modules.

## Directory Structure

```
tests/
├── Models/            # Model tests
├── Repositories/      # Repository tests
├── Services/          # Service layer tests
├── TestCase.php       # Base test class
└── README.md
```

## Test Coverage

### Models

- **BaseModelTest**: Tests the base model functionality
  - Extends Eloquent Model
  - Tree building capability
  - Has Package Factory trait
  - Translatable capability
  - Table prefix handling

- **ProductTest**: Tests product model
  - Product attributes (name, description, price, stock)
  - Relationships (category, reviews, images)
  - Translatable fields
  - Slug generation

- **CategoryTest**: Tests category model
  - Category attributes
  - Parent/children relationships
  - Product relationship
  - Tree structure
  - Active scope

- **OrderTest**: Tests order model
  - Order attributes (number, total amount)
  - Customer relationship
  - Address relationships
  - Order items and transactions
  - Status scopes
  - Payment and cancellation capabilities

- **CustomerTest**: Tests customer model
  - Customer attributes
  - Address, order, cart, review relationships
  - Default address handling

- **CartTest**: Tests cart model
  - Customer relationship
  - Items relationship
  - Coupon relationship
  - Subtotal, discount, total calculations
  - Add/remove/clear operations

- **AddressTest**: Tests address model
  - Customer relationship
  - Full name and full address attributes
  - Country and city attributes
  - Phone attribute
  - Default scope and set as default method

### Repositories

- **BaseRepoTest**: Tests base repository
  - Singleton pattern
  - Model class retrieval
  - CRUD operations
  - Query building
  - Filtering and pagination

- **ProductRepoTest**: Tests product repository
  - Frontend list and detail retrieval
  - Category, price, keyword filtering
  - Sorting
  - Featured, new, best-selling products

- **CategoryRepoTest**: Tests category repository
  - Frontend tree and list retrieval
  - Detail retrieval
  - Active categories
  - Top-level categories
  - Products count

- **OrderRepoTest**: Tests order repository
  - Frontend list and detail retrieval
  - Status, customer, date range filtering
  - Order number search
  - Statistics

- **CustomerRepoTest**: Tests customer repository
  - Frontend detail retrieval
  - Email and phone lookup
  - Customer search
  - Active customers
  - Registration and profile update

### Services

- **CartServiceTest**: Tests cart service
  - Add/remove items
  - Update quantity
  - Clear cart
  - Subtotal and total calculation
  - Coupon application and removal
  - Customer and guest cart retrieval
  - Stock validation

- **CheckoutServiceTest**: Tests checkout service
  - Start checkout
  - Validate cart, customer, addresses, payment
  - Create order from cart
  - Process payment
  - Error handling
  - Send confirmation
  - Clear cart

- **OrderServiceTest**: Tests order service
  - Create order
  - Update status
  - Cancel and refund
  - Add tracking number
  - Send tracking notification
  - Statistics
  - Export

- **ProductPriceServiceTest**: Tests product price service
  - Base and sale price
  - Volume pricing
  - Currency conversion
  - Discount calculation
  - Final price
  - Formatted price
  - Price comparison

- **StockServiceTest**: Tests stock service
  - Get and update product stock
  - Deduct and restore stock
  - Check availability
  - Set stock alert
  - Get low stock products
  - Handle stock reservation

## Running Tests

### Run All Common Tests

```bash
php artisan test innopacks/common/tests
```

### Run Specific Test File

```bash
php artisan test innopacks/common/tests/Repositories/BaseRepoTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_add_item_to_cart innopacks/common/tests/Services/CartServiceTest.php
```

### Run Tests by Directory

```bash
php artisan test innopacks/common/tests/Models/
php artisan test innopacks/common/tests/Repositories/
php artisan test innopacks/common/tests/Services/
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

1. Tests use mock objects and won't modify production databases
2. The TestCase class sets up Chinese translator by default
3. All tests extend from InnoShop\Common\Tests\TestCase
4. Tests follow naming conventions: PascalCase + Test suffix for classes, snake_case + test_ prefix for methods

## Contributing Guidelines

1. Extend the TestCase class when writing new tests
2. Test method names should clearly express their purpose
3. Each test method should test only one functionality
4. Use mock objects for external dependencies
5. Maintain test code readability and maintainability
