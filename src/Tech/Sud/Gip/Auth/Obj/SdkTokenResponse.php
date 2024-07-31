<?php
namespace Tech\Sud\Gip\Auth\Obj;

class SdkTokenResponse
{
    private $token;
    private $expireDate;

    public function __construct($token, $expireDate)
    {
        $this->token = $token;
        $this->expireDate = $expireDate;
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
