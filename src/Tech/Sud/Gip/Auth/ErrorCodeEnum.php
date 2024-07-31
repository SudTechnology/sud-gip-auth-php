<?php
namespace Tech\Sud\Gip\Auth;

class ErrorCodeEnum
{
    const SUCCESS = 0;
    const TOKEN_CREATE_FAILED = 1001;
    const TOKEN_VERIFY_FAILED = 1002;
    const TOKEN_DECODE_FAILED = 1003;
    const TOKEN_INVALID = 1004;
    const TOKEN_EXPIRED = 1005;
    const APP_DATA_INVALID = 1101;
    const UNDEFINE = 9999;

    private static $messages = [
        self::SUCCESS => '成功',
        self::TOKEN_CREATE_FAILED => 'Token创建失败',
        self::TOKEN_VERIFY_FAILED => 'Token校验失败',
        self::TOKEN_DECODE_FAILED => 'Token解析失败',
        self::TOKEN_INVALID => 'Token非法',
        self::TOKEN_EXPIRED => 'Token过期',
        self::APP_DATA_INVALID => 'App数据非法',
        self::UNDEFINE => '未知错误'
    ];

    public static function getMessage($code) {
        return self::$messages[$code] ?? '未知错误';
    }
}
?>
