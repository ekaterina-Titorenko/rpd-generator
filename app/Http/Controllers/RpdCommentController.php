<?php

namespace App\Http\Controllers;

use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdCommentController extends Controller
{
    public function store(Request $request, RpdProgram $rpdProgram)
    {
        $this->authorizeProgramAccess($request, $rpdProgram);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'message.required' => 'Напишите комментарий.',
            'message.max' => 'Комментарий не должен быть длиннее 5000 символов.',
        ]);

        $rpdProgram->comments()->create([
            'user_id' => $request->user()->id,
            'type' => 'comment',
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Комментарий добавлен.');
    }

    private function authorizeProgramAccess(Request $request, RpdProgram $rpdProgram): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($rpdProgram->user_id === $request->user()->id, 403);
    }
}