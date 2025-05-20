<?php

namespace Sud\Gip\Auth\Api;

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