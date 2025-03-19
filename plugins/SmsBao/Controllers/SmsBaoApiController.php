<?php

namespace Plugin\SmsBao\Controllers;

use Illuminate\Http\Request;
use Plugin\SmsBao\Repositories\CustomerLoginTokenRepo;

class SmsBaoApiController
{
    public function postSmsCode(Request $request)
    {
        try {
            CustomerLoginTokenRepo::getInstance()->post_sms_code($request);

            return submit_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
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

            return json_success('', $data);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
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
