@extends('layouts.app', ['title' => 'Редактирование РПД'])

@section('content')

@php
preg_match_all('/\d+/', (string) old('lessons_per_week', $rpdProgram->lessons_per_week), $lessonMatches);

$lessonNumbers = collect($lessonMatches[0] ?? [])
->map(fn($value) => (int) $value)
->filter(fn($value) => $value > 0)
->values();

$defaultMinLessons = $lessonNumbers->first() ?: 1;
$defaultMaxLessons = $lessonNumbers->count() > 1 ? $lessonNumbers->max() : $defaultMinLessons;
@endphp

<form method="POST" action="{{ route('rpd-programs.update', $rpdProgram) }}" class="card">
    @csrf
    @method('PUT')

    <div class="card-header">
        <div>
            <h2 class="card-title">Редактирование РПД</h2>
            <p class="card-description">
                Изменение общих сведений программы. Стандартные блоки документа заполняются автоматически.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}" class="btn btn-secondary">Назад</a>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </div>

    <div class="card-body form-grid">
        <div class="form-field form-field-wide">
            <label for="title">Название программы *</label>
            <input
                id="title"
                name="title"
                type="text"
                value="{{ old('title', $rpdProgram->title) }}"
                required>
        </div>

        <div class="form-field">
            <label for="direction">Направленность *</label>
            <select id="direction" name="direction" required>
                <option value="">Выберите направленность</option>
                <option value="technical" @selected(old('direction', $rpdProgram->direction) === 'technical')>Техническая</option>
                <option value="science" @selected(old('direction', $rpdProgram->direction) === 'science')>Естественно-научная</option>
                <option value="social_humanitarian" @selected(old('direction', $rpdProgram->direction) === 'social_humanitarian')>Социально-гуманитарная</option>
            </select>
        </div>

        <div class="form-field">
            <label for="complexity_level">Уровень сложности *</label>
            <input
                id="complexity_level"
                name="complexity_level"
                type="text"
                value="{{ old('complexity_level', $rpdProgram->complexity_level) }}"
                required>
        </div>

        <div class="form-field">
            <label for="year">Год *</label>
            <input
                id="year"
                name="year"
                type="number"
                value="{{ old('year', $rpdProgram->year) }}"
                min="2020"
                max="2100"
                required>
        </div>

        <div class="form-field">
            <label for="smko_code">Код СМКО</label>
            <input
                id="smko_code"
                name="smko_code"
                type="text"
                value="{{ old('smko_code', $rpdProgram->smko_code) }}">
        </div>

        <div class="form-field">
            <label for="total_hours">Объем программы, ак. часов *</label>
            <input
                id="total_hours"
                name="total_hours"
                type="number"
                value="{{ old('total_hours', $rpdProgram->total_hours) }}"
                min="1"
                max="1000"
                required>
        </div>

        <div class="form-field">
            <label for="study_period">Срок освоения *</label>
            <input
                id="study_period"
                name="study_period"
                type="text"
                value="{{ old('study_period', $rpdProgram->study_period) }}"
                required>
        </div>

        <div class="form-field">
            <label for="students_age">Возраст слушателей *</label>
            <input
                id="students_age"
                name="students_age"
                type="text"
                value="{{ old('students_age', $rpdProgram->students_age) }}"
                required>
        </div>

        <div class="form-field">
            <label for="education_format">Формат обучения *</label>
            <select id="education_format" name="education_format" required>
                <option value="mixed" @selected(old('education_format', $rpdProgram->education_format) === 'mixed')>Очный и дистанционный</option>
                <option value="offline" @selected(old('education_format', $rpdProgram->education_format) === 'offline')>Очный</option>
                <option value="online" @selected(old('education_format', $rpdProgram->education_format) === 'online')>Дистанционный</option>
            </select>
        </div>

        <div class="form-field">
            <label for="min_lessons_per_week">Минимум занятий в неделю *</label>
            <input
                id="min_lessons_per_week"
                name="min_lessons_per_week"
                type="number"
                value="{{ old('min_lessons_per_week', $defaultMinLessons) }}"
                min="1"
                max="14"
                required>
            <small class="form-hint">Если занятия проходят 1–2 раза в неделю, укажите здесь 1.</small>
        </div>

        <div class="form-field">
            <label for="max_lessons_per_week">Максимум занятий в неделю *</label>
            <input
                id="max_lessons_per_week"
                name="max_lessons_per_week"
                type="number"
                value="{{ old('max_lessons_per_week', $defaultMaxLessons) }}"
                min="1"
                max="14"
                required>
            <small class="form-hint">Для расчёта календарного графика используется максимальное значение.</small>
        </div>

        <div class="form-field">
            <label for="academic_hours_per_lesson">Академических часов за одно занятие *</label>
            <input
                id="academic_hours_per_lesson"
                name="academic_hours_per_lesson"
                type="number"
                value="{{ old('academic_hours_per_lesson', $rpdProgram->academic_hours_per_lesson) }}"
                min="1"
                max="12"
                required>
        </div>

        <div class="form-field">
            <label for="academic_hour_minutes">Длительность академического часа, минут *</label>
            <input
                id="academic_hour_minutes"
                name="academic_hour_minutes"
                type="number"
                value="{{ old('academic_hour_minutes', $rpdProgram->academic_hour_minutes) }}"
                min="30"
                max="60"
                required>
        </div>
    </div>
</form>
@endsection