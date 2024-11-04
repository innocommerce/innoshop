<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Libraries\MiniApp;

class BizDataCrypt
{
    private string $appid;

    private string $sessionKey;

    /**
     * @param  $appid
     * @param  $sessionKey
     */
    public function __construct($appid, $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid      = $appid;
    }

    /**
     * @param  $encryptedData
     * @param  $iv
     * @param  $data
     * @return int
     */
    public function decryptData($encryptedData, $iv, &$data): int
    {
        if (strlen($this->sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey = base64_decode($this->sessionKey);

        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, 'AES-128-CBC', $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);
        if ($dataObj == null) {
            return ErrorCode::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result;

        return ErrorCode::$OK;
    }
}
