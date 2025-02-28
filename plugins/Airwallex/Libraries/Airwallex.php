<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Airwallex\Libraries;

class Airwallex
{
    const API_URL = 'https://api.airwallex.com/api/v1';

    const TEST_API_URL = 'https://api-demo.airwallex.com/api/v1';

    private string $gatewayUrl;

    /**
     * @param  $testMode
     */
    public function __construct($testMode = null)
    {
        if (is_null($testMode)) {
            $testMode = (bool) plugin_setting('airwallex', 'test_mode');
        }
        if ($testMode) {
            $this->gatewayUrl = self::TEST_API_URL;
        } else {
            $this->gatewayUrl = self::API_URL;
        }
    }

    /**
     * @param  $clientID
     * @param  $apiKey
     * @return mixed
     */
    public function getToken($clientID, $apiKey): mixed
    {
        $headerArray = [
            'Content-type:application/json',
            "x-client-id:$clientID",
            "x-api-key:$apiKey",
        ];
        $submitUrl  = $this->gatewayUrl.'/authentication/login';
        $output     = $this->postCurl($submitUrl, $headerArray, null);
        $tokenArray = json_decode($output, true);

        return $tokenArray['token'];
    }

    /**
     * @param  $token
     * @param  $amount
     * @param  $currency
     * @param  $merchantOrderID
     * @param  $returnUrl
     * @return mixed
     */
    public function initializePayment($token, $amount, $currency, $merchantOrderID, $returnUrl): mixed
    {
        $headerArray = [
            'Content-type: application/json',
            "Authorization: Bearer $token",
        ];
        $submitUrl = $this->gatewayUrl.'/pa/payment_intents/create';
        $data      = [
            'request_id'        => uniqid(),
            'amount'            => $amount,
            'currency'          => $currency,
            'merchant_order_id' => $merchantOrderID,
            'return_url'        => $returnUrl,
        ];
        $output       = $this->postCurl($submitUrl, $headerArray, json_encode($data));
        $result_array = json_decode($output, true);

        return $result_array['id'];
    }

    /**
     * @param  $token
     * @param  $paymentID
     * @return mixed
     */
    public function obtainAlipayBrowserUrl($token, $paymentID): mixed
    {
        $headerArray = [
            'Content-type: application/json',
            "Authorization: Bearer $token",
        ];
        $submitUrl = $this->gatewayUrl.'/pa/payment_intents/'.$paymentID.'/confirm';

        if (self::checkMobile()) {
            $data = [
                'request_id'     => uniqid(),
                'payment_method' => [
                    'type'     => 'alipaycn',
                    'alipaycn' => [
                        'flow'    => 'mweb',
                        'os_type' => 'android',
                    ],
                ],
            ];
        } else {
            $data = [
                'request_id'     => uniqid(),
                'payment_method' => [
                    'type'     => 'alipaycn',
                    'alipaycn' => [
                        'flow' => 'webqr',
                    ],
                ],
            ];
        }
        $output       = $this->postCurl($submitUrl, $headerArray, json_encode($data));
        $result_array = json_decode($output, true);
        $action_array = $result_array['next_action'];

        return $action_array['url'];
    }

    /**
     * @param  $token
     * @param  $id
     * @return mixed
     */
    public function queryOrder($token, $id): mixed
    {
        $headerArray = [
            'Content-type: application/json',
            "Authorization: Bearer $token",
        ];
        $queryUrl = $this->gatewayUrl.'/pa/payment_intents/'.$id;
        $response = $this->getCurl($queryUrl, $headerArray);

        return json_decode($response, true);
    }

    /**
     * @param  $timestamp
     * @param  $signature
     * @param  $sign_key
     * @param  $body
     * @return bool
     */
    public function verifySignature($timestamp, $signature, $sign_key, $body): bool
    {
        $value_to_digest    = $timestamp + $body;
        $generate_signature = hash_hmac('sha256', $value_to_digest, $sign_key);
        if ($generate_signature == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param  $url
     * @param  $header
     * @param  $data
     * @return bool|string
     */
    private function postCurl($url, $header, $data): bool|string
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }

    /**
     * @param  $url
     * @param  $header
     * @return bool|string
     */
    private function getCurl($url, $header): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * @return bool
     */
    private function checkMobile(): bool
    {
        return is_mobile();
    }
}
