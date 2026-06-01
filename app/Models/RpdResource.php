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
        'source_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
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

    public function getSourceTypeLabelAttribute(): string
    {
        return match ($this->source_type) {
            'book' => 'Книга',
            'article' => 'Статья',
            'electronic' => 'Электронный ресурс',
            'legal' => 'Нормативный документ',
            default => 'Источник',
        };
    }
}
