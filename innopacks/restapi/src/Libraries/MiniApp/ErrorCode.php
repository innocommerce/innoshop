<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *
 * error code Description
 * 41001: encodingAesKey 非法
 * 41003: aes 解密失败
 * 41004: 解密后得到的buffer非法
 * 41005: base64加密失败
 * 41016: base64解密失败
 */

namespace InnoShop\RestAPI\Libraries\MiniApp;

class ErrorCode
{
    public static int $OK = 0;

    public static int $IllegalAesKey = -41001;

    public static int $IllegalIv = -41002;

    public static int $IllegalBuffer = -41003;

    public static int $DecodeBase64Error = -41004;
}
