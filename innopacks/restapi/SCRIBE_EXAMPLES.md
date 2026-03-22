# Scribe Annotation Examples for InnoShop Controllers

This document provides examples of how to add Scribe PHP 8.1 Attribute annotations to InnoShop controllers.

## Public Endpoint (No Auth)

```php
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Unauthenticated;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Front - Products')]
class ProductController extends BaseController
{
    #[Endpoint('List products')]
    #[Unauthenticated]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    #[QueryParam('sort', 'string', required: false, example: 'id')]
    #[QueryParam('order', 'string', required: false, example: 'desc')]
    #[QueryParam('category_id', 'integer', required: false)]
    #[QueryParam('brand_id', 'integer', required: false)]
    public function index(Request $request): mixed
    {
        // existing code...
    }

    #[Endpoint('Get product details')]
    #[Unauthenticated]
    #[UrlParam('product', 'integer', description: 'Product ID')]
    public function show(Product $product): mixed
    {
        // existing code...
    }
}
```

## Authenticated Endpoint (Class-Level Auth)

```php
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Front - Cart')]
#[Authenticated]
class CartController extends BaseController
{
    #[Endpoint('Get cart items')]
    public function index(): mixed
    {
        // existing code...
    }

    #[Endpoint('Add item to cart')]
    #[BodyParam('sku_id', 'integer', required: true)]
    #[BodyParam('quantity', 'integer', required: true, example: 1)]
    public function store(Request $request): mixed
    {
        // existing code...
    }

    #[Endpoint('Remove cart item')]
    #[UrlParam('cart', 'integer', description: 'Cart item ID')]
    public function destroy(CartItem $cart): mixed
    {
        // existing code...
    }
}
```

## Mixed Auth (Some Methods Public, Some Auth)

```php
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Front - Checkout')]
class CheckoutController extends BaseController
{
    #[Endpoint('Get billing methods')]
    #[Unauthenticated]
    public function billingMethods(): mixed
    {
        // public, no auth needed
    }

    #[Endpoint('Get checkout data')]
    #[Authenticated]
    public function index(): mixed
    {
        // requires auth
    }
}
```

## Panel API Example (Auth Default = true)

For panel controllers, the Scribe config has `auth.default = true`, so all endpoints
are authenticated by default. Only mark login endpoints as `#[Unauthenticated]`.

```php
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Panel - Authentication')]
class AuthController extends BaseController
{
    #[Endpoint('Admin login')]
    #[Unauthenticated]
    #[BodyParam('email', 'string', required: true, example: 'admin@example.com')]
    #[BodyParam('password', 'string', required: true)]
    public function login(Request $request): mixed
    {
        // existing code...
    }

    #[Endpoint('Get current admin')]
    public function admin(Request $request): mixed
    {
        // authenticated by default (panel config)
    }
}
```

## File Upload Example

```php
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

#[Group('Front - Upload')]
#[Authenticated]
class UploadController
{
    #[Endpoint('Upload images')]
    #[BodyParam('images', 'file', required: true, description: 'Image files to upload')]
    public function images(UploadImageRequest $request): mixed
    {
        // existing code...
    }
}
```

## Quick Reference

### Common Attributes

| Attribute | Usage |
|---|---|
| `#[Group('Group Name')]` | Class-level grouping |
| `#[Endpoint('Title')]` | Method title |
| `#[Authenticated]` | Requires bearer token |
| `#[Unauthenticated]` | Public endpoint |
| `#[QueryParam('name', 'type')]` | `?name=value` parameter |
| `#[UrlParam('name', 'type')]` | `/{name}` path parameter |
| `#[BodyParam('name', 'type')]` | Request body field |

### Parameter Options

```php
#[QueryParam('name', 'type', required: false, description: '...', example: '...')]
#[BodyParam('name', 'type', required: true, description: '...', example: '...')]
#[UrlParam('name', 'type', description: '...', example: 123)]
```

## Generate and Verify

```bash
# Generate Front API docs
php artisan scribe:generate

# Generate Panel API docs
php artisan scribe:generate --config scribe_panel

# View in browser
open http://localhost:8000/docs
open http://localhost:8000/docs/panel
```
