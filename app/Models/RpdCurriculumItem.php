<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpdCurriculumItem extends Model
{
    protected $fillable = [
        'rpd_program_id',
        'number',
        'title',
        'total_hours',
        'theory_hours',
        'practice_hours',
        'control_form',
        'is_final_work',
        'sort_order',
    ];

    protected $casts = [
        'is_final_work' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(RpdProgram::class, 'rpd_program_id');
    }
}