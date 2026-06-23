@extends('layouts.app', ['title' => 'Календарный учебный график'])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Календарный учебный график</h2>
            <p class="card-description">
                {{ $rpdProgram->title }}. График формируется автоматически после заполнения учебного плана.
                При необходимости его можно скорректировать вручную.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}#section-schedule" class="btn btn-secondary">К РПД</a>

            <form method="POST" action="{{ route('rpd-programs.schedule.generate', $rpdProgram) }}">
                @csrf

                <button
                    type="submit"
                    class="btn btn-primary"
                    onclick="return confirm('Текущий календарный график будет перезаписан автоматическим распределением. Продолжить?')">
                    Сбросить к автоматическому распределению
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <div class="alert alert-info">
            <strong>Как заполнять вручную:</strong>
            в ячейке недели укажите тип занятия и количество часов в скобках.
            Например: <strong>Т (2)</strong> — 2 часа теории, <strong>П (2)</strong> — 2 часа практики.
            Если в одной неделе есть и теория, и практика, укажите их с новой строки.
        </div>

        <div class="alert alert-warning">
            Кнопка «Сбросить к автоматическому распределению» перезаписывает текущий график.
            Используйте её, если хотите заново получить график на основе учебного плана.
        </div>

        @if ($weeksCount !== $recommendedWeeksCount)
            <div class="alert alert-info">
                Количество недель изменено вручную: {{ $weeksCount }} вместо рекомендуемых {{ $recommendedWeeksCount }}.
                Это допустимо, просто проверьте календарный учебный график перед отправкой РПД.
            </div>
        @endif

        <div class="schedule-weeks-panel">
            <div class="schedule-weeks-copy">
                <strong>Количество недель</strong>
                <p>
                    Рекомендуемое количество по параметрам программы:
                    <span>{{ $recommendedWeeksCount }}</span>.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('rpd-programs.schedule.weeks.update', $rpdProgram) }}"
                class="schedule-weeks-form">
                @csrf
                @method('PATCH')

                <div class="form-field schedule-weeks-input">
                    <label for="schedule_weeks_count">Недели</label>
                    <input
                        id="schedule_weeks_count"
                        type="number"
                        name="schedule_weeks_count"
                        value="{{ $weeksCount }}"
                        min="1"
                        max="52"
                        required>
                </div>

                <button type="submit" class="btn btn-secondary">
                    Обновить
                </button>
            </form>
        </div>

        @if ($rpdProgram->curriculumItems->isEmpty())
            <div class="empty-state">
                <h2>Учебный план пока не заполнен</h2>
                <p>Сначала добавьте разделы и темы в учебный план.</p>
            </div>
        @else
            <form method="POST" action="{{ route('rpd-programs.schedule.update', $rpdProgram) }}">
                @csrf
                @method('PUT')

                <div class="table-scroll schedule-scroll">
                    <table class="table schedule-table">
                        <thead>
                            <tr>
                                <th rowspan="2">Наименование разделов</th>
                                <th colspan="{{ $weeksCount }}">Недели обучения / количество часов</th>
                            </tr>
                            <tr>
                                @for ($week = 1; $week <= $weeksCount; $week++)
                                    <th>
                                        <div class="schedule-week-heading">
                                            <span>{{ $week }} неделя</span>
                                            <small>Всего: {{ $weekTotals[$week] ?? 0 }} ч.</small>
                                        </div>
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($rpdProgram->curriculumItems->where('type', 'section') as $item)
                                <tr class="schedule-row-{{ $item->type }} {{ isset($scheduleWarnings[$item->id]) ? 'schedule-row-has-warning' : '' }}">
                                    <td>
                                        <strong>{{ $item->number }}. {{ $item->title }}</strong>

                                        <div class="schedule-hours-hint">
                                            Всего: {{ $item->total_hours }} ч. ·
                                            Теория: {{ $item->theory_hours }} ч. ·
                                            Практика: {{ $item->practice_hours }} ч.
                                        </div>

                                        @if (isset($scheduleWarnings[$item->id]))
                                            <div class="schedule-row-warning">
                                                По графику:
                                                Т {{ $scheduleWarnings[$item->id]['planned_theory'] }} ч.,
                                                П {{ $scheduleWarnings[$item->id]['planned_practice'] }} ч.
                                                Ожидается:
                                                Т {{ $scheduleWarnings[$item->id]['expected_theory'] }} ч.,
                                                П {{ $scheduleWarnings[$item->id]['expected_practice'] }} ч.
                                            </div>
                                        @endif
                                    </td>

                                    @for ($week = 1; $week <= $weeksCount; $week++)
                                        @php
                                            $scheduleItem = $rpdProgram->scheduleItems
                                                ->where('rpd_curriculum_item_id', $item->id)
                                                ->firstWhere('week_number', $week);
                                        @endphp

                                        <td>
                                            <textarea
                                                name="schedule[{{ $item->id }}][{{ $week }}]"
                                                rows="2"
                                                placeholder="Т (2)&#10;П (2)"
                                                title="Укажите тип занятия и часы: Т (2) — теория, П (2) — практика. Можно писать несколько строк."
                                                data-autoresize>{{ $scheduleItem?->content }}</textarea>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Сохранить график
                    </button>
                </div>
            </form>
        @endif
    </div>
</section>
@endsection