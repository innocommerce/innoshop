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
use Illuminate\Support\Facades\App;
use InnoShop\Common\Models\Locale;
use InnoShop\Common\Repositories\LocaleRepo;
use InnoShop\Panel\Requests\LocaleRequest;
use InnoShop\Panel\Services\TranslationService;
use Throwable;

class LocaleController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $data = [
            'locales' => LocaleRepo::getInstance()->getFrontListWithPath(),
        ];

        return inno_view('panel::locales.index', $data);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function switch(Request $request): RedirectResponse
    {
        $admin      = current_admin();
        $destCode   = $request->code;
        $refererUrl = $request->headers->get('referer');

        $admin->locale = $destCode;
        $admin->save();
        App::setLocale($destCode);

        return redirect()->to($refererUrl);
    }

    /**
     * @param  LocaleRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function install(Request $request): RedirectResponse
    {
        try {
            $code   = $request->get('code');
            $list   = LocaleRepo::getInstance()->getFrontListWithPath();
            $data   = collect($list)->where('code', $code)->first();
            $locale = TranslationService::getInstance()->createLocale($data);

            return redirect(panel_route('locales.index'))
                ->with('instance', $locale)
                ->with('success', panel_trans('common.install_success'));
        } catch (Exception $e) {
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

        return inno_view('panel::locales.form', $data);
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

            return back()->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
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
            if ($locale->code == system_setting('front_locale')) {
                throw new Exception('默认语言不能卸载');
            }
            TranslationService::getInstance()->deleteLocale($locale);
            session('locale', setting_locale_code());

            return redirect(panel_route('locales.index'))
                ->with('instance', $locale)
                ->with('success', panel_trans('common.uninstall_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @throws Exception|Throwable
     */
    public function active(Request $request, int $id): JsonResponse
    {
        try {
            $item = Locale::query()->findOrFail($id);
            if ($item->code == system_setting('front_locale')) {
                throw new Exception(panel_trans('locale.cannot_disable_default_locale'));
            }

            $item->active = $request->get('status');
            $item->saveOrFail();

            return json_success(panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
