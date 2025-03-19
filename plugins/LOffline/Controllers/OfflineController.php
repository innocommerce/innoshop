<?php

namespace Plugin\LOffline\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plugin\LOffline\Repositories\OfflinePaymentOrderRepo;

class OfflineController
{
    public function imgUpload(Request $request)
    {

        try {
            $rs = OfflinePaymentOrderRepo::getInstance()->img_upload($request);

            return submit_json_success($rs);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

    }

    /**
     * 支付完后跳转页面
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function pay_result(Request $request)
    {
        try {
            $rs = OfflinePaymentOrderRepo::getInstance()->pay_result($request);

            return submit_json_success(['callback' => $rs]);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

    }
}
