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
            <h2 class="card-title">Следующие разделы</h2>
            <p class="card-description">
                Эти блоки будут заполняться на следующих этапах конструктора.
            </p>
        </div>
    </div>

    <div class="card-body">
        <div class="steps-list">
            <div class="step-item">1. Комплекс основных характеристик</div>
            <div class="step-item">2. Учебный план</div>
            <div class="step-item">3. Календарный учебный график</div>
            <div class="step-item">4. Содержание учебного плана</div>
            <div class="step-item">5. Оценочные материалы</div>
            <div class="step-item">6. Литература и интернет-ресурсы</div>
            <div class="step-item">7. Разработчики</div>
            <div class="step-item">8. Проверка и генерация DOCX</div>
        </div>
    </div>
</section>
@endsection