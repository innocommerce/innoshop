<?php

namespace Plugin\SmsBao\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Repositories\CustomerRepo;
use InnoShop\Common\Services\CartService;
use InnoShop\Front\Services\AccountService;
use Plugin\SmsBao\Models\CustomerMobile;
use Plugin\SmsBao\Services\JiYanTools;
use Plugin\SmsBao\Services\SmsTools;
use Plugin\SmsBao\Services\TencentTools;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SmsBaoController
{
    public function postSmsCode(Request $request)
    {
        /**
        if (! $this->checkCaptcha($request)) {
            return response()->json([
                'code' => -1,
                'msg'  => trans('SmsBao::login.verification_failed'),
            ]);
        }
         * **/
        $phone      = $request->telephone;
        $phone_code = $request->telephone_code;
        $phone_code = str_replace('+', '', $phone_code);

        // 检测是否为手机号码
        if (! $this->checkPhone($phone_code, $phone)) {
            return response()->json([
                'code' => -2,
                'msg'  => trans('SmsBao::login.invalid_number'),
            ]);
        }

        $code = str_pad(mt_rand(10, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put($this->getCodeCacheKey($phone_code, $phone), $code, Carbon::now()->addMinutes(5));
        $smsTools = new SmsTools;
        $rs       = $smsTools->sendCode($phone_code, $phone, $code);
        if (is_string($rs)) {
            return response()->json([
                'code' => -3,
                'msg'  => $rs,
            ]);
        }

        return response()->json([
            'code' => 0,
            'msg'  => trans('SmsBao::login.success'),
            //'发送成功'
        ]);
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
        $setting    = plugin_setting('sms_bao');
        $login_type = isset($setting['login_type']) ? $setting['login_type'] : 1;
        if ($login_type != 1) {
            return response()->json([
                'code'    => -2,
                'message' => trans('SmsBao::login.invalid_number'),
            ]);
        }
        $phone      = $request->telephone;
        $phone_code = $request->telephone_code;
        $phone_code = str_replace('+', '', $phone_code);

        // 检测是否为手机号码
        if (! $this->checkPhone($phone_code, $phone)) {
            return response()->json([
                'code'    => -1,
                'message' => trans('SmsBao::login.invalid_number'),
            ]);
        }

        $code    = $request->code;
        $key     = $this->getCodeCacheKey($phone_code, $phone);
        $oldCode = Cache::get($key);
        if ($code != $oldCode) {
            return response()->json([
                'code'    => -1,
                'message' => trans('SmsBao::login.invalid_code'),
            ]);
        }
        $customer = null;
        try {
            DB::beginTransaction();
            $customerMobile = CustomerMobile::query()->where('mobile_code', $phone_code)->where('mobile', $phone)->first();
            if ($customerMobile) {
                $customer = $customerMobile->customer;
            } else {
                //保存用户，
                $email        = $phone_code.$phone.'@'.$this->getTopLevelDomain($request);
                $customerData = [
                    'from'     => 'sms',
                    'email'    => $phone_code.$phone.'@'.$this->getTopLevelDomain($request),
                    'name'     => $phone,
                    'avatar'   => '',
                    'password' => substr(md5(time().rand().$email), 0, 16),
                ];
                $customer = AccountService::getInstance()->register($customerData);

                $data = [
                    'customer_id' => $customer->id,
                    'mobile_code' => $phone_code,
                    'mobile'      => $phone,
                ];

                CustomerMobile::query()->create($data);
                DB::commit();
            }
            //执行登录
            //;
            if (! Auth::guard('customer')->loginUsingId($customer->id)) {
                throw new NotAcceptableHttpException(front_trans('login.account_or_password_error'));
            }

            $customer = current_customer();
            if (empty($customer)) {
                throw new NotFoundHttpException(front_trans('login.empty_customer'));
            }

            if (! $customer->active) {
                auth('customer')->logout();
                throw new Exception(front_trans('login.inactive_customer'));
            }

            Cache::pull($this->getCodeCacheKey($phone_code, $phone));

            $redirectUri = session('front_redirect_uri');
            session()->forget('front_redirect_uri');
            $data = ['redirect_uri' => $redirectUri];

            return response()->json([
                'code'    => 0,
                'message' => front_trans('login.login_success'),
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            exit($e->getMessage());
        }

        //return view('TencentLogin::shop/callback');
    }

    public function loginPhoneByPwd(Request $request)
    {
        $setting    = plugin_setting('sms_bao');
        $login_type = isset($setting['login_type']) ? $setting['login_type'] : 1;
        if ($login_type != 2) {
            return response()->json([
                'code'    => -2,
                'message' => trans('SmsBao::login.invalid_number'),
            ]);
        }
        $phone      = $request->telephone;
        $phone_code = $request->telephone_code;
        $phone_code = str_replace('+', '', $phone_code);

        // 检测是否为手机号码
        if (! $this->checkPhone($phone_code, $phone)) {
            return response()->json([
                'code'    => -1,
                'message' => trans('SmsBao::login.invalid_number'),
            ]);
        }

        $customer = null;
        try {
            $customerMobile = CustomerMobile::query()->where('mobile_code', $phone_code)->where('mobile', $phone)->first();
            if ($customerMobile) {
                $customer = $customerMobile->customer;
                if (empty($customer)) {
                    throw new NotFoundHttpException(trans('shop/login.empty_customer'));
                }
                $data = [
                    'request_data' => $request->all(),
                ];

                try {
                    fire_hook_action('front.account.login.before', $data);

                    if (! auth('customer')->attempt([
                        'email'    => $customer->email,
                        'password' => $request->password,
                    ])) {
                        throw new NotAcceptableHttpException(trans('SmsBao::login.phone_or_password_error'));
                    }

                    if (! $customer->active) {
                        auth('customer')->logout();
                        throw new Exception(front_trans('login.inactive_customer'));
                    }

                    $oldGuestId = current_guest_id();
                    CartService::getInstance(current_customer_id())->mergeCart($oldGuestId);

                    fire_hook_action('front.account.login.after', $data);

                    $redirectUri = session('front_redirect_uri');
                    session()->forget('front_redirect_uri');
                    $data = ['redirect_uri' => $redirectUri];

                    return response()->json([
                        'code'    => 0,
                        'message' => front_trans('login.login_success'),
                        'data'    => $data,
                    ]);
                } catch (NotAcceptableHttpException $e) {
                    return response()->json([
                        'code'    => -1,
                        'message' => $e->getMessage(),
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'code'    => -1,
                        'message' => $e->getMessage(),
                    ]);
                }

            } else {
                //登录失败
                return response()->json([
                    'code'    => -2,
                    'message' => trans('SmsBao::login.invalid_number'),
                ]);
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }

        //return view('TencentLogin::shop/callback');
    }

    private function getCodeCacheKey($phone_code, $phone)
    {
        return 'reg_code-'.$phone_code.'-'.$phone;
    }

    private function checkPhone($phone_code, $phone)
    {
        if (empty($phone_code) || ! is_numeric($phone_code)) {
            return false;
        }
        if (empty($phone) || ! is_numeric($phone) || strlen($phone) < 5) {
            return false;
        }

        return true;
    }

    public function register(Request $request)
    {

        $phone      = $request->telephone;
        $phone_code = $request->telephone_code;
        $phone_code = str_replace('+', '', $phone_code);

        // 检测是否为手机号码
        if (! $this->checkPhone($phone_code, $phone)) {
            return response()->json([
                'code'    => -1,
                'message' => trans('SmsBao::login.invalid_number'),
            ]);
        }

        $code    = $request->code;
        $key     = $this->getCodeCacheKey($phone_code, $phone);
        $oldCode = Cache::get($key);
        if ($code != $oldCode) {
            return response()->json([
                'code'    => -2,
                'message' => trans('SmsBao::login.invalid_code'),
            ]);
        }
        $customer = null;
        try {
            DB::beginTransaction();
            $customerMobile = CustomerMobile::query()->where('mobile_code', $phone_code)->where('mobile', $phone)->first();
            if ($customerMobile) {
                return response()->json([
                    'code'    => -3,
                    'message' => trans('SmsBao::login.exist_number'),
                ]);
            } else {

                //保存用户，
                $customerData = [
                    'from'     => 'sms',
                    'email'    => $phone_code.$phone.'@'.$this->getTopLevelDomain($request),
                    'name'     => $phone,
                    'avatar'   => '',
                    'password' => $request->password,
                ];
                $customer = AccountService::getInstance()->register($customerData);

                $data = [
                    'customer_id' => $customer->id,
                    'mobile_code' => $phone_code,
                    'mobile'      => $phone,
                ];

                CustomerMobile::query()->create($data);
                DB::commit();
            }
            if ($customer->status == 'approved') {
                return json_success(trans('shop/login.register_success'));
            }
            //执行登录
            Auth::guard('customer')->login($customer);
            Cache::pull($this->getCodeCacheKey($phone_code, $phone));

            return response()->json([
                'code'    => 0,
                'message' => trans('shop/login.login_successfully'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            exit($e->getMessage());
        }

        if ($customer->status == 'approved') {
            return json_success(trans('shop/login.register_success'));
        }

        return json_fail(trans('shop/login.should_be_approved'));

    }

    private function getTopLevelDomain(Request $request)
    {
        $host = $request->getHost();
        // 使用正则表达式匹配顶级域名
        preg_match('/[^\.]*\.[^\.]*$/', $host, $matches);

        return $matches[0];
    }

    public function forgotten(Request $request)
    {
        $setting    = plugin_setting('sms_bao');
        $login_type = isset($setting['login_type']) ? $setting['login_type'] : 1;
        if ($login_type != 2) {
            return response()->json([
                'code'    => -2,
                'message' => trans('SmsBao::login.invalid_number'),
            ]);
        }
        $plugin = app('plugin')->getPlugin('sms_bao');

        $codes = explode(',', $setting['mobile_codes']);

        $data = [
            'name'          => '优惠券管理',
            'description'   => $plugin->getLocaleDescription(),
            'country_codes' => $codes,
            'captcha_type'  => $setting['captcha_type'],
        ];

        return view('SmsBao::shop.forgotten_type_2', $data);
    }

    public function forgotten_update(Request $request)
    {
        $phone      = $request->telephone;
        $phone_code = $request->telephone_code;
        $phone_code = str_replace('+', '', $phone_code);

        // 检测是否为手机号码
        if (! $this->checkPhone($phone_code, $phone)) {
            return response()->json([
                'code'    => -1,
                'message' => trans('SmsBao::login.invalid_number'),
            ]);
        }

        $code    = $request->code;
        $key     = $this->getCodeCacheKey($phone_code, $phone);
        $oldCode = Cache::get($key);
        if ($code != $oldCode) {
            return response()->json([
                'code'    => -2,
                'message' => trans('SmsBao::login.invalid_code'),
            ]);
        }

        $customerMobile = CustomerMobile::query()->where('mobile_code', $phone_code)->where('mobile', $phone)->first();
        if (! $customerMobile) {
            return response()->json([
                'code'    => -3,
                'message' => trans('SmsBao::login.invalid_number'),
            ]);
        } else {
            CustomerRepo::update($customerMobile->customer, ['password' => $request->get('password')]);
        }

        return response()->json([
            'code'    => 0,
            'message' => trans('shop/account/edit.password_edit_success'),
        ]);

    }
}
