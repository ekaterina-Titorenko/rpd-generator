<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RpdProgram extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'direction',
        'complexity_level',
        'year',
        'smko_code',
        'total_hours',
        'study_period',
        'students_age',
        'education_form',
        'study_mode',
        'students_category',
        'preparation_requirements',
        'program_description',
        'legal_basis',
        'relevance',
        'goal',
        'learning_tasks',
        'development_tasks',
        'planned_results',
        'personal_competencies',
        'metasubject_competencies',
        'subject_competencies',
        'organizational_conditions',
        'methodical_conditions',
        'material_conditions',
        'staffing_conditions',
        'attestation_forms',
        'control_criteria',
        'knowledge_criteria',
        'competency_criteria',
        'oral_control_criteria',
        'practical_work_criteria',
        'status',
    ];

    protected $casts = [
        'learning_tasks' => 'array',
        'development_tasks' => 'array',
        'planned_results' => 'array',
        'personal_competencies' => 'array',
        'metasubject_competencies' => 'array',
        'subject_competencies' => 'array',
        'knowledge_criteria' => 'array',
        'competency_criteria' => 'array',
    ];

    public function getDirectionLabelAttribute(): string
    {
        return match ($this->direction) {
            'technical' => 'Техническая',
            'science' => 'Естественно-научная',
            'social_humanitarian' => 'Социально-гуманитарная',
            default => 'Не указана',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Черновик',
            'submitted' => 'На проверке',
            'revision' => 'На доработке',
            'approved' => 'Утверждена',
            'generated' => 'Документ сформирован',
            default => 'Неизвестно',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function curriculumItems(): HasMany
    {
        return $this->hasMany(RpdCurriculumItem::class)->orderBy('sort_order');
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(RpdScheduleItem::class);
    }

    public function contentSections(): HasMany
    {
        return $this->hasMany(RpdContentSection::class)->orderBy('sort_order');
    }

    public function assessmentItems(): HasMany
    {
        return $this->hasMany(RpdAssessmentItem::class)->orderBy('sort_order');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(RpdResource::class)->orderBy('sort_order');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(RpdAuthor::class)->orderBy('sort_order');
    }
}
