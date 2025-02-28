<?php

namespace Plugin\Sitemap\Models;

use Illuminate\Database\Eloquent\Model;

class LSeoUrl extends Model
{
    const type_products = 'products';

    const type_categories = 'categories';

    const type_brands = 'brands';

    const type_pages = 'pages';

    const type_catalogs = 'catalogs';

    const type_articles = 'articles';

    public $table = 'seo_url';
}
