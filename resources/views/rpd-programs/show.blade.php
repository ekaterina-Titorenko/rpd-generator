@extends('layouts.app', ['title' => $rpdProgram->title])

@section('content')
<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">{{ $rpdProgram->title }}</h2>
            <p class="card-description">
                Карточка РПД. Сейчас отображаются общие сведения, следующие разделы добавим отдельными шагами.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('rpd-programs.index') }}" class="btn btn-secondary">К списку</a>
            <a href="{{ route('rpd-programs.edit', $rpdProgram) }}" class="btn btn-primary">Редактировать</a>
            <form
                method="POST"
                action="{{ route('rpd-programs.destroy', $rpdProgram) }}"
                onsubmit="return confirm('Удалить эту РПД? Это действие нельзя отменить.')">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-danger">
                    Удалить
                </button>
            </form>
        </div>
    </div>

    <div class="card-body details-grid">
        <div class="detail-item">
            <div class="detail-label">Направленность</div>
            <div class="detail-value">{{ $rpdProgram->direction_label }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Уровень сложности</div>
            <div class="detail-value">{{ $rpdProgram->complexity_level }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Год</div>
            <div class="detail-value">{{ $rpdProgram->year }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Код СМКО</div>
            <div class="detail-value">{{ $rpdProgram->smko_code ?: 'Не указан' }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Объем программы</div>
            <div class="detail-value">{{ $rpdProgram->total_hours }} ак. ч.</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Срок освоения</div>
            <div class="detail-value">{{ $rpdProgram->study_period }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Возраст слушателей</div>
            <div class="detail-value">{{ $rpdProgram->students_age }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Статус</div>
            <div class="detail-value">
                <span class="badge">{{ $rpdProgram->status_label }}</span>
            </div>
        </div>
    </div>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Разделы конструктора</h2>
            <p class="card-description">
                Заполняйте РПД по шагам. Сначала общие сведения, затем учебный план, график, содержание и материалы.
            </p>
        </div>
    </div>

    <div class="card-body constructor-grid">
        <a href="{{ route('rpd-programs.edit', $rpdProgram) }}" class="constructor-card">
            <div class="constructor-number">1</div>
            <div>
                <strong>Общие сведения</strong>
                <span>Название, направленность, часы, срок освоения.</span>
            </div>
        </a>

        <div class="constructor-card constructor-card-disabled">
            <div class="constructor-number">2</div>
            <div>
                <strong>Комплекс основных характеристик</strong>
                <span>Актуальность, цель, задачи, результаты, компетенции.</span>
            </div>
        </div>

        <a href="{{ route('rpd-programs.curriculum.index', $rpdProgram) }}" class="constructor-card">
            <div class="constructor-number">3</div>
            <div>
                <strong>Учебный план</strong>
                <span>Разделы, темы, теория, практика, формы контроля.</span>
            </div>
        </a>

        <div class="constructor-card constructor-card-disabled">
            <div class="constructor-number">4</div>
            <div>
                <strong>Календарный график</strong>
                <span>Распределение теории и практики по неделям.</span>
            </div>
        </div>

        <div class="constructor-card constructor-card-disabled">
            <div class="constructor-number">5</div>
            <div>
                <strong>Содержание учебного плана</strong>
                <span>Описание содержания по каждому разделу.</span>
            </div>
        </div>

        <div class="constructor-card constructor-card-disabled">
            <div class="constructor-number">6</div>
            <div>
                <strong>Оценочные материалы</strong>
                <span>Вопросы, практические работы, критерии оценивания.</span>
            </div>
        </div>

        <div class="constructor-card constructor-card-disabled">
            <div class="constructor-number">7</div>
            <div>
                <strong>Литература и ресурсы</strong>
                <span>Основная, дополнительная литература и интернет-ресурсы.</span>
            </div>
        </div>

        <div class="constructor-card constructor-card-disabled">
            <div class="constructor-number">8</div>
            <div>
                <strong>Разработчики</strong>
                <span>Должности и ФИО авторов программы.</span>
            </div>
        </div>

        <div class="constructor-card constructor-card-disabled">
            <div class="constructor-number">9</div>
            <div>
                <strong>Проверка и генерация</strong>
                <span>Контроль заполнения и формирование DOCX.</span>
            </div>
        </div>
    </div>
</section>
@endsection