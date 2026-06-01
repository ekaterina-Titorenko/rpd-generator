<?php

namespace App\Http\Controllers;

use App\Models\RpdProgram;
use App\Models\RpdResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RpdResourceController extends Controller
{
    public function index(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load('resources');

        return view('rpd-programs.resources.index', compact('rpdProgram'));
    }

    public function store(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $validated = $request->validate([
            'type' => ['required', Rule::in([
                'main_recommended',
                'additional',
                'internet',
            ])],
            'title' => ['required', 'string', 'max:1000'],
            'url' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['sort_order'] = (int) $rpdProgram->resources()->max('sort_order') + 1;

        $rpdProgram->resources()->create($validated);

        return redirect()
            ->route('rpd-programs.resources.index', $rpdProgram)
            ->with('success', 'Источник добавлен.');
    }

    public function update(Request $request, RpdProgram $rpdProgram, RpdResource $resource)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($resource->rpd_program_id === $rpdProgram->id, 404);

        $validated = $request->validate([
            'type' => ['required', Rule::in([
                'main_recommended',
                'additional',
                'internet',
            ])],
            'title' => ['required', 'string', 'max:1000'],
            'url' => ['nullable', 'string', 'max:1000'],
        ]);

        $resource->update($validated);

        return redirect()
            ->route('rpd-programs.resources.index', $rpdProgram)
            ->with('success', 'Источник обновлён.');
    }

    public function destroy(Request $request, RpdProgram $rpdProgram, RpdResource $resource)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($resource->rpd_program_id === $rpdProgram->id, 404);

        $resource->delete();

        return redirect()
            ->route('rpd-programs.resources.index', $rpdProgram)
            ->with('success', 'Источник удалён.');
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }
}