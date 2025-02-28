<?php

namespace Plugin\SmsBao;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Plugin\SmsBao\Models\CustomerLoginToken;
use Plugin\SmsBao\Models\CustomerMobile;
use Plugin\SmsBao\Services\SmsTools;

class Boot
{
    private $priority = 2;

    public function init(): void
    {

        listen_hook_filter('front.account.login.index.response', function (Response $response) {

            if (is_string($response->getOriginalContent())) {
                return $response;
            }
            //$data = $response->getOriginalContent()->getData();
            // 增加顶部切换和增加手机号登录框
            $setting                  = plugin_setting('sms_bao');
            $login_type               = isset($setting['login_type']) ? $setting['login_type'] : 1;
            $setting['login_type']    = $login_type;
            $mobile_codes             = isset($setting['mobile_codes']) ? $setting['mobile_codes'] : '+86';
            $codes                    = explode(',', $mobile_codes);
            $setting['country_codes'] = $codes;
            if ($login_type == 1) {
                $view = view('SmsBao::front.login_type_1', $setting)->render();
            } elseif ($login_type == 2) {
                $view = view('SmsBao::front.login_type_2', $setting)->render();
            } elseif ($login_type == 3) {
                $view = view('SmsBao::front.login_type_3', $setting)->render();
            }

            return $view;
        });

        /**
         * listen_blade_update('account.login.forget_password', function ($output, $data) {
         * $setting    = plugin_setting('sms_bao');
         * $login_type = isset($setting['login_type']) ? $setting['login_type'] : 1;
         * //隐藏密码
         * if ($login_type == 3) {
         * return '<el-form-item></el-form-item>';
         * } else if ($login_type == 2) {//
         * $codes                    = explode(",", $setting['mobile_codes']);
         * $setting['country_codes'] = $codes;
         * $view                     = view('SmsBao::shop.forgotten_link_type_2', $setting)->render();
         * return $view;
         * }
         * return $output;
         * }, $this->priority);
         * **/
        listen_hook_action('front.account.login.after', function ($customer) {//登录之后
            //Log::debug("front.account.login.after");
            $this->saveLogin();
        }, $this->priority);

        listen_hook_action('front.account.register.after', function ($customer) {//注册之后
            //Log::debug("front.account.register.after");
            $this->saveLogin();
        }, $this->priority);

        listen_hook_action('front.account.logout.before', function ($customer) {//退出登录
            $loginTokenKey = 'login_token_key';
            setcookie($loginTokenKey, '', 0, '/');
            $sessionLoginTokenKey = $loginTokenKey.'_'.session()->getId();
            Cache::delete($sessionLoginTokenKey);
        }, $this->priority);

        //订单发货通知
        /**
         * listen_hook_action('service.state_machine.change_status.after', function ($data) {
         * $status = $data['status'];
         * if ($status == StateMachineService::SHIPPED && $data['notify']) {
         * $order          = $data['order'];
         * $customer       = $order->customer;
         * $customerMobile = CustomerMobile::query()->where('customer_id', $customer->id)->first();
         * if ($customerMobile) {
         * $phone_code = $customer->mobile_code;
         * $smsTools   = new SmsTools();
         * $smsTools->sendNotify($phone_code, $customerMobile->mobile, $order->number);
         * }
         * }
         * }, $this->priority);
         * **/
    }

    private function saveLogin()
    {
        $customer = current_customer();
        if (! $customer) {
            return;
        }
        $userId     = $customer->id;
        $session_id = session_id();
        $index_id   = md5($userId.$session_id.time());
        $token      = md5($customer->id.$session_id.time().time());

        $plugin = plugin_setting('sms_bao');
        $day    = 7;
        if (isset($plugin['remember_login_days']) && ! empty($plugin['remember_login_days']) && is_numeric($plugin['remember_login_days'])) {
            $day = $plugin['remember_login_days'];
        }

        $expire     = 60 * 60 * 24 * $day; //7天
        $expireTime = time() + $expire;
        CustomerLoginToken::query()->insert([
            'user_id'     => $userId,
            'index_id'    => $index_id,
            'token'       => $token,
            'expire_time' => date('Y-m-d H:i:s', $expireTime),
        ]);
        $loginTokenKey = 'login_token_key';
        setcookie($loginTokenKey, $index_id.'.'.$token, $expireTime, '/');

        $sessionLoginTokenKey = $loginTokenKey.'_'.session()->getId();
        Cache::put($sessionLoginTokenKey, 1, $expireTime);

        //清理一下登录过期的数据
        CustomerLoginToken::query()->where('expire_time', '<', date('Y-m-d H:i:s', time() - $expire))->delete();

    }
}
