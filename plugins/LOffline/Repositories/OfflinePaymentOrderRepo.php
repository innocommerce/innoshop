<?php

namespace Plugin\LOffline\Repositories;

use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\BaseRepo;
use InnoShop\Common\Services\StateMachineService;
use Plugin\LOffline\Models\OfflinePaymentOrder;
use Throwable;

class OfflinePaymentOrderRepo extends BaseRepo
{
    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function img_upload($request)
    {
        // 判断上传的文件是否存在
        if ($request->hasFile('file')) {

            // 获取上传的文件
            $file = $request->file('file');
            // 判断文件是否上传成功
            if ($file->isValid()) {
                $upload_path = public_path('plugins/loffline/uploads');
                // 获取文件扩展名
                $ext = $file->getClientOriginalExtension();
                // 生成新的文件名
                $newName = md5(time().rand(0, 10000)).'.'.$ext;
                // 将文件移动到指定目录
                $file->move($upload_path, $newName);

                // 返回文件路径
                return [
                    'url'  => asset('/plugins/loffline/uploads/'.$newName),
                    'path' => $newName,
                ];
            }

        }
        throw new \Exception('文件上传上出错');
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function pay_result($request)
    {
        $imgs = $request->imgs;

        if (empty($imgs)) {
            throw new \Exception(trans('LOffline::common.certificate_empty'));
        }
        $order = Order::query()->where('number', $request->order_no)->first();
        if ($order && $order->status == StateMachineService::UNPAID) {

            OfflinePaymentOrder::query()->insert([
                'order_id' => $order->id,
                'imgs'     => json_encode($imgs, true),
            ]);

            StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
            //再修改为
            $order->status = 'l_offline';
            $order->update();

            return front_route('checkout.success', ['order_number' => $order->number]);
        }

        throw new \Exception('订单号错误');
    }
}
