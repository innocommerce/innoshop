<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use InnoShop\Install\Libraries\Checker;
use InnoShop\Install\Libraries\Creator;
use InnoShop\Install\Requests\CompleteRequest;
use Throwable;

class InstallController extends Controller
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        if (installed()) {
            return redirect(front_route('home.index'));
        }

        $defaultLocale = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        $defaultLocale = ($defaultLocale == 'zh' ? 'zh_cn' : $defaultLocale);
        $locale        = $request->get('locale', $defaultLocale);
        App::setLocale($locale);

        $data = Checker::getInstance()->getEnvironment();

        $data['locale'] = $locale;

        return view('install::installer.index', $data);
    }

    /**
     * @param  Request  $request
     * @return array
     */
    public function checkConnected(Request $request): array
    {
        return (new Checker())->checkConnection($request->all());
    }

    /**
     * @param  CompleteRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function complete(CompleteRequest $request): JsonResponse
    {
        try {
            $data      = $request->all();
            $outputLog = Creator::getInstance()->setup($data)->getOutputLog();

            return json_success($outputLog->fetch());
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
