<?php

namespace App\Services;

use App\Models\RpdProgram;
use Illuminate\Http\Request;

class RpdActivityLogger
{
    public function log(Request $request, RpdProgram $rpdProgram, string $type, string $message): void
    {
        $rpdProgram->comments()->create([
            'user_id' => $request->user()->id,
            'type' => $type,
            'message' => $message,
        ]);
    }
}