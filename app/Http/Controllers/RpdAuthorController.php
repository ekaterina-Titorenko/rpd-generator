<?php

namespace App\Http\Controllers;

use App\Models\RpdAuthor;
use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdAuthorController extends Controller
{
    public function index(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $rpdProgram->load('authors');

        $defaultAuthor = $this->findLastAuthorForUser($request, $rpdProgram);

        return view('rpd-programs.authors.index', compact(
            'rpdProgram',
            'defaultAuthor'
        ));
    }

    public function store(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['sort_order'] = (int) $rpdProgram->authors()->max('sort_order') + 1;

        $rpdProgram->authors()->create($validated);

        return redirect()
            ->route('rpd-programs.authors.index', $rpdProgram)
            ->with('success', 'Разработчик добавлен.');
    }

    public function update(Request $request, RpdProgram $rpdProgram, RpdAuthor $author)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($author->rpd_program_id === $rpdProgram->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
        ]);

        $author->update($validated);

        return redirect()
            ->route('rpd-programs.authors.index', $rpdProgram)
            ->with('success', 'Данные разработчика обновлены.');
    }

    public function destroy(Request $request, RpdProgram $rpdProgram, RpdAuthor $author)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        abort_unless($author->rpd_program_id === $rpdProgram->id, 404);

        $author->delete();

        return redirect()
            ->route('rpd-programs.authors.index', $rpdProgram)
            ->with('success', 'Разработчик удалён.');
    }

    private function findLastAuthorForUser(Request $request, RpdProgram $rpdProgram): ?RpdAuthor
    {
        if ($rpdProgram->authors->isNotEmpty()) {
            return null;
        }

        return RpdAuthor::query()
            ->whereHas('program', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest('updated_at')
            ->first();
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }
}