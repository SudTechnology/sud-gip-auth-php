<?php

namespace Sud\Gip\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Sud\Gip\Api\SdkTokenResponse;

/**
 * Token工具类
 */
class TokenUtils
{
    const UID_KEY = "uid";
    const APP_ID_KEY = "app_id";

    /**
     * 生成Token
     *
     * @param string $appId 应用ID
     * @param string $appSecret 应用密钥
     * @param string $uid 用户ID
     * @param int $expireDuration 过期时长（毫秒）
     * @return SdkTokenResponse
     */
    public static function token($appId, $appSecret, $uid, $expireDuration)
    {
        $claimMap = [
            self::UID_KEY => $uid,
            self::APP_ID_KEY => $appId
        ];
        return self::doCreateToken($appSecret, $claimMap, $expireDuration);
    }

    /**
     * 创建Token
     *
     * @param string $appSecret 应用密钥
     * @param array $claimMap 声明映射
     * @param int $expireDuration 过期时长（毫秒）
     * @return SdkTokenResponse
     */
    public static function doCreateToken($appSecret, $claimMap, $expireDuration)
    {
        $expireDate = time() + ($expireDuration / 1000);
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        $payload = array_merge($claimMap, ['exp' => $expireDate]);

        try {
            $token = JWT::encode($payload, $appSecret, 'HS256', null, $header);
            // 建议使用专业日志库替换 error_log
            error_log("Generated Token: " . $token);
            return new SdkTokenResponse($token, $expireDate * 1000);
        } catch (\Exception $e) {
            // 建议使用专业日志库替换 error_log
            error_log("Token generation failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 验证Token
     *
     * @param string $token Token
     * @param string $appSecret 应用密钥
     * @return array 错误码和消息
     */
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
        } catch (ExpiredException $e) {
            return ErrorCodeEnum::TOKEN_EXPIRED;
        } catch (\Exception $e) {
            return ErrorCodeEnum::TOKEN_VERIFY_FAILED;
        }
    }

    /**
     * 执行Token验证
     *
     * @param string $token Token
     * @param string $appSecret 应用密钥
     * @throws \Exception
     */
    public static function doVerify($token, $appSecret)
    {
        try {
            $decoded = JWT::decode($token, new Key($appSecret, 'HS256'));
            // 建议使用专业日志库替换 error_log
            error_log("Decoded Token: " . json_encode((array)$decoded));
            return $decoded;
        } catch (\Exception $e) {
            // 建议使用专业日志库替换 error_log
            error_log("Token verification failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 从Token获取UID
     *
     * @param string $token Token
     * @return string|null UID
     */
    public static function getUID($token, $appSecret)
    {
        return self::doGetValueFromToken($token, self::UID_KEY, $appSecret);
    }

    /**
     * 从Token获取AppID
     *
     * @param string $token Token
     * @return string|null AppID
     */
    public static function getAppID($token, $appSecret)
    {
        return self::doGetValueFromToken($token, self::APP_ID_KEY, $appSecret);
    }

    /**
     * 从Token获取值
     *
     * @param string $token Token
     * @param string $key 键
     * @return string|null 值
     */
    public static function doGetValueFromToken($token, $key, $appSecret)
    {
        try {
            $decoded = JWT::decode($token, new Key($appSecret, 'HS256'));
            // 建议使用专业日志库替换 error_log
            error_log("Decoded token claims: " . json_encode((array)$decoded));
            return isset($decoded->$key) ? $decoded->$key : null;
        } catch (\Exception $e) {
            // 建议使用专业日志库替换 error_log
            error_log("Failed to decode token: " . $e->getMessage());
            throw $e; // 抛出异常以便测试捕获
        }
    }
}