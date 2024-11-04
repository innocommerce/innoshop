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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Panel\Requests\ArticleRequest;

class ArticleController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        return ArticleRepo::getInstance()->list($filters);
    }

    /**
     * @param  ArticleRequest  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(ArticleRequest $request): JsonResponse
    {
        try {
            $data    = $request->all();
            $article = ArticleRepo::getInstance()->create($data);

            return json_success(panel_trans('common.updated_success'), $article);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  ArticleRequest  $request
     * @param  Article  $article
     * @return JsonResponse
     */
    public function update(ArticleRequest $request, Article $article): JsonResponse
    {
        try {
            $data = $request->all();
            ArticleRepo::getInstance()->update($article, $data);

            return json_success(panel_trans('common.updated_success'), $article);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Article  $article
     * @return JsonResponse
     */
    public function destroy(Article $article): JsonResponse
    {
        try {
            ArticleRepo::getInstance()->destroy($article);

            return json_success(panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
