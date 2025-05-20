<?php

namespace Sud\Gip\Api;

use Sud\Gip\Api\SdkTokenResponse; // 添加命名空间引用

/**
 * 服务器到服务器Token类
 */
class SudSSToken
{
    public $token;
    public $expireDate;

    public function __construct($token, $expireDate)
    {
        $this->token = $token;
        $this->expireDate = $expireDate;
    }

    public static function createBySdkTokenResponse(SdkTokenResponse $sdkTokenResponse)
    {
        return new self($sdkTokenResponse->token, $sdkTokenResponse->expireDate);
    }
}