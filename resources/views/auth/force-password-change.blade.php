@extends('layouts.app', ['title' => 'Смена пароля'])

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <div class="app-kicker">Детский технопарк «Альтаир»</div>
                <h1>Смена временного пароля</h1>
                <p>
                    Для продолжения работы задайте новый пароль.
                    Временный пароль больше не должен использоваться.
                </p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <strong>Проверьте форму.</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.force.update') }}" class="auth-form">
                @csrf
                @method('PATCH')

                <div class="form-field">
                    <label for="current_password">Текущий временный пароль</label>
                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="form-field">
                    <label for="password">Новый пароль</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                    >
                    <div class="field-hint">
                        Минимум 8 символов, хотя бы одна буква и одна цифра.
                    </div>
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Повторите новый пароль</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">
                        Сменить пароль
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection