@extends('layouts.app', ['title' => $rpdProgram->title])

@section('content')
@php
$isAdmin = auth()->user()->role === 'admin';
$canDownloadDocx = $isAdmin || ($rpdProgram->status === 'approved' && filled($rpdProgram->smko_code));
$canPrint = $canDownloadDocx;
$isTeacher = auth()->user()->role === 'teacher';

$teacherCanSubmit = $isTeacher
&& $isReadyForReview
&& in_array($rpdProgram->status, ['draft', 'revision'], true);

$adminCanReview = $isAdmin
&& in_array($rpdProgram->status, ['draft', 'revision', 'submitted'], true);

$workflowHasHistory = $rpdProgram->comments->isNotEmpty()
|| filled($rpdProgram->review_comment);

$canOpenWorkflow = $isAdmin
|| $teacherCanSubmit
|| $workflowHasHistory
|| in_array($rpdProgram->status, ['submitted', 'revision', 'approved', 'rejected'], true);

$workflowLocked = $rpdProgram->status === 'approved';
@endphp
<section class="card" id="section-general">
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
            @if ($canPrint)
            <a href="{{ route('rpd-programs.print', $rpdProgram) }}" class="btn btn-secondary">
                Печатная версия
            </a>
            @endif

            @if ($canDownloadDocx)
            <a href="{{ route('rpd-programs.download-docx', $rpdProgram) }}" class="btn btn-primary">
                Скачать DOCX
            </a>
            @endif
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
            <div class="detail-value">
                @if ($isAdmin)
                <button type="button" class="smko-inline-button" data-modal-open="smko-modal">
                    {{ $rpdProgram->smko_code ?: 'Не указан' }}
                </button>
                @else
                {{ $rpdProgram->smko_code ?: 'Не указан' }}
                @endif
            </div>
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

@if ($canOpenWorkflow)
<section class="card rpd-action-panel">
    <div class="card-header">
        <div>
            <h2 class="card-title">Проверка РПД</h2>
            <p class="card-description">
                История отправки, замечаний, доработок и утверждения.
            </p>
        </div>

        <div class="actions">
            <button type="button" class="btn btn-primary" data-modal-open="workflow-modal">
                Открыть проверку
            </button>
        </div>
    </div>
</section>
@endif
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

<section class="card" id="section-readiness">
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
<section class="card" id="section-characteristics">
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

<section class="card" id="section-curriculum">
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

<section class="card" id="section-schedule">
    <div class="card-header">
        <div>
            <h2 class="card-title">3. Календарный учебный график</h2>
            <p class="card-description">
                Распределение часов по неделям обучения.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.schedule.index', $rpdProgram) }}" class="btn btn-secondary">
                Редактировать график
            </a>
        </div>
    </div>

    <div class="card-body">
        @if ($rpdProgram->scheduleItems->isEmpty())
        <div class="empty-state">
            <h2>Календарный график пока не заполнен</h2>
            <p>Сформируйте график на основе учебного плана и проверьте распределение часов.</p>
        </div>
        @else
        @php
        $weeks = $scheduleWeeksCount;
        $scheduleActualWeeks = $scheduleWeeksCount;
        @endphp

        @if ($scheduleActualWeeks !== $scheduleRecommendedWeeks)
        <div class="alert alert-info">
            Количество недель календарного графика изменено вручную:
            {{ $scheduleActualWeeks }} вместо рекомендуемых {{ $scheduleRecommendedWeeks }}.
            Это допустимое отклонение.
        </div>
        @endif
        <div class="table-scroll schedule-scroll">
            <table class="table schedule-table">
                <thead>
                    <tr>
                        <th rowspan="2">Наименование разделов</th>
                        <th colspan="{{ $weeks }}">Недели обучения / количество часов</th>
                    </tr>
                    <tr>
                        @for ($week = 1; $week <= $weeks; $week++)
                            <th>
                            <div class="schedule-week-heading">
                                <span>{{ $week }} неделя</span>
                                <small>Всего: {{ $scheduleWeekTotals[$week] ?? 0 }} ч.</small>
                            </div>
                            </th>
                            @endfor
                    </tr>
                </thead>

                <tbody>
                    @foreach ($curriculumItems as $row)
                    <tr class="schedule-row-section">
                        <td>
                            <strong>{{ $row->number }}. {{ $row->title }}</strong>

                            <div class="schedule-hours-hint">
                                Всего: {{ $row->total_hours }} ч. ·
                                Теория: {{ $row->theory_hours }} ч. ·
                                Практика: {{ $row->practice_hours }} ч.
                            </div>
                        </td>

                        @for ($week = 1; $week <= $weeks; $week++)
                            @php
                            $scheduleItem=$rpdProgram->scheduleItems
                            ->where('rpd_curriculum_item_id', $row->id)
                            ->firstWhere('week_number', $week);
                            @endphp

                            <td>{!! nl2br(e($scheduleItem?->content)) !!}</td>
                            @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</section>

<section class="card" id="section-content">
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

<section class="card" id="section-assessment">
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
            <div class="numbered-text">
                {!! $rpdProgram->control_survey_materials
                ? nl2br(e($rpdProgram->control_survey_materials))
                : 'Не заполнено' !!}
            </div>

            <h3>Материалы для проведения итоговой практической работы</h3>
            <div class="numbered-text">
                {!! $rpdProgram->final_practical_work_materials
                ? nl2br(e($rpdProgram->final_practical_work_materials))
                : 'Не заполнено' !!}
            </div>

            <h3>Типовые темы проектных работ</h3>
            <div class="numbered-text">
                {!! $rpdProgram->project_topics
                ? nl2br(e($rpdProgram->project_topics))
                : 'Не заполнено' !!}
            </div>
        </div>
        @endif
    </div>
</section>
<section class="card" id="section-resources">
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

<section class="card" id="section-authors">
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
@if ($canOpenWorkflow)
<div class="modal-backdrop" data-modal="workflow-modal" hidden>
    <div class="modal-dialog modal-dialog-chat" role="dialog" aria-modal="true" aria-labelledby="workflow-modal-title">
        <div class="modal-header">
            <div>
                <h2 id="workflow-modal-title">Проверка РПД</h2>
                <p>История замечаний, доработок, отправки и утверждения.</p>
            </div>

            <button type="button" class="modal-close" data-modal-close aria-label="Закрыть">×</button>
        </div>

        <div class="modal-body rpd-workflow">
            <div class="rpd-chat-list" data-chat-list>
                @forelse ($rpdProgram->comments as $comment)
                @php
                $isOwnComment = $comment->user_id === auth()->id();

                $isSystemEvent = str_starts_with($comment->type, 'system_');

                $statusLabel = match ($comment->type) {
                'status_submitted' => 'Отправлено на проверку',
                'status_approved' => 'Утверждено',
                'status_revision' => 'На доработку',
                'status_rejected' => 'Отклонено',
                'system_update' => 'Изменение',
                'system_smko_changed' => 'Изменён СМКО',
                default => null,
                };

                $statusClass = match ($comment->type) {
                'status_submitted' => 'rpd-chat-status-submitted',
                'status_approved' => 'rpd-chat-status-approved',
                'status_revision' => 'rpd-chat-status-revision',
                'status_rejected' => 'rpd-chat-status-rejected',
                'system_update' => 'rpd-chat-status-system',
                'system_smko_changed' => 'rpd-chat-status-submitted',
                default => '',
                };
                @endphp

                @if ($isSystemEvent)
                <div class="rpd-chat-system-event {{ $statusClass }}">
                    <span class="rpd-chat-system-text">{!! nl2br(e($comment->message)) !!}</span>
                    <span class="rpd-chat-system-separator">·</span>
                    <span class="rpd-chat-system-user">{{ $comment->user?->name ?? 'Пользователь' }}</span>
                    <span class="rpd-chat-system-separator">·</span>
                    <time>{{ $comment->created_at?->format('d.m.Y H:i') }}</time>
                </div>
                @else
                <article class="rpd-chat-message {{ $isOwnComment ? 'rpd-chat-message-own' : 'rpd-chat-message-opponent' }}">
                    <div class="rpd-chat-bubble {{ $statusClass }}">
                        <div class="rpd-chat-meta">
                            <strong>{{ $comment->user?->name ?? 'Пользователь' }}</strong>
                            <span>{{ $comment->user?->role === 'admin' ? 'Администратор' : 'Преподаватель' }}</span>
                            <time>{{ $comment->created_at?->format('d.m.Y H:i') }}</time>
                        </div>

                        @if ($statusLabel)
                        <div class="rpd-chat-status-label">
                            {{ $statusLabel }}
                        </div>
                        @endif

                        <div class="rpd-chat-text">
                            {!! nl2br(e($comment->message)) !!}
                        </div>
                    </div>
                </article>
                @endif

                @empty
                <div class="empty-state compact-empty-state">
                    <h2>История пока пуста</h2>
                    <p>Здесь появятся отправка на проверку, замечания, доработки и утверждение.</p>
                </div>
                @endforelse
            </div>

            @if ($workflowLocked)
            <div class="rpd-workflow-locked">
                РПД утверждена. История проверки доступна только для просмотра.
            </div>
            @elseif ($teacherCanSubmit)
            <form
                method="POST"
                action="{{ route('rpd-programs.submit', $rpdProgram) }}"
                class="rpd-chat-composer">
                @csrf
                @method('PATCH')

                <textarea
                    name="workflow_comment"
                    rows="1"
                    placeholder="Кратко опишите, что отправляете или что исправлено...">{{ old('workflow_comment') }}</textarea>

                <button type="submit" class="rpd-chat-send-button" aria-label="Отправить на проверку">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3.6 20.4L21 12 3.6 3.6 3 10.2 14.5 12 3 13.8l.6 6.6z" />
                    </svg>
                </button>
            </form>
            @elseif ($adminCanReview)
            <div class="rpd-review-composer">
                @if ($isReadyForReview)
                <form method="POST" action="{{ route('rpd-programs.approve', $rpdProgram) }}" class="admin-review-form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="smko_code" value="{{ $rpdProgram->smko_code }}">

                    <div class="rpd-chat-composer rpd-chat-composer-review">
                        <textarea
                            id="approve_review_comment"
                            name="review_comment"
                            rows="1"
                            placeholder="Комментарий к решению...">{{ old('review_comment', $rpdProgram->review_comment) }}</textarea>

                        @if (filled($rpdProgram->smko_code))
                        <button type="submit" class="rpd-chat-send-button rpd-chat-send-button-approve" aria-label="Утвердить">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.2 16.6 4.9 12.3 3.5 13.7l5.7 5.7L21 7.6 19.6 6.2z" />
                            </svg>
                        </button>
                        @else
                        <button
                            type="button"
                            class="rpd-chat-send-button rpd-chat-send-button-disabled"
                            data-modal-open="smko-required-modal"
                            title="Введите сначала СМКО"
                            aria-label="Введите сначала СМКО">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.2 16.6 4.9 12.3 3.5 13.7l5.7 5.7L21 7.6 19.6 6.2z" />
                            </svg>
                        </button>
                        @endif
                    </div>

                    @if ($rpdProgram->status === 'submitted')
                    <div class="review-secondary-actions">
                        <button type="submit" class="btn btn-secondary" form="return-for-revision-form">
                            Вернуть на доработку
                        </button>

                        <button type="submit" class="btn btn-danger" form="reject-form">
                            Отклонить
                        </button>
                    </div>
                    @endif
                </form>

                @if ($rpdProgram->status === 'submitted')
                <form id="return-for-revision-form" method="POST" action="{{ route('rpd-programs.return-for-revision', $rpdProgram) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="review_comment" value="" data-review-comment-copy>
                </form>

                <form id="reject-form" method="POST" action="{{ route('rpd-programs.reject', $rpdProgram) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="review_comment" value="" data-review-comment-copy>
                </form>
                @endif
                @else
                <div class="alert alert-warning">
                    РПД пока нельзя утвердить. Ниже перечислены незаполненные разделы.
                </div>

                <div class="readiness-grid readiness-grid-compact">
                    @foreach ($readiness as $item)
                    @unless ($item['is_ready'])
                    <a href="{{ $item['url'] }}" class="readiness-item readiness-item-warning">
                        <div class="readiness-status">!</div>

                        <div>
                            <strong>{{ $item['title'] }}</strong>
                            <p>{{ $item['message'] }}</p>
                        </div>
                    </a>
                    @endunless
                    @endforeach
                </div>
                @endif
            </div>
            @else
            <div class="rpd-workflow-locked">
                Сейчас РПД находится в статусе «{{ $rpdProgram->status_label }}». Действия недоступны.
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@if ($canOpenWorkflow)
<button
    type="button"
    class="workflow-floating-button"
    data-modal-open="workflow-modal"
    aria-label="Проверка РПД"
    title="Проверка РПД">
    ✓
</button>
@endif

@if ($isAdmin)
<div class="modal-backdrop" data-modal="smko-modal" hidden>
    <div class="modal-dialog modal-dialog-small" role="dialog" aria-modal="true" aria-labelledby="smko-modal-title">
        <div class="modal-header">
            <div>
                <h2 id="smko-modal-title">Код СМКО</h2>
                <p>Укажите код СМКО для этой РПД.</p>
            </div>

            <button type="button" class="modal-close" data-modal-close aria-label="Закрыть">×</button>
        </div>

        <form method="POST" action="{{ route('rpd-programs.update-smko-code', $rpdProgram) }}" class="modal-body smko-modal-form">
            @csrf
            @method('PATCH')

            <div class="form-field">
                <label for="smko_code_modal">Код СМКО</label>
                <input
                    id="smko_code_modal"
                    name="smko_code"
                    type="text"
                    value="{{ old('smko_code', $rpdProgram->smko_code) }}"
                    placeholder="СМКО МИРЭА 8.5.1/03.Пр _____-1__"
                    required>
            </div>

            <div class="modal-actions-row">
                <button type="button" class="btn btn-secondary" data-modal-close>
                    Отмена
                </button>

                <button type="submit" class="btn btn-primary">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" data-modal="smko-required-modal" hidden>
    <div class="modal-dialog modal-dialog-small" role="dialog" aria-modal="true" aria-labelledby="smko-required-modal-title">
        <div class="modal-header">
            <div>
                <h2 id="smko-required-modal-title">Нужен код СМКО</h2>
                <p>Перед утверждением РПД необходимо указать код СМКО.</p>
            </div>

            <button type="button" class="modal-close" data-modal-close aria-label="Закрыть">×</button>
        </div>

        <form method="POST" action="{{ route('rpd-programs.approve', $rpdProgram) }}" class="modal-body smko-modal-form">
            @csrf
            @method('PATCH')

            <div class="form-field">
                <label for="smko_code_required">Код СМКО</label>
                <input
                    id="smko_code_required"
                    name="smko_code"
                    type="text"
                    value="{{ old('smko_code', $rpdProgram->smko_code) }}"
                    placeholder="СМКО МИРЭА 8.5.1/03.Пр _____-1__"
                    required>
            </div>

            <input type="hidden" name="review_comment" value="{{ old('review_comment', $rpdProgram->review_comment) }}">

            <div class="modal-actions-row">
                <button type="button" class="btn btn-secondary" data-modal-close>
                    Отмена
                </button>

                <button type="submit" class="btn btn-primary">
                    Указать СМКО и утвердить
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- (removed duplicate admin review section) --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openModal = (modalName) => {
            const modal = document.querySelector(`[data-modal="${modalName}"]`);

            if (!modal) {
                return;
            }

            modal.hidden = false;
            document.body.classList.add('modal-open');

            const chatList = modal.querySelector('[data-chat-list]');

            if (chatList) {
                setTimeout(() => {
                    chatList.scrollTop = chatList.scrollHeight;
                }, 50);
            }

            const focusTarget = modal.querySelector('textarea, input, button');

            if (focusTarget) {
                setTimeout(() => focusTarget.focus(), 50);
            }
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.hidden = true;

            if (!document.querySelector('[data-modal]:not([hidden])')) {
                document.body.classList.remove('modal-open');
            }
        };

        document.querySelectorAll('[data-modal-open]').forEach((button) => {
            button.addEventListener('click', () => {
                openModal(button.dataset.modalOpen);
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(button.closest('[data-modal]'));
            });
        });

        document.querySelectorAll('[data-modal]').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('[data-modal]:not([hidden])').forEach(closeModal);
        });

        const reviewComment = document.querySelector('#approve_review_comment');
        const commentCopies = document.querySelectorAll('[data-review-comment-copy]');

        if (reviewComment && commentCopies.length > 0) {
            const syncReviewComment = () => {
                commentCopies.forEach((input) => {
                    input.value = reviewComment.value;
                });
            };

            reviewComment.addEventListener('input', syncReviewComment);
            syncReviewComment();
        }

        const autoOpenModal = @json(session('open_modal'));

        if (autoOpenModal) {
            openModal(autoOpenModal);
        }


    });
</script>
@endsection