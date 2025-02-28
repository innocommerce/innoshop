<?php

namespace Plugin\RegisterCaptcha;

use Illuminate\Http\Request;
use Plugin\RegisterCaptcha\Models\Otp;
use Plugin\RegisterCaptcha\Services\JiYanTools;
use Plugin\RegisterCaptcha\Services\TencentTools;

class Boot
{
    private $js = [
        '1' => '<script src="https://static.geetest.com/v4/gt4.js"></script>',
        '2' => '<script src="https://turing.captcha.qcloud.com/TCaptcha.js"></script>',
    ];

    private function checkCaptchaJs($output, $captcha_type)
    {
        if (isset($this->js[$captcha_type])) {
            $js     = $this->js[$captcha_type];
            $result = strpos($output, $js);
            if ($result !== false) {
                return true;
            }
        }

        return false;
    }

    private function check(Request $request, $setting)
    {
        if ($setting['captcha_type'] == 0) {
            return true;
        }
        if (! isset($setting['captcha_id']) || empty($setting['captcha_id']) || ! isset($setting['captcha_key']) || empty($setting['captcha_key'])) {//
            return false;
        }
        if ($setting['captcha_type'] == 1) {
            $jy = new JiYanTools;

            return $jy->checkCaptcha($request, $setting);
        } elseif ($setting['captcha_type'] == 2) {
            if (! isset($setting['tencent_secret_id']) || empty($setting['tencent_secret_id']) || ! isset($setting['tencent_secret_key']) || empty($setting['tencent_secret_key'])) {//
                return false;
            }
            $tt = new TencentTools;

            return $tt->checkCaptcha($request, $setting);
        }
    }

    public function init(): void
    {
        //注册时验证是否通过了人机校验
        listen_hook_action('front.account.register.store.before', function (Request $request) {
            $request_data = $request;
            if (empty($request_data['email'])) {
                return;
            }
            $otp = Otp::query()->where('email', $request_data['email'])->orderByDesc('id')->first();
            if (! $otp) {//验证不通过
                throw new \Exception('code error(0)');
            } else {
                $code      = request()->code;
                $send_code = $otp->code;
                if ($code != $send_code) {
                    throw new \Exception(trans('RegisterCaptcha::login.invalid_code').'(1)');
                }
                if (strtotime($otp->expire_time) < time()) {
                    throw new \Exception(trans('RegisterCaptcha::login.invalid_code').'(2)');
                }
            }
        });

        //增加验证码
        listen_blade_insert('account.register.email.after', function ($data) {
            $setting      = plugin_setting('register_captcha');
            $captcha_type = $setting['captcha_type'];
            //检测是否要加载图形验证码js文件
            if ($captcha_type == 0) {
                $setting['js'] = '';
            } else {
                $setting['js'] = isset($this->js[$captcha_type]) ? $this->js[$captcha_type] : '';
            }
            //print_r($captcha_type);exit;
            $view = view('RegisterCaptcha::front.reg_code', $setting)->render();

            return $view;
        }, 3);
    }
}
