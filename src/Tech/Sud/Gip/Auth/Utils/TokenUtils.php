<?php
namespace Tech\Sud\Gip\Auth\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Tech\Sud\Gip\Auth\ErrorCodeEnum;
use Tech\Sud\Gip\Auth\Obj\SdkTokenResponse;

class TokenUtils
{
    private static $UID_KEY = 'uid';
    private static $APP_ID_KEY = 'app_id';

    public static function token($appId, $appSecret, $uid, $expireDuration)
    {
        $claimMap = [
            self::$UID_KEY => $uid,
            self::$APP_ID_KEY => $appId
        ];
        return self::doCreateToken($appSecret, $claimMap, $expireDuration);
    }

    public static function doCreateToken($appSecret, $claimMap, $expireDuration)
    {
        $expireDate = time() + ($expireDuration / 1000);
        $key = $appSecret;
        $payload = array_merge($claimMap, ['exp' => $expireDate]);
        $token = JWT::encode($payload, $key, 'HS256');

        return new SdkTokenResponse($token, $expireDate);
    }

    public static function verify($token, $appSecret)
    {
        if (empty($token)) {
            return ErrorCodeEnum::TOKEN_INVALID;
        }
        if (empty($appSecret)) {
            return ErrorCodeEnum::APP_DATA_INVALID;
        }
        try {
            self::doVerify($token, $appSecret);
            return ErrorCodeEnum::SUCCESS;
        } catch (\Exception $e) {
            return self::handleTokenException($e);
        }
    }

    public static function doVerify($token, $appSecret)
    {
        JWT::decode($token, new Key($appSecret, 'HS256'));
    }

    public static function getUID($token)
    {
        return self::doGetValueFromToken($token, self::$UID_KEY);
    }

    public static function getAppID($token)
    {
        return self::doGetValueFromToken($token, self::$APP_ID_KEY);
    }

    public static function doGetValueFromToken($token, $key)
    {
        try {
            $decoded = (array) JWT::decode($token, new Key($appSecret, 'HS256'));
            return $decoded[$key] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function handleTokenException($e)
    {
        if ($e instanceof \Firebase\JWT\SignatureInvalidException) {
            return ErrorCodeEnum::TOKEN_VERIFY_FAILED;
        } else if ($e instanceof \Firebase\JWT\ExpiredException) {
            return ErrorCodeEnum::TOKEN_EXPIRED;
        } else {
            return ErrorCodeEnum::UNDEFINE;
        }
    }
}
?>
