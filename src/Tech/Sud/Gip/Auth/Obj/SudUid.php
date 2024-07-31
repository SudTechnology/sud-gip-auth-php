<?php
namespace Tech\Sud\Gip\Auth\Obj;

class SudUid
{
    private $uid;
    private $isSuccess;
    private $errorCode;

    public function setSuccess($success)
    {
        $this->isSuccess = $success;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }
}
?>
