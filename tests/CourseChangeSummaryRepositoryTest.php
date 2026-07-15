<?php

declare(strict_types=1);

namespace BVP\Tenjisagi\Tests;

use BVP\Tenjisagi\CourseChangeSummaryRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author shimomo
 */
final class CourseChangeSummaryRepositoryTest extends TestCase
{
    /**
     * @var string
     */
    private string $directory;

    /**
     * @var string
     */
    private string $path;

    /**
     * @return void
     */
    #[Test]
    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/tenjisagi-test-' . uniqid();
        $this->path = $this->directory . '/summary.json';
    }

    /**
     * @return void
     */
    #[Test]
    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }

        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }

    /**
     * @return void
     */
    #[Test]
    public function testSaveCreatesTheDirectoryAndWritesTheFile(): void
    {
        $repository = new CourseChangeSummaryRepository($this->path);

        $path = $repository->save([]);

        self::assertSame($this->path, $path);
        self::assertFileExists($path);
    }

    /**
     * @return void
     */
    #[Test]
    public function testSaveSortsRacersByChangedRaceRateDescending(): void
    {
        $repository = new CourseChangeSummaryRepository($this->path);

        $stats = [
            1001 => ['number' => 1001, 'name' => 'A', 'race_count' => 10, 'changed_race_count' => 1, 'changed_race_rate' => 0.1],
            2002 => ['number' => 2002, 'name' => 'B', 'race_count' => 10, 'changed_race_count' => 9, 'changed_race_rate' => 0.9],
            3003 => ['number' => 3003, 'name' => 'C', 'race_count' => 10, 'changed_race_count' => 5, 'changed_race_rate' => 0.5],
        ];

        $path = $repository->save($stats);
        $saved = json_decode((string) file_get_contents($path), true);

        self::assertSame([2002, 3003, 1001], array_map('intval', array_keys($saved['racers'])));
        self::assertArrayHasKey('generated_at', $saved);
    }

    /**
     * @return void
     */
    #[Test]
    public function testSaveReplacesAnExistingFileInsteadOfMerging(): void
    {
        $repository = new CourseChangeSummaryRepository($this->path);

        $repository->save([
            1001 => ['number' => 1001, 'name' => 'A', 'race_count' => 10, 'changed_race_count' => 1, 'changed_race_rate' => 0.1],
        ]);

        $path = $repository->save([
            2002 => ['number' => 2002, 'name' => 'B', 'race_count' => 10, 'changed_race_count' => 9, 'changed_race_rate' => 0.9],
        ]);

        $saved = json_decode((string) file_get_contents($path), true);

        self::assertSame([2002], array_map('intval', array_keys($saved['racers'])));
    }
}
