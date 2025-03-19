<?php

namespace Plugin\SmsBao\Controllers;

use Illuminate\Http\Request;
use Plugin\SmsBao\Repositories\CustomerLoginTokenRepo;
use Plugin\SmsBao\Services\JiYanTools;
use Plugin\SmsBao\Services\TencentTools;

class SmsBaoController
{
    public function postSmsCode(Request $request)
    {
        if (! $this->checkCaptcha($request)) {
            return json_fail(trans('SmsBao::login.verification_failed'));
        }
        try {
            CustomerLoginTokenRepo::getInstance()->post_sms_code($request);

            return submit_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function checkCaptcha($request)
    {

        $smsBaoSetting = plugin_setting('sms_bao');
        if ($smsBaoSetting['captcha_type'] == 0) {
            return true;
        }
        if (! isset($smsBaoSetting['captcha_id']) || empty($smsBaoSetting['captcha_id']) || ! isset($smsBaoSetting['captcha_key']) || empty($smsBaoSetting['captcha_key'])) {//
            return false;
        }
        if ($smsBaoSetting['captcha_type'] == 1) {
            $jy = new JiYanTools;

            return $jy->checkCaptcha($request, $smsBaoSetting);
        } elseif ($smsBaoSetting['captcha_type'] == 2) {
            if (! isset($smsBaoSetting['tencent_secret_id']) || empty($smsBaoSetting['tencent_secret_id']) || ! isset($smsBaoSetting['tencent_secret_key']) || empty($smsBaoSetting['tencent_secret_key'])) {//
                return false;
            }
            $tt = new TencentTools;

            return $tt->checkCaptcha($request, $smsBaoSetting);
        }
    }

    public function loginBySms(Request $request)
    {

        try {
            $rs = CustomerLoginTokenRepo::getInstance()->loginBySms($request);
            if (is_string($rs)) {
                return json_fail($rs);
            } else {
                return submit_json_success([$rs]);
            }
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
        //return view('TencentLogin::shop/callback');
    }

    public function loginPhoneByPwd(Request $request)
    {
        try {
            $rs = CustomerLoginTokenRepo::getInstance()->loginPhoneByPwd($request);
            if (is_string($rs)) {
                return json_fail($rs);
            } else {
                return submit_json_success([$rs]);
            }
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function register(Request $request)
    {
        try {
            $rs = CustomerLoginTokenRepo::getInstance()->register($request);
            if (is_string($rs)) {
                return json_fail($rs);
            } elseif ($rs === 1 || $rs === 2) {
                return json_success(trans('shop/login.register_success'));
            }
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

    }

    public function forgotten(Request $request)
    {
        try {
            $data = CustomerLoginTokenRepo::getInstance()->forgotten($request);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

        return view('SmsBao::shop.forgotten_type_2', $data);
    }

    public function forgotten_update(Request $request)
    {
        try {
            CustomerLoginTokenRepo::getInstance()->forgotten($request);

            return update_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

    }
}
