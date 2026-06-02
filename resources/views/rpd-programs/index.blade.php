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

        <div id="rpd-programs-results" data-live-search-results>
            @include('rpd-programs._table')
        </div>

        <div class="pagination-wrap">
            {{ $rpdPrograms->links() }}
        </div>
    </div>

</section>
@endsection