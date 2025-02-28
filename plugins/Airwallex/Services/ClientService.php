<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Airwallex\Services;

/*
 * This is the main Airwallex API client class.
 */

use Exception;

class ClientService
{
    const API_URL = 'https://api.airwallex.com/api/v1';

    const TEST_API_URL = 'https://api-demo.airwallex.com/api/v1';

    private $apiKey;

    private $clientId;

    private $config;

    private $factory;

    public function __construct($config = [])
    {
        $this->config = [
            'production' => false, //test or prod environment?
        ];

        foreach ($config as $key => $item) {
            switch ($key) {
                case 'clientId':
                    $this->clientId = $item;
                    break;
                case 'apiKey':
                    $this->apiKey = $item;
                    break;
                default:
                    $this->config[$key] = $item;
                    break;
            }
        }
    }

    public function __get($name)
    {
        if ($this->factory === null) {
            $this->factory = new CoreServiceFactory($this);
        }

        return $this->factory->__get($name);
    }

    //execute curl
    public function request($method, $path, $params = [])
    {
        $token = $this->authenticate();

        $headers = [
            'Authorization' => $token,
        ];
        $response = $this->curl($method, $path, $headers, $params);

        return $response;
    }

    protected function getApiUrl()
    {
        return $this->config['production'] === true ? self::API_URL : self::TEST_API_URL;
    }

    protected function authenticate()
    {
        //prepare credentials (login)
        $headers = [
            'x-client-id' => $this->getClientId(),
            'x-api-key'   => $this->getApiKey(),
        ];

        [$code, $response] = $this->curl('POST', '/authentication/login', $headers);

        if (! ($response->token ?? '')) {
            throw new Exception($response->message);
        }

        return $response->token;
    }

    //curl wrapper
    protected function curl($method, $url, $headers = [], $params = [])
    {
        $headers     = array_merge(['Content-Type' => 'application/json'], $headers);
        $curlHeaders = [];

        foreach ($headers as $key => $header) {
            if ($key === 'Authorization') {
                $curlHeaders[] = "$key: Bearer ".$header;
            } else {
                $curlHeaders[] = "$key: ".$header;
            }
        }

        $curl = curl_init();

        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                $params = json_encode($params);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            case 'GET':
                if ($params['id']) {
                    $url = $url.'/'.$params['id'];
                }

                unset($params['id']);

                if (count($params) > 0) {
                    $url = $url.'?'.http_build_query($params);
                }
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $this->getApiUrl().$url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);

        $result = curl_exec($curl);

        $result = json_decode($result);
        $code   = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return [$code, $result];
    }

    /*
     * @return string the API key used for requests
     */
    private function getApiKey()
    {
        return $this->apiKey;
    }

    /*
     * @return string the client_id used for requests
     */
    private function getClientId()
    {
        return $this->clientId;
    }
}
