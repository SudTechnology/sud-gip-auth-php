<?php

namespace Sud\Gip\Api;

/**
 * SDK Token响应类
 */
class SdkTokenResponse
{
    public $token;
    public $expireDate;

    public function __construct($token, $expireDate)
    {
        $this->token = $token;
        $this->expireDate = $expireDate;
    }
}