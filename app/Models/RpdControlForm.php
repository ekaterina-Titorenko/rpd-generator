<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RpdControlForm extends Model
{
    protected $fillable = [
        'name',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function curriculumItems(): HasMany
    {
        return $this->hasMany(RpdCurriculumItem::class, 'control_form_id');
    }
}