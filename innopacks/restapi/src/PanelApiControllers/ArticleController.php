<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Resources\ArticleName;
use InnoShop\Panel\Requests\ArticleRequest;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Panel - Articles')]
class ArticleController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('List articles')]
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        return ArticleRepo::getInstance()->list($filters);
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Get articles by IDs')]
    #[QueryParam('ids', 'string', required: true)]
    public function names(Request $request): AnonymousResourceCollection
    {
        $articles = ArticleRepo::getInstance()->getListByArticleIDs($request->get('ids'));

        return ArticleName::collection($articles);
    }

    /**
     * @param  ArticleRequest  $request
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Create article')]
    public function store(ArticleRequest $request): mixed
    {
        try {
            $data    = $request->all();
            $article = ArticleRepo::getInstance()->create($data);

            return json_success(common_trans('base.updated_success'), $article);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  ArticleRequest  $request
     * @param  Article  $article
     * @return mixed
     */
    #[Endpoint('Update article')]
    #[UrlParam('article', 'integer', description: 'Article ID')]
    public function update(ArticleRequest $request, Article $article): mixed
    {
        try {
            $data = $request->all();
            ArticleRepo::getInstance()->update($article, $data);

            return json_success(common_trans('base.updated_success'), $article);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update an article.
     * PATCH /api/panel/articles/{article}
     *
     * @param  ArticleRequest  $request
     * @param  Article  $article
     * @return mixed
     */
    #[Endpoint('Partial update article')]
    #[UrlParam('article', 'integer', description: 'Article ID')]
    public function patch(ArticleRequest $request, Article $article): mixed
    {
        try {
            $data = $request->validated();
            ArticleRepo::getInstance()->patch($article, $data);

            return update_json_success($article);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Article  $article
     * @return mixed
     */
    #[Endpoint('Delete article')]
    #[UrlParam('article', 'integer', description: 'Article ID')]
    public function destroy(Article $article): mixed
    {
        try {
            ArticleRepo::getInstance()->destroy($article);

            return json_success(common_trans('base.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Fuzzy search for auto complete.
     * /api/panel/articles/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    #[Endpoint('Autocomplete articles')]
    #[QueryParam('keyword', 'string', required: false)]
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $categories = ArticleRepo::getInstance()->autocomplete($request->get('keyword') ?? '');

        return ArticleName::collection($categories);
    }
}
