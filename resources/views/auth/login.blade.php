@extends('layouts.app', ['title' => 'Вход'])

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <div class="app-kicker">Детский технопарк «Альтаир»</div>
                <h1>Конструктор РПД</h1>
                <p>
                    Войдите в систему, чтобы создавать, проверять и выгружать рабочие программы.
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

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <div class="form-field">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-field">
                    <label for="password">Пароль</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <label class="checkbox-field">
                    <input type="checkbox" name="remember">
                    <span>Запомнить меня</span>
                </label>

                <div class="auth-actions">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="muted">
                            Забыли пароль?
                        </a>
                    @endif

                    <button type="submit" class="btn btn-primary">
                        Войти
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection