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
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\CurrencyRepo;
use InnoShop\Common\Repositories\MailRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Common\Repositories\SmsRepo;
use InnoShop\Common\Repositories\WeightClassRepo;
use InnoShop\Common\Services\AI\AIServiceManager;
use InnoShop\Common\Services\SmsService;
use InnoShop\Panel\Repositories\ContentAIRepo;
use InnoShop\Panel\Requests\SettingRequest;
use Throwable;

class SettingController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $data = [
            'locales'        => locales()->toArray(),
            'currencies'     => CurrencyRepo::getInstance()->enabledList()->toArray(),
            'weight_classes' => WeightClassRepo::getInstance()->withActive()->all()->toArray(),
            'categories'     => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'catalogs'       => CatalogRepo::getInstance()->getTopCatalogs(),
            'pages'          => PageRepo::getInstance()->withActive()->builder()->get(),
            'mail_engines'   => MailRepo::getInstance()->getEngines(),
            'sms_gateways'   => SmsRepo::getInstance()->getGateways(),
            'sms_repo'       => SmsRepo::getInstance(),
            'ai_models'      => AIServiceManager::getInstance()->getModelsForSelect(),
            'ai_prompts'     => ContentAIRepo::getInstance()->getPrompts(),
        ];

        return inno_view('panel::settings.index', $data);
    }

    /**
     * @param  SettingRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function update(SettingRequest $request): mixed
    {
        $settings = $request->all();
        $tab      = $request->get('tab'); // Get current tab from request

        try {
            // Get old panel_name before update
            $oldAdminName = panel_name();

            // Get the new panel_name from request (before update)
            $newAdminName = ! empty($settings['panel_name']) ? $settings['panel_name'] : 'panel';

            // Update settings
            SettingRepo::getInstance()->updateValues($settings);

            // Build redirect URL manually using the new panel_name
            // Since routes are registered at boot time, we need to manually construct the URL
            $baseUrl    = request()->getSchemeAndHttpHost();
            $settingUrl = $baseUrl.'/'.$newAdminName.'/settings';

            // Add tab parameter if provided
            if ($tab) {
                $settingUrl .= '?tab='.$tab;
            }

            return redirect($settingUrl)
                ->with('instance', $settings)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            $errorUrl = panel_route('settings.index');
            if ($tab) {
                $errorUrl .= '?tab='.$tab;
            }

            return redirect($errorUrl)->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Test SMS sending
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function testSms(Request $request): mixed
    {
        $request->validate([
            'calling_code' => 'required|string|max:10',
            'telephone'    => 'required|string|max:20',
            'type'         => 'required|string|in:register,login,reset',
        ]);

        try {
            $smsService = new SmsService;
            $smsService->sendVerificationCode(
                $request->input('calling_code'),
                $request->input('telephone'),
                $request->input('type')
            );

            return response()->json([
                'success' => true,
                'message' => panel_trans('setting.sms_test_success'),
            ]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            // Translate error message with specific error details
            // Use __() helper which handles translation better than trans()
            $translatedMessage = __('common/sms.send_failed', ['message' => $errorMessage]);

            return response()->json([
                'success' => false,
                'message' => $translatedMessage,
            ], 400);
        }
    }
}
