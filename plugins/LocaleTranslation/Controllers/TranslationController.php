<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\LocaleTranslation\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Panel\Services\TranslatorService;
use InnoShop\Plugin\Resources\PluginResource;
use Plugin\LocaleTranslation\Services\LocaleService;

class TranslationController extends Controller
{
    /**
     * Get locale list.
     *
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $service = new LocaleService;

        $pluginCode = $request->get('plugin_code');
        $filePath   = $request->get('file_path');

        $plugins     = app('plugin')->getPlugins();
        $pluginItems = array_values(PluginResource::collection($plugins)->jsonSerialize());

        try {
            $plugin = plugin($pluginCode);
            if ($plugin) {
                $service->setPlugin($plugin);
            }

            $data = [
                'file_path'   => $filePath,
                'plugin_code' => $pluginCode,
                'plugins'     => $pluginItems,
                'locales'     => $service->getLocaleItems(),
            ];
        } catch (Exception $e) {
            $data = [
                'file_path'   => $filePath,
                'plugin_code' => $pluginCode,
                'plugins'     => $pluginItems,
                'locales'     => [
                    'folders' => [],
                    'files'   => [],
                ],
            ];
        }

        return view('LocaleTranslation::locale', $data);
    }

    /**
     * Get locale data
     *
     * @throws Exception
     */
    public function values(Request $request): array
    {
        $service = new LocaleService;

        $pluginCode = $request->get('plugin_code');
        $filePath   = $request->get('file_path');
        $plugin     = plugin($pluginCode);
        if ($plugin) {
            $service->setPlugin($plugin);
        }

        $allLanguages = $service->getLanguages($filePath);

        return [
            'plugin_code'  => $pluginCode,
            'locale_codes' => array_keys($allLanguages),
            'base'         => $service->getBaseLanguage($filePath),
            'extra'        => $allLanguages,
        ];
    }

    /**
     * Format locale package.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function format(Request $request): JsonResponse
    {
        try {
            $service    = new LocaleService;
            $targets    = $request->get('targets', []);
            $pluginCode = $request->get('plugin_code');
            $filePath   = $request->get('file_path');
            $plugin     = plugin($pluginCode);
            if ($plugin) {
                $service->setPlugin($plugin);
            }
            $service->formatValues($filePath, $targets);

            return json_success(trans('common.get_success'), []);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Translate Text.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function translateText(Request $request): JsonResponse
    {
        try {
            $targets = $request->get('targets', []);
            $keys    = $request->get('keys', []);
            if (empty($targets) || empty($keys)) {
                throw new Exception('目标语言或者源键为空!');
            }

            $service    = new LocaleService;
            $pluginCode = $request->get('plugin_code');
            $filePath   = $request->get('file_path');
            $plugin     = plugin($pluginCode);
            if ($plugin) {
                $service->setPlugin($plugin);
            }

            $baseValue = $service->getBaseLanguage($filePath);

            $from         = LocaleService::BASE_LOCALE;
            $localeValues = [];
            foreach ($targets as $target) {
                foreach ($keys as $key) {
                    $text = $baseValue['values'][$key] ?? '';
                    if ($text) {
                        $response   = TranslatorService::getInstance()->translate($from, $target, $text);
                        $localeItem = collect($response)->where('locale', $target)->first();
                        $itemResult = $localeItem['result'] ?? '';
                        if ($itemResult) {
                            $localeValues[$target][$key] = $itemResult;
                        }
                    }
                }
            }

            $service->replaceValues($filePath, $localeValues);

            return json_success(trans('common.get_success'), []);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
