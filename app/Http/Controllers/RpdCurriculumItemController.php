<?php

namespace App\Http\Controllers;

use App\Models\RpdCurriculumItem;
use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdCurriculumItemController extends Controller
{
    public function index(RpdProgram $rpdProgram)
    {
        $rpdProgram->load('curriculumItems');

        return view('rpd-programs.curriculum.index', compact('rpdProgram'));
    }

    public function store(Request $request, RpdProgram $rpdProgram)
    {
        $validated = $request->validate([
            'number' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'total_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'theory_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'practice_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'control_form' => ['nullable', 'string', 'max:255'],
            'is_final_work' => ['nullable', 'boolean'],
        ]);

        $validated['is_final_work'] = $request->boolean('is_final_work');
        $validated['sort_order'] = $rpdProgram->curriculumItems()->max('sort_order') + 1;

        $rpdProgram->curriculumItems()->create($validated);

        return redirect()
            ->route('rpd-programs.curriculum.index', $rpdProgram)
            ->with('success', 'Строка учебного плана добавлена.');
    }

    public function update(Request $request, RpdProgram $rpdProgram, RpdCurriculumItem $curriculumItem)
    {
        abort_unless($curriculumItem->rpd_program_id === $rpdProgram->id, 404);

        $validated = $request->validate([
            'number' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'total_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'theory_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'practice_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'control_form' => ['nullable', 'string', 'max:255'],
            'is_final_work' => ['nullable', 'boolean'],
        ]);

        $validated['is_final_work'] = $request->boolean('is_final_work');

        $curriculumItem->update($validated);

        return redirect()
            ->route('rpd-programs.curriculum.index', $rpdProgram)
            ->with('success', 'Строка учебного плана обновлена.');
    }

    public function destroy(RpdProgram $rpdProgram, RpdCurriculumItem $curriculumItem)
    {
        abort_unless($curriculumItem->rpd_program_id === $rpdProgram->id, 404);

        $curriculumItem->delete();

        return redirect()
            ->route('rpd-programs.curriculum.index', $rpdProgram)
            ->with('success', 'Строка учебного плана удалена.');
    }
}