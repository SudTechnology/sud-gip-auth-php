<?php

namespace Sud\Gip\Auth\Api;

/**
 * 用户ID响应类
 */
class SudUid
{
    public $uid;
    public $isSuccess;
    public $errorCode;

    public function __construct($uid = null, $isSuccess = false, $errorCode = 0)
    {
        $this->uid = $uid;
        $this->isSuccess = $isSuccess;
        $this->errorCode = $errorCode;
    }
}