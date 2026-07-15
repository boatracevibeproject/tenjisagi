<?php

declare(strict_types=1);

namespace BVP\Tenjisagi;

use Carbon\CarbonImmutable as Carbon;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

/**
 * @author shimomo
 */
final class ProgramsFetcher
{
    private const BASE_URL = 'https://boatraceopenapi.github.io/api-mirror/v1/%s/%s.json';

    /**
     * @param \GuzzleHttp\ClientInterface $client
     */
    public function __construct(private readonly ClientInterface $client)
    {
        //
    }

    /**
     * @param \Carbon\CarbonImmutable $date
     * @return array{
     *     stadiums: non-empty-array<int<1, 24>, array{
     *         races: non-empty-array<int<1, 12>, array{
     *             preview: array{
     *                 racers: non-empty-array<int<1, 6>, array{
     *                     entry_number: int<1, 6>,
     *                     course_number: ?int<1, 6>,
     *                 }>,
     *             },
     *             result: array{
     *                 racers: non-empty-array<int<1, 6>, array{
     *                     entry_number: int<1, 6>,
     *                     course_number: ?int<1, 6>,
     *                     number: ?int,
     *                 }>,
     *             },
     *         }>,
     *     }>,
     * }
     * @throws \RuntimeException
     */
    public function fetch(Carbon $date): array
    {
        $url = sprintf(self::BASE_URL, $date->format('Y'), $date->format('Ymd'));

        try {
            $response = $this->client->request('GET', $url);
        } catch (GuzzleException $exception) {
            throw new RuntimeException(
                "Failed to fetch race program from: {$url}: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        /**
         * @var array{
         *     programs: array{
         *         stadiums: non-empty-array<int<1, 24>, array{
         *             races: non-empty-array<int<1, 12>, array{
         *                 preview: array{
         *                     racers: non-empty-array<int<1, 6>, array{
         *                         entry_number: int<1, 6>,
         *                         course_number: ?int<1, 6>,
         *                     }>,
         *                 },
         *                 result: array{
         *                     racers: non-empty-array<int<1, 6>, array{
         *                         entry_number: int<1, 6>,
         *                         course_number: ?int<1, 6>,
         *                         number: ?int,
         *                     }>,
         *                 },
         *             }>,
         *         }>,
         *     },
         * }
         */
        $payload = JsonDecoder::decode((string) $response->getBody(), $url);

        return $payload['programs'];
    }
}
