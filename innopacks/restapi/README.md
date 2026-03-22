# InnoShop REST API Documentation

This module uses [Scribe](https://scribe.knuckles.wtf/) (v5.x) to automatically generate API documentation from your Laravel codebase using PHP 8.1 Attributes.

## Features

- Automatic documentation generation from PHP Attributes
- OpenAPI 3.0 spec export
- Apifox integration for team collaboration
- Interactive "Try It Out" testing from the docs page
- Separate Front API and Panel API documentation

## Directory Structure

```
innopacks/restapi/
├── config/
│   ├── scribe_front.php       # Front API Scribe config
│   └── scribe_panel.php       # Panel API Scribe config
├── scripts/
│   ├── sync-apifox-sample.sh  # Apifox sync script template
│   └── sync-apifox.sh         # (gitignored) Script with real credentials
├── src/
│   ├── Commands/
│   │   └── SyncApifoxCommand.php  # Artisan command for Apifox sync
│   ├── FrontApiControllers/   # Frontend API endpoints
│   ├── PanelApiControllers/   # Admin panel API endpoints
│   └── RestAPIServiceProvider.php
└── routes/
    ├── front-api.php
    └── panel-api.php
```

## Quick Start

### 1. Generate Documentation

```bash
# Generate Front API docs (available at /docs)
php artisan scribe:generate

# Generate Panel API docs (available at /docs/panel)
php artisan scribe:generate --config scribe_panel
```

### 2. View Documentation

- Front API: `http://localhost:8000/docs`
- Panel API: `http://localhost:8000/docs/panel`

### 3. OpenAPI Spec Files

After generation:
- Front API: `storage/app/scribe/openapi.yaml`
- Panel API: `storage/app/scribe_panel/openapi.yaml`

## Sync to Apifox

### Option 1: Artisan Command (Recommended)

Add to your `.env`:
```env
APIFOX_TOKEN=your-apifox-token
APIFOX_FRONT_PROJECT_ID=your-front-project-id
APIFOX_PANEL_PROJECT_ID=your-panel-project-id
# Optional: if your account uses another region (see Apifox Open API docs)
# APIFOX_API_BASE_URL=https://api.apifox.com
# Optional: do not remove endpoints that are not in the OpenAPI file
# APIFOX_KEEP_UNMATCHED=true
```

**Project ID** must be the numeric **Project ID** from Apifox **Project Settings → Open API** (not the team ID or online doc URL slug). If sync “succeeds” but no interfaces appear, run `php artisan apifox:sync -v` and confirm counters; the command sends OpenAPI as a JSON string in `input`, as required by the Apifox API.

Run the sync command:
```bash
# Sync both Front and Panel APIs
php artisan apifox:sync

# Sync only Front API
php artisan apifox:sync --type=front

# Sync only Panel API
php artisan apifox:sync --type=panel

# Override credentials via command line
php artisan apifox:sync --token=your-token --front-project=123 --panel-project=456

# Verbose: print HTTP status and full Apifox JSON response
php artisan apifox:sync -v

# Keep endpoints in Apifox that are not in the file (disable sync-delete)
php artisan apifox:sync --keep-unmatched
```

### Option 2: Shell Script

```bash
# Copy the sample script and fill in your credentials
cp innopacks/restapi/scripts/sync-apifox-sample.sh innopacks/restapi/scripts/sync-apifox.sh

# Edit sync-apifox.sh with your credentials, then run
bash innopacks/restapi/scripts/sync-apifox.sh
```

Note: `sync-apifox.sh` with real credentials is gitignored.

## Configuration

Two separate Scribe config files control documentation generation:

| Config File | HTML | OpenAPI | Postman | API routes |
|---|---|---|---|---|
| `config/scribe_front.php` (merged as `config/scribe.php`) | `/docs` | `/docs.openapi` | `/docs.postman` | `api/*` (excluding `api/panel/*`) |
| `config/scribe_panel.php` | `/docs/panel` | `/docs/panel.openapi` | `/docs/panel.postman` | `api/panel/*` |

Front and panel use different Sanctum tokens (`customer-token` vs `admin-token`); each OpenAPI file only describes its own prefix. Regenerate both after code changes: `php artisan scribe:generate` and `php artisan scribe:generate --config=scribe_panel`.

To publish config files to the main `config/` directory:

```bash
php artisan vendor:publish --tag=innoshop-scribe-config
```

## Adding Annotations

Use PHP 8.1 Attributes on your controllers:

```php
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
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
    public function index(Request $request): mixed { ... }

    #[Endpoint('Get product details')]
    #[UrlParam('product', 'integer', description: 'Product ID')]
    public function show(Product $product): mixed { ... }
}
```

## Scribe Attributes Reference

| Attribute | Level | Description |
|---|---|---|
| `#[Group('name')]` | Class | Group related endpoints |
| `#[Endpoint('title')]` | Method | Endpoint title and description |
| `#[Authenticated]` | Class/Method | Requires authentication |
| `#[Unauthenticated]` | Class/Method | Public endpoint |
| `#[QueryParam(...)]` | Method | Query string parameter |
| `#[UrlParam(...)]` | Method | URL path parameter |
| `#[BodyParam(...)]` | Method | Request body parameter |

## Troubleshooting

### Documentation not generating

1. Clear cache: `php artisan cache:clear && php artisan config:clear`
2. Run with verbose output: `php artisan scribe:generate -v`

### Routes not showing in docs

1. Verify route prefix matches the config (`api/*` for front, `api/panel/*` for panel)
2. Ensure the controller method has `#[Endpoint(...)]` attribute
3. Check route is not in the `exclude` list

## Resources

- [Scribe Documentation](https://scribe.knuckles.wtf/)
- [Scribe PHP Attributes](https://scribe.knuckles.wtf/laravel/reference/annotations)
- [OpenAPI Specification](https://swagger.io/specification/)

## License

Same as InnoShop.
