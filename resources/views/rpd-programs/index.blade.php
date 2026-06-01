@extends('layouts.app', ['title' => 'РПД'])

@section('content')
    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Рабочие программы</h2>
                <p class="card-description">
                    Список РПД/ДООП, созданных в конструкторе. Пока здесь только базовая навигация.
                </p>
            </div>

            <div class="actions">
                <a href="{{ route('rpd-programs.create') }}" class="btn btn-primary">
                    Создать РПД
                </a>
            </div>
        </div>

        <div class="card-body">
            @if ($programs->isEmpty())
                <div class="empty-state">
                    <h2>РПД пока нет</h2>
                    <p>Создайте первую программу, чтобы заполнить общие сведения, учебный план и график.</p>
                </div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>Направленность</th>
                            <th>Часы</th>
                            <th>Статус</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($programs as $program)
                            <tr>
                                <td>
                                    <strong>{{ $program->title }}</strong>
                                    <br>
                                    <span class="muted">{{ $program->year }}</span>
                                </td>
                                <td>{{ $program->direction_label }}</td>
                                <td>{{ $program->total_hours }}</td>
                                <td>
                                    <span class="badge">{{ $program->status_label }}</span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('rpd-programs.show', $program) }}" class="btn btn-secondary">
                                            Открыть
                                        </a>
                                        <a href="{{ route('rpd-programs.edit', $program) }}" class="btn btn-secondary">
                                            Редактировать
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection