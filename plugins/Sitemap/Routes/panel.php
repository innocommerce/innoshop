<?php

use Illuminate\Support\Facades\Route;
use Plugin\Sitemap\Controllers\SitemapController;

Route::middleware('can:seo_url_index')->get('/seo_url/index', [
    SitemapController::class,
    'index',
])->name('seo_index');

Route::middleware('can:url_update')->put('/sitemap', [
    SitemapController::class,
    'updateUrl',
])->name('sitemap');

Route::middleware('can:url_update')->delete('/sitemap', [
    SitemapController::class,
    'deleteUrl',
])->name('sitemap');

Route::middleware('can:seo_url_index')->get('/seo_url/google_feeds', [
    SitemapController::class,
    'googleFeeds',
])->name('seo_index.google_feeds');

Route::middleware('can:seo_url_index')->get('/seo_url/site_map', [
    SitemapController::class,
    'siteMapData',
])->name('seo_index.site_map');

Route::middleware('can:google_feed_update')->put('/google_feed', [
    SitemapController::class,
    'updateGoogleFeed',
])->name('google_feed');
