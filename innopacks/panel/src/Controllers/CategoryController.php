<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Resources\CategorySimple;
use InnoShop\Panel\Requests\CategoryRequest;

class CategoryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        $filters['parent_id'] = 0;

        $categories = CategoryRepo::getInstance()->all($filters);

        $data = [
            'categories' => CategorySimple::collection($categories)->jsonSerialize(),
        ];

        return inno_view('panel::categories.index', $data);
    }

    /**
     * Category creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Category);
    }

    /**
     * @param  CategoryRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        try {
            $data     = $request->all();
            $category = CategoryRepo::getInstance()->create($data);

            return redirect(panel_route('categories.index'))
                ->with('instance', $category)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Category  $category
     * @return mixed
     * @throws Exception
     */
    public function edit(Category $category): mixed
    {
        return $this->form($category);
    }

    /**
     * @param  $category
     * @return mixed
     */
    public function form($category): mixed
    {
        $categories = CategorySimple::collection(CategoryRepo::getInstance()->all(['active' => 1]))->jsonSerialize();
        $data       = [
            'category'   => $category,
            'categories' => $categories,
        ];

        return inno_view('panel::categories.form', $data);
    }

    /**
     * @param  CategoryRequest  $request
     * @param  Category  $category
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $data = $request->all();
            CategoryRepo::getInstance()->update($category, $data);

            return redirect(panel_route('categories.index'))
                ->with('instance', $category)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Category  $category
     * @return JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            if ($category->children()->count()) {
                throw new \Exception(panel_trans('category.has_children'));
            }
            CategoryRepo::getInstance()->destroy($category);

            return json_success(panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
