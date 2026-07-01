<?php

namespace App\Http\Controllers;

use App\Models\RpdContentSection;
use App\Models\RpdProgram;
use Illuminate\Http\Request;
use App\Services\RpdActivityLogger;

class RpdContentSectionController extends Controller
{
    public function index(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load([
            'contentSections' => fn($query) => $query
                ->whereNotNull('rpd_curriculum_item_id')
                ->orderBy('sort_order'),
            'curriculumItems',
        ]);

        return view('rpd-programs.content.index', compact('rpdProgram'));
    }

    public function sync(Request $request, RpdProgram $rpdProgram, RpdActivityLogger $activityLogger)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $sections = $rpdProgram->curriculumItems()
            ->where('type', 'section')
            ->orderBy('sort_order')
            ->get();

        foreach ($sections as $section) {
            $rpdProgram->contentSections()->updateOrCreate(
                [
                    'rpd_curriculum_item_id' => $section->id,
                ],
                [
                    'number' => $section->number,
                    'title' => $section->title,
                    'sort_order' => $section->sort_order,
                ]
            );
        }

        $rpdProgram->contentSections()
            ->whereNull('rpd_curriculum_item_id')
            ->delete();

        $rpdProgram->contentSections()
            ->whereNotIn('rpd_curriculum_item_id', $sections->pluck('id'))
            ->delete();

        $activityLogger->log($request, $rpdProgram, 'system_update', 'Содержание синхронизировано с учебным планом.');

        return redirect()
            ->route('rpd-programs.content.index', $rpdProgram)
            ->with('success', 'Содержание синхронизировано с разделами учебного плана.');
    }

   public function update(Request $request, RpdProgram $rpdProgram, RpdContentSection $contentSection, RpdActivityLogger $activityLogger)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($contentSection->rpd_program_id === $rpdProgram->id, 404);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:100'],
        ], [
            'content.required' => 'Заполните описание раздела.',
            'content.min' => 'Описание раздела должно содержать не менее 100 символов.',
        ]);

        $contentSection->update($validated);

        $activityLogger->log($request, $rpdProgram, 'system_update', 'Содержание учебного плана изменено.');

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
