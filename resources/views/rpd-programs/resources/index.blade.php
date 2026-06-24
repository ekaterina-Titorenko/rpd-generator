@extends('layouts.app', ['title' => 'Литература и интернет-ресурсы'])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Литература и интернет-ресурсы</h2>
            <p class="card-description">
                {{ $rpdProgram->title }}. Заполните поля источника, система сформирует описание.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}#section-resources" class="btn btn-secondary">К РПД</a>
        </div>
    </div>

    <form method="POST" action="{{ route('rpd-programs.resources.store', $rpdProgram) }}" class="card-body resource-builder-form">
        @csrf

        <div class="form-field">
            <label for="type">Раздел *</label>
            <select id="type" name="type" required data-resource-section-select>
                <option value="main_recommended">Список основной рекомендуемой литературы</option>
                <option value="additional">Дополнительная литература</option>
                <option value="internet">Ресурсы информационно-телекоммуникационной сети Интернет</option>
            </select>
        </div>

        <div class="form-field">
            <label for="source_type">Вид источника *</label>
            <select id="source_type" name="source_type" required data-source-type-select>
                <option value="book">Книга</option>
                <option value="article">Статья</option>
                <option value="electronic">Электронный ресурс</option>
                <option value="legal">Нормативный документ</option>
            </select>
            <small class="form-hint form-hint-inline" data-internet-source-hint hidden>
                Автоматически: «Электронный ресурс».
            </small>
        </div>

        <div class="form-field form-field-wide" data-source-field="book article">
            <label for="authors">Автор(ы)</label>
            <input id="authors" name="authors" type="text" placeholder="Например: Иванов И. И., Петров П. П.">
        </div>

        <div class="form-field form-field-wide">
            <label for="title">Название *</label>
            <textarea id="title" name="title" rows="2" required></textarea>
        </div>

        <div class="form-field" data-source-field="book">
            <label for="publication_place">Место издания</label>
            <input id="publication_place" name="publication_place" type="text" placeholder="М.">
        </div>

        <div class="form-field" data-source-field="book">
            <label for="publisher">Издательство</label>
            <input id="publisher" name="publisher" type="text" placeholder="Просвещение">
        </div>

        <div class="form-field" data-source-field="book article">
            <label for="year">Год</label>
            <input id="year" name="year" type="number" min="1900" max="2100">
        </div>

        <div class="form-field" data-source-field="book">
            <label for="pages">Количество страниц</label>
            <input id="pages" name="pages" type="number" min="1" max="10000">
        </div>

        <div class="form-field form-field-wide" data-source-field="article">
            <label for="journal">Название журнала/сборника</label>
            <input id="journal" name="journal" type="text">
        </div>

        <div class="form-field" data-source-field="article">
            <label for="issue">Номер выпуска</label>
            <input id="issue" name="issue" type="text">
        </div>

        <div class="form-field" data-source-field="article">
            <label for="article_pages">Страницы статьи</label>
            <input id="article_pages" name="article_pages" type="text" placeholder="15–21">
        </div>

        <div class="form-field form-field-wide" data-source-field="electronic">
            <label for="site_name">Название сайта</label>
            <input id="site_name" name="site_name" type="text">
        </div>

        <div class="form-field form-field-wide" data-source-field="electronic">
            <label for="url">URL</label>
            <input id="url" name="url" type="text" placeholder="https://...">
        </div>

        <div class="form-field" data-source-field="electronic">
            <label for="access_date">Дата обращения</label>
            <input id="access_date" name="access_date" type="date">
        </div>

        <div class="form-field" data-source-field="legal">
            <label for="document_date">Дата документа</label>
            <input id="document_date" name="document_date" type="text" placeholder="29.12.2012">
        </div>

        <div class="form-field" data-source-field="legal">
            <label for="document_number">Номер документа</label>
            <input id="document_number" name="document_number" type="text" placeholder="273-ФЗ">
        </div>

        <div class="resource-add-actions">
            <button type="submit" class="btn btn-primary">Добавить источник</button>
        </div>
    </form>
</section>
@if (auth()->user()->role === 'admin')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Массовая загрузка источников</h2>
            <p class="card-description">
                Для администратора. Вставьте готовые описания по ГОСТ: одна строка — один источник.
            </p>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('rpd-programs.resources.bulk-store', $rpdProgram) }}"
        class="card-body resource-bulk-form">
        @csrf

        <div class="form-field">
            <label for="bulk-type">Раздел *</label>
            <select id="bulk-type" name="type" required>
                <option value="main_recommended">
                    Список основной рекомендуемой литературы
                </option>
                <option value="additional">
                    Дополнительная литература
                </option>
                <option value="internet">
                    Ресурсы информационно-телекоммуникационной сети Интернет
                </option>
            </select>
        </div>

        <div class="form-field form-field-wide">
            <label for="bulk-items">Готовые источники по ГОСТ *</label>
            <textarea
                id="bulk-items"
                name="items"
                rows="8"
                required
                placeholder="Каждый источник с новой строки.&#10;Например: Иванов И. И. Основы программирования. — М.: Просвещение, 2022. — 240 с.">{{ old('items') }}</textarea>
        </div>

        <div class="resource-add-actions">
            <button type="submit" class="btn btn-primary">
                Добавить списком
            </button>
        </div>
    </form>
</section>
@endif
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Сформированные источники</h2>
            <p class="card-description">
                Ниже показано итоговое описание источников. Для правки удалите источник и добавьте заново.
            </p>
        </div>
    </div>

    <div class="card-body">
        @if ($rpdProgram->resources->isEmpty())
        <div class="empty-state">
            <h2>Источники пока не добавлены</h2>
            <p>Добавьте источник через форму выше.</p>
        </div>
        @else
        <div class="resource-list">
            @foreach ($rpdProgram->resources as $resource)
            <div class="resource-card">
                <div class="resource-preview">
                    <div>
                        <span class="badge">{{ $resource->type_label }}</span>
                        <span class="badge">{{ $resource->source_type_label }}</span>
                    </div>

                    <p>{{ $resource->title }}</p>
                </div>

                <form
                    method="POST"
                    action="{{ route('rpd-programs.resources.destroy', [$rpdProgram, $resource]) }}"
                    onsubmit="return confirm('Удалить источник?')">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn-icon-danger" title="Удалить" aria-label="Удалить">
                        ×
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sectionSelect = document.querySelector('[data-resource-section-select]');
        const sourceTypeSelect = document.querySelector('[data-source-type-select]');
        const internetHint = document.querySelector('[data-internet-source-hint]');
        const sourceFields = document.querySelectorAll('[data-source-field]');

        if (!sectionSelect || !sourceTypeSelect) {
            return;
        }
        const syncSourceFields = () => {
            const selectedSourceType = sourceTypeSelect.value;

            sourceFields.forEach((field) => {
                const allowedTypes = field.dataset.sourceField.split(' ');

                field.hidden = !allowedTypes.includes(selectedSourceType);
            });
        };
        const syncSourceType = () => {
            const isInternet = sectionSelect.value === 'internet';

            if (isInternet) {
                sourceTypeSelect.value = 'electronic';
                sourceTypeSelect.setAttribute('readonly', 'readonly');
                sourceTypeSelect.classList.add('is-readonly-soft');

                if (internetHint) {
                    internetHint.hidden = false;
                }
                syncSourceFields();
                return;
            }

            sourceTypeSelect.removeAttribute('readonly');
            sourceTypeSelect.classList.remove('is-readonly-soft');

            if (internetHint) {
                internetHint.hidden = true;
            }
            syncSourceFields();
        };

        sectionSelect.addEventListener('change', syncSourceType);
        sourceTypeSelect.addEventListener('change', () => {
            if (sectionSelect.value === 'internet') {
                sourceTypeSelect.value = 'electronic';
            }

            syncSourceFields();
        });

        syncSourceType();
    });
</script>
@endsection