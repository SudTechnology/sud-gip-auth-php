<?php

namespace Sud\Gip\Auth\Api;

use Sud\Gip\Auth\ErrorCodeEnum;
use Sud\Gip\Auth\TokenUtils;

/**
 * GIP认证工具类
 */
class SudGIPAuth
{
    const DEFAULT_MAX_CODE_EXPIRE_DURATION = 2 * 60 * 60 * 1000;
    const DEFAULT_MIN_CODE_EXPIRE_DURATION = 30 * 60 * 1000;
    const DEFAULT_MIN_SSTOKEN_EXPIRE_DURATION = 2 * 60 * 60 * 1000;

    private $appId;
    private $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * 获取认证码
     *
     * @param string $uid 用户ID
     * @param int|null $expireDuration 过期时长（毫秒）
     * @return SudCode
     */
    public function getCode($uid, $expireDuration = null)
    {
        if ($expireDuration === null) {
            $expireDuration = self::DEFAULT_MAX_CODE_EXPIRE_DURATION;
        } elseif ($expireDuration < self::DEFAULT_MIN_CODE_EXPIRE_DURATION) {
            $expireDuration = self::DEFAULT_MIN_CODE_EXPIRE_DURATION;
        }

        $sdkTokenResponse = TokenUtils::token($this->appId, $this->appSecret, $uid, $expireDuration);
        return SudCode::createBySdkTokenResponse($sdkTokenResponse);
    }

    /**
     * 获取服务器到服务器Token
     *
     * @param string $uid 用户ID
     * @param int|null $expireDuration 过期时长（毫秒）
     * @return SudSSToken
     */
    public function getSSToken($uid, $expireDuration = null)
    {
        if ($expireDuration === null) {
            $expireDuration = self::DEFAULT_MIN_SSTOKEN_EXPIRE_DURATION;
        } elseif ($expireDuration < self::DEFAULT_MIN_SSTOKEN_EXPIRE_DURATION) {
            $expireDuration = self::DEFAULT_MIN_SSTOKEN_EXPIRE_DURATION;
        }

        $sdkTokenResponse = TokenUtils::token($this->appId, $this->appSecret, $uid, $expireDuration);
        return SudSSToken::createBySdkTokenResponse($sdkTokenResponse);
    }

    /**
     * 通过认证码获取用户ID
     *
     * @param string $code 认证码
     * @return SudUid
     */
    public function getUidByCode($code)
    {
        return $this->getSdkUidResponse($code);
    }

    /**
     * 通过SSToken获取用户ID
     *
     * @param string $ssToken SSToken
     * @return SudUid
     */
    public function getUidBySSToken($ssToken)
    {
        return $this->getSdkUidResponse($ssToken);
    }

    /**
     * 验证认证码
     *
     * @param string $code 认证码
     * @return array 错误码和消息
     */
    public function verifyCode($code)
    {
        return TokenUtils::verify($code, $this->appSecret);
    }

    /**
     * 验证SSToken
     *
     * @param string $token SSToken
     * @return array 错误码和消息
     */
    public function verifySSToken($token)
    {
        return TokenUtils::verify($token, $this->appSecret);
    }

    /**
     * 获取用户ID响应
     *
     * @param string $ssToken SSToken
     * @return SudUid
     */
// src/Api/SudGIPAuth.php
    private function getSdkUidResponse($ssToken)
    {
        $resp = new SudUid();
        $errorCodeEnum = $this->verifySSToken($ssToken);
        $rs = $errorCodeEnum[0] === ErrorCodeEnum::SUCCESS[0];
        $resp->isSuccess = $rs;

        if (!$rs) {
            $resp->errorCode = $errorCodeEnum[0];
            return $resp;
        }

        // ✅ 传递 $this->appSecret 作为第二个参数
        $resp->uid = TokenUtils::getUID($ssToken, $this->appSecret);
        return $resp;
    }
}