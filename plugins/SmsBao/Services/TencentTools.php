<?php

namespace Plugin\SmsBao\Services;

use Illuminate\Http\Request;
use Matrix\Exception;

class TencentTools
{
    public function checkCaptcha(Request $request, $setting)
    {

        $ip                 = $request->getClientIp();
        $api_server         = 'https://captcha.tencentcloudapi.com';
        $captcha_id         = $setting['captcha_id'];
        $captcha_key        = $setting['captcha_key'];
        $Ticket             = $request->ticket;
        $Randstr            = $request->randstr;
        $tencent_secret_id  = $setting['tencent_secret_id'];
        $tencent_secret_key = $setting['tencent_secret_key'];
        $nonce              = time().rand(100, 999);

        $query1 = [
            'Action'    => 'DescribeCaptchaResult',
            'Timestamp' => time(),
            'Nonce'     => $nonce,
            'SecretId'  => $tencent_secret_id,
            'Version'   => '2019-07-22',
        ];

        $query2 = [
            'CaptchaType'  => '9',
            'Ticket'       => $Ticket,
            'Randstr'      => $Randstr,
            'UserIp'       => $ip,
            'CaptchaAppId' => $captcha_id,
            'AppSecretKey' => $captcha_key,
        ];
        $query = array_merge($query1, $query2);

        $signature          = $this->getSign($query, $tencent_secret_key);
        $query['Signature'] = $signature;
        $res                = $this->pushData($api_server, $query);

        $obj = json_decode($res, true);

        if ($obj['Response']['CaptchaCode'] == '1') {
            //缓存验证结果
            return true;
        }
        if (isset($obj['Response']) && isset($obj['Response']['Error']) && isset($obj['Response']['Error']['Message'])) {
            throw new Exception($obj['Response']['Error']['Message']);
        }

        return false;
    }

    // 计算签名
    private function getSign($param, $secretKey)
    {
        ksort($param);

        $signStr = 'POSTcaptcha.tencentcloudapi.com/?';
        foreach ($param as $key => $value) {
            $signStr = $signStr.$key.'='.$value.'&';
        }
        $signStr = substr($signStr, 0, -1);

        $signature = base64_encode(hash_hmac('sha1', $signStr, $secretKey, true));

        return $signature;
    }

    protected function pushData($api_url, $vars, $header = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);

        if (is_array($vars)) {
            $vars = http_build_query($vars, '', '&');
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);

        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($header != null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (isset($this->http_errors[$code])) {
            throw new \Exception('Response Http Error - '.$this->http_errors[$code], $code);
        }

        $code = curl_errno($ch);
        if ($code > 0) {
            throw new \Exception('Unable to connect to '.$api_url.' Error: '."$code :".curl_error($ch), $code);
        }

        curl_close($ch);

        return $response;
    }
}
