@extends('layouts.app', ['title' => 'Литература и интернет-ресурсы'])

@section('content')
    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Литература и интернет-ресурсы</h2>
                <p class="card-description">
                    {{ $rpdProgram->title }}. Добавьте источники, которые будут включены в РПД.
                </p>
            </div>

            <div class="actions">
                <a href="{{ route('rpd-programs.show', $rpdProgram) }}" class="btn btn-secondary">К РПД</a>
            </div>
        </div>

        <form method="POST" action="{{ route('rpd-programs.resources.store', $rpdProgram) }}" class="card-body resource-add-form">
            @csrf

            <div class="form-field">
                <label for="type">Раздел *</label>
                <select id="type" name="type" required>
                    <option value="main_recommended" @selected(old('type') === 'main_recommended')>
                        Список основной рекомендуемой литературы
                    </option>
                    <option value="additional" @selected(old('type') === 'additional')>
                        Дополнительная литература
                    </option>
                    <option value="internet" @selected(old('type') === 'internet')>
                        Ресурсы информационно-телекоммуникационной сети Интернет
                    </option>
                </select>
            </div>

            <div class="form-field">
                <label for="title">Описание источника *</label>
                <textarea
                    id="title"
                    name="title"
                    rows="2"
                    required
                    placeholder="Например: Иванов И.И. Основы программирования. — М.: ..."
                >{{ old('title') }}</textarea>
            </div>

            <div class="form-field">
                <label for="url">Ссылка</label>
                <input
                    id="url"
                    name="url"
                    type="text"
                    value="{{ old('url') }}"
                    placeholder="https://..."
                >
            </div>

            <div class="resource-add-actions">
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Список источников</h2>
                <p class="card-description">
                    Источники сохраняются автоматически при редактировании.
                </p>
            </div>
        </div>

        <div class="card-body">
            @if ($rpdProgram->resources->isEmpty())
                <div class="empty-state">
                    <h2>Источники пока не добавлены</h2>
                    <p>Добавьте основную литературу, дополнительную литературу или интернет-ресурсы.</p>
                </div>
            @else
                <div class="resource-list">
                    @foreach ($rpdProgram->resources as $resource)
                        <div class="resource-card">
                            <form
                                id="resource-form-{{ $resource->id }}"
                                method="POST"
                                action="{{ route('rpd-programs.resources.update', [$rpdProgram, $resource]) }}"
                            >
                                @csrf
                                @method('PUT')
                            </form>

                            <div class="resource-card-grid">
                                <div class="form-field">
                                    <label for="resource-type-{{ $resource->id }}">Раздел</label>
                                    <select
                                        id="resource-type-{{ $resource->id }}"
                                        name="type"
                                        form="resource-form-{{ $resource->id }}"
                                        required
                                        data-autosubmit
                                    >
                                        <option value="main_recommended" @selected($resource->type === 'main_recommended')>
                                            Список основной рекомендуемой литературы
                                        </option>
                                        <option value="additional" @selected($resource->type === 'additional')>
                                            Дополнительная литература
                                        </option>
                                        <option value="internet" @selected($resource->type === 'internet')>
                                            Ресурсы информационно-телекоммуникационной сети Интернет
                                        </option>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <label for="resource-title-{{ $resource->id }}">Описание источника</label>
                                    <textarea
                                        id="resource-title-{{ $resource->id }}"
                                        name="title"
                                        rows="2"
                                        form="resource-form-{{ $resource->id }}"
                                        required
                                        data-autosubmit
                                        data-autoresize
                                    >{{ old('title', $resource->title) }}</textarea>
                                </div>

                                <div class="form-field">
                                    <label for="resource-url-{{ $resource->id }}">Ссылка</label>
                                    <input
                                        id="resource-url-{{ $resource->id }}"
                                        name="url"
                                        type="text"
                                        form="resource-form-{{ $resource->id }}"
                                        value="{{ old('url', $resource->url) }}"
                                        data-autosubmit
                                    >
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('rpd-programs.resources.destroy', [$rpdProgram, $resource]) }}"
                                    onsubmit="return confirm('Удалить источник?')"
                                >
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