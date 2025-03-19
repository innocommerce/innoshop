<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FrequentQuestion\Controllers\Panel;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Resources\ArticleListItem;
use InnoShop\Common\Resources\ProductSimple;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\FrequentQuestion\Models\FaqCategory;
use Plugin\FrequentQuestion\Repositories\FaqCategoryRepo;
use Plugin\FrequentQuestion\Requests\FaqCategoryRequest;
use Throwable;

class FaqCategoryController extends BaseController
{
    protected string $modelClass = FaqCategory::class;

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria'       => FaqCategoryRepo::getCriteria(),
            'faq_categories' => FaqCategoryRepo::getInstance()->list($filters),
        ];

        return inno_view('FrequentQuestion::panel.faq_category_index', $data);
    }

    /**
     * FaqCategory creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new FaqCategory);
    }

    /**
     * @param  FaqCategoryRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(FaqCategoryRequest $request): RedirectResponse
    {
        try {
            $data        = $request->all();
            $faqCategory = FaqCategoryRepo::getInstance()->create($data);

            return redirect(panel_route('faq_categories.index'))
                ->with('instance', $faqCategory)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('faq_categories.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  FaqCategory  $faqCategory
     * @return mixed
     * @throws Exception
     */
    public function show(FaqCategory $faqCategory): mixed
    {
        return $this->form($faqCategory);
    }

    /**
     * @param  FaqCategory  $faqCategory
     * @return mixed
     * @throws Exception
     */
    public function edit(FaqCategory $faqCategory): mixed
    {
        return $this->form($faqCategory);
    }

    /**
     * @param  $faqCategory
     * @return mixed
     * @throws Exception
     */
    public function form($faqCategory): mixed
    {
        $products = ProductRepo::getInstance()->withActive()->builder()->get();
        $articles = ArticleRepo::getInstance()->withActive()->builder()->get();

        $data = [
            'faq_category' => $faqCategory,
            'products'     => ProductSimple::collection($products)->jsonSerialize(),
            'articles'     => ArticleListItem::collection($articles)->jsonSerialize(),
        ];

        return inno_view('FrequentQuestion::panel.faq_category_form', $data);
    }

    /**
     * @param  FaqCategoryRequest  $request
     * @param  FaqCategory  $faqCategory
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(FaqCategoryRequest $request, FaqCategory $faqCategory): RedirectResponse
    {
        try {
            $data = $request->all();
            FaqCategoryRepo::getInstance()->update($faqCategory, $data);

            return redirect(panel_route('faq_categories.index'))
                ->with('instance', $faqCategory)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('faq_categories.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  FaqCategory  $faqCategory
     * @return RedirectResponse
     */
    public function destroy(FaqCategory $faqCategory): RedirectResponse
    {
        try {
            FaqCategoryRepo::getInstance()->destroy($faqCategory);

            return back()->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
