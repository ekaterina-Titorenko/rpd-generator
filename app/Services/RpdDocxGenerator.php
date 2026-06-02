<?php

namespace App\Services;

use App\Models\RpdProgram;
use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class RpdDocxGenerator
{
    public function generate(RpdProgram $rpdProgram): string
    {
        $rpdProgram->load([
            'curriculumItems.children',
            'curriculumItems.controlForm',
            'contentSections' => fn($query) => $query
                ->whereNotNull('rpd_curriculum_item_id')
                ->orderBy('sort_order'),
            'scheduleItems',
            'resources',
            'authors',
        ]);

        $templatePath = $this->resolveTemplatePath($rpdProgram);

        $processor = new TemplateProcessor($templatePath);

        $this->fillCommonFields($processor, $rpdProgram);
        $this->fillCurriculumTable($processor, $rpdProgram);
        $this->fillContentSections($processor, $rpdProgram);
        $this->fillScheduleTable($processor, $rpdProgram);
        $this->fillResources($processor, $rpdProgram);
        $this->fillAuthors($processor, $rpdProgram);

        $directory = storage_path('app/generated/rpd');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = 'rpd_' . $rpdProgram->id . '_' . now()->format('Ymd_His') . '.docx';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        $processor->saveAs($path);

        return $path;
    }

    private function resolveTemplatePath(RpdProgram $rpdProgram): string
    {
        $templateName = match ($rpdProgram->direction) {
            'technical' => 'technical.docx',
            'science' => 'science.docx',
            'social' => 'social.docx',
            default => 'technical.docx',
        };

        $path = storage_path('app/templates/rpd/' . $templateName);

        if (! file_exists($path)) {
            throw new RuntimeException("Шаблон РПД не найден: {$templateName}");
        }

        return $path;
    }

    private function fillCommonFields(TemplateProcessor $processor, RpdProgram $rpdProgram): void
    {
        $values = [
            'program_title' => $rpdProgram->title,
            'direction' => $rpdProgram->direction,
            'direction_label' => $rpdProgram->direction_label ?? $rpdProgram->direction,
            'complexity_level' => $rpdProgram->complexity_level,
            'year' => $rpdProgram->year,
            'smko_code' => $rpdProgram->smko_code,
            'total_hours' => $rpdProgram->total_hours,
            'study_period' => $rpdProgram->study_period,
            'students_age' => $rpdProgram->students_age,
            'education_form' => $rpdProgram->education_form,
            'study_mode' => $rpdProgram->study_mode,
            'students_category' => $rpdProgram->students_category,
            'preparation_requirements' => $rpdProgram->preparation_requirements,
            'program_description' => $rpdProgram->program_description,
            'legal_basis' => $rpdProgram->legal_basis,
            'relevance' => $rpdProgram->relevance,
            'goal' => $rpdProgram->goal,
            'learning_tasks' => $this->formatList($rpdProgram->learning_tasks),
            'development_tasks' => $this->formatList($rpdProgram->development_tasks),
            'planned_results' => $this->formatList($rpdProgram->planned_results),
            'personal_competencies' => $this->formatList($rpdProgram->personal_competencies),
            'metasubject_competencies' => $this->formatList($rpdProgram->metasubject_competencies),
            'subject_competencies' => $this->formatList($rpdProgram->subject_competencies),
            'control_survey_materials' => $rpdProgram->control_survey_materials,
            'final_practical_work_materials' => $rpdProgram->final_practical_work_materials,
            'project_topics' => $rpdProgram->project_topics,
        ];

        foreach ($values as $key => $value) {
            $processor->setValue($key, $this->cleanValue($value));
        }
    }

    private function fillCurriculumTable(TemplateProcessor $processor, RpdProgram $rpdProgram): void
    {
        if (! $this->hasVariable($processor, 'curriculum_number')) {
            return;
        }

        $rows = [];

        $sections = $rpdProgram->curriculumItems
            ->whereNull('parent_id')
            ->sortBy('sort_order');

        foreach ($sections as $section) {
            $rows[] = $this->makeCurriculumRow($section);

            foreach ($section->children->sortBy('sort_order') as $topic) {
                $rows[] = $this->makeCurriculumRow($topic);
            }
        }

        if (empty($rows)) {
            $rows[] = [
                'curriculum_number' => '—',
                'curriculum_title' => 'Учебный план не заполнен',
                'curriculum_total_hours' => '—',
                'curriculum_theory_hours' => '—',
                'curriculum_practice_hours' => '—',
                'curriculum_control' => '—',
            ];
        }

        $processor->cloneRow('curriculum_number', count($rows));

        foreach ($rows as $index => $row) {
            $number = $index + 1;

            foreach ($row as $key => $value) {
                $processor->setValue("{$key}#{$number}", $this->cleanValue($value));
            }
        }
    }

    private function makeCurriculumRow($item): array
    {
        return [
            'curriculum_number' => $item->number,
            'curriculum_title' => $item->title,
            'curriculum_total_hours' => $item->total_hours,
            'curriculum_theory_hours' => $item->theory_hours,
            'curriculum_practice_hours' => $item->practice_hours,
            'curriculum_control' => $item->controlForm?->title ?? $item->control_form ?? '—',
        ];
    }

    private function fillContentSections(TemplateProcessor $processor, RpdProgram $rpdProgram): void
    {
        if (! $this->hasVariable($processor, 'content_number')) {
            return;
        }

        $sections = $rpdProgram->contentSections;

        if ($sections->isEmpty()) {
            $sections = collect([
                (object) [
                    'number' => '—',
                    'title' => 'Содержание учебного плана не заполнено',
                    'content' => '—',
                ],
            ]);
        }

        $processor->cloneRow('content_number', $sections->count());

        foreach ($sections->values() as $index => $section) {
            $number = $index + 1;

            $processor->setValue("content_number#{$number}", $this->cleanValue($section->number));
            $processor->setValue("content_title#{$number}", $this->cleanValue($section->title));
            $processor->setValue("content_text#{$number}", $this->cleanValue($section->content));
        }
    }

    private function fillScheduleTable(TemplateProcessor $processor, RpdProgram $rpdProgram): void
    {
        if (! $this->hasVariable($processor, 'schedule_table')) {
            if ($this->hasVariable($processor, 'schedule_note')) {
                $processor->setValue(
                    'schedule_note',
                    'Календарный учебный график формируется в системе.'
                );
            }

            return;
        }

        $weeks = (int) ($rpdProgram->scheduleItems->max('week_number') ?: 0);

        if ($weeks <= 0) {
            $textRun = new TextRun();
            $textRun->addText('Календарный учебный график не заполнен.');

            $processor->setComplexBlock('schedule_table', $textRun);

            return;
        }

        $sections = $rpdProgram->curriculumItems
            ->whereNull('parent_id')
            ->where('type', 'section')
            ->sortBy('sort_order')
            ->values();

        $table = new Table([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);

        $headerCellStyle = [
            'valign' => 'center',
            'bgColor' => 'D9D9D9',
        ];

        $cellStyle = [
            'valign' => 'center',
        ];

        $center = [
            'alignment' => Jc::CENTER,
            'spaceAfter' => 0,
        ];

        $left = [
            'alignment' => Jc::START,
            'spaceAfter' => 0,
        ];

        $table->addRow();

        $table->addCell(
            Converter::cmToTwip(6),
            array_merge($headerCellStyle, [
                'vMerge' => 'restart',
            ])
        )->addText('Наименование разделов', ['bold' => true], $center);

        $table->addCell(
            Converter::cmToTwip(max(6, $weeks * 2)),
            array_merge($headerCellStyle, [
                'gridSpan' => $weeks,
            ])
        )->addText('Недели обучения/количество часов', ['bold' => true], $center);

        $table->addRow();

        $table->addCell(
            Converter::cmToTwip(6),
            array_merge($headerCellStyle, [
                'vMerge' => 'continue',
            ])
        );

        for ($week = 1; $week <= $weeks; $week++) {
            $table->addCell(Converter::cmToTwip(2), $headerCellStyle)
                ->addText($week . ' неделя', ['bold' => true], $center);
        }

        if ($sections->isEmpty()) {
            $table->addRow();

            $table->addCell(Converter::cmToTwip(6), $cellStyle)
                ->addText('Разделы не заполнены', [], $left);

            for ($week = 1; $week <= $weeks; $week++) {
                $table->addCell(Converter::cmToTwip(2), $cellStyle)
                    ->addText('', [], $center);
            }
        }

        foreach ($sections as $section) {
            $table->addRow();

            $table->addCell(Converter::cmToTwip(6), $cellStyle)
                ->addText($section->title, [], $left);

            for ($week = 1; $week <= $weeks; $week++) {
                $scheduleItem = $rpdProgram->scheduleItems
                    ->where('rpd_curriculum_item_id', $section->id)
                    ->firstWhere('week_number', $week);

                $cell = $table->addCell(Converter::cmToTwip(2), $cellStyle);

                $this->addMultilineCellText(
                    $cell,
                    $scheduleItem?->content ?? '',
                    [],
                    $center
                );
            }
        }

        $processor->setComplexBlock('schedule_table', $table);
    }

    private function fillResources(TemplateProcessor $processor, RpdProgram $rpdProgram): void
    {
        $processor->setValue(
            'main_recommended_resources',
            $this->cleanValue($this->formatResources($rpdProgram, 'main_recommended'))
        );

        $processor->setValue(
            'additional_resources',
            $this->cleanValue($this->formatResources($rpdProgram, 'additional'))
        );

        $processor->setValue(
            'internet_resources',
            $this->cleanValue($this->formatResources($rpdProgram, 'internet'))
        );
    }

    private function fillAuthors(TemplateProcessor $processor, RpdProgram $rpdProgram): void
    {
        $authors = $rpdProgram->authors->values();

        if ($authors->isEmpty()) {
            $authors = collect([
                (object) [
                    'position' => '—',
                    'name' => '—',
                    'organization' => null,
                ],
            ]);
        }

        if ($this->hasVariable($processor, 'author_position')) {
            $processor->cloneRow('author_position', $authors->count());

            foreach ($authors as $index => $author) {
                $number = $index + 1;

                $processor->setValue(
                    "author_position#{$number}",
                    $this->cleanValue($author->position ?: '—')
                );

                $processor->setValue(
                    "author_name#{$number}",
                    $this->cleanValue($this->formatAuthorName($author->name))
                );
            }

            return;
        }

        if ($this->hasVariable($processor, 'authors')) {
            $processor->setValue('authors', $this->cleanValue($this->formatAuthorsText($authors)));
        }
    }

    private function addMultilineCellText(Cell $cell, ?string $text, array $fontStyle = [], array $paragraphStyle = []): void
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $text);

        if (empty($lines)) {
            $cell->addText('', $fontStyle, $paragraphStyle);
            return;
        }

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $cell->addTextBreak();
            }

            $cell->addText($line !== '' ? $line : ' ', $fontStyle, $paragraphStyle);
        }
    }

    private function formatAuthorName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return '—';
        }

        $parts = preg_split('/\s+/u', $name);

        if (count($parts) < 2) {
            return $name;
        }

        $lastName = $parts[0];

        $initials = collect(array_slice($parts, 1))
            ->filter()
            ->map(fn($part) => mb_substr($part, 0, 1) . '.')
            ->implode('');

        return trim($initials . ' ' . $lastName);
    }

    private function formatAuthorsText($authors): string
    {
        return $authors
            ->map(function ($author) {
                $parts = array_filter([
                    $author->position,
                    $this->formatAuthorName($author->name),
                    $author->organization,
                ]);

                return implode(', ', $parts);
            })
            ->implode("\n");
    }

    private function formatResources(RpdProgram $rpdProgram, string $type): string
    {
        $resources = $rpdProgram->resources
            ->where('type', $type)
            ->values();

        if ($resources->isEmpty()) {
            return 'Не заполнено.';
        }

        return $resources
            ->map(fn($resource, $index) => ($index + 1) . '. ' . $resource->title)
            ->implode("\n");
    }

    private function formatList($value): string
    {
        if (is_array($value)) {
            return collect($value)
                ->filter()
                ->map(fn($item) => '• ' . $item)
                ->implode("\n");
        }

        return (string) $value;
    }

    private function cleanValue($value): string
    {
        $value = (string) ($value ?? '');

        return htmlspecialchars($value !== '' ? $value : '—', ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function hasVariable(TemplateProcessor $processor, string $variable): bool
    {
        return in_array($variable, $processor->getVariables(), true);
    }
}
