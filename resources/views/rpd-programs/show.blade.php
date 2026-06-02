@extends('layouts.app', ['title' => $rpdProgram->title])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">{{ $rpdProgram->title }}</h2>
            <p class="card-description">
                Структурный обзор РПД. Для внесения изменений используйте режим редактирования.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.index') }}" class="btn btn-secondary">К списку</a>
            <a href="{{ route('rpd-programs.edit', $rpdProgram) }}" class="btn btn-primary">Редактировать</a>

            <form
                method="POST"
                action="{{ route('rpd-programs.destroy', $rpdProgram) }}"
                onsubmit="return confirm('Удалить эту РПД? Это действие нельзя отменить.')">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-danger">
                    Удалить
                </button>
            </form>
        </div>
    </div>

    <div class="card-body details-grid">
        <div class="detail-item">
            <div class="detail-label">Направленность</div>
            <div class="detail-value">{{ $rpdProgram->direction_label }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Уровень сложности</div>
            <div class="detail-value">{{ $rpdProgram->complexity_level }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Год</div>
            <div class="detail-value">{{ $rpdProgram->year }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Код СМКО</div>
            <div class="detail-value">{{ $rpdProgram->smko_code ?: 'Не указан' }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Объем программы</div>
            <div class="detail-value">{{ $rpdProgram->total_hours }} ак. ч.</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Срок освоения</div>
            <div class="detail-value">{{ $rpdProgram->study_period }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Возраст слушателей</div>
            <div class="detail-value">{{ $rpdProgram->students_age }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Формат обучения</div>
            <div class="detail-value">{{ $rpdProgram->education_format_label }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Периодичность занятий</div>
            <div class="detail-value">{{ $rpdProgram->lessons_per_week }} в неделю</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Академических часов за занятие</div>
            <div class="detail-value">{{ $rpdProgram->academic_hours_per_lesson }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Статус</div>
            <div class="detail-value">
                <span class="badge">{{ $rpdProgram->status_label }}</span>
            </div>
        </div>
    </div>
</section>


@if ($rpdProgram->review_comment)
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Комментарий администратора</h2>
            <p class="card-description">
                Комментарий по результатам проверки РПД.
            </p>
        </div>
    </div>

    <div class="card-body">
        <div class="review-comment">
            {{ $rpdProgram->review_comment }}
        </div>
    </div>
</section>
@endif
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Готовность РПД</h2>
            <p class="card-description">
                Основные разделы, которые нужно заполнить перед отправкой на проверку.
            </p>
        </div>
    </div>

    <div class="card-body readiness-grid">
        @foreach ($readiness as $item)
        <a
            href="{{ $item['url'] }}"
            class="readiness-item {{ $item['is_ready'] ? 'readiness-item-ready' : 'readiness-item-warning' }}">
            <div class="readiness-status">
                {{ $item['is_ready'] ? '✓' : '!' }}
            </div>

            <div>
                <strong>{{ $item['title'] }}</strong>
                <p>{{ $item['is_ready'] ? 'Раздел заполнен.' : $item['message'] }}</p>
            </div>
        </a>
        @endforeach
    </div>
</section>
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">1. Основные параметры программы</h2>
            <p class="card-description">
                Параметры, влияющие на формирование текста РПД.
            </p>
        </div>
    </div>

    <div class="card-body document-section">

        <h3>Форма обучения</h3>
        <p>{{ $rpdProgram->education_form }}</p>

        <h3>Режим занятий</h3>
        <p>{{ $rpdProgram->study_mode }}</p>

        <h3>Категория слушателей</h3>
        <p>{{ $rpdProgram->students_category }}</p>

        <h3>Требования к уровню подготовки слушателей</h3>
        <p>{{ $rpdProgram->preparation_requirements }}</p>
    </div>


</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">2. Учебный план</h2>
            <p class="card-description">
                Разделы, темы, часы теории и практики, формы контроля.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.curriculum.index', $rpdProgram) }}" class="btn btn-secondary">
                Редактировать учебный план
            </a>
        </div>
    </div>

    <div class="card-body">
        @php
        $sectionTotal = $rpdProgram->curriculumItems->where('type', 'section')->sum('total_hours');
        $sectionTheory = $rpdProgram->curriculumItems->where('type', 'section')->sum('theory_hours');
        $sectionPractice = $rpdProgram->curriculumItems->where('type', 'section')->sum('practice_hours');
        $hasCurriculumMismatch = (int) $sectionTotal !== (int) $rpdProgram->total_hours;
        @endphp

        @if ($hasCurriculumMismatch)
        <div class="alert alert-warning overview-warning">
            Сумма часов по разделам не совпадает с объемом программы.
            В программе указано {{ $rpdProgram->total_hours }} ч., в учебном плане — {{ $sectionTotal }} ч.
        </div>
        @endif

        @if ($curriculumItems->isEmpty())
        <div class="empty-state">
            <h2>Учебный план пока не заполнен</h2>
            <p>Добавьте разделы и темы в режиме редактирования.</p>
        </div>
        @else
        <div class="table-scroll">
            <table class="table curriculum-table overview-table">
                <thead>
                    <tr>
                        <th>№ п. п.</th>
                        <th>Наименование уровней, разделов и тем</th>
                        <th>Всего часов</th>
                        <th>Теория</th>
                        <th>Практика</th>
                        <th>Формы аттестации/контроля</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($curriculumItems as $item)
                    @php
                    $children = $item->children;

                    $childrenTotal = $children->sum('total_hours');
                    $childrenTheory = $children->sum('theory_hours');
                    $childrenPractice = $children->sum('practice_hours');

                    $sectionMismatch = $item->type === 'section'
                    && $children->isNotEmpty()
                    && (
                    (int) $item->total_hours !== (int) $childrenTotal
                    || (int) $item->theory_hours !== (int) $childrenTheory
                    || (int) $item->practice_hours !== (int) $childrenPractice
                    );
                    @endphp

                    <tr class="curriculum-row-{{ $item->type }} {{ $sectionMismatch ? 'table-row-warning' : '' }}">
                        <td class="curriculum-number-cell">{{ $item->number ?: '—' }}</td>
                        <td><strong>{{ $item->title }}</strong></td>
                        <td>{{ $item->total_hours }}</td>
                        <td>{{ $item->theory_hours }}</td>
                        <td>{{ $item->practice_hours }}</td>
                        <td>{{ $item->control_form ?: '—' }}</td>
                    </tr>

                    @if ($sectionMismatch)
                    <tr class="table-note-row">
                        <td colspan="6">
                            В разделе «{{ $item->title }}» часы раздела не совпадают с суммой тем:
                            раздел — {{ $item->total_hours }} / {{ $item->theory_hours }} / {{ $item->practice_hours }},
                            темы — {{ $childrenTotal }} / {{ $childrenTheory }} / {{ $childrenPractice }}.
                        </td>
                    </tr>
                    @endif

                    @foreach ($children as $child)
                    <tr class="curriculum-row-topic">
                        <td class="curriculum-number-cell">{{ $child->number }}</td>
                        <td class="curriculum-child-title">{{ $child->title }}</td>
                        <td>{{ $child->total_hours }}</td>
                        <td>{{ $child->theory_hours }}</td>
                        <td>{{ $child->practice_hours }}</td>
                        <td>{{ $child->control_form ?: '—' }}</td>
                    </tr>
                    @endforeach
                    @endforeach

                    <tr class="curriculum-total-row">
                        <td></td>
                        <td><strong>ИТОГО</strong></td>
                        <td><strong>{{ $sectionTotal }}</strong></td>
                        <td><strong>{{ $sectionTheory }}</strong></td>
                        <td><strong>{{ $sectionPractice }}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">3. Календарный учебный график</h2>
            <p class="card-description">
                Раздел будет добавлен следующим этапом.
            </p>
        </div>
    </div>

    <div class="card-body">
        <div class="empty-state">
            <h2>Календарный график пока не заполнен</h2>
            <p>После реализации раздела здесь будет отображаться распределение часов по неделям.</p>
        </div>
    </div>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">4. Содержание учебного плана</h2>
            <p class="card-description">
                Содержательное описание разделов программы.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.content.index', $rpdProgram) }}" class="btn btn-secondary">
                Редактировать содержание
            </a>
        </div>
    </div>


    <div class="card-body">
        @if ($rpdProgram->contentSections->isEmpty())
        <div class="empty-state">
            <h2>Содержание пока не заполнено</h2>
            <p>Позже здесь будет отображаться описание каждого раздела.</p>
        </div>
        @else
        <div class="document-section">
            @foreach ($rpdProgram->contentSections as $contentSection)
            <h3>{{ $contentSection->number }}. {{ $contentSection->title }}</h3>
            <p>{{ $contentSection->content }}</p>
            @endforeach
        </div>
        @endif
    </div>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">5. Оценочные материалы</h2>
            <p class="card-description">
                Материалы для текущего и итогового контроля.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.assessment.index', $rpdProgram) }}" class="btn btn-secondary">
                Редактировать оценочные материалы
            </a>
        </div>
    </div>

    <div class="card-body">
        @if (
        blank($rpdProgram->control_survey_materials)
        && blank($rpdProgram->final_practical_work_materials)
        && blank($rpdProgram->project_topics)
        )
        <div class="empty-state">
            <h2>Оценочные материалы пока не заполнены</h2>
            <p>Заполните материалы для опросов, итоговой практической работы и темы проектов.</p>
        </div>
        @else
        <div class="document-section">
            <h3>Материалы для проведения контрольных опросов</h3>
            <p>{{ $rpdProgram->control_survey_materials ?: 'Не заполнено' }}</p>

            <h3>Материалы для проведения итоговой практической работы</h3>
            <p>{{ $rpdProgram->final_practical_work_materials ?: 'Не заполнено' }}</p>

            <h3>Типовые темы проектных работ</h3>
            <p>{{ $rpdProgram->project_topics ?: 'Не заполнено' }}</p>
        </div>
        @endif
    </div>
</section>
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">6. Литература и интернет-ресурсы</h2>
            <p class="card-description">
                Список основной рекомендуемой литературы, дополнительная литература и интернет-ресурсы.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.resources.index', $rpdProgram) }}" class="btn btn-secondary">
                Редактировать источники
            </a>
        </div>
    </div>

    <div class="card-body">
        @if ($rpdProgram->resources->isEmpty())
        <div class="empty-state">
            <h2>Источники пока не добавлены</h2>
            <p>Добавьте литературу и интернет-ресурсы.</p>
        </div>
        @else
        <div class="document-section">
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
                <li>
                    {{ $resource->title }}
                    @if ($resource->url)
                    <br>
                    <span class="muted">{{ $resource->url }}</span>
                    @endif
                </li>
                @endforeach
            </ol>
            @endif
            @endforeach
        </div>
        @endif
    </div>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">7. Разработчики</h2>
            <p class="card-description">
                Сведения о разработчиках программы.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.authors.index', $rpdProgram) }}" class="btn btn-secondary">
                Редактировать разработчиков
            </a>
        </div>
    </div>

    <div class="card-body">
        @if ($rpdProgram->authors->isEmpty())
        <div class="empty-state">
            <h2>Разработчики пока не указаны</h2>
            <p>Добавьте сведения о разработчике программы.</p>
        </div>
        @else
        <div class="document-section">
            @foreach ($rpdProgram->authors as $author)
            <p>
                <strong>{{ $author->name }}</strong>
                @if ($author->position)
                <br>{{ $author->position }}
                @endif
                @if ($author->organization)
                <br><span class="muted">{{ $author->organization }}</span>
                @endif
            </p>
            @endforeach
        </div>
        @endif
    </div>
</section>
@if (auth()->user()->role === 'teacher' && in_array($rpdProgram->status, ['draft', 'revision'], true))
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Отправка на проверку</h2>
            <p class="card-description">
                После отправки администратор сможет проверить РПД, оставить комментарий и принять решение.
            </p>
        </div>

        <form method="POST" action="{{ route('rpd-programs.submit', $rpdProgram) }}">
            @csrf
            @method('PATCH')

            <button type="submit" class="btn btn-primary">
                Отправить на проверку
            </button>
        </form>
    </div>
</section>
@endif

@if (auth()->user()->role === 'admin' && in_array($rpdProgram->status, ['draft', 'submitted', 'revision', 'approved'], true))
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Проверка администратором</h2>
            <p class="card-description">
                Укажите комментарий при возврате на доработку или отклонении. При утверждении комментарий необязателен.
            </p>
        </div>
    </div>

    <div class="card-body">
        <div class="review-actions">
            <div class="form-field form-field-wide">
                <label for="review_comment">Комментарий администратора</label>
                <textarea
                    id="review_comment"
                    name="review_comment"
                    rows="4"
                    form="approve-form"
                    placeholder="Например: уточнить календарный график, исправить распределение часов, добавить литературу.">{{ old('review_comment', $rpdProgram->review_comment) }}</textarea>
            </div>

            <div class="review-buttons">
                <form id="approve-form" method="POST" action="{{ route('rpd-programs.approve', $rpdProgram) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-primary">
                        Утвердить
                    </button>
                </form>

                <form method="POST" action="{{ route('rpd-programs.return-for-revision', $rpdProgram) }}">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="review_comment" value="" data-review-comment-copy>

                    <button type="submit" class="btn btn-secondary">
                        Вернуть на доработку
                    </button>
                </form>

                <form method="POST" action="{{ route('rpd-programs.reject', $rpdProgram) }}">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="review_comment" value="" data-review-comment-copy>

                    <button type="submit" class="btn btn-danger">
                        Отклонить
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endif
@endsection