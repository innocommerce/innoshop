<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\CatalogRepo;

class CatalogController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $catalogs = CatalogRepo::getInstance()->getTopCatalogs();

        $data = [
            'catalogs' => $catalogs,
        ];

        return inno_view('catalogs.index', $data);
    }

    /**
     * @param  Catalog  $catalog
     * @return mixed
     * @throws Exception
     */
    public function show(Catalog $catalog): mixed
    {
        if (! $catalog->active) {
            abort(404);
        }

        $catalogs = CatalogRepo::getInstance()->list(['active' => true]);
        $articles = ArticleRepo::getInstance()->list(['active' => true, 'catalog_id' => $catalog->id]);

        $data = [
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
            'articles' => $articles,
        ];

        return inno_view('catalogs.show', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug    = $request->slug;
        $catalog = CatalogRepo::getInstance()->builder(['active' => true])->where('slug', $slug)->firstOrFail();

        if (! $catalog->active) {
            abort(404);
        }

        $catalogs = CatalogRepo::getInstance()->list(['active' => true]);
        $articles = ArticleRepo::getInstance()->list(['active' => true, 'catalog_id' => $catalog->id]);

        $data = [
            'slug'     => $slug,
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
            'articles' => $articles,
        ];

        return inno_view('catalogs.show', $data);
    }
}
