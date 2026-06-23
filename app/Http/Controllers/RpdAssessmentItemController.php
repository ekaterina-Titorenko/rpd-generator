<?php

namespace App\Http\Controllers;

use App\Models\RpdProgram;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class RpdAssessmentItemController extends Controller
{
    private const MIN_LIST_ITEMS = 15;

    public function index(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        return view('rpd-programs.assessment.index', compact('rpdProgram'));
    }

    public function update(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $validated = $request->validate([
            'control_survey_materials' => ['nullable', 'string'],
            'final_practical_work_materials' => ['nullable', 'string'],
            'project_topics' => ['nullable', 'string'],
        ]);

        $errors = new MessageBag();

        $this->validateNumberedList(
            $validated['control_survey_materials'] ?? null,
            'control_survey_materials',
            'Материалы для проведения контрольных опросов',
            $errors
        );

        $this->validateNumberedList(
            $validated['final_practical_work_materials'] ?? null,
            'final_practical_work_materials',
            'Материалы для проведения итоговой практической работы',
            $errors
        );

        $this->validateNumberedList(
            $validated['project_topics'] ?? null,
            'project_topics',
            'Типовые темы проектных работ',
            $errors
        );

        if ($errors->isNotEmpty()) {
            return back()
                ->withErrors($errors)
                ->withInput();
        }

        $validated['control_survey_materials'] = $this->normalizeNumberedLines(
            $validated['control_survey_materials'] ?? null
        );

        $validated['final_practical_work_materials'] = $this->normalizeNumberedLines(
            $validated['final_practical_work_materials'] ?? null
        );

        $validated['project_topics'] = $this->normalizeNumberedLines(
            $validated['project_topics'] ?? null
        );

        $rpdProgram->update($validated);

        return redirect()
            ->route('rpd-programs.assessment.index', $rpdProgram)
            ->with('success', 'Оценочные материалы обновлены.');
    }

    private function validateNumberedList(?string $value, string $field, string $label, MessageBag $errors): void
    {
        $lines = collect(preg_split('/\R/u', (string) $value))
            ->map(fn($line) => trim($line));

        $filledItems = $lines
            ->map(fn($line) => preg_replace('/^\s*\d+[\).\s-]*/u', '', $line))
            ->map(fn($line) => trim((string) $line))
            ->filter()
            ->values();

        $emptyNumberedItems = $lines
            ->filter(fn($line) => preg_match('/^\s*\d+[\).\s-]*$/u', $line))
            ->values();

        if ($emptyNumberedItems->isNotEmpty()) {
            $errors->add(
                $field,
                "{$label}: удалите пустые пункты списка или заполните их текстом."
            );
        }

        if ($filledItems->count() < self::MIN_LIST_ITEMS) {
            $errors->add(
                $field,
                "{$label}: укажите не менее " . self::MIN_LIST_ITEMS . " пунктов. Одна непустая строка считается одной единицей."
            );
        }
    }

    private function normalizeNumberedLines(?string $value): ?string
    {
        $lines = collect(preg_split('/\R/u', (string) $value))
            ->map(fn($line) => trim($line))
            ->map(fn($line) => preg_replace('/^\s*\d+[\).\s-]*/u', '', $line))
            ->map(fn($line) => trim((string) $line))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            return null;
        }

        return $lines->implode("\n");
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }
}
