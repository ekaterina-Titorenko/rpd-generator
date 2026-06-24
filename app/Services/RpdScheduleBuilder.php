<?php

namespace App\Services;

use App\Models\RpdProgram;

class RpdScheduleBuilder
{
    public function ensureGeneratedIfEmpty(RpdProgram $rpdProgram): bool
    {
        $rpdProgram->loadMissing([
            'curriculumItems' => fn($query) => $query->orderBy('sort_order'),
            'scheduleItems',
        ]);

        if ($rpdProgram->curriculumItems->where('type', 'section')->isEmpty()) {
            return false;
        }

        if ($rpdProgram->scheduleItems->isNotEmpty()) {
            return false;
        }

        $this->generate($rpdProgram);

        return true;
    }

    public function regenerate(RpdProgram $rpdProgram): void
    {
        $rpdProgram->forceFill([
            'schedule_weeks_count' => null,
        ])->save();

        $rpdProgram->scheduleItems()->delete();

        $rpdProgram->load([
            'curriculumItems' => fn($query) => $query->orderBy('sort_order'),
        ]);

        $this->generate($rpdProgram);
    }

    public function recommendedWeeksCount(RpdProgram $rpdProgram): int
    {
        $hoursPerWeek = $this->hoursPerWeek($rpdProgram);

        if ($hoursPerWeek <= 0) {
            return 1;
        }

        $rpdProgram->loadMissing('curriculumItems');

        $sectionsHours = (int) $rpdProgram->curriculumItems
            ->where('type', 'section')
            ->sum('total_hours');

        $totalHours = max((int) $rpdProgram->total_hours, $sectionsHours);

        return max(1, (int) ceil($totalHours / $hoursPerWeek));
    }

    public function calculateWeeksCount(RpdProgram $rpdProgram): int
    {
        if ($rpdProgram->schedule_weeks_count) {
            return (int) $rpdProgram->schedule_weeks_count;
        }

        return $this->recommendedWeeksCount($rpdProgram);
    }

    public function hoursPerWeek(RpdProgram $rpdProgram): int
    {
        return max(1, $this->maxLessonsPerWeek($rpdProgram) * (int) $rpdProgram->academic_hours_per_lesson);
    }

    public function makeWeekTotals(RpdProgram $rpdProgram, int $weeksCount): array
    {
        $totals = array_fill(1, $weeksCount, 0);

        foreach ($rpdProgram->scheduleItems as $item) {
            $weekNumber = (int) $item->week_number;

            if ($weekNumber < 1 || $weekNumber > $weeksCount) {
                continue;
            }

            $totals[$weekNumber] += $this->extractTotalHours((string) $item->content);
        }

        return $totals;
    }

    public function extractHoursByType(string $content, string $type): int
    {
        preg_match_all('/' . preg_quote($type, '/') . '\s*\((\d+)\)/u', $content, $matches);

        return collect($matches[1] ?? [])
            ->map(fn($value) => (int) $value)
            ->sum();
    }

    private function generate(RpdProgram $rpdProgram): void
    {
        $weeksCount = $this->calculateWeeksCount($rpdProgram);
        $currentWeek = 1;
        $remainingWeekHours = $this->hoursPerWeek($rpdProgram);

        foreach ($rpdProgram->curriculumItems->where('type', 'section') as $item) {
            $remainingTheory = (int) $item->theory_hours;
            $remainingPractice = (int) $item->practice_hours;

            while (($remainingTheory > 0 || $remainingPractice > 0) && $currentWeek <= $weeksCount) {
                $cellParts = [];

                if ($remainingTheory > 0 && $remainingWeekHours > 0) {
                    $hours = min($remainingTheory, $remainingWeekHours);

                    $cellParts[] = "Т ({$hours})";
                    $remainingTheory -= $hours;
                    $remainingWeekHours -= $hours;
                }

                if ($remainingPractice > 0 && $remainingWeekHours > 0) {
                    $hours = min($remainingPractice, $remainingWeekHours);

                    $cellParts[] = "П ({$hours})";
                    $remainingPractice -= $hours;
                    $remainingWeekHours -= $hours;
                }

                if (! empty($cellParts)) {
                    $rpdProgram->scheduleItems()->create([
                        'rpd_curriculum_item_id' => $item->id,
                        'week_number' => $currentWeek,
                        'content' => implode("\n", $cellParts),
                    ]);
                }

                if ($remainingWeekHours <= 0) {
                    $currentWeek++;
                    $remainingWeekHours = $this->hoursPerWeek($rpdProgram);
                }
            }
        }
    }

    private function maxLessonsPerWeek(RpdProgram $rpdProgram): int
    {
        preg_match_all('/\d+/', (string) $rpdProgram->lessons_per_week, $matches);

        $numbers = collect($matches[0] ?? [])
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0);

        return max(1, (int) $numbers->max());
    }

    private function extractTotalHours(string $content): int
    {
        preg_match_all('/\((\d+)\)/u', $content, $matches);

        return collect($matches[1] ?? [])
            ->map(fn($value) => (int) $value)
            ->sum();
    }
}
