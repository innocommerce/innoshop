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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Resources\CategoryName;
use InnoShop\Common\Resources\CategorySimple;
use InnoShop\Panel\Requests\CategoryRequest;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Categories')]
class CategoryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    #[Endpoint('List categories')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $categories = CategoryRepo::getInstance()->builder($filters)->paginate($perPage);

        return CategorySimple::collection($categories);
    }

    /**
     * Get single category by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    #[Endpoint('Get category detail')]
    #[UrlParam('id', 'integer', description: 'Category ID', example: 1)]
    public function show(int $id): JsonResponse
    {
        try {
            $category = CategoryRepo::getInstance()->builder()->findOrFail($id);

            return json_success('Success', new CategorySimple($category));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Get categories by IDs')]
    #[QueryParam('ids', 'string', required: true, example: '1,2,3')]
    public function names(Request $request): AnonymousResourceCollection
    {
        $products = CategoryRepo::getInstance()->getListByCategoryIDs($request->get('ids'));

        return CategoryName::collection($products);
    }

    /**
     * Fuzzy search for auto complete.
     * /api/panel/categories/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    #[Endpoint('Autocomplete categories')]
    #[QueryParam('keyword', 'string', required: false)]
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $categories = CategoryRepo::getInstance()->autocomplete($request->get('keyword') ?? '');

        return CategoryName::collection($categories);
    }

    /**
     * Update a category.
     * PUT /api/panel/categories/{id}
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    #[Endpoint('Update category')]
    #[UrlParam('id', 'integer', description: 'Category ID', example: 1)]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $data     = $request->all();
            CategoryRepo::getInstance()->update($category, $data);

            return json_success('Category updated successfully', new CategorySimple($category));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update a category.
     * PATCH /api/panel/categories/{id}
     *
     * @param  CategoryRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    #[Endpoint('Partial update category')]
    #[UrlParam('id', 'integer', description: 'Category ID', example: 1)]
    public function patch(CategoryRequest $request, int $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $data     = $request->validated();
            CategoryRepo::getInstance()->patch($category, $data);

            return update_json_success(new CategorySimple($category));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Create a new category.
     * POST /api/panel/categories
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    #[Endpoint('Create category')]
    public function store(Request $request): JsonResponse
    {
        try {
            $data     = $request->all();
            $category = CategoryRepo::getInstance()->create($data);

            return json_success('Category created successfully', new CategorySimple($category));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Delete a category.
     * DELETE /api/panel/categories/{id}
     *
     * @param  int  $id
     * @return JsonResponse
     */
    #[Endpoint('Delete category')]
    #[UrlParam('id', 'integer', description: 'Category ID', example: 1)]
    public function destroy(int $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            if ($category->children()->count()) {
                return json_fail('Cannot delete category with children');
            }

            CategoryRepo::getInstance()->destroy($category);

            return json_success('Category deleted successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
