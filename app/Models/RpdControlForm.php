<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}