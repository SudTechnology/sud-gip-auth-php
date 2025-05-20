<?php

namespace Sud\Gip\Api;

/**
 * 认证码类
 */
class SudCode
{
    public $code;
    public $expireDate;

    public function __construct($code, $expireDate)
    {
        $this->code = $code;
        $this->expireDate = $expireDate;
    }

    public static function createBySdkTokenResponse(SdkTokenResponse $sdkTokenResponse)
    {
        return new self($sdkTokenResponse->token, $sdkTokenResponse->expireDate);
    }
}