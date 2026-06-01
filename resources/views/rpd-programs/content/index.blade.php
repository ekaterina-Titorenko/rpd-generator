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
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}" class="btn btn-secondary">К РПД</a>

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
                        <p>Описание раздела для документа РПД. Сохраняется автоматически.</p>
                    </div>

                    <span class="badge">Автосохранение</span>
                </div>

                <div class="form-field">
                    <label for="content-{{ $contentSection->id }}">Содержание раздела</label>
                    <textarea
                        id="content-{{ $contentSection->id }}"
                        name="content"
                        rows="6"
                        form="content-section-form-{{ $contentSection->id }}"
                        placeholder="Кратко опишите темы, понятия, практические работы и результаты по разделу."
                        data-autosubmit
                        data-autoresize>{{ old('content', $contentSection->content) }}</textarea>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
@endsection