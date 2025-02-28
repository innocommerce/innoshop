<?php

namespace Plugin\SmsBao\Services;

use Illuminate\Http\Request;

class JiYanTools
{
    public function checkCaptcha(Request $request, $setting)
    {
        $captcha_id  = $setting['captcha_id'];
        $captcha_key = $setting['captcha_key'];
        $api_server  = 'http://gcaptcha4.geetest.com';

        $lot_number     = $request->lot_number;
        $captcha_output = $request->captcha_output;
        $pass_token     = $request->pass_token;
        $gen_time       = $request->gen_time;
        $sign_token     = hash_hmac('sha256', $lot_number, $captcha_key);

        $query = [
            'lot_number'     => $lot_number,
            'captcha_output' => $captcha_output,
            'pass_token'     => $pass_token,
            'gen_time'       => $gen_time,
            'sign_token'     => $sign_token,
        ];
        $url = sprintf($api_server.'/validate'.'?captcha_id=%s', $captcha_id);
        $res = $this->post_request($url, $query);
        $obj = json_decode($res, true);
        if ($obj['result'] == 'success') {
            return true;
        } else {
            return false;
        }
    }

    private function post_request($url, $postdata)
    {
        $data    = http_build_query($postdata);
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $data,
                'timeout' => 5,
            ],
        ];
        $context = stream_context_create($options);
        $result  = file_get_contents($url, false, $context);
        if ($http_response_header[0] != 'HTTP/1.1 200 OK') {
            $result = [
                'result' => 'success',
                'reason' => 'request geetest api fail',
            ];

            return json_encode($result);
        } else {
            return $result;
        }
    }
}
