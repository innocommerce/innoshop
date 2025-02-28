<?php

namespace Plugin\SmsBao\Services;

class SmsBaoTools
{
    public function sendSmsBao($phone_code, $phone, $content)
    {
        $setting = plugin_setting('sms_bao');
        $codes   = explode(',', $setting['mobile_codes']);
        //print_r($phone_code);print_r($codes);exit;
        if (! in_array($phone_code, $codes) && ! in_array('+'.$phone_code, $codes)) {
            return '不支持该区号';
        }

        $statusStr = [
            '0'  => '短信发送成功',
            '-1' => '短信宝接口：参数不全',
            '-2' => '短信宝接口：服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！',
            '30' => '短信宝接口：密码错误',
            '40' => '短信宝接口：账号不存在',
            '41' => '短信宝接口：余额不足',
            '42' => '短信宝接口：帐户已过期',
            '43' => '短信宝接口：IP地址限制',
            '50' => '短信宝接口：内容含有敏感词',
            '51' => '短信宝接口：手机号码不正确',
        ];

        $smsapi = 'https://api.smsbao.com/';

        $user = $setting['username']; //短信平台帐号
        $pass = md5($setting['pwd']); //短信平台密码

        if (substr($phone_code, 0, 1) != '+') {
            $phone_code = '+'.$phone_code;
        }

        $sendurl = null;

        if ($phone_code == '+86') {//国内
            $sendurl = $smsapi.'sms?u='.$user.'&p='.$pass.'&m='.$phone.'&c='.urlencode($content);
        } else {
            $phone   = urlencode($phone_code).$phone;
            $sendurl = $smsapi.'wsms?u='.$user.'&p='.$pass.'&m='.$phone.'&c='.urlencode($content);
        }
        if (! empty($sendurl)) {
            $result = file_get_contents($sendurl);
            if ($result == 0) {
                return true;
            }

            //\Illuminate\Support\Facades\Log::debug($sendurl);
            return isset($statusStr[$result]) ? $statusStr[$result] : trans('SmsBao::login.invalid_number');
        }

        return trans('SmsBao::login.fail'); //'发送成功'
    }
}
