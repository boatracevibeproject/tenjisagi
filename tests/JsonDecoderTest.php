<?php

declare(strict_types=1);

namespace BVP\Tenjisagi\Tests;

use BVP\Tenjisagi\JsonDecoder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author shimomo
 */
final class JsonDecoderTest extends TestCase
{
    /**
     * @return void
     */
    #[Test]
    public function testDecodeReturnsArrayForValidJson(): void
    {
        $payload = JsonDecoder::decode('{"foo":"bar","baz":[1,2,3]}', 'test.json');

        self::assertSame(['foo' => 'bar', 'baz' => [1, 2, 3]], $payload);
    }

    /**
     * @return void
     */
    #[Test]
    public function testDecodeThrowsForMalformedJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode JSON from test.json');

        JsonDecoder::decode('{invalid json', 'test.json');
    }

    /**
     * @return void
     */
    #[Test]
    public function testDecodeThrowsWhenPayloadIsNotAnArray(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('payload is not an array');

        JsonDecoder::decode('"just a string"', 'test.json');
    }
}
