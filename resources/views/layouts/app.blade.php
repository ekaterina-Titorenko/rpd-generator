<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Конструктор РПД' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app">
        <header class="app-header">
            <div>
                <div class="app-kicker">Детский технопарк «Альтаир»</div>
                <h1>Конструктор РПД</h1>
            </div>

            <nav class="app-nav">
                <a href="{{ route('rpd-programs.index') }}">РПД</a>

                @auth
                <span class="app-user">
                    {{ auth()->user()->name }}
                    @if (auth()->user()->role === 'admin')
                    · администратор
                    @else
                    · преподаватель
                    @endif
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Выйти</button>
                </form>
                @endauth

                @if (auth()->check() && auth()->user()->role === 'admin')
                <a href="{{ route('admin.users.index') }}">Пользователи</a>
                @endif
            </nav>
        </header>

        <main class="app-main">
            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

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

            @yield('content')
        </main>
    </div>

    <button
        type="button"
        class="scroll-to-top"
        data-scroll-to-top
        aria-label="Наверх"
        title="Наверх">
        ↑
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.querySelector('[data-scroll-to-top]');

            if (!button) {
                return;
            }

            const toggleButton = () => {
                button.classList.toggle('is-visible', window.scrollY > 40);
            };

            button.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth',
                });
            });

            window.addEventListener('scroll', toggleButton, {
                passive: true
            });
            toggleButton();
        });
    </script>
</body>

</html>