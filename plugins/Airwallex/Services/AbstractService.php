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
 * Abstract base class for all services.
 */

abstract class AbstractService
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    protected function request($method, $path, $params)
    {
        return $this->client->request($method, $path, $params);
    }
}
