@extends('layouts.app', ['title' => 'Печатная версия РПД'])

@section('content')
    <section class="print-document">
        <div class="print-actions">
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}" class="btn btn-secondary">
                К обзору
            </a>

            <button type="button" class="btn btn-primary" onclick="window.print()">
                Печать
            </button>
        </div>

        <article class="print-page">
            <h1>{{ $rpdProgram->title }}</h1>

            <p><strong>Направленность:</strong> {{ $rpdProgram->direction_label }}</p>
            <p><strong>Уровень сложности:</strong> {{ $rpdProgram->complexity_level }}</p>
            <p><strong>Год:</strong> {{ $rpdProgram->year }}</p>
            <p><strong>Объем:</strong> {{ $rpdProgram->total_hours }} академических часов</p>

            <h2>1. Основные параметры программы</h2>

            <h3>Форма обучения</h3>
            <p>{{ $rpdProgram->education_form }}</p>

            <h3>Режим занятий</h3>
            <p>{!! nl2br(e($rpdProgram->study_mode)) !!}</p>

            <h3>Категория слушателей</h3>
            <p>{{ $rpdProgram->students_category }}</p>

            <h3>Требования к уровню подготовки слушателей</h3>
            <p>{!! nl2br(e($rpdProgram->preparation_requirements)) !!}</p>

            <h2>2. Учебный план</h2>

            <table class="print-table">
                <thead>
                    <tr>
                        <th>№ п. п.</th>
                        <th>Наименование разделов и тем</th>
                        <th>Всего</th>
                        <th>Теория</th>
                        <th>Практика</th>
                        <th>Форма контроля</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($curriculumItems as $item)
                        <tr>
                            <td>{{ $item->number }}</td>
                            <td><strong>{{ $item->title }}</strong></td>
                            <td>{{ $item->total_hours }}</td>
                            <td>{{ $item->theory_hours }}</td>
                            <td>{{ $item->practice_hours }}</td>
                            <td>{{ $item->control_form ?: '—' }}</td>
                        </tr>

                        @foreach ($item->children as $child)
                            <tr>
                                <td>{{ $child->number }}</td>
                                <td>{{ $child->title }}</td>
                                <td>{{ $child->total_hours }}</td>
                                <td>{{ $child->theory_hours }}</td>
                                <td>{{ $child->practice_hours }}</td>
                                <td>{{ $child->control_form ?: '—' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <h2>3. Календарный учебный график</h2>

            @php
                $weeks = $rpdProgram->scheduleItems->max('week_number') ?: 0;
            @endphp

            @if ($weeks > 0)
                <table class="print-table print-schedule-table">
                    <thead>
                        <tr>
                            <th>Раздел</th>
                            @for ($week = 1; $week <= $weeks; $week++)
                                <th>{{ $week }} нед.</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($curriculumItems as $item)
                            <tr>
                                <td>{{ $item->number }}. {{ $item->title }}</td>
                                @for ($week = 1; $week <= $weeks; $week++)
                                    @php
                                        $scheduleItem = $rpdProgram->scheduleItems
                                            ->where('rpd_curriculum_item_id', $item->id)
                                            ->firstWhere('week_number', $week);
                                    @endphp
                                    <td>{!! nl2br(e($scheduleItem?->content)) !!}</td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Календарный учебный график не заполнен.</p>
            @endif

            <h2>4. Содержание учебного плана</h2>

            @foreach ($rpdProgram->contentSections as $contentSection)
                <h3>{{ $contentSection->number }}. {{ $contentSection->title }}</h3>
                <p>{!! nl2br(e($contentSection->content)) !!}</p>
            @endforeach

            <h2>5. Оценочные материалы</h2>

            <h3>Материалы для проведения контрольных опросов</h3>
            <p>{!! nl2br(e($rpdProgram->control_survey_materials)) !!}</p>

            <h3>Материалы для проведения итоговой практической работы</h3>
            <p>{!! nl2br(e($rpdProgram->final_practical_work_materials)) !!}</p>

            <h3>Типовые темы проектных работ</h3>
            <p>{!! nl2br(e($rpdProgram->project_topics)) !!}</p>

            <h2>6. Литература и интернет-ресурсы</h2>

            @foreach ([
                'main_recommended' => 'Список основной рекомендуемой литературы',
                'additional' => 'Дополнительная литература',
                'internet' => 'Ресурсы информационно-телекоммуникационной сети Интернет',
            ] as $type => $label)
                @php
                    $resources = $rpdProgram->resources->where('type', $type);
                @endphp

                @if ($resources->isNotEmpty())
                    <h3>{{ $label }}</h3>
                    <ol>
                        @foreach ($resources as $resource)
                            <li>{{ $resource->title }}</li>
                        @endforeach
                    </ol>
                @endif
            @endforeach

            <h2>7. Разработчики</h2>

            @foreach ($rpdProgram->authors as $author)
                <p>
                    <strong>{{ $author->name }}</strong>
                    @if ($author->position)
                        <br>{{ $author->position }}
                    @endif
                    @if ($author->organization)
                        <br>{{ $author->organization }}
                    @endif
                </p>
            @endforeach
        </article>
    </section>
@endsection