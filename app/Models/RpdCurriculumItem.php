<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RpdCurriculumItem extends Model
{
    protected $fillable = [
        'rpd_program_id',
        'type',
        'parent_id',
        'number',
        'title',
        'total_hours',
        'theory_hours',
        'practice_hours',
        'control_form',
        'control_form_id',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RpdCurriculumItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(RpdCurriculumItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function controlForm(): BelongsTo
    {
        return $this->belongsTo(RpdControlForm::class, 'control_form_id');
    }
}
