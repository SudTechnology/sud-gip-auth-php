<?php
namespace Tech\Sud\Gip\Auth;

use Tech\Sud\Gip\Auth\ErrorCodeEnum;
use Tech\Sud\Gip\Auth\Utils\TokenUtils;
use Tech\Sud\Gip\Auth\Obj\SudCode;

class SudGIPAuth
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
