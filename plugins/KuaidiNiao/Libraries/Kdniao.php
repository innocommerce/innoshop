<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * @技术QQ群: 可登录官网https://www.kdniao.com/右侧查看技术群号
 * @see: https://kdniao.com/api-trackexpress
 * @copyright: 深圳市快金数据技术服务有限公司
 * ID和Key请到官网申请：https://kdniao.com/reg
 * 快递查询接口
 * 此接口用于向快递公司实时查询物流轨迹信息。该功能支持情况需查看技术文档。
 * 正式地址：https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx
 *
 *
 * 系统级参数
 * RequestData     String R 请求内容为JSON格式 详情可参考接口技术文档：https://www.kdniao.com/documents
 * EBusinessID     String R 用户ID
 * RequestType     String R 请求接口指令
 * DataSign        String R 数据内容签名，加密方法为：把(请求内容(未编码)+ApiKey)进行MD5加密--32位小写，然后Base64编码，最后进行URL(utf-8)编码
 * DataType        String R DataType=2，请求、返回数据类型均为JSON格式
 * 应用级参数
 * R-必填（Required），O-可选（Optional），C-报文中该参数在一定条件下可选（Conditional）
 * OrderCode      String(30)  O 订单编号
 * CustomerName     String(50)  C ShipperCode为SF时必填，需要在CustomerName赋值单号对应的寄件人或收件人的手机号后四位数字
 * ShipperCode      String(10)  O 快递公司编码 详细编码参考《快递鸟接口支持快递公司编码.xlsx》
 * LogisticCode     String(30)  R 快递单号
 *
 *
 * 请求示例
 * ZTO请求示例：
 * {
 * "OrderCode": "",
 * "LogisticCode": "638650888018",
 * }
 *
 * JD请求示例：
 * {
 * "OrderCode": "",
 * "CustomerName": "",
 * "LogisticCode": "JDVA00003618100",
 * }
 *
 * SF请求示例：
 * {
 * "OrderCode": "",
 * "CustomerName": "1234",
 * "LogisticCode": "SF00003618100",
 * }
 */

namespace Plugin\KuaidiNiao\Libraries;

use Exception;

class Kdniao
{
    private string $eBusinessID;

    private string $apiKey;

    private string $reqUrl = 'https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';

    public function __construct()
    {
        $this->eBusinessID = plugin_setting('kuaidi_niao', 'business_id');
        $this->apiKey      = plugin_setting('kuaidi_niao', 'api_key');
    }

    /**
     * @param  $code
     * @param  $number
     * @return array
     * @throws Exception
     */
    public function getTraces($code, $number): array
    {
        $traces    = $this->getOrderTracesByJson($code, $number);
        $traceJson = json_decode($traces, true);

        if (! $traceJson['Success']) {
            throw new Exception($traceJson['Reason']);
        }

        return [
            'ship_code'   => $traceJson['ShipperCode'],
            'ship_number' => $traceJson['LogisticCode'],
            'traces'      => $this->handleTraces($traceJson['Traces']),
        ];
    }

    /**
     * @param  $originTraces
     * @return array
     */
    private function handleTraces($originTraces): array
    {
        $traces = [];
        foreach ($originTraces as $traceItem) {
            $traces[] = [
                'time'    => $traceItem['AcceptTime'],
                'station' => $traceItem['AcceptStation'],
            ];
        }

        return $traces;
    }

    /**
     * @param  $code
     * @param  $number
     * @return false|string
     */
    public function getOrderTracesByJson($code, $number): false|string
    {
        $requestData = [
            'OrderCode'    => '',
            'CustomerName' => $code,
            'LogisticCode' => $number,
        ];
        $requestData = json_encode($requestData);

        $datas = [
            'EBusinessID' => $this->eBusinessID,
            'RequestType' => '8002', //快递查询接口指令8002/地图版快递查询接口指令8004
            'RequestData' => urlencode($requestData),
            'DataType'    => '2',
        ];

        $datas['DataSign'] = $this->encrypt($requestData, $this->apiKey);

        return $this->sendPost($this->reqUrl, $datas);
    }

    /**
     * post提交数据
     *
     * @param  string  $url  请求Url
     * @param  array  $datas  提交的数据
     * @return string
     */
    public function sendPost(string $url, array $datas): string
    {
        $postData = http_build_query($datas);
        $options  = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postData,
                'timeout' => 15 * 60, // 超时时间（单位:s）
            ],
        ];
        $context = stream_context_create($options);
        $result  = file_get_contents($url, false, $context);

        return (string) $result;
    }

    /**
     * 电商Sign签名生成
     *
     * @param  $data
     * @param  $ApiKey
     * @return string
     */
    public function encrypt($data, $ApiKey): string
    {
        return urlencode(base64_encode(md5($data.$ApiKey)));
    }
}
