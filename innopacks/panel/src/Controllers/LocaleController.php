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
use InnoShop\Common\Models\Locale;
use InnoShop\Common\Repositories\LocaleRepo;
use InnoShop\Panel\Requests\LocaleRequest;

class LocaleController extends BaseController
{
    /**
     * @return mixed
     * @throws \Exception
     */
    public function index(): mixed
    {
        $data = [
            'locales' => LocaleRepo::getInstance()->getListWithPath(),
        ];

        return view('panel::locales.index', $data);
    }

    /**
     * @param  LocaleRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function install(Request $request): RedirectResponse
    {
        try {
            $code = $request->get('code');
            $list = LocaleRepo::getInstance()->getListWithPath();
            $data = collect($list)->where('code', $code)->first();
            LocaleRepo::getInstance()->create($data);

            return redirect(panel_route('locales.index'))->with('success', trans('panel::common.install_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('locales.index'))->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Locale  $locale
     * @return mixed
     */
    public function edit(Locale $locale): mixed
    {
        $data = [
            'locale' => $locale,
        ];

        return view('panel::locales.form', $data);
    }

    /**
     * @param  LocaleRequest  $request
     * @param  Locale  $locale
     * @return RedirectResponse
     */
    public function update(LocaleRequest $request, Locale $locale): RedirectResponse
    {
        try {
            $data = $request->all();
            LocaleRepo::getInstance()->update($locale, $data);

            return back()->with('success', trans('panel::common.updated_success'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function uninstall(Request $request): RedirectResponse
    {
        try {
            $code   = $request->code;
            $locale = LocaleRepo::getInstance()->builder(['code' => $code])->firstOrFail();
            LocaleRepo::getInstance()->destroy($locale);

            return redirect(panel_route('locales.index'))->with('success', trans('panel::common.uninstall_success'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
