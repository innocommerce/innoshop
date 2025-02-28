<?php

return [
    [
        'name'        => 'username',
        'label'       => '用户名',
        'type'        => 'string',
        'required'    => true,
        'rules'       => 'required',
        'description' => '短信宝注册的用户名,注册地址：http://www.smsbao.com/reg?r=3NNG',
    ],
    [
        'name'        => 'pwd',
        'label'       => '密码',
        'type'        => 'string',
        'required'    => true,
        'rules'       => 'required',
        'description' => '短信宝注册的帐户密码',
    ],
    [
        'name'        => 'model_cn',
        'label'       => '国内短信模板',
        'type'        => 'textarea',
        'required'    => true,
        'rules'       => 'required',
        'description' => '在短信宝后台国内短信申请一个验证码的模板',
    ],
    [
        'name'        => 'notify_model_cn',
        'label'       => '国内发货通知短信模板',
        'type'        => 'textarea',
        'required'    => true,
        'rules'       => 'required',
        'description' => '在短信宝后台国内短信申请一个发货通知的模板。如：您的订单{order_no}已经发货，请注意查收',
    ],
    [
        'name'        => 'model_other',
        'label'       => '国际短信模板',
        'type'        => 'textarea',
        'required'    => true,
        'rules'       => 'required',
        'description' => '在短信宝后台国际短信申请一个验证码的模板',
    ],
    [
        'name'    => 'login_type',
        'label'   => '登录注册方式',
        'type'    => 'select',
        'options' => [
            [
                'value' => '1',
                'label' => '邮箱密码登录+手机验证码登录+邮箱密码注册',
            ],
        /**
            [
                'value' => '2',
                'label' => '纯手机密码登录+手机密码注册'
            ],
            [
                'value' => '3',
                'label' => '纯手机验证码登录+无注册功能'
            ],
         * **/
        ],
        'required'    => true,
        'description' => '',
    ],
    [
        'name'        => 'notify_model_other',
        'label'       => '国际发货通知短信模板',
        'type'        => 'textarea',
        'required'    => true,
        'rules'       => 'required',
        'description' => '在短信宝后台国际短信申请一个发货通知的模板。如：您的订单{order_no}已经发货，请注意查收',
    ],
    [
        'name'        => 'mobile_codes',
        'label'       => '支持的区号',
        'type'        => 'textarea',
        'required'    => true,
        'rules'       => 'required',
        'description' => '国家区号，如中国是+86，多个用英文逗号隔开',
    ],
/**
    [
        'name'        => 'captcha_type',
        'label'       => '图形验证码类型',
        'type'        => 'select',
        'options'     => [
            [
                'value' => '0',
                'label' => '关闭'
            ],
            [
                'value' => '2',
                'label' => '腾讯'
            ],
        ],
        'required'    => true,
        'description' => '开启后有效防止机器人注册',
    ],
    [
        'name'        => 'tencent_secret_id',
        'label'       => '腾讯云 Captcha AppID',
        'type'        => 'string',
        'required'    => false,
        'description' => '腾讯云 Captcha SecretId ID',
        'description' => '腾讯平台的Secret ID Secret Key申请 https://console.cloud.tencent.com/cam/capi',

    ],
    [
        'name'        => 'tencent_secret_key',
        'label'       => '腾讯云 Secret Key',
        'type'        => 'string',
        'required'    => false,
        'description' => '腾讯云 SecretId Key',
        'description' => '腾讯平台的Secret ID Secret Key申请 https://console.cloud.tencent.com/cam/capi',
    ],

    [
        'name'        => 'remember_login_days',
        'label'       => '保持登录天数',
        'type'        => 'string',
        'required'    => false,
        'description' => '请填写天数',
        'description' => '默认是7天。填写保存后，会按填写的天数保持登录状态,用户不用频繁登录，浪费短信流量',
    ],
 * **/
];

//腾讯平台的图形验证码申请 https://console.cloud.tencent.com/captcha/graphical
//腾讯平台的Secret ID Secret Key申请 https://console.cloud.tencent.com/cam/capi
