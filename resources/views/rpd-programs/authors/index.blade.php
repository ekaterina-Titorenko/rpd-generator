@extends('layouts.app', ['title' => 'Разработчики'])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Разработчики</h2>
            <p class="card-description">
                {{ $rpdProgram->title }}. Данные можно заполнить вручную или использовать ранее сохранённые.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}#section-content" class="btn btn-secondary">К РПД</a>
        </div>
    </div>

    @php
    $prefillName = old('name');
    $prefillPosition = old('position');
    $prefillOrganization = old('organization');
    @endphp


    <form method="POST" action="{{ route('rpd-programs.authors.store', $rpdProgram) }}" class="card-body author-add-form">
        @csrf

        <div class="form-field">
            <label for="name">ФИО разработчика *</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ $prefillName }}"
                required>
        </div>

        <div class="form-field">
            <label for="position">Должность</label>
            <input
                id="position"
                name="position"
                type="text"
                value="{{ $prefillPosition }}">
        </div>

        <div class="form-field">
            <label for="organization">Организация / подразделение</label>
            <input
                id="organization"
                name="organization"
                type="text"
                value="{{ $prefillOrganization }}">
        </div>

        <div class="author-add-actions">
            <button type="submit" class="btn btn-primary">Добавить разработчика</button>
        </div>
    </form>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Список разработчиков</h2>
            <p class="card-description">
                Если данные уже использовались ранее, они подставляются автоматически. Изменения сохраняются автоматически.
            </p>
        </div>
    </div>

    <div class="card-body">
        @if ($rpdProgram->authors->isEmpty())
        <div class="empty-state">
            <h2>Разработчики пока не добавлены</h2>
            <p>Добавьте хотя бы одного разработчика программы.</p>
        </div>
        @else
        <div class="author-list">
            @foreach ($rpdProgram->authors as $author)
            <div class="author-card">
                <form
                    id="author-form-{{ $author->id }}"
                    method="POST"
                    action="{{ route('rpd-programs.authors.update', [$rpdProgram, $author]) }}">
                    @csrf
                    @method('PUT')
                </form>

                <div class="author-card-grid">
                    <div class="form-field">
                        <label for="author-full-name-{{ $author->id }}">ФИО</label>
                        <input
                            id="author-full-name-{{ $author->id }}"
                            name="name"
                            type="text"
                            form="author-form-{{ $author->id }}"
                            value="{{ old('name', $author->name) }}"
                            required
                            data-autosubmit>
                    </div>

                    <div class="form-field">
                        <label for="author-position-{{ $author->id }}">Должность</label>
                        <input
                            id="author-position-{{ $author->id }}"
                            name="position"
                            type="text"
                            form="author-form-{{ $author->id }}"
                            value="{{ old('position', $author->position) }}"
                            data-autosubmit>
                    </div>

                    <div class="form-field">
                        <label for="author-organization-{{ $author->id }}">Организация / подразделение</label>
                        <input
                            id="author-organization-{{ $author->id }}"
                            name="organization"
                            type="text"
                            form="author-form-{{ $author->id }}"
                            value="{{ old('organization', $author->organization) }}"
                            data-autosubmit>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('rpd-programs.authors.destroy', [$rpdProgram, $author]) }}"
                        onsubmit="return confirm('Удалить разработчика?')">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn-icon-danger" title="Удалить" aria-label="Удалить">
                            ×
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
@endsection