<?php

namespace Plugin\LOffline;

use Illuminate\Http\Response;
use InnoShop\Common\Libraries\Dom;
use InnoShop\Common\Services\StateMachineService;
use InnoShop\Seller\Services\OrderSplitService;
use Plugin\LOffline\Models\OfflinePaymentConfigDescriptions;
use Plugin\LOffline\Models\OfflinePaymentOrder;

class Boot
{
    public function init(): void
    {
        listen_hook_filter('order.status_format', function ($status_format) {
            if ($status_format == 'order.cash_on_delivery') {
                $status_format = trans('LOffline::common.payment_name');
            }

            return $status_format;
        });

        listen_hook_filter('service.state_machine.machines', function ($data) {
            $order = $data['order'];
            if ($order->status == 'l_offline') {
                $data['machines']['l_offline'] = $data['machines']['paid'];
            }

            return $data;
        });

        listen_hook_filter('service.state_machine.all_statuses', function ($data) {
            $data[] = [
                'status' => 'l_offline',
                'name'   => trans('LOffline::common.payment_name'),
            ];

            listen_hook_filter('service.state_machine.machines', function ($data) {
                $data['machines'][StateMachineService::UNPAID][StateMachineService::PAID][] = function () use ($data) {
                    OrderSplitService::getInstance($data['order'])->split();
                };

                return $data;
            });

            return $data;
        });

        listen_hook_filter('service.payment.pay.l_offline.data', function ($data) {
            $offlinePD = OfflinePaymentConfigDescriptions::query()->where('locale', locale_code())->first();
            if ($offlinePD) {
                $data['offline_des'] = $offlinePD->content;
            } else {
                $data['offline_des'] = '';
            }

            return $data;
        });

        listen_hook_filter('panel.order.show.response', function (Response $response) {

            if (is_string($response->getOriginalContent())) {
                return $response;
            }
            $data = $response->getOriginalContent()->getData();

            $order = $data['order'];

            if ($order['billing_method_code'] != 'l_offline') {
                return $response;
            }
            $offlineP = OfflinePaymentOrder::query()->where('order_id', $order->id)->first();
            if (empty($offlineP)) {
                return $response;
            }

            $view = view('LOffline::panel.show_certificate_btn', ['order_id' => $data['order']->id])->render();

            return Dom::getInstance($response->getContent())->insertAfter('#status-app', $view);
        });

        listen_hook_filter('panel.component.sidebar.setting.routes', function ($data) {
            $data[] = [
                'route' => 'l_offline.save_config.index',
                'title' => '离线支付',
            ];

            return $data;
        });
    }
}
