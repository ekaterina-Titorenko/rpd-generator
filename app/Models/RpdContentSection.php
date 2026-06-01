<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class RpdContentSection extends Model
{
    protected $fillable = [
        'rpd_program_id',
        'number',
        'title',
        'content',
        'sort_order',
        'rpd_curriculum_item_id',
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
