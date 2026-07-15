<?php

declare(strict_types=1);

namespace BVP\Tenjisagi;

/**
 * @author shimomo
 */
final class CourseChangeAnalyzer
{
    /**
     * @param array{
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
     *                     name: ?string,
     *                 }>,
     *             },
     *         }>,
     *     }>,
     * } $programs
     * @return array<int, array{
     *     number: int,
     *     name: string,
     *     race_count: int,
     *     changed_race_count: int,
     *     changed_race_rate: float,
     * }>
     */
    public function analyze(array $programs): array
    {
        $stats = [];

        foreach ($programs['stadiums'] as $stadium) {
            foreach ($stadium['races'] as $race) {
                $this->accumulate($race, $stats);
            }
        }

        foreach ($stats as &$stat) {
            $stat['changed_race_rate'] = $stat['race_count'] >= 1
                ? round($stat['changed_race_count'] / $stat['race_count'], 4)
                : 0.0;
        }

        unset($stat);

        return $stats;
    }

    /**
     * @param array{
     *     preview: array{
     *         racers: non-empty-array<int<1, 6>, array{
     *             entry_number: int<1, 6>,
     *             course_number: ?int<1, 6>,
     *         }>,
     *     },
     *     result: array{
     *         racers: non-empty-array<int<1, 6>, array{
     *             entry_number: int<1, 6>,
     *             course_number: ?int<1, 6>,
     *             number: ?int,
     *             name: ?string,
     *         }>,
     *     },
     * } $race
     * @param array<int, array{
     *     number: int,
     *     name: string,
     *     race_count: int,
     *     changed_race_count: int,
     *     changed_race_rate: float,
     * }> $stats
     * @return void
     */
    private function accumulate(array $race, array &$stats): void
    {
        $previewByEntryNumber = [];

        foreach ($race['preview']['racers'] as $previewRacer) {
            if (isset($previewRacer['entry_number'])) {
                $previewByEntryNumber[$previewRacer['entry_number']] = $previewRacer;
            }
        }

        foreach ($race['result']['racers'] as $resultRacer) {
            $number = $resultRacer['number'] ?? null;
            $name = $resultRacer['name'] ?? null;
            $resultCourseNumber = $resultRacer['course_number'] ?? null;
            $previewCourseNumber = $previewByEntryNumber[$resultRacer['entry_number']]['course_number'] ?? null;

            if ($number === null || $name === null || $resultCourseNumber === null || $previewCourseNumber === null) {
                continue;
            }

            $stats[$number] ??= [
                'number' => $number,
                'name' => $name,
                'race_count' => 0,
                'changed_race_count' => 0,
                'changed_race_rate' => 0.0,
            ];

            $stats[$number]['race_count']++;

            if ($resultCourseNumber !== $previewCourseNumber) {
                $stats[$number]['changed_race_count']++;
            }
        }
    }
}
