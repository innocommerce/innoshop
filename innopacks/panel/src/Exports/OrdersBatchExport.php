<?php

namespace InnoShop\Panel\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersBatchExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct(Collection $orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            '订单号',
            '下单时间',
            '客户名称',
            '业务员',
            '订单状态',
            '总金额',
            '支付方式',
            '配送方式',
            '收货人',
            '收货电话',
            '收货地址',
            '备注',
        ];
    }

    public function map($order): array
    {
        return [
            $order->number,
            $order->created_at,
            $order->customer_name ?? ($order->customer->name ?? ''),
            $order->sales_name    ?? '',
            $order->status_format ?? $order->status,
            $order->total_format,
            $order->billing_method_name,
            $order->shipping_method_name,
            $order->shipping_customer_name,
            $order->shipping_telephone,
            $order->shipping_address_1.' '.$order->shipping_city.' '.$order->shipping_state.' '.$order->shipping_country,
            $order->comment,
        ];
    }
}
