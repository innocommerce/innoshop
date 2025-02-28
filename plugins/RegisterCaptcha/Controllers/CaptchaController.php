<?php

namespace Plugin\RegisterCaptcha\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Plugin\RegisterCaptcha\Models\Otp;
use Plugin\RegisterCaptcha\Services\TencentTools;

class CaptchaController
{
    public function checkCaptcha(Request $request)
    {

        $setting = plugin_setting('register_captcha');
        if ($setting['captcha_type'] == 0) {//未开启
            $this->sendEmail($request);

            return response()->json([
                'code' => 0,
                'msg'  => '',
            ]);
        }

        if ($this->check($request, $setting)) {//验证通过
            $this->sendEmail($request);

            return response()->json([
                'code' => 0,
                'msg'  => '',
            ]);
        }

        return response()->json([
            'code' => -3,
            'msg'  => '图形验证码错误',
        ]);

    }

    private function sendEmail(Request $request)
    {
        $code = str_pad(mt_rand(10, 999999), 6, '0', STR_PAD_LEFT);
        //$code = 123456;
        //缓存验证结果
        $email = $request->email;
        //$ip    = $request->ip();
        //$key   = $ip . '-register_captcha_code-' . $email;
        //Cache::put($key, $code, Carbon::now()->addMinutes(5));

        $otp = Otp::query()->where('email', $email)->where('code', $code)->first();
        if (! $otp) {
            Otp::query()->insertGetId([
                'email'       => $email,
                'code'        => $code,
                'expire_time' => \Illuminate\Support\Carbon::now()->addMinutes(5)->toDateTimeString(),
            ]);
            $otp = Otp::query()->where('email', $email)->where('code', $code)->first();
        }
        $otp->notifyAdmin($email, 'RegisterCaptcha::front/reg_email_model', trans('RegisterCaptcha::login.email_subject'), ['code' => $code]);
    }

    public function send($email, $code) {}

    private function check(Request $request, $setting)
    {
        if (! isset($setting['captcha_id']) || empty($setting['captcha_id']) || ! isset($setting['captcha_key']) || empty($setting['captcha_key'])) {//
            Log::debug('check11111111');

            return false;
        }
        if (! isset($setting['tencent_secret_id']) || empty($setting['tencent_secret_id']) || ! isset($setting['tencent_secret_key']) || empty($setting['tencent_secret_key'])) {//
            Log::debug('check222222222');

            return false;
        }
        $tt = new TencentTools;

        return $tt->checkCaptcha($request, $setting);

    }
}
