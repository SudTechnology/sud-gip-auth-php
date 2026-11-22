<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Sud\Gip\Api\RequestSigner;

final class RequestSignerTest extends TestCase
{
    /** Fixed vectors independently computed with Python and shared with Go. */
    public function testFixedVectorsAndAuthorization(): void
    {
        $signer = new RequestSigner('test-app', 'test-secret');
        $vectors = [
            ['', 'cb6d64fd0a3dfd78b19da2c7625ec2317b05ef8d'],
            ["{\r\n \"message\":\"用户\", \"n\":1\r\n}", '0e6f9c9c7ba300bb03ec32277b497b67a62054bc'],
            ["\x00\xff\n", 'd2d001cfd566df480128991a0a124ea6b373727d'],
        ];
        foreach ($vectors as [$body, $expected]) {
            $original = $body;
            self::assertSame($expected, $signer->generateSignature('1688464949817', 'fixed-nonce', $body));
            self::assertSame($original, $body);
            self::assertTrue($signer->verifySignature('test-app', '1688464949817', 'fixed-nonce', $body, $expected));
            self::assertSame(
                'Sud-Auth app_id="test-app",timestamp="1688464949817",nonce="fixed-nonce",signature="' . $expected . '"',
                $signer->generateAuthorization('1688464949817', 'fixed-nonce', $body)
            );
            self::assertFalse($signer->verifySignature('test-app', '1688464949817', 'fixed-nonce', $body . ' ', $expected));
            self::assertFalse($signer->verifySignature('other-app', '1688464949817', 'fixed-nonce', $body, $expected));
            self::assertFalse($signer->verifySignature('test-app', '1688464949818', 'fixed-nonce', $body, $expected));
            self::assertFalse($signer->verifySignature('test-app', '1688464949817', 'other-nonce', $body, $expected));
            $wrongKey = new RequestSigner('test-app', 'wrong-secret');
            self::assertFalse($wrongKey->verifySignature('test-app', '1688464949817', 'fixed-nonce', $body, $expected));
            foreach (['', strtoupper($expected), str_repeat('z', 40), str_repeat('0', 40), $expected . '00'] as $bad) {
                self::assertFalse($signer->verifySignature('test-app', '1688464949817', 'fixed-nonce', $body, $bad));
            }
        }
    }

    /** Invalid metadata must never yield ambiguous or multiline HTTP headers. */
    public function testInvalidMetadata(): void
    {
        $signer = new RequestSigner('app', 'secret');
        foreach (['', ' ', "a\nb", "a\rb", "a\x00b", 'a"b', 'a\\b', 'a,b', '用户'] as $bad) {
            foreach ([
                static fn() => new RequestSigner($bad, 'secret'),
                static fn() => $signer->generateSignature($bad, 'nonce', ''),
                static fn() => $signer->generateAuthorization('123', $bad, ''),
            ] as $operation) {
                try {
                    $operation();
                    self::fail('Invalid signing metadata accepted');
                } catch (InvalidArgumentException $exception) {
                    self::assertStringNotContainsString('secret', $exception->getMessage());
                }
            }
            self::assertFalse($signer->verifySignature('app', $bad, 'nonce', '', str_repeat('0', 40)));
            self::assertFalse($signer->verifySignature('app', '123', $bad, '', str_repeat('0', 40)));
        }
    }

    public function testEmptySecretRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RequestSigner('app', '');
    }

    public function testBlankSecretRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RequestSigner('app', '   ');
    }
}
