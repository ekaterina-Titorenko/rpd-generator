<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpdResource extends Model
{
    protected $fillable = [
        'rpd_program_id',
        'type',
        'title',
        'url',
        'sort_order',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(RpdProgram::class, 'rpd_program_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'main_recommended' => 'Список основной рекомендуемой литературы',
            'additional' => 'Дополнительная литература',
            'internet' => 'Ресурсы информационно-телекоммуникационной сети Интернет',
            default => 'Источник',
        };
    }
}
