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
</body>
</html>