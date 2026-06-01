<?php

namespace App\Http\Controllers;

use App\Models\RpdContentSection;
use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdContentSectionController extends Controller
{
    public function index(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load(['contentSections', 'curriculumItems']);

        return view('rpd-programs.content.index', compact('rpdProgram'));
    }

    public function sync(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $sections = $rpdProgram->curriculumItems()
            ->where('type', 'section')
            ->orderBy('sort_order')
            ->get();

        foreach ($sections as $section) {
            $rpdProgram->contentSections()->firstOrCreate(
                [
                    'number' => $section->number,
                    'title' => $section->title,
                ],
                [
                    'content' => null,
                    'sort_order' => $section->sort_order,
                ]
            );
        }

        return redirect()
            ->route('rpd-programs.content.index', $rpdProgram)
            ->with('success', 'Содержание синхронизировано с разделами учебного плана.');
    }

    public function update(Request $request, RpdProgram $rpdProgram, RpdContentSection $contentSection)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($contentSection->rpd_program_id === $rpdProgram->id, 404);

        $validated = $request->validate([
            'content' => ['nullable', 'string'],
        ]);

        $contentSection->update($validated);

        return redirect()
            ->route('rpd-programs.content.index', $rpdProgram)
            ->with('success', 'Содержание раздела обновлено.');
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }
}