<?php

declare(strict_types=1);

namespace BVP\Tenjisagi\Tests;

use BVP\Tenjisagi\CourseChangeRepository;
use Carbon\CarbonImmutable as Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author shimomo
 */
final class CourseChangeRepositoryTest extends TestCase
{
    /**
     * @var string
     */
    private string $directory;

    /**
     * @return void
     */
    #[Test]
    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/tenjisagi-test-' . uniqid();
    }

    /**
     * @return void
     */
    #[Test]
    protected function tearDown(): void
    {
        if (is_dir($this->directory)) {
            array_map('unlink', glob($this->directory . '/*.json') ?: []);
            rmdir($this->directory);
        }
    }

    /**
     * @return void
     */
    #[Test]
    public function testSaveCreatesTheDirectoryAndWritesTheFile(): void
    {
        $repository = new CourseChangeRepository($this->directory);

        $path = $repository->save(Carbon::parse('2026-05-01'), []);

        self::assertSame($this->directory . DIRECTORY_SEPARATOR . '20260501.json', $path);
        self::assertFileExists($path);
    }

    /**
     * @return void
     */
    #[Test]
    public function testSaveWritesTheDateAndRacersSortedByNumberAscending(): void
    {
        $repository = new CourseChangeRepository($this->directory);

        $stats = [
            3002 => ['number' => 3002, 'name' => 'B', 'race_count' => 1, 'changed_race_count' => 0, 'changed_race_rate' => 0.0],
            1001 => ['number' => 1001, 'name' => 'A', 'race_count' => 1, 'changed_race_count' => 1, 'changed_race_rate' => 1.0],
        ];

        $path = $repository->save(Carbon::parse('2026-05-01'), $stats);
        $saved = json_decode((string) file_get_contents($path), true);

        self::assertSame('2026-05-01', $saved['date']);
        self::assertSame([1001, 3002], array_map('intval', array_keys($saved['racers'])));
    }
}
