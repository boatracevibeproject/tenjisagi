#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use BVP\Tenjisagi\CourseChangeAggregator;
use BVP\Tenjisagi\CourseChangeSummaryRepository;

$directory = $argv[1] ?? __DIR__ . '/../docs/course-changes';
$path = $argv[2] ?? __DIR__ . '/../docs/course-changes-summary.json';

$aggregator = new CourseChangeAggregator();
$stats = $aggregator->aggregate($directory);

$repository = new CourseChangeSummaryRepository($path);
$savedPath = $repository->save($stats);

fwrite(STDOUT, "Saved: {$savedPath}" . PHP_EOL);
