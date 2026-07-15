#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use BVP\Tenjisagi\CourseChangeAnalyzer;
use BVP\Tenjisagi\CourseChangeRepository;
use BVP\Tenjisagi\ProgramsFetcher;
use Carbon\CarbonImmutable as Carbon;
use GuzzleHttp\Client;

$date = isset($argv[1]) ? Carbon::parse($argv[1]) : Carbon::today();
$directory = $argv[2] ?? __DIR__ . '/../docs/course-changes';

$fetcher = new ProgramsFetcher(new Client());
$programs = $fetcher->fetch($date);

$analyzer = new CourseChangeAnalyzer();
$stats = $analyzer->analyze($programs);

$repository = new CourseChangeRepository($directory);
$path = $repository->save($date, $stats);

fwrite(STDOUT, "Saved: {$path}" . PHP_EOL);
