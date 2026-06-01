@extends('layouts.app', ['title' => 'Учебный план'])

@section('content')
    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Учебный план</h2>
                <p class="card-description">
                    {{ $rpdProgram->title }}. Добавьте разделы, темы, часы теории и практики.
                </p>
            </div>

            <div class="actions">
                <a href="{{ route('rpd-programs.show', $rpdProgram) }}" class="btn btn-secondary">К РПД</a>
            </div>
        </div>

        <div class="card-body">
            @php
                $totalHours = $rpdProgram->curriculumItems->sum('total_hours');
                $theoryHours = $rpdProgram->curriculumItems->sum('theory_hours');
                $practiceHours = $rpdProgram->curriculumItems->sum('practice_hours');
                $hasHoursMismatch = $totalHours !== (int) $rpdProgram->total_hours;
            @endphp

            <div class="summary-grid">
                <div class="summary-item">
                    <span>По программе</span>
                    <strong>{{ $rpdProgram->total_hours }} ч.</strong>
                </div>
                <div class="summary-item {{ $hasHoursMismatch ? 'summary-item-warning' : '' }}">
                    <span>В учебном плане</span>
                    <strong>{{ $totalHours }} ч.</strong>
                </div>
                <div class="summary-item">
                    <span>Теория</span>
                    <strong>{{ $theoryHours }} ч.</strong>
                </div>
                <div class="summary-item">
                    <span>Практика</span>
                    <strong>{{ $practiceHours }} ч.</strong>
                </div>
            </div>

            @if ($hasHoursMismatch)
                <div class="alert alert-warning">
                    Сумма часов в учебном плане не совпадает с объемом программы.
                    В программе указано {{ $rpdProgram->total_hours }} ч., в учебном плане — {{ $totalHours }} ч.
                </div>
            @endif
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Добавить строку</h2>
                <p class="card-description">
                    Для крупных разделов можно указывать только номер и название, а часы распределять по темам.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('rpd-programs.curriculum.store', $rpdProgram) }}" class="card-body compact-form">
            @csrf

            <div class="form-field">
                <label for="number">№</label>
                <input id="number" name="number" type="text" value="{{ old('number') }}" placeholder="1.1">
            </div>

            <div class="form-field compact-form-title">
                <label for="title">Наименование раздела или темы *</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" required>
            </div>

            <div class="form-field">
                <label for="total_hours">Всего *</label>
                <input id="total_hours" name="total_hours" type="number" value="{{ old('total_hours', 0) }}" min="0" max="1000" required>
            </div>

            <div class="form-field">
                <label for="theory_hours">Теория *</label>
                <input id="theory_hours" name="theory_hours" type="number" value="{{ old('theory_hours', 0) }}" min="0" max="1000" required>
            </div>

            <div class="form-field">
                <label for="practice_hours">Практика *</label>
                <input id="practice_hours" name="practice_hours" type="number" value="{{ old('practice_hours', 0) }}" min="0" max="1000" required>
            </div>

            <div class="form-field">
                <label for="control_form">Контроль</label>
                <input id="control_form" name="control_form" type="text" value="{{ old('control_form') }}" placeholder="Опрос">
            </div>

            <label class="checkbox-field">
                <input type="checkbox" name="is_final_work" value="1" @checked(old('is_final_work'))>
                Итоговая работа
            </label>

            <div class="compact-form-actions">
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Строки учебного плана</h2>
                <p class="card-description">
                    Каждую строку можно редактировать отдельно.
                </p>
            </div>
        </div>

        <div class="card-body">
            @if ($rpdProgram->curriculumItems->isEmpty())
                <div class="empty-state">
                    <h2>Учебный план пока пуст</h2>
                    <p>Добавьте первый раздел или тему программы.</p>
                </div>
            @else
                <div class="editable-table">
                    @foreach ($rpdProgram->curriculumItems as $item)
                        @php
                            $rowMismatch = (int) $item->total_hours !== ((int) $item->theory_hours + (int) $item->practice_hours);
                        @endphp

                        <form method="POST" action="{{ route('rpd-programs.curriculum.update', [$rpdProgram, $item]) }}" class="editable-row {{ $rowMismatch ? 'editable-row-warning' : '' }}">
                            @csrf
                            @method('PUT')

                            <div class="form-field">
                                <label>№</label>
                                <input name="number" type="text" value="{{ old('number', $item->number) }}">
                            </div>

                            <div class="form-field editable-row-title">
                                <label>Наименование</label>
                                <input name="title" type="text" value="{{ old('title', $item->title) }}" required>
                            </div>

                            <div class="form-field">
                                <label>Всего</label>
                                <input name="total_hours" type="number" value="{{ old('total_hours', $item->total_hours) }}" min="0" max="1000" required>
                            </div>

                            <div class="form-field">
                                <label>Теория</label>
                                <input name="theory_hours" type="number" value="{{ old('theory_hours', $item->theory_hours) }}" min="0" max="1000" required>
                            </div>

                            <div class="form-field">
                                <label>Практика</label>
                                <input name="practice_hours" type="number" value="{{ old('practice_hours', $item->practice_hours) }}" min="0" max="1000" required>
                            </div>

                            <div class="form-field">
                                <label>Контроль</label>
                                <input name="control_form" type="text" value="{{ old('control_form', $item->control_form) }}">
                            </div>

                            <label class="checkbox-field">
                                <input type="checkbox" name="is_final_work" value="1" @checked(old('is_final_work', $item->is_final_work))>
                                Итоговая
                            </label>

                            <div class="editable-row-actions">
                                <button type="submit" class="btn btn-secondary">Сохранить</button>
                            </div>
                        </form>

                        <form
                            method="POST"
                            action="{{ route('rpd-programs.curriculum.destroy', [$rpdProgram, $item]) }}"
                            class="delete-row-form"
                            onsubmit="return confirm('Удалить строку учебного плана?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger">Удалить строку</button>
                        </form>

                        @if ($rowMismatch)
                            <div class="row-note row-note-warning">
                                В строке «{{ $item->title }}» часы не сходятся: всего {{ $item->total_hours }}, теория + практика = {{ $item->theory_hours + $item->practice_hours }}.
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection