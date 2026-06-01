<?php

namespace App\Http\Controllers;

use App\Models\RpdControlForm;
use App\Models\RpdCurriculumItem;
use App\Models\RpdProgram;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RpdCurriculumItemController extends Controller
{
    public function index(RpdProgram $rpdProgram)
    {
        $rpdProgram->load([
            'curriculumItems.children',
            'curriculumItems.controlForm',
        ]);

        $items = $rpdProgram->curriculumItems()
            ->with(['children', 'controlForm'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $sections = $rpdProgram->curriculumItems()
            ->where('type', 'section')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $controlForms = RpdControlForm::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('rpd-programs.curriculum.index', compact(
            'rpdProgram',
            'items',
            'sections',
            'controlForms'
        ));
    }

    public function store(Request $request, RpdProgram $rpdProgram)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['section', 'topic', 'final_work'])],
            'parent_id' => ['nullable', 'integer', 'exists:rpd_curriculum_items,id'],
            'title' => ['required', 'string', 'max:255'],
            'theory_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'practice_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'control_form_id' => ['nullable', 'integer', 'exists:rpd_control_forms,id'],
            'control_form' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['total_hours'] = (int) $validated['theory_hours'] + (int) $validated['practice_hours'];

        if ($validated['type'] === 'topic') {
            if (empty($validated['parent_id'])) {
                return back()
                    ->withErrors(['parent_id' => 'Для темы необходимо выбрать родительский раздел.'])
                    ->withInput();
            }

            $parent = $rpdProgram->curriculumItems()
                ->where('type', 'section')
                ->findOrFail($validated['parent_id']);

            $validated['parent_id'] = $parent->id;
            $validated['number'] = $this->makeTopicNumber($parent);
        } elseif ($validated['type'] === 'section') {
            $validated['parent_id'] = null;
            $validated['number'] = $this->makeSectionNumber($rpdProgram);
        } else {
            $validated['parent_id'] = null;
            $validated['number'] = null;
            $validated['is_final_work'] = true;
        }

        if (! empty($validated['control_form_id'])) {
            $controlForm = RpdControlForm::find($validated['control_form_id']);
            $validated['control_form'] = $controlForm?->name;
        } elseif (! empty($validated['control_form'])) {
            $controlForm = RpdControlForm::query()->firstOrCreate(
                ['name' => trim($validated['control_form'])],
                ['is_default' => false, 'is_active' => true]
            );

            $validated['control_form_id'] = $controlForm->id;
            $validated['control_form'] = $controlForm->name;
        }

        $validated['sort_order'] = $this->nextSortOrder($rpdProgram, $validated['parent_id']);
        $validated['is_final_work'] = $validated['type'] === 'final_work';

        $rpdProgram->curriculumItems()->create($validated);

        $this->renumberProgram($rpdProgram);

        return redirect()
            ->route('rpd-programs.curriculum.index', $rpdProgram)
            ->with('success', 'Строка учебного плана добавлена.');
    }

    public function update(Request $request, RpdProgram $rpdProgram, RpdCurriculumItem $curriculumItem)
    {
        abort_unless($curriculumItem->rpd_program_id === $rpdProgram->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'theory_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'practice_hours' => ['required', 'integer', 'min:0', 'max:1000'],
            'control_form_id' => ['nullable', 'integer', 'exists:rpd_control_forms,id'],
            'control_form' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['total_hours'] = (int) $validated['theory_hours'] + (int) $validated['practice_hours'];

        if (! empty($validated['control_form_id'])) {
            $controlForm = RpdControlForm::find($validated['control_form_id']);
            $validated['control_form'] = $controlForm?->name;
        } elseif (! empty($validated['control_form'])) {
            $controlForm = RpdControlForm::query()->firstOrCreate(
                ['name' => trim($validated['control_form'])],
                ['is_default' => false, 'is_active' => true]
            );

            $validated['control_form_id'] = $controlForm->id;
            $validated['control_form'] = $controlForm->name;
        } else {
            $validated['control_form_id'] = null;
            $validated['control_form'] = null;
        }

        $curriculumItem->update($validated);

        return redirect()
            ->route('rpd-programs.curriculum.index', $rpdProgram)
            ->with('success', 'Строка учебного плана обновлена.');
    }

    public function destroy(RpdProgram $rpdProgram, RpdCurriculumItem $curriculumItem)
    {
        abort_unless($curriculumItem->rpd_program_id === $rpdProgram->id, 404);

        if ($curriculumItem->type === 'section') {
            $curriculumItem->children()->delete();
        }

        $curriculumItem->delete();

        $this->renumberProgram($rpdProgram);

        return redirect()
            ->route('rpd-programs.curriculum.index', $rpdProgram)
            ->with('success', 'Строка учебного плана удалена.');
    }
    private function makeSectionNumber(RpdProgram $rpdProgram): string
    {
        $count = $rpdProgram->curriculumItems()
            ->where('type', 'section')
            ->whereNull('parent_id')
            ->count();

        return (string) ($count + 1);
    }

    private function makeTopicNumber(RpdCurriculumItem $parent): string
    {
        $count = $parent->children()
            ->where('type', 'topic')
            ->count();

        return $parent->number . '.' . ($count + 1);
    }

    private function nextSortOrder(RpdProgram $rpdProgram, ?int $parentId): int
    {
        return (int) $rpdProgram->curriculumItems()
            ->where('parent_id', $parentId)
            ->max('sort_order') + 1;
    }

    private function renumberProgram(RpdProgram $rpdProgram): void
    {
        $sections = $rpdProgram->curriculumItems()
            ->where('type', 'section')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        foreach ($sections as $sectionIndex => $section) {
            $section->update([
                'number' => (string) ($sectionIndex + 1),
            ]);

            $topics = $section->children()
                ->where('type', 'topic')
                ->orderBy('sort_order')
                ->get();

            foreach ($topics as $topicIndex => $topic) {
                $topic->update([
                    'number' => $section->number . '.' . ($topicIndex + 1),
                ]);
            }
        }
    }
}
