<?php

declare(strict_types=1);

namespace Sud\Gip\Api;

use InvalidArgumentException;

/**
 * Calculate App Server Config (ASC) signatures independently of request signing.
 */
final class AscSigner
{
    /**
     * Return the 32-character lowercase hexadecimal HMAC-MD5 ASC signature.
     *
     * The message is the original UTF-8 app ID and the key is the original UTF-8
     * application secret. Do not append a newline, trim, hash the secret first,
     * or otherwise transform either value. Preserve any existing whitespace.
     *
     * This only calculates the ASC sign; it performs no HTTP I/O or caching.
     * Business requests and callbacks use RequestSigner (HMAC-SHA1) instead.
     * ASC signing does not impose the JWT HS256 32-byte key minimum.
     *
     * @throws InvalidArgumentException If either input is empty or invalid UTF-8.
     */
    public static function ascSign(string $appId, string $appSecret): string
    {
        if ($appId === '' || $appSecret === ''
            || preg_match('//u', $appId) !== 1 || preg_match('//u', $appSecret) !== 1) {
            throw new InvalidArgumentException('Invalid ASC signing input.');
        }

        return hash_hmac('md5', $appId, $appSecret);
    }
}
