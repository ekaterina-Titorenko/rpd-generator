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
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(RpdProgram::class, 'rpd_program_id');
    }
}