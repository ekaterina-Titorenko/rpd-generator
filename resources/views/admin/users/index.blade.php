@extends('layouts.app', ['title' => 'Пользователи'])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Пользователи</h2>
            <p class="card-description">
                Управление аккаунтами преподавателей и администраторов.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                Создать пользователя
            </a>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-form">
            <div class="form-field">
                <label for="search">Поиск</label>
                <input
                    id="search"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Имя или email">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">Найти</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Сбросить</a>
            </div>
        </form>

        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Пароль</th>
                        <th>Создан</th>
                        <th>Действия</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge">
                                {{ $user->role === 'admin' ? 'Администратор' : 'Преподаватель' }}
                            </span>
                        </td>
                        <td>
                            @if ($user->must_change_password)
                            <span class="badge badge-warning">Требуется смена</span>
                            @else
                            <span class="badge">Обычный</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at?->format('d.m.Y H:i') }}</td>
                        <td>
                            <div class="table-actions">
                                <form
                                    method="POST"
                                    action="{{ route('admin.users.reset-password', $user) }}"
                                    onsubmit="return confirm('Сбросить пароль пользователя на 12345678? После входа пользователь должен будет сменить пароль.');">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn btn-secondary btn-compact">
                                        Сбросить пароль
                                    </button>
                                </form>

                                @if (auth()->id() !== $user->id)
                                <form
                                    method="POST"
                                    action="{{ route('admin.users.destroy', $user) }}"
                                    onsubmit="return confirm('Удалить пользователя? Это действие нельзя отменить.');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-compact">
                                        Удалить
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @empty

                    <tr>
                        <td colspan="4">Пользователи не найдены.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $users->links() }}
        </div>
    </div>
</section>
@endsection