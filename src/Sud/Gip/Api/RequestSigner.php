<?php

declare(strict_types=1);

namespace Sud\Gip\Api;

use InvalidArgumentException;

/**
 * Shared HMAC-SHA1 signing for outbound API requests and incoming callbacks.
 *
 * This component is independent of JWT authentication. It performs no HTTP I/O
 * and stores no nonce or token state. Use the issued application secret unchanged.
 */
final class RequestSigner
{
    private string $appId;
    private string $appSecret;

    /**
     * HMAC-SHA1 signing does not impose the JWT HS256 32-byte key minimum.
     * Metadata must be printable ASCII without spaces, quotes, backslashes, or
     * commas so it can be represented safely in a single Authorization header.
     */
    public function __construct(string $appId, string $appSecret)
    {
        if (!self::validField($appId) || trim($appSecret) === '') {
            throw new InvalidArgumentException('Invalid request signing credentials.');
        }
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * Return a lowercase hexadecimal HMAC-SHA1 digest.
     *
     * $rawBody must be the EXACT bytes sent or received. PHP strings preserve
     * bytes, including binary data. Never deserialize/reserialize JSON, trim,
     * normalize line endings, convert encodings, or decompress before signing.
     * An empty string represents an empty body. The final protocol LF is required.
     */
    public function generateSignature(string $timestamp, string $nonce, string $rawBody): string
    {
        if (!self::validField($timestamp) || !self::validField($nonce)) {
            throw new InvalidArgumentException('Invalid request signing metadata.');
        }
        $content = $this->appId . "\n" . $timestamp . "\n" . $nonce . "\n" . $rawBody . "\n";
        return hash_hmac('sha1', $content, $this->appSecret);
    }

    /**
     * Check the callback app ID and compare decoded digests in constant time.
     *
     * Call BEFORE parsing the original body. Malformed metadata/signatures return
     * false. This verifies integrity, not freshness: timestamp age, nonce storage,
     * and method/path/query binding are not enforced by this signing protocol.
     */
    public function verifySignature(
        string $appId,
        string $timestamp,
        string $nonce,
        string $rawBody,
        string $signature
    ): bool {
        if ($appId !== $this->appId || !self::validField($timestamp) || !self::validField($nonce)
            || preg_match('/\A[0-9a-f]{40}\z/', $signature) !== 1) {
            return false;
        }
        $expected = hex2bin($this->generateSignature($timestamp, $nonce, $rawBody));
        $provided = hex2bin($signature);
        return hash_equals($expected, $provided);
    }

    /**
     * Return the complete single-line Authorization VALUE, without its field name.
     * Send the same $rawBody bytes unchanged. Supply the API timestamp (Unix
     * milliseconds) and a fresh cryptographically random nonce from the caller.
     */
    public function generateAuthorization(string $timestamp, string $nonce, string $rawBody): string
    {
        $signature = $this->generateSignature($timestamp, $nonce, $rawBody);
        return sprintf(
            'Sud-Auth app_id="%s",timestamp="%s",nonce="%s",signature="%s"',
            $this->appId,
            $timestamp,
            $nonce,
            $signature
        );
    }

    private static function validField(string $value): bool
    {
        return preg_match('/\A[\x21-\x7e]+\z/', $value) === 1
            && strpbrk($value, '\\",') === false;
    }
}
