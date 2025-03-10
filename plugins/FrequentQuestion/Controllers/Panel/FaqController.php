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
use InnoShop\Panel\Controllers\BaseController;
use Plugin\FrequentQuestion\Models\Faq;
use Plugin\FrequentQuestion\Repositories\FaqRepo;
use Plugin\FrequentQuestion\Requests\FaqRequest;
use Throwable;

class FaqController extends BaseController
{
    protected string $modelClass = Faq::class;

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria' => FaqRepo::getCriteria(),
            'faqs'     => FaqRepo::getInstance()->list($filters),
        ];

        return inno_view('FrequentQuestion::panel.faq_index', $data);
    }

    /**
     * Faq creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Faq);
    }

    /**
     * @param  FaqRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(FaqRequest $request): RedirectResponse
    {
        try {
            $data = $request->all();
            $faq  = FaqRepo::getInstance()->create($data);

            return redirect(panel_route('faqs.index'))
                ->with('instance', $faq)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('faqs.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Faq  $faq
     * @return mixed
     * @throws Exception
     */
    public function show(Faq $faq): mixed
    {
        return $this->form($faq);
    }

    /**
     * @param  Faq  $faq
     * @return mixed
     * @throws Exception
     */
    public function edit(Faq $faq): mixed
    {
        return $this->form($faq);
    }

    /**
     * @param  $faq
     * @return mixed
     * @throws Exception
     */
    public function form($faq): mixed
    {
        $data = [
            'faq' => $faq,
        ];

        return inno_view('FrequentQuestion::panel.faq_form', $data);
    }

    /**
     * @param  FaqRequest  $request
     * @param  Faq  $faq
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        try {
            $data = $request->all();
            FaqRepo::getInstance()->update($faq, $data);

            return redirect(panel_route('faqs.index'))
                ->with('instance', $faq)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('faqs.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Faq  $faq
     * @return RedirectResponse
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        try {
            FaqRepo::getInstance()->destroy($faq);

            return back()->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
