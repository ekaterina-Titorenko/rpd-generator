@extends('layouts.app', ['title' => 'Создание РПД'])

@section('content')
<form method="POST" action="{{ route('rpd-programs.store') }}" class="card">
    @csrf

    <div class="card-header">
        <div>
            <h2 class="card-title">Создание РПД</h2>
            <p class="card-description">
                Заполните общие сведения программы. Остальные разделы добавим следующими шагами.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.index') }}" class="btn btn-secondary">Назад</a>
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
                value="{{ old('title') }}"
                placeholder="Например: HTML5 и CSS3 для создания сайтов"
                required>
        </div>

        <div class="form-field">
            <label for="direction">Направленность *</label>
            <select id="direction" name="direction" required>
                <option value="">Выберите направленность</option>
                <option value="technical" @selected(old('direction')==='technical' )>Техническая</option>
                <option value="science" @selected(old('direction')==='science' )>Естественно-научная</option>
                <option value="social_humanitarian" @selected(old('direction')==='social_humanitarian' )>Социально-гуманитарная</option>
            </select>
        </div>

        <div class="form-field">
            <label for="complexity_level">Уровень сложности *</label>
            <select id="complexity_level" name="complexity_level" required>
                <option value="базовый" @selected(old('complexity_level', 'базовый' )==='базовый' )>Базовый</option>
                <option value="продвинутый" @selected(old('complexity_level')==='продвинутый' )>Продвинутый</option>
            </select>
        </div>

        <div class="form-field">
            <label for="year">Год *</label>
            <input
                id="year"
                name="year"
                type="number"
                value="{{ old('year', 2026) }}"
                min="2020"
                max="2100"
                required>
        </div>

        @if (auth()->user()->role === 'admin')
        <div class="form-field">
            <label for="smko_code">Код СМКО</label>
            <input
                id="smko_code"
                name="smko_code"
                type="text"
                value="{{ old('smko_code') }}"
                placeholder="СМКО МИРЭА 8.5.1/03.Пр _____-1__">
        </div>
        @endif

        <div class="form-field">
            <label for="total_hours">Объем программы, ак. часов *</label>
            <input
                id="total_hours"
                name="total_hours"
                type="number"
                value="{{ old('total_hours', 36) }}"
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
                value="{{ old('study_period', '1 год') }}"
                required>
        </div>

        <div class="form-field">
            <label for="students_age">Возраст слушателей *</label>
            <input
                id="students_age"
                name="students_age"
                type="text"
                value="{{ old('students_age', '14–18 лет') }}"
                required>
        </div>
        <div class="program-format-row form-field-wide">
            <div class="program-format-stack">
                <div class="form-field">
                    <label for="education_format">Формат обучения *</label>
                    <select id="education_format" name="education_format" required>
                        <option value="mixed" @selected(old('education_format', 'mixed' )==='mixed' )>Очный и дистанционный</option>
                        <option value="offline" @selected(old('education_format')==='offline' )>Очный</option>
                        <option value="online" @selected(old('education_format')==='online' )>Дистанционный</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="academic_hours_per_lesson">Академических часов за одно занятие *</label>
                    <input
                        id="academic_hours_per_lesson"
                        name="academic_hours_per_lesson"
                        type="number"
                        value="{{ old('academic_hours_per_lesson', 2) }}"
                        min="1"
                        max="12"
                        required>
                </div>
            </div>

            <div class="lesson-frequency-group">
                <div class="lesson-frequency-header">
                    <strong>Количество занятий в неделю *</strong>
                    <span>Для календарного графика используется максимум.</span>
                </div>

                <div class="lesson-frequency-fields">
                    <div class="form-field">
                        <label for="min_lessons_per_week">Минимум</label>
                        <input
                            id="min_lessons_per_week"
                            name="min_lessons_per_week"
                            type="number"
                            value="{{ old('min_lessons_per_week', 1) }}"
                            min="1"
                            max="14"
                            required>
                    </div>

                    <div class="form-field">
                        <label for="max_lessons_per_week">Максимум</label>
                        <input
                            id="max_lessons_per_week"
                            name="max_lessons_per_week"
                            type="number"
                            value="{{ old('max_lessons_per_week', 2) }}"
                            min="1"
                            max="14"
                            required>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection