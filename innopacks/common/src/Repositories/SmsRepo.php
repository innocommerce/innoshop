<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

class SmsRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Get available SMS gateways
     *
     * @return array[]
     */
    public function getGateways(): array
    {
        $gateways = [
            ['code' => '', 'name' => trans('panel/setting.sms_gateway_none'), 'value' => ''],
            ['code' => 'yunpian', 'name' => '云片 (Yunpian)', 'value' => 'yunpian'],
            ['code' => 'aliyun', 'name' => '阿里云 (Aliyun)', 'value' => 'aliyun'],
            ['code' => 'tencent', 'name' => '腾讯云 (Tencent)', 'value' => 'tencent'],
            ['code' => 'huawei', 'name' => '华为云 (Huawei)', 'value' => 'huawei'],
            ['code' => 'qiniu', 'name' => '七牛云 (Qiniu)', 'value' => 'qiniu'],
            ['code' => 'juhe', 'name' => '聚合数据 (Juhe)', 'value' => 'juhe'],
            ['code' => 'yunzhixun', 'name' => '云之讯 (Yunzhixun)', 'value' => 'yunzhixun'],
            ['code' => 'huyi', 'name' => '互亿无线 (Huyi)', 'value' => 'huyi'],
            ['code' => 'luosimao', 'name' => '螺丝帽 (Luosimao)', 'value' => 'luosimao'],
            ['code' => 'yuntongxun', 'name' => '容联云通讯 (Yuntongxun)', 'value' => 'yuntongxun'],
            ['code' => 'rongcloud', 'name' => '融云 (Rongcloud)', 'value' => 'rongcloud'],
            ['code' => 'avatardata', 'name' => '阿凡达数据 (Avatardata)', 'value' => 'avatardata'],
            ['code' => 'baiwu', 'name' => '百悟科技 (Baiwu)', 'value' => 'baiwu'],
            ['code' => 'huaxin', 'name' => '华信 (Huaxin)', 'value' => 'huaxin'],
            ['code' => 'chuanglan', 'name' => '创蓝 (Chuanglan)', 'value' => 'chuanglan'],
            ['code' => 'sendcloud', 'name' => 'SendCloud', 'value' => 'sendcloud'],
            ['code' => 'baidu', 'name' => '百度云 (Baidu)', 'value' => 'baidu'],
            ['code' => 'ucloud', 'name' => 'UCloud', 'value' => 'ucloud'],
            ['code' => 'smsbao', 'name' => '短信宝 (Smsbao)', 'value' => 'smsbao'],
            ['code' => 'moduyun', 'name' => '摩杜云 (Moduyun)', 'value' => 'moduyun'],
        ];

        return fire_hook_filter('common.repo.sms.gateways', $gateways);
    }

    /**
     * Get registration URL for SMS gateway
     *
     * @param  string  $gateway
     * @return string
     */
    public function getGatewayRegisterUrl(string $gateway): string
    {
        $urls = [
            'yunpian'    => 'https://www.yunpian.com/',
            'aliyun'     => 'https://www.aliyun.com/product/sms',
            'tencent'    => 'https://cloud.tencent.com/product/sms',
            'huawei'     => 'https://www.huaweicloud.com/product/sms.html',
            'qiniu'      => 'https://www.qiniu.com/products/sms',
            'juhe'       => 'https://www.juhe.cn/service/sms',
            'yunzhixun'  => 'https://www.ucpaas.com/',
            'huyi'       => 'https://www.ihuyi.com/',
            'luosimao'   => 'https://luosimao.com/',
            'yuntongxun' => 'https://www.yuntongxun.com/',
            'rongcloud'  => 'https://www.rongcloud.cn/',
            'avatardata' => 'https://www.avatardata.cn/',
            'baiwu'      => 'https://www.baiwutong.com/',
            'huaxin'     => 'https://www.ihuxin.com/',
            'chuanglan'  => 'https://www.chuanglan.com/',
            'sendcloud'  => 'https://www.sendcloud.net/',
            'baidu'      => 'https://cloud.baidu.com/product/sms.html',
            'ucloud'     => 'https://www.ucloud.cn/site/product/usms.html',
            'smsbao'     => 'https://www.smsbao.com/',
            'moduyun'    => 'https://www.moduyun.com/',
        ];

        return $urls[$gateway] ?? '';
    }

    /**
     * Check if gateway requires template
     *
     * @param  string  $gateway
     * @return bool
     */
    public function requiresTemplate(string $gateway): bool
    {
        $templateGateways = [
            'aliyun',
            'tencent',
            'huawei',
            'qiniu',
            'baidu',
            'ucloud',
            'moduyun',
        ];

        return in_array($gateway, $templateGateways);
    }
}
