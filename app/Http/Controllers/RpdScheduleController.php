<?php

namespace App\Http\Controllers;

use App\Models\RpdCurriculumItem;
use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdScheduleController extends Controller
{
    public function index(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load([
            'curriculumItems' => fn($query) => $query->orderBy('sort_order'),
            'scheduleItems',
        ]);

        $weeksCount = $this->calculateWeeksCount($rpdProgram);
        $recommendedWeeksCount = $this->recommendedWeeksCount($rpdProgram);
        
        return view('rpd-programs.schedule.index', compact(
            'rpdProgram',
            'weeksCount',
            'recommendedWeeksCount'
        ));
    }

    public function generate(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load([
            'curriculumItems' => fn($query) => $query->orderBy('sort_order'),
        ]);

        $rpdProgram->scheduleItems()->delete();

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

        return redirect()
            ->route('rpd-programs.schedule.index', $rpdProgram)
            ->with('success', 'Календарный учебный график сформирован.');
    }

    public function update(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $validated = $request->validate([
            'schedule' => ['array'],
            'schedule.*.*' => ['nullable', 'string'],
        ]);

        foreach ($validated['schedule'] ?? [] as $curriculumItemId => $weeks) {
            foreach ($weeks as $weekNumber => $content) {
                $curriculumItem = RpdCurriculumItem::query()
                    ->where('rpd_program_id', $rpdProgram->id)
                    ->where('id', $curriculumItemId)
                    ->first();

                if (! $curriculumItem) {
                    continue;
                }

                $content = trim((string) $content);

                if ($content === '') {
                    $rpdProgram->scheduleItems()
                        ->where('rpd_curriculum_item_id', $curriculumItemId)
                        ->where('week_number', $weekNumber)
                        ->delete();

                    continue;
                }

                $rpdProgram->scheduleItems()->updateOrCreate(
                    [
                        'rpd_curriculum_item_id' => $curriculumItemId,
                        'week_number' => (int) $weekNumber,
                    ],
                    [
                        'content' => $content,
                    ]
                );
            }
        }

        return redirect()
            ->route('rpd-programs.schedule.index', $rpdProgram)
            ->with('success', 'Календарный учебный график обновлён.');
    }

    private function calculateWeeksCount(RpdProgram $rpdProgram): int
    {
        if ($rpdProgram->schedule_weeks_count) {
            return (int) $rpdProgram->schedule_weeks_count;
        }

        return $this->recommendedWeeksCount($rpdProgram);
    }

    private function recommendedWeeksCount(RpdProgram $rpdProgram): int
    {
        $hoursPerWeek = $this->hoursPerWeek($rpdProgram);

        if ($hoursPerWeek <= 0) {
            return 1;
        }

        return max(1, (int) ceil((int) $rpdProgram->total_hours / $hoursPerWeek));
    }

    private function hoursPerWeek(RpdProgram $rpdProgram): int
    {
        preg_match('/\d+/', (string) $rpdProgram->lessons_per_week, $matches);

        $lessonsPerWeek = isset($matches[0]) ? (int) $matches[0] : 1;

        return max(1, $lessonsPerWeek * (int) $rpdProgram->academic_hours_per_lesson);
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }

    public function updateWeeks(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $validated = $request->validate([
            'schedule_weeks_count' => ['required', 'integer', 'min:1', 'max:52'],
        ]);

        $rpdProgram->update([
            'schedule_weeks_count' => $validated['schedule_weeks_count'],
        ]);

        $rpdProgram->scheduleItems()
            ->where('week_number', '>', $validated['schedule_weeks_count'])
            ->delete();

        return redirect()
            ->route('rpd-programs.schedule.index', $rpdProgram)
            ->with('success', 'Количество недель обновлено.');
    }
}
