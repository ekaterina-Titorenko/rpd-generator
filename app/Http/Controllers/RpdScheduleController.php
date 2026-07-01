<?php

namespace App\Http\Controllers;

use App\Models\RpdCurriculumItem;
use App\Models\RpdProgram;
use App\Services\RpdScheduleBuilder;
use Illuminate\Http\Request;
use App\Services\RpdActivityLogger;

class RpdScheduleController extends Controller
{
    public function index(Request $request, RpdProgram $rpdProgram, RpdScheduleBuilder $scheduleBuilder)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load([
            'curriculumItems' => fn ($query) => $query->orderBy('sort_order'),
            'scheduleItems',
        ]);

        if ($scheduleBuilder->ensureGeneratedIfEmpty($rpdProgram)) {
            $rpdProgram->load([
                'curriculumItems' => fn ($query) => $query->orderBy('sort_order'),
                'scheduleItems',
            ]);

            session()->flash(
                'success',
                'Календарный учебный график сформирован автоматически. Проверьте распределение и при необходимости скорректируйте вручную.'
            );
        }

        $weeksCount = $scheduleBuilder->calculateWeeksCount($rpdProgram);
        $recommendedWeeksCount = $scheduleBuilder->recommendedWeeksCount($rpdProgram);
        $scheduleWarnings = $this->makeScheduleWarnings($rpdProgram, $scheduleBuilder);
        $weekTotals = $scheduleBuilder->makeWeekTotals($rpdProgram, $weeksCount);

        return view('rpd-programs.schedule.index', compact(
            'rpdProgram',
            'weeksCount',
            'recommendedWeeksCount',
            'scheduleWarnings',
            'weekTotals'
        ));
    }

    public function generate(Request $request, RpdProgram $rpdProgram, RpdScheduleBuilder $scheduleBuilder, RpdActivityLogger $activityLogger)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $scheduleBuilder->regenerate($rpdProgram);
        $activityLogger->log($request, $rpdProgram, 'system_update', 'Календарный учебный график сформирован заново.');

        return redirect()
            ->route('rpd-programs.schedule.index', $rpdProgram)
            ->with('success', 'Календарный учебный график сброшен к автоматическому распределению.');
    }

    public function update(Request $request, RpdProgram $rpdProgram, RpdActivityLogger $activityLogger)
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

        $activityLogger->log($request, $rpdProgram, 'system_update', 'Календарный учебный график изменён.');
        

        return redirect()
            ->route('rpd-programs.schedule.index', $rpdProgram)
            ->with('success', 'Календарный учебный график обновлён.');
    }

    public function updateWeeks(Request $request, RpdProgram $rpdProgram, RpdActivityLogger $activityLogger)
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

        $activityLogger->log($request, $rpdProgram, 'system_update', 'Количество недель календарного графика изменено.');

        return redirect()
            ->route('rpd-programs.schedule.index', $rpdProgram)
            ->with('success', 'Количество недель обновлено.');
    }

    private function makeScheduleWarnings(RpdProgram $rpdProgram, RpdScheduleBuilder $scheduleBuilder): array
    {
        $warnings = [];

        foreach ($rpdProgram->curriculumItems->where('type', 'section') as $section) {
            $items = $rpdProgram->scheduleItems
                ->where('rpd_curriculum_item_id', $section->id);

            $plannedTheory = 0;
            $plannedPractice = 0;

            foreach ($items as $item) {
                $plannedTheory += $scheduleBuilder->extractHoursByType((string) $item->content, 'Т');
                $plannedPractice += $scheduleBuilder->extractHoursByType((string) $item->content, 'П');
            }

            if (
                $plannedTheory !== (int) $section->theory_hours
                || $plannedPractice !== (int) $section->practice_hours
            ) {
                $warnings[$section->id] = [
                    'planned_theory' => $plannedTheory,
                    'planned_practice' => $plannedPractice,
                    'expected_theory' => (int) $section->theory_hours,
                    'expected_practice' => (int) $section->practice_hours,
                ];
            }
        }

        return $warnings;
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }
}