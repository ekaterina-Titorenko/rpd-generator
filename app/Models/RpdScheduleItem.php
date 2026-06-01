<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpdScheduleItem extends Model
{
    protected $fillable = [
        'rpd_program_id',
        'rpd_curriculum_item_id',
        'week_number',
        'theory_hours',
        'practice_hours',
        'is_final_work',
    ];

    protected $casts = [
        'is_final_work' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(RpdProgram::class, 'rpd_program_id');
    }

    public function curriculumItem(): BelongsTo
    {
        return $this->belongsTo(RpdCurriculumItem::class, 'rpd_curriculum_item_id');
    }
}