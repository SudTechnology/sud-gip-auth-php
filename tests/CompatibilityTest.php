<?php

use PHPUnit\Framework\TestCase;
use Sud\Gip\Api\SudGIPAuth;
use Sud\Gip\Auth\TokenUtils;
use Sud\Gip\Auth\ErrorCodeEnum;

final class CompatibilityTest extends TestCase
{
    private const SECRET = '0123456789abcdef0123456789abcdef';

    public function testCodeAndSSTokenRoundTrip(): void
    {
        $auth = new SudGIPAuth('test-app', self::SECRET);
        $code = $auth->getCode('user-123');
        $token = $auth->getSSToken('user-123');
        self::assertSame('user-123', $auth->getUidByCode($code->code)->uid);
        self::assertSame('user-123', $auth->getUidBySSToken($token->token)->uid);
        self::assertGreaterThan(time() * 1000, $code->expireDate);
    }

    // Independently sign the legacy HS256 wire format, without JWT 7's encoder.
    private function legacyToken(int $expiry, string $secret = self::SECRET): string
    {
        $encode = static function (string $value): string {
            return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        };
        $body = $encode('{"typ":"JWT","alg":"HS256"}') . '.' .
            $encode(json_encode(['uid' => 'legacy-user', 'app_id' => 'test-app', 'exp' => $expiry]));
        return $body . '.' . $encode(hash_hmac('sha256', $body, $secret, true));
    }

    public function testLegacyTokenAndFailureResponses(): void
    {
        $auth = new SudGIPAuth('test-app', self::SECRET);
        self::assertSame('legacy-user', $auth->getUidByCode($this->legacyToken(time() + 3600))->uid);
        self::assertSame(ErrorCodeEnum::TOKEN_EXPIRED, $auth->verifyCode($this->legacyToken(time() - 60)));
        self::assertSame(ErrorCodeEnum::TOKEN_VERIFY_FAILED, $auth->verifyCode($this->legacyToken(time() + 3600, str_repeat('x', 32))));
        self::assertFalse($auth->getUidByCode('malformed')->isSuccess);
    }

    public function testShortSecretRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SudGIPAuth('test-app', str_repeat('x', 31));
    }

    public function testCreationFailureHasExplicitException(): void
    {
        $this->expectException(RuntimeException::class);
        TokenUtils::token('test-app', 'short', 'user', 3600000);
    }
}
