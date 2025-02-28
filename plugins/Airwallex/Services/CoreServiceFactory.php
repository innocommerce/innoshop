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
 * This is the main service container factory.
 * it instantiates the service object based on the class key in $classMap array.
 */

class CoreServiceFactory
{
    private $client;

    private $services;

    private static $classMap = [
        'beneficiary'   => BeneficiaryService::class,
        'paymentLink'   => PaymentLinkService::class,
        'paymentIntent' => PaymentIntentService::class,
    ];

    public function __construct($client)
    {
        $this->client   = $client;
        $this->services = [];
    }

    protected function getServiceClass($name)
    {
        return array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }

    public function __get($name)
    {
        $serviceClass = $this->getServiceClass($name);
        if ($serviceClass !== null) {
            if (! array_key_exists($name, $this->services)) {
                $this->services[$name] = new $serviceClass($this->client);
            }

            return $this->services[$name];
        }

        return null;
    }
}
