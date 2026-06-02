<?php

namespace App\Services;

use App\Models\RpdProgram;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\Language;

class RpdDocxGenerator
{
    public function generate(RpdProgram $rpdProgram): string
    {
        $rpdProgram->load([
            'curriculumItems.children',
            'curriculumItems.controlForm',
            'contentSections' => fn ($query) => $query
                ->whereNotNull('rpd_curriculum_item_id')
                ->orderBy('sort_order'),
            'scheduleItems',
            'resources',
            'authors',
        ]);

        $curriculumItems = $rpdProgram->curriculumItems
            ->whereNull('parent_id')
            ->sortBy('sort_order');

        $phpWord = new PhpWord();
        $phpWord->getSettings()->setThemeFontLang(new Language('ru-RU'));

        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16], ['alignment' => 'center', 'spaceAfter' => 240]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14], ['spaceBefore' => 240, 'spaceAfter' => 120]);
        $phpWord->addTitleStyle(3, ['bold' => true, 'size' => 12], ['spaceBefore' => 180, 'spaceAfter' => 80]);

        $section = $phpWord->addSection([
            'marginTop' => 1134,
            'marginRight' => 850,
            'marginBottom' => 1134,
            'marginLeft' => 1134,
        ]);

        $section->addTitle($rpdProgram->title, 1);

        $this->addParagraph($section, 'Направленность: ' . ($rpdProgram->direction_label ?? $rpdProgram->direction));
        $this->addParagraph($section, 'Уровень сложности: ' . $rpdProgram->complexity_level);
        $this->addParagraph($section, 'Год: ' . $rpdProgram->year);
        $this->addParagraph($section, 'Объем: ' . $rpdProgram->total_hours . ' академических часов');

        $section->addTitle('1. Основные параметры программы', 2);
        $this->addSubsection($section, 'Форма обучения', $rpdProgram->education_form);
        $this->addSubsection($section, 'Режим занятий', $rpdProgram->study_mode);
        $this->addSubsection($section, 'Категория слушателей', $rpdProgram->students_category);
        $this->addSubsection($section, 'Требования к уровню подготовки слушателей', $rpdProgram->preparation_requirements);

        $section->addTitle('2. Учебный план', 2);
        $this->addCurriculumTable($section, $curriculumItems);

        $section->addTitle('3. Календарный учебный график', 2);
        $this->addScheduleTable($section, $curriculumItems, $rpdProgram);

        $section->addTitle('4. Содержание учебного плана', 2);
        if ($rpdProgram->contentSections->isEmpty()) {
            $this->addParagraph($section, 'Содержание учебного плана не заполнено.');
        } else {
            foreach ($rpdProgram->contentSections as $contentSection) {
                $section->addTitle($contentSection->number . '. ' . $contentSection->title, 3);
                $this->addParagraph($section, $contentSection->content);
            }
        }

        $section->addTitle('5. Оценочные материалы', 2);
        $this->addSubsection($section, 'Материалы для проведения контрольных опросов', $rpdProgram->control_survey_materials);
        $this->addSubsection($section, 'Материалы для проведения итоговой практической работы', $rpdProgram->final_practical_work_materials);
        $this->addSubsection($section, 'Типовые темы проектных работ', $rpdProgram->project_topics);

        $section->addTitle('6. Литература и интернет-ресурсы', 2);
        foreach ([
            'main_recommended' => 'Список основной рекомендуемой литературы',
            'additional' => 'Дополнительная литература',
            'internet' => 'Ресурсы информационно-телекоммуникационной сети Интернет',
        ] as $type => $label) {
            $resources = $rpdProgram->resources->where('type', $type);

            if ($resources->isEmpty()) {
                continue;
            }

            $section->addTitle($label, 3);

            foreach ($resources->values() as $index => $resource) {
                $this->addParagraph($section, ($index + 1) . '. ' . $resource->title);
            }
        }

        $section->addTitle('7. Разработчики', 2);
        if ($rpdProgram->authors->isEmpty()) {
            $this->addParagraph($section, 'Разработчики не указаны.');
        } else {
            foreach ($rpdProgram->authors as $author) {
                $text = $author->name;

                if ($author->position) {
                    $text .= ', ' . $author->position;
                }

                if ($author->organization) {
                    $text .= ', ' . $author->organization;
                }

                $this->addParagraph($section, $text);
            }
        }

        $directory = storage_path('app/generated/rpd');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = 'rpd_' . $rpdProgram->id . '_' . now()->format('Ymd_His') . '.docx';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    private function addSubsection($section, string $title, ?string $content): void
    {
        $section->addTitle($title, 3);
        $this->addParagraph($section, $content ?: 'Не заполнено.');
    }

    private function addParagraph($section, ?string $text): void
    {
        foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
            $section->addText(
                $line !== '' ? $line : ' ',
                [],
                [
                    'alignment' => 'both',
                    'spaceAfter' => 120,
                    'lineHeight' => 1.15,
                ]
            );
        }
    }

    private function addCurriculumTable($section, $curriculumItems): void
    {
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'alignment' => JcTable::CENTER,
        ]);

        $table->addRow();
        foreach (['№ п. п.', 'Наименование разделов и тем', 'Всего', 'Теория', 'Практика', 'Форма контроля'] as $heading) {
            $table->addCell(1600)->addText($heading, ['bold' => true], ['alignment' => 'center']);
        }

        foreach ($curriculumItems as $item) {
            $this->addCurriculumRow($table, $item, true);

            foreach ($item->children as $child) {
                $this->addCurriculumRow($table, $child, false);
            }
        }
    }

    private function addCurriculumRow($table, $item, bool $isSection): void
    {
        $table->addRow();

        $table->addCell(900)->addText($item->number);
        $table->addCell(5200)->addText($item->title, ['bold' => $isSection]);
        $table->addCell(900)->addText((string) $item->total_hours, [], ['alignment' => 'center']);
        $table->addCell(900)->addText((string) $item->theory_hours, [], ['alignment' => 'center']);
        $table->addCell(900)->addText((string) $item->practice_hours, [], ['alignment' => 'center']);

        $control = $item->controlForm?->title ?? $item->control_form ?? '—';
        $table->addCell(1800)->addText($control);
    }

    private function addScheduleTable($section, $curriculumItems, RpdProgram $rpdProgram): void
    {
        $weeks = (int) ($rpdProgram->scheduleItems->max('week_number') ?: 0);

        if ($weeks <= 0) {
            $this->addParagraph($section, 'Календарный учебный график не заполнен.');
            return;
        }

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'alignment' => JcTable::CENTER,
        ]);

        $table->addRow();
        $table->addCell(4200)->addText('Раздел', ['bold' => true], ['alignment' => 'center']);

        for ($week = 1; $week <= $weeks; $week++) {
            $table->addCell(900)->addText($week . ' нед.', ['bold' => true], ['alignment' => 'center']);
        }

        foreach ($curriculumItems as $item) {
            $table->addRow();
            $table->addCell(4200)->addText($item->number . '. ' . $item->title);

            for ($week = 1; $week <= $weeks; $week++) {
                $scheduleItem = $rpdProgram->scheduleItems
                    ->where('rpd_curriculum_item_id', $item->id)
                    ->firstWhere('week_number', $week);

                $table->addCell(900)->addText($scheduleItem?->content ?? '', [], ['alignment' => 'center']);
            }
        }
    }
}