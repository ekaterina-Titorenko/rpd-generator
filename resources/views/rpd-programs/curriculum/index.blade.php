@extends('layouts.app', ['title' => 'Учебный план'])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Учебный план</h2>
            <p class="card-description">
                {{ $rpdProgram->title }}. Разделы и темы нумеруются автоматически.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}" class="btn btn-secondary">К РПД</a>
        </div>
    </div>

    <div class="card-body">
        @php
        $allItems = $rpdProgram->curriculumItems;

        $totalHours = $allItems->where('type', 'section')->sum('total_hours');
        $theoryHours = $allItems->where('type', 'section')->sum('theory_hours');
        $practiceHours = $allItems->where('type', 'section')->sum('practice_hours');

        $hasHoursMismatch = $totalHours !== (int) $rpdProgram->total_hours;
        @endphp

        <div class="summary-grid">
            <div class="summary-item">
                <span>По программе</span>
                <strong>{{ $rpdProgram->total_hours }} ч.</strong>
            </div>
            <div class="summary-item {{ $hasHoursMismatch ? 'summary-item-warning' : '' }}">
                <span>По разделам</span>
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
            Сумма часов по разделам не совпадает с объемом программы.
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
                Выберите тип строки. Для темы нужно указать родительский раздел.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('rpd-programs.curriculum.store', $rpdProgram) }}" class="card-body curriculum-add-form">
        @csrf

        <div class="form-field">
            <label for="type">Тип *</label>
            <select id="type" name="type" required>
                <option value="section" @selected(old('type')==='section' )>Раздел</option>
                <option value="topic" @selected(old('type')==='topic' )>Тема</option>
                <option value="final_work" @selected(old('type')==='final_work' )>Итоговая работа</option>
            </select>
        </div>

        <div class="form-field">
            <label for="parent_id">Раздел для темы</label>
            <select id="parent_id" name="parent_id">
                <option value="">Не требуется</option>
                @foreach ($sections as $section)
                <option value="{{ $section->id }}" @selected((int) old('parent_id')===$section->id)>
                    {{ $section->number }}. {{ $section->title }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-field curriculum-title-field">
            <label for="title">Наименование *</label>
            <textarea
                id="title"
                name="title"
                rows="1"
                required
                data-autoresize>{{ old('title') }}</textarea>
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
            <input
                id="control_form"
                name="control_form"
                type="text"
                value="{{ old('control_form') }}"
                list="control-forms-list"
                placeholder="Начните вводить...">
        </div>

        <datalist id="control-forms-list">
            @foreach ($controlForms as $controlForm)
            <option value="{{ $controlForm->name }}"></option>
            @endforeach
        </datalist>

        <div class="curriculum-add-actions">
            <button type="submit" class="btn btn-primary">Добавить</button>
        </div>
    </form>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Итоговый учебный план</h2>
            <p class="card-description">
                Таблица приближена к структуре документа. Ошибки по часам подсвечиваются.
            </p>
        </div>
    </div>

    <div class="card-body">
        @if ($items->isEmpty())
        <div class="empty-state">
            <h2>Учебный план пока пуст</h2>
            <p>Добавьте первый раздел программы.</p>
        </div>
        @else
        <div class="table-scroll">
            <table class="table curriculum-table">
                <thead>
                    <tr>
                        <th>№ п. п.</th>
                        <th>Наименование уровней, разделов и тем</th>
                        <th>Всего часов</th>
                        <th>Теория</th>
                        <th>Практика</th>
                        <th>Формы аттестации/контроля</th>
                        <th>Действия</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($items as $item)
                    @php
                    $children = $item->children;

                    $childrenTotal = $children->sum('total_hours');
                    $childrenTheory = $children->sum('theory_hours');
                    $childrenPractice = $children->sum('practice_hours');

                    $rowMismatch = (int) $item->total_hours !== ((int) $item->theory_hours + (int) $item->practice_hours);

                    $sectionMismatch = $item->type === 'section'
                    && $children->isNotEmpty()
                    && (
                    (int) $item->total_hours !== (int) $childrenTotal
                    || (int) $item->theory_hours !== (int) $childrenTheory
                    || (int) $item->practice_hours !== (int) $childrenPractice
                    );

                    $rowClass = trim(($rowMismatch || $sectionMismatch) ? 'table-row-warning' : '');
                    @endphp

                    @include('rpd-programs.curriculum.partials.row', [
                    'rpdProgram' => $rpdProgram,
                    'item' => $item,
                    'controlForms' => $controlForms,
                    'rowClass' => $rowClass,
                    'isChild' => false,
                    ])

                    @if ($rowMismatch)
                    <tr class="table-note-row">
                        <td colspan="7">
                            В строке «{{ $item->title }}» часы не сходятся:
                            всего {{ $item->total_hours }},
                            теория + практика = {{ $item->theory_hours + $item->practice_hours }}.
                        </td>
                    </tr>
                    @endif

                    @if ($sectionMismatch)
                    <tr class="table-note-row">
                        <td colspan="7">
                            В разделе «{{ $item->title }}» часы раздела не совпадают с суммой тем:
                            раздел — {{ $item->total_hours }} / {{ $item->theory_hours }} / {{ $item->practice_hours }},
                            темы — {{ $childrenTotal }} / {{ $childrenTheory }} / {{ $childrenPractice }}.
                        </td>
                    </tr>
                    @endif

                    @foreach ($children as $child)
                    @php
                    $childMismatch = (int) $child->total_hours !== ((int) $child->theory_hours + (int) $child->practice_hours);
                    @endphp

                    @include('rpd-programs.curriculum.partials.row', [
                    'rpdProgram' => $rpdProgram,
                    'item' => $child,
                    'controlForms' => $controlForms,
                    'rowClass' => $childMismatch ? 'table-row-warning' : '',
                    'isChild' => true,
                    ])

                    @if ($childMismatch)
                    <tr class="table-note-row">
                        <td colspan="7">
                            В строке «{{ $child->title }}» часы не сходятся:
                            всего {{ $child->total_hours }},
                            теория + практика = {{ $child->theory_hours + $child->practice_hours }}.
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</section>
@endsection