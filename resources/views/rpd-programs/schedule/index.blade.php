@extends('layouts.app', ['title' => 'Календарный учебный график'])

@section('content')
    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Календарный учебный график</h2>
                <p class="card-description">
                    {{ $rpdProgram->title }}. График можно сформировать автоматически и затем скорректировать вручную.
                </p>
            </div>

            <div class="actions">
                <a href="{{ route('rpd-programs.show', $rpdProgram) }}" class="btn btn-secondary">К РПД</a>

                <form method="POST" action="{{ route('rpd-programs.schedule.generate', $rpdProgram) }}">
                    @csrf

                    <button type="submit" class="btn btn-primary">
                        Сформировать график
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-warning">
                Автоформирование перезаписывает текущий график. После формирования проверьте распределение по неделям.
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
                                        <th>{{ $week }} неделя</th>
                                    @endfor
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($rpdProgram->curriculumItems as $item)
                                    @continue(! in_array($item->type, ['section', 'topic'], true))

                                    <tr class="schedule-row-{{ $item->type }}">
                                        <td>
                                            <strong>{{ $item->number }}. {{ $item->title }}</strong>
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
                                                    data-autoresize
                                                >{{ $scheduleItem?->content }}</textarea>
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