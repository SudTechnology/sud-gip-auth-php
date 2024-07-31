<?php
namespace Tech\Sud\Mgp\Auth\Api;

use Tech\Sud\Mgp\Auth\ErrorCodeEnum;
use Tech\Sud\Mgp\Auth\Utils\TokenUtils;

class SudMGPAuth
{
    private $appId;
    private $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function getCode($uid, $expireDuration = 7200000)
    {
        $sdkTokenResponse = TokenUtils::token($this->appId, $this->appSecret, $uid, $expireDuration);
        return SudCode::createBySdkTokenResponse($sdkTokenResponse);
    }

    public function verifyCode($code)
    {
        return TokenUtils::verify($code, $this->appSecret);
    }
}
?>
