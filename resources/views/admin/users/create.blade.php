@extends('layouts.app', ['title' => 'Создать пользователя'])

@section('content')
    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Создать пользователя</h2>
                <p class="card-description">
                    Администратор может создать аккаунт преподавателя или другого администратора.
                </p>
            </div>

            <div class="actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    К пользователям
                </a>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}" class="form-grid">
                @csrf

                <div class="form-field">
                    <label for="name">ФИО / имя пользователя *</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="email">Email *</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="role">Роль *</label>
                    <select id="role" name="role" required>
                        <option value="teacher" @selected(old('role') === 'teacher')>
                            Преподаватель
                        </option>
                        <option value="admin" @selected(old('role') === 'admin')>
                            Администратор
                        </option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="password">Пароль *</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Повтор пароля *</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                    >
                </div>

                <div class="form-actions form-field-wide">
                    <button type="submit" class="btn btn-primary">
                        Создать пользователя
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection