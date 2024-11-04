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
use Illuminate\Http\Request;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Repositories\TagRepo;

class ArticleController extends Controller
{
    /**
     * @return mixed
     * @throws \Exception
     */
    public function index(): mixed
    {
        $data = [
            'articles' => ArticleRepo::getInstance()->list(['active' => true]),
            'catalogs' => CatalogRepo::getInstance()->list(['active' => true]),
            'tags'     => TagRepo::getInstance()->list(['active' => true]),
        ];

        return inno_view('articles.index', $data);
    }

    /**
     * @param  Article  $article
     * @return mixed
     * @throws \Exception
     */
    public function show(Article $article): mixed
    {
        $article->increment('viewed');
        $data = [
            'article'  => $article,
            'catalogs' => CatalogRepo::getInstance()->list(['active' => true]),
        ];

        return inno_view('articles.show', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug    = $request->slug;
        $article = ArticleRepo::getInstance()->builder(['active' => true])->where('slug', $slug)->firstOrFail();
        $article->increment('viewed');
        $data = [
            'slug'     => $slug,
            'article'  => $article,
            'catalogs' => CatalogRepo::getInstance()->list(['active' => true]),
        ];

        return inno_view('articles.show', $data);
    }
}
