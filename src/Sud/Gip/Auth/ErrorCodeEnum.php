<?php

namespace Sud\Gip\Auth;

/**
 * 错误码信息定义
 */
class ErrorCodeEnum
{
    const SUCCESS = [0, "成功"];
    const TOKEN_CREATE_FAILED = [1001, "Token创建失败"];
    const TOKEN_VERIFY_FAILED = [1002, "Token校验失败"];
    const TOKEN_DECODE_FAILED = [1003, "Token解析失败"];
    const TOKEN_INVALID = [1004, "Token非法"];
    const TOKEN_EXPIRED = [1005, "Token过期"];
    const APP_DATA_INVALID = [1101, "App数据非法"];
    const UNDEFINE = [9999, "未知错误"];

    public static function getMessage($code)
    {
        $constants = (new \ReflectionClass(__CLASS__))->getConstants();
        foreach ($constants as $constant => $value) {
            if ($value[0] === $code) {
                return $value[1];
            }
        }
        return null;
    }
}