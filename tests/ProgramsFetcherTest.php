<?php

declare(strict_types=1);

namespace BVP\Tenjisagi\Tests;

use BVP\Tenjisagi\ProgramsFetcher;
use BVP\Tenjisagi\ProgramsNotFoundException;
use Carbon\CarbonImmutable as Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author shimomo
 */
final class ProgramsFetcherTest extends TestCase
{
    /**
     * @return void
     */
    #[Test]
    public function testFetchReturnsTheProgramsPayload(): void
    {
        $programs = [
            'stadiums' => [
                1 => [
                    'races' => [
                        1 => [
                            'preview' => ['racers' => []],
                            'result' => ['racers' => []],
                        ],
                    ],
                ],
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [], (string) json_encode(['programs' => $programs])),
        ]);

        $fetcher = new ProgramsFetcher(new Client(['handler' => HandlerStack::create($mock)]));

        self::assertSame($programs, $fetcher->fetch(Carbon::parse('2026-05-01')));
    }

    /**
     * @return void
     */
    #[Test]
    public function testFetchThrowsRuntimeExceptionWhenTheRequestFails(): void
    {
        $mock = new MockHandler([
            new ConnectException('connection failed', new Request('GET', 'https://example.com')),
        ]);

        $fetcher = new ProgramsFetcher(new Client(['handler' => HandlerStack::create($mock)]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch race program from:');

        $fetcher->fetch(Carbon::parse('2026-05-01'));
    }

    /**
     * @return void
     */
    #[Test]
    public function testFetchThrowsProgramsNotFoundExceptionOnA404Response(): void
    {
        $mock = new MockHandler([
            new Response(404, [], 'not found'),
        ]);

        $fetcher = new ProgramsFetcher(new Client(['handler' => HandlerStack::create($mock)]));

        $this->expectException(ProgramsNotFoundException::class);

        $fetcher->fetch(Carbon::parse('2026-07-16'));
    }

    /**
     * @return void
     */
    #[Test]
    public function testFetchThrowsPlainRuntimeExceptionOnANonNotFoundClientError(): void
    {
        $mock = new MockHandler([
            new Response(403, [], 'forbidden'),
        ]);

        $fetcher = new ProgramsFetcher(new Client(['handler' => HandlerStack::create($mock)]));

        try {
            $fetcher->fetch(Carbon::parse('2026-05-01'));
            self::fail('Expected a RuntimeException to be thrown.');
        } catch (RuntimeException $exception) {
            self::assertNotInstanceOf(ProgramsNotFoundException::class, $exception);
            self::assertStringContainsString('Failed to fetch race program from:', $exception->getMessage());
        }
    }
}
