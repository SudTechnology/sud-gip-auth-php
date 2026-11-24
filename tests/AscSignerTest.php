<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Sud\Gip\Api\AscSigner;

final class AscSignerTest extends TestCase
{
    /** Fixed vectors independently computed with Python and shared with Go. */
    public function testFixedVectors(): void
    {
        $vectors = [
            ['appId', 'appSecret', '1a5f39304181dbc2a7fe3c908acf5ef6'],
            ['应用ID', '应用密钥', '95216b1f04d8c9e8788a9ba35b11001d'],
            [' appId ', ' appSecret ', 'd963b97ffb9d6613b06d7931b6965684'],
            ["appId\n", 'appSecret', '5fa35ef2782656fe753e30f2dbbc1dfb'],
        ];
        foreach ($vectors as [$appId, $appSecret, $expected]) {
            self::assertSame($expected, AscSigner::ascSign($appId, $appSecret));
        }
    }

    /** Invalid credentials must fail without exposing their contents. */
    public function testInvalidInputs(): void
    {
        foreach ([['', 'secret'], ['app', ''], ["\xff", 'secret'], ['app', "\xff"]] as $input) {
            try {
                AscSigner::ascSign($input[0], $input[1]);
                self::fail('Invalid ASC input accepted.');
            } catch (InvalidArgumentException $exception) {
                self::assertSame('Invalid ASC signing input.', $exception->getMessage());
            }
        }
    }
}
