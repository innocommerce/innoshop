<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Libraries\MiniApp;

use EasyWeChat\Kernel\Exceptions\HttpException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\MiniApp\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InnoShop\Common\Repositories\Customer\SocialRepo;
use Symfony\Contracts\HttpClient\Exception;
use Throwable;

class Auth
{
    private Application $app;

    private string $code;

    private array $socialData;

    /**
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function __construct($code)
    {
        $options = $this->getOptions();
        if (empty($code)) {
            throw new \Exception('Empty code for MiniApp');
        }
        $this->code       = $code;
        $this->app        = new Application($options);
        $this->socialData = [];
    }

    /**
     * @param  $code
     * @return Auth
     * @throws InvalidArgumentException
     */
    public static function getInstance($code): self
    {
        return new self($code);
    }

    /**
     * @return array
     */
    private function getOptions(): array
    {
        $options = [
            'app_id' => plugin_setting('we_chat_mini.app_id'),
            'secret' => plugin_setting('we_chat_mini.app_secret'),
            'debug'  => true,
            'log'    => [
                'level' => 'debug',
                'file'  => storage_path('logs').'/easywechat.log',
            ],
        ];
        Log::info(json_encode($options));

        return $options;
    }

    /**
     * @return mixed
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws HttpException|Throwable
     */
    public function findOrCreateCustomerByCode(): mixed
    {
        if ($customer = $this->findCustomerByCode()) {
            return $customer;
        }

        $socialData = $this->getSocialData();
        $userData   = [
            'uid'    => $socialData['uid'],
            'email'  => '',
            'name'   => '',
            'avatar' => '',
            'token'  => $socialData['access_token'],
            'raw'    => '',
        ];

        return SocialRepo::getInstance()->createCustomer('miniapp', $userData);
    }

    /**
     * @return mixed
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws HttpException
     * @throws \Exception
     */
    public function findCustomerByCode(): mixed
    {
        $socialData = $this->getSocialData();
        if (! Schema::hasTable('customer_socials')) {
            $message = '第三方登录未安装，请到网站后台 插件 - 插件设置 - Social，安装';

            throw new \Exception($message);
        }

        $customerSocial = SocialRepo::getInstance()->getSocialByProviderAndUser('miniapp', $socialData['uid']);
        $customer       = $customerSocial->customer ?? null;
        if ($customer) {
            $socialData['customer_id'] = $customer->id;
            SocialRepo::getInstance()->createSocial($customer, 'miniapp', $socialData);

            return $customer;
        }

        return null;
    }

    /**
     * @return array
     * @throws HttpException
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     */
    private function getSocialData(): array
    {
        if ($this->socialData) {
            return $this->socialData;
        }

        $utils            = $this->app->getUtils();
        $session          = $utils->codeToSession($this->code);
        $this->socialData = [
            'uid'          => $session['openid'],
            'unionid'      => $session['unionid'] ?? '',
            'provider'     => 'miniapp',
            'access_token' => $session['session_key'],
        ];

        return $this->socialData;
    }
}
