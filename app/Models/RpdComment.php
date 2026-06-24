<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpdComment extends Model
{
    protected $fillable = [
        'rpd_program_id',
        'user_id',
        'type',
        'message',
    ];

    public function rpdProgram(): BelongsTo
    {
        return $this->belongsTo(RpdProgram::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}