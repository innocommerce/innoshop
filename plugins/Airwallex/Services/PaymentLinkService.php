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
 * https://www.airwallex.com/docs/api#/Payment_Acceptance/Payment_Links/Intro
 */

class PaymentLinkService extends AbstractService
{
    public function get($id)
    {
        return $this->request('GET', '/pa/payment_links', ['id' => $id]);
    }

    public function create($params = [])
    {
        return $this->request('POST', '/pa/payment_links/create', $params);
    }
}
