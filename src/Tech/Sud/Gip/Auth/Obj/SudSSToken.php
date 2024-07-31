<?php
namespace Tech\Sud\Mgp\Auth\Obj;

class SudSSToken
{
    private $token;
    private $expireDate;

    public static function createBySdkTokenResponse(SdkTokenResponse $sdkTokenResponse)
    {
        $instance = new self();
        $instance->token = $sdkTokenResponse->getToken();
        $instance->expireDate = $sdkTokenResponse->getExpireDate();
        return $instance;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getExpireDate()
    {
        return $this->expireDate;
    }
}
?>
