<?php

namespace Plugin\SmsBao\Services;

class SmsTools
{
    public function sendCode($phone_code, $phone, $sms_code)
    {
        $setting    = plugin_setting('sms_bao');
        $phone_code = str_replace('+', '', $phone_code);
        if ($phone_code == '86') {//国内
            $content = $setting['model_cn'];
        } else {
            $content = $setting['model_other'];
        }

        $content = str_replace('{user_name}', $phone, $content); //要发送的短信内容
        $content = str_replace('{code}', $sms_code, $content); //要发送的短信内容
        $content = str_replace('{time}', 5, $content); //要发送的短信内容

        return $this->sendSms($phone_code, $phone, $content);
    }

    public function sendNotify($phone_code, $phone, $order_no)
    {
        $setting    = plugin_setting('sms_bao');
        $phone_code = str_replace('+', '', $phone_code);
        if ($phone_code == '86') {//国内
            $content = $setting['notify_model_cn'];
        } else {
            $content = $setting['notify_model_other'];
        }

        $content = str_replace('{order_no}', $order_no, $content); //要发送的短信内容

        return $this->sendSms($phone_code, $phone, $content);
    }

    //TODO 可以调用不同的短信平台

    private function sendSms($phone_code, $phone, $content)
    {

        $smsPlatform = new SmsBaoTools;

        return $smsPlatform->sendSmsBao($phone_code, $phone, $content);
    }
}
