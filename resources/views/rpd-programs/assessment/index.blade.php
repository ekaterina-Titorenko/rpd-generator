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
                    action="{{ route('rpd-programs.assessment.update', $rpdProgram) }}"
                >
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
                        >{{ old('control_survey_materials', $rpdProgram->control_survey_materials) }}</textarea>
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
                        >{{ old('final_practical_work_materials', $rpdProgram->final_practical_work_materials) }}</textarea>
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
                        >{{ old('project_topics', $rpdProgram->project_topics) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection