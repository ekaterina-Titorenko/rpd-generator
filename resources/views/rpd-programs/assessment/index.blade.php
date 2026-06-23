@extends('layouts.app', ['title' => 'Оценочные материалы'])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Оценочные материалы</h2>
            <p class="card-description">
                {{ $rpdProgram->title }}. Заполните три обязательных блока оценочных материалов.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.show', $rpdProgram) }}#section-assessment" class="btn btn-secondary">К РПД</a>
        </div>
    </div>

    <div class="card-body">
        <div class="assessment-blocks">
            <form
                id="assessment-form"
                method="POST"
                action="{{ route('rpd-programs.assessment.update', $rpdProgram) }}">
                @csrf
                @method('PUT')
            </form>

            <div class="assessment-block">
                <div class="assessment-block-header">
                    <div>
                        <h3>Материалы для проведения контрольных опросов</h3>
                        <p>Вопросы, перечни тем, задания для текущего контроля.</p>
                    </div>
                    <span class="badge autosave-status" data-autosave-status>Автосохранение</span>
                </div>

                <div class="form-field">
                    <label for="control_survey_materials">Содержание блока</label>
                    <textarea
                        id="control_survey_materials"
                        name="control_survey_materials"
                        rows="8"
                        form="assessment-form"
                        data-autosubmit
                        data-autoresize
                        data-auto-numbered-list
                        data-min-lines="15"
                        data-line-counter-target="control-survey-counter"
                        placeholder="Каждая непустая строка — один вопрос или тема. Минимум 15 строк.">{{ old('control_survey_materials', $rpdProgram->control_survey_materials) }}</textarea>

                    <div class="line-counter" id="control-survey-counter">
                        0 / 15 пунктов
                    </div>

                    @error('control_survey_materials')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="assessment-block">
                <div class="assessment-block-header">
                    <div>
                        <h3>Материалы для проведения итоговой практической работы</h3>
                        <p>Описание итоговой работы, требования к выполнению и защите.</p>
                    </div>
                    <span class="badge autosave-status" data-autosave-status>Автосохранение</span>
                </div>

                <div class="form-field">
                    <label for="final_practical_work_materials">Содержание блока</label>
                    <textarea
                        id="final_practical_work_materials"
                        name="final_practical_work_materials"
                        rows="8"
                        form="assessment-form"
                        data-autosubmit
                        data-autoresize
                        data-auto-numbered-list
                        data-min-lines="15"
                        data-line-counter-target="final-practical-work-counter"
                        placeholder="Каждая непустая строка — один пункт итоговой практической работы. Минимум 15 строк.">{{ old('final_practical_work_materials', $rpdProgram->final_practical_work_materials) }}</textarea>

                    <div class="line-counter" id="final-practical-work-counter">
                        0 / 15 пунктов
                    </div>

                    @error('final_practical_work_materials')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="assessment-block">
                <div class="assessment-block-header">
                    <div>
                        <h3>Типовые темы проектных работ</h3>
                        <p>Примерные темы проектов, которые могут выполнять слушатели.</p>
                    </div>
                    <span class="badge autosave-status" data-autosave-status>Автосохранение</span>
                </div>

                <div class="form-field">
                    <label for="project_topics">Содержание блока</label>
                    <textarea
                        id="project_topics"
                        name="project_topics"
                        rows="8"
                        form="assessment-form"
                        data-autosubmit
                        data-autoresize
                        data-auto-numbered-list
                        data-min-lines="15"
                        data-line-counter-target="project-topics-counter"
                        placeholder="Каждая непустая строка — одна тема проекта. Минимум 15 строк.">{{ old('project_topics', $rpdProgram->project_topics) }}</textarea>

                    <div class="line-counter" id="project-topics-counter">
                        0 / 15 пунктов
                    </div>

                    @error('project_topics')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const stripNumber = (line) => {
            return line.replace(/^\s*\d+[\).\s-]*/u, '').trim();
        };

        const countLines = (value) => {
            return value
                .split(/\r\n|\r|\n/)
                .map((line) => stripNumber(line))
                .filter(Boolean)
                .length;
        };

        const getNextNumber = (value, cursorPosition) => {
            const beforeCursor = value.slice(0, cursorPosition);
            const lines = beforeCursor.split(/\r\n|\r|\n/);

            return lines
                .map((line) => stripNumber(line))
                .filter(Boolean)
                .length + 1;
        };

        const syncCounter = (textarea) => {
            const min = Number(textarea.dataset.minLines || 15);
            const counter = document.getElementById(textarea.dataset.lineCounterTarget);

            if (!counter) {
                return;
            }

            const count = countLines(textarea.value);
            counter.textContent = `${count} / ${min} пунктов`;

            counter.classList.toggle('is-ok', count >= min);
            counter.classList.toggle('is-warning', count > 0 && count < min);
            counter.classList.toggle('is-empty', count === 0);
        };

        document.querySelectorAll('[data-min-lines]').forEach((textarea) => {
            textarea.addEventListener('input', () => syncCounter(textarea));
            syncCounter(textarea);
        });

        document.querySelectorAll('[data-auto-numbered-list]').forEach((textarea) => {
            const ensureFirstNumber = () => {
                if (textarea.value.trim() !== '') {
                    return;
                }

                textarea.value = '1. ';
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                textarea.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            };

            textarea.addEventListener('focus', ensureFirstNumber);

            textarea.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;
                const nextNumber = getNextNumber(value, start);
                const insert = `\n${nextNumber}. `;

                textarea.value = value.slice(0, start) + insert + value.slice(end);

                const cursor = start + insert.length;
                textarea.setSelectionRange(cursor, cursor);
                textarea.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            });

            textarea.addEventListener('paste', () => {
                window.setTimeout(() => {
                    const lines = textarea.value
                        .split(/\r\n|\r|\n/)
                        .map((line) => stripNumber(line))
                        .filter(Boolean);

                    if (lines.length === 0) {
                        syncCounter(textarea);
                        return;
                    }

                    textarea.value = lines
                        .map((line, index) => `${index + 1}. ${line}`)
                        .join('\n');

                    textarea.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }, 0);
            });
        });
    });
</script>
@endsection