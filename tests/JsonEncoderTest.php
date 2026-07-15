<?php

declare(strict_types=1);

namespace BVP\Tenjisagi\Tests;

use BVP\Tenjisagi\JsonEncoder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author shimomo
 */
final class JsonEncoderTest extends TestCase
{
    /**
     * @return void
     */
    #[Test]
    public function testEncodeReturnsJsonRoundTrippingToTheSamePayload(): void
    {
        $payload = ['foo' => 'bar', 'baz' => [1, 2, 3]];

        $json = JsonEncoder::encode($payload);

        self::assertSame($payload, json_decode($json, true));
    }

    /**
     * @return void
     */
    #[Test]
    public function testEncodeDoesNotEscapeUnicodeOrSlashes(): void
    {
        $json = JsonEncoder::encode(['name' => '田中 堅', 'url' => 'https://example.com/a']);

        self::assertStringContainsString('田中 堅', $json);
        self::assertStringContainsString('https://example.com/a', $json);
    }

    /**
     * @return void
     */
    #[Test]
    public function testEncodeThrowsWhenPayloadCannotBeEncoded(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to encode payload to JSON');

        JsonEncoder::encode(['invalid' => "\xB1\x31"]);
    }
}
