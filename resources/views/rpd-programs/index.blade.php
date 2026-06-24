@extends('layouts.app', ['title' => 'Рабочие программы'])

@section('content')
@php
$sortLink = function (string $key) use ($sort, $direction) {
$nextDirection = $sort === $key && $direction === 'asc' ? 'desc' : 'asc';

return request()->fullUrlWithQuery([
'sort' => $key,
'direction' => $nextDirection,
]);
};

$sortMark = function (string $key) use ($sort, $direction) {
if ($sort !== $key) {
return '';
}

return $direction === 'asc' ? ' ↑' : ' ↓';
};
@endphp

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Рабочие программы</h2>
            <p class="card-description">
                Список РПД/ДООП, созданных в конструкторе.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.create') }}" class="btn btn-primary">
                Создать РПД
            </a>
        </div>
    </div>

    <div class="card-body">
        <form
            method="GET"
            action="{{ route('rpd-programs.index') }}"
            class="filter-form"
            data-live-search-form>
            <div class="form-field">
                <label for="search">Поиск</label>
                <input
                    id="search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Название программы или преподаватель"
                    autocomplete="off"
                    data-live-search-input>
            </div>
            <div class="form-field">
                <label for="status">Статус</label>
                <select
                    id="status"
                    name="status"
                    data-status-filter>
                    <option value="">Все статусы</option>
                    <option value="draft" @selected(request('status')==='draft' )>Черновик</option>
                    <option value="submitted" @selected(request('status')==='submitted' )>На проверке</option>
                    <option value="revision" @selected(request('status')==='revision' )>На доработке</option>
                    <option value="approved" @selected(request('status')==='approved' )>Утверждена</option>
                    <option value="rejected" @selected(request('status')==='rejected' )>Отклонена</option>
                    <option value="generated" @selected(request('status')==='generated' )>Документ сформирован</option>
                </select>
            </div>

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">
                    Найти
                </button>

                <a href="{{ route('rpd-programs.index') }}" class="btn btn-secondary">
                    Сбросить
                </a>
            </div>
        </form>

        <div class="table-scroll">
            <table class="table rpd-programs-table">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ $sortLink('title') }}">
                                Название{{ $sortMark('title') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('teacher') }}">
                                Создал{{ $sortMark('teacher') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('direction') }}">
                                Направленность{{ $sortMark('direction') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('year') }}">
                                Год{{ $sortMark('year') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('status') }}">
                                Статус{{ $sortMark('status') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('created_at') }}">
                                Создана{{ $sortMark('created_at') }}
                            </a>
                        </th>
                        <th>Действия</th>
                    </tr>
                </thead>

                <tbody data-rpd-programs-tbody>
                    @include('rpd-programs._rows')
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap" data-rpd-programs-pagination>
            {{ $rpdPrograms->links() }}
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const statusFilter = document.querySelector('[data-status-filter]');

        if (!statusFilter) {
            return;
        }

        statusFilter.addEventListener('change', () => {
            const form = statusFilter.closest('form');
            const params = new URLSearchParams(new FormData(form));

            params.delete('page');

            window.location.href = `${form.action}?${params.toString()}`;
        });
    });
</script>
@endsection