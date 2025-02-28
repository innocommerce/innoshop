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
 * This is the service class for Beneficiaries.
 * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Intents/Intro
 */

class PaymentIntentService extends AbstractService
{
    public function get($id)
    {
        return $this->request('GET', '/pa/payment_intents', ['id' => $id]);
    }

    public function create($params = [])
    {
        return $this->request('POST', '/pa/payment_intents/create', $params);
    }
}
