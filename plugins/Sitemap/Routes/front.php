<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Plugin\Sitemap\Controllers\SitemapController;
use Plugin\Sitemap\Models\LSeoUrl;

Route::get('/sitemap/{type}/{pwd}/{page}', [
    SitemapController::class,
    'sitemap_xml',
])->name('sitemap.xml');

Route::get('/google_feed/{pwd}/{page}', [
    SitemapController::class,
    'google_feed_xml',
])->name('google_feed.xml');
/**
function getUrlExt()
{
    $extKey = "site_map_ext";
    $ext    = Cache::get($extKey);
    if (empty($ext)) {
        $setting = plugin_setting('site_map');
        $ext     = 'html';
        if (isset($setting['ext'])) {
            $ext = trim($setting['ext']);
        }
        if (empty($ext)) {//说明填写的是空
            Cache::put($extKey, "-1", \Illuminate\Support\Carbon::now()->addMinute());
        } else {
            Cache::put($extKey, $ext, \Illuminate\Support\Carbon::now()->addMinute());
        }
    }
    if ($ext == '-1') {
        $ext = "";
    }
    return $ext;
}

 * **/
Route::fallback(function () {
    // 根据 $target 动态决定要执行的控制器
    $request = request();
    if (! $request->isMethod('get')) {
        return response()->view('errors.404', [], 404);
    }
    $path = $request->path();
    $path = urldecode($path);

    $url_name = $path;

    $lseoUrl = LSeoUrl::query()->where('url_name', $url_name)->first();
    if (empty($lseoUrl)) {
        return response()->view('errors.404', [], 404);
    }

    $response = null;
    switch ($lseoUrl->type) {
        case LSeoUrl::type_products:
            $controller = app()->make(\InnoShop\Front\Controllers\ProductController::class);
            $product    = \InnoShop\Common\Models\Product::query()->where('id', $lseoUrl->type_id)->first();
            if ($product) {
                $response = $controller->show(request(), $product);
            }
            break;
        case LSeoUrl::type_categories:
            $controller = app()->make(\InnoShop\Front\Controllers\CategoryController::class);
            $category   = \InnoShop\Common\Models\Category::query()->where('id', $lseoUrl->type_id)->first();
            if ($category) {
                $response = $controller->show(request(), $category);
            }
            break;
        case LSeoUrl::type_brands:
            $controller = app()->make(\InnoShop\Front\Controllers\BrandController::class);
            $response   = $controller->show($lseoUrl->type_id);
            break;
        case LSeoUrl::type_pages:
            $controller = app()->make(\InnoShop\Front\Controllers\PageController::class);
            $page       = \InnoShop\Common\Models\Product::query()->where('id', $lseoUrl->type_id)->first();
            if ($page) {
                $response = $controller->show($page);
            }
            break;
        case LSeoUrl::type_catalogs:
            $controller   = app()->make(\InnoShop\Front\Controllers\CatalogController::class);
            $pageCategory = InnoShop\Common\Models\Catalog::query()->where('id', $lseoUrl->type_id)->first();
            if ($pageCategory) {
                $response = $controller->show($pageCategory);
            }
            break;
        default:
            $response = null;
            break;

    }
    if (empty($response)) {
        return response()->view('errors.404', [], 404);
    }

    return $response;
});
