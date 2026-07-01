@extends('layouts.app', ['title' => 'Содержание учебного плана'])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Содержание учебного плана</h2>
            <p class="card-description">
                {{ $rpdProgram->title }}. Заполните описание содержания по разделам программы.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}#section-content" class="btn btn-secondary">К РПД</a>

            <form method="POST" action="{{ route('rpd-programs.content.sync', $rpdProgram) }}">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    Обновить разделы
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        @if ($rpdProgram->contentSections->isEmpty())
        <div class="empty-state">
            <h2>Содержание пока не создано</h2>
            <p>Сначала синхронизируйте разделы с учебным планом.</p>
        </div>
        @else
        <div class="content-editor-list">
            @foreach ($rpdProgram->contentSections as $contentSection)
            <div class="content-editor-card">
                <form
                    id="content-section-form-{{ $contentSection->id }}"
                    method="POST"
                    action="{{ route('rpd-programs.content.update', [$rpdProgram, $contentSection]) }}">
                    @csrf
                    @method('PUT')
                </form>

                <div class="content-editor-header">
                    <div>
                        <strong>{{ $contentSection->number }}. {{ $contentSection->title }}</strong>
                        <p>Описание раздела для документа РПД. После редактирования нажмите «Сохранить раздел».</p>
                    </div>

                    <span class="badge">Ручное сохранение</span>
                </div>

                <div class="form-field">
                    <label for="content-{{ $contentSection->id }}">Содержание раздела</label>
                    <textarea
                        id="content-{{ $contentSection->id }}"
                        name="content"
                        rows="6"
                        form="content-section-form-{{ $contentSection->id }}"
                        placeholder="Опишите содержание раздела: темы, понятия, практические работы и результаты. Минимум 100 символов."
                        data-autoresize
                        data-min-chars="100"
                        data-char-counter-target="content-counter-{{ $contentSection->id }}">{{ old('content_' . $contentSection->id, $contentSection->content) }}</textarea>

                    <div class="char-counter" id="content-counter-{{ $contentSection->id }}">
                        0 / 100 символов
                    </div>

                    <div class="form-actions">
                        <button
                            type="submit"
                            form="content-section-form-{{ $contentSection->id }}"
                            class="btn btn-primary">
                            Сохранить раздел
                        </button>
                    </div>

                    @error('content')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const normalizeText = (value) => {
            return value.replace(/\s+/g, ' ').trim();
        };

        document.querySelectorAll('[data-min-chars]').forEach((textarea) => {
            const min = Number(textarea.dataset.minChars || 100);
            const counter = document.getElementById(textarea.dataset.charCounterTarget);

            if (!counter) {
                return;
            }

            const syncCounter = () => {
                const count = normalizeText(textarea.value).length;
                counter.textContent = `${count} / ${min} символов`;

                counter.classList.toggle('is-ok', count >= min);
                counter.classList.toggle('is-warning', count > 0 && count < min);
                counter.classList.toggle('is-empty', count === 0);
            };

            textarea.addEventListener('input', syncCounter);
            syncCounter();
        });
    });
</script>
@endsection