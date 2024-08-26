<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Resources\CatalogSimple;
use InnoShop\Panel\Requests\ArticleRequest;

class ArticleController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'articles' => ArticleRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::articles.index', $data);
    }

    /**
     * Article creation page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function create(): mixed
    {
        return $this->form(new Article);
    }

    /**
     * @param  ArticleRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(ArticleRequest $request): RedirectResponse
    {
        try {
            $data    = $request->all();
            $article = ArticleRepo::getInstance()->create($data);

            return redirect(panel_route('articles.index'))
                ->with('instance', $article)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Article  $article
     * @return mixed
     * @throws \Exception
     */
    public function edit(Article $article): mixed
    {
        return $this->form($article);
    }

    /**
     * @param  $article
     * @return mixed
     * @throws \Exception
     */
    public function form($article): mixed
    {
        $catalogs = CatalogSimple::collection(CatalogRepo::getInstance()->all(['active' => 1]))->jsonSerialize();
        $data     = [
            'article'  => $article,
            'catalogs' => $catalogs,
        ];

        return inno_view('panel::articles.form', $data);
    }

    /**
     * @param  ArticleRequest  $request
     * @param  Article  $article
     * @return RedirectResponse
     */
    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        try {
            $data = $request->all();
            ArticleRepo::getInstance()->update($article, $data);

            return redirect(panel_route('articles.index'))
                ->with('instance', $article)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Article  $article
     * @return RedirectResponse
     */
    public function destroy(Article $article): RedirectResponse
    {
        try {
            ArticleRepo::getInstance()->destroy($article);

            return back()->with('success', panel_trans('common.deleted_success'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
