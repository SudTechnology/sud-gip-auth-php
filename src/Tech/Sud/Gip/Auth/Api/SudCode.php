<?php
namespace Tech\Sud\Mgp\Auth\Api;

class SudCode
{
    private $code;
    private $expireDate;

    public static function createBySdkTokenResponse(SdkTokenResponse $sdkTokenResponse)
    {
        $instance = new self();
        $instance->code = $sdkTokenResponse->getToken();
        $instance->expireDate = $sdkTokenResponse->getExpireDate();
        return $instance;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getExpireDate()
    {
        return $this->expireDate;
    }
}
?>
