<?php

namespace Plugin\OrderEmail;

use Illuminate\Support\Facades\Log;
use InnoShop\Common\Services\StateMachineService;
use Plugin\OrderEmail\Services\OrderEmailService;

class Boot
{
    public function init(): void
    {

        //创建订单和支付后要执行
        listen_hook_filter('service.state_machine.machines', function ($data) {
            $setting = plugin_setting('order_email');
            if (! isset($setting['send_node']) || empty($setting['send_node']) || $setting['send_node'] == 1 || $setting['send_node'] == 3) {
                $that                                                                          = $this;
                $data['machines'][StateMachineService::CREATED][StateMachineService::UNPAID][] = function () use ($data) {
                    Log::debug('下单成功邮件触发');
                    //$that->sendEmail($data['order'], '下单成功');
                    OrderEmailService::httpGet(front_route('order_email.send', ['order_id' => $data['order']->id,
                        'status_str'                                                       => urlencode('下单成功'),
                    ]));
                };
            }
            if (! isset($setting['send_node']) || empty($setting['send_node']) || $setting['send_node'] == 2 || $setting['send_node'] == 3) {
                $that                                                                       = $this;
                $data['machines'][StateMachineService::UNPAID][StateMachineService::PAID][] = function () use ($data) {
                    Log::debug('支付成功邮件触发');
                    //$that->sendEmail($data['order'], '支付成功');
                    OrderEmailService::httpGet(front_route('order_email.send', ['order_id' => $data['order']->id,
                        'status_str'                                                       => urlencode('支付成功'),
                    ]));
                };
            }

            return $data;
        });

        //货到付款后要通过
        listen_hook_filter('plugin.cash.delivery.after', function ($data) {
            $setting = plugin_setting('order_email');
            if (! isset($setting['send_node']) || empty($setting['send_node']) || $setting['send_node'] == 2 || $setting['send_node'] == 3) {
                $this->sendEmail($data['order'], '货到付款');
            }

            return $data;
        });
    }
}
