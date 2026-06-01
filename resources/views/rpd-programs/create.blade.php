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
            <input
                id="complexity_level"
                name="complexity_level"
                type="text"
                value="{{ old('complexity_level', 'базовый') }}"
                required>
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

        <div class="form-field">
            <label for="smko_code">Код СМКО</label>
            <input
                id="smko_code"
                name="smko_code"
                type="text"
                value="{{ old('smko_code') }}"
                placeholder="СМКО МИРЭА 8.5.1/03.Пр _____-1__">
        </div>

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

    </div>
</form>
@endsection