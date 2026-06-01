<?php

namespace App\Http\Controllers;

use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdAssessmentItemController extends Controller
{
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

        $rpdProgram->update($validated);

        return redirect()
            ->route('rpd-programs.assessment.index', $rpdProgram)
            ->with('success', 'Оценочные материалы обновлены.');
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }
}