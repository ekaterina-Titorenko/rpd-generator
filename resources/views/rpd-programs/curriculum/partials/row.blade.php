<tr class="{{ $rowClass }}">
    <td class="curriculum-number-cell">
        {{ $item->number ?: '—' }}
        @if ($item->type === 'final_work')
        <span class="badge">Итог</span>
        @endif
    </td>

    <td class="{{ $isChild ? 'curriculum-child-title' : '' }}">
        <textarea
            form="update-curriculum-item-{{ $item->id }}"
            name="title"
            rows="1"
            required
            data-autosubmit
            data-autoresize>{{ old('title', $item->title) }}</textarea>
    </td>

    <td class="readonly-hours-cell">
        {{ $item->total_hours }}
    </td>

    <td>
        <input
            form="update-curriculum-item-{{ $item->id }}"
            name="theory_hours"
            type="number"
            value="{{ old('theory_hours', $item->theory_hours) }}"
            min="0"
            max="1000"
            required
            data-autosubmit>
    </td>

    <td>
        <input
            form="update-curriculum-item-{{ $item->id }}"
            name="practice_hours"
            type="number"
            value="{{ old('practice_hours', $item->practice_hours) }}"
            min="0"
            max="1000"
            required
            data-autosubmit>
    </td>

    <td>
        <input
            form="update-curriculum-item-{{ $item->id }}"
            name="control_form"
            type="text"
            value="{{ old('control_form', $item->control_form) }}"
            list="control-forms-list"
            data-autosubmit>
    </td>

    <td>
        <div class="table-actions table-actions-inline">
            <form
                id="update-curriculum-item-{{ $item->id }}"
                method="POST"
                action="{{ route('rpd-programs.curriculum.update', [$rpdProgram, $item]) }}">
                @csrf
                @method('PUT')
            </form>

            <form
                method="POST"
                action="{{ route('rpd-programs.curriculum.destroy', [$rpdProgram, $item]) }}"
                onsubmit="return confirm('Удалить строку учебного плана?')">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn-icon-danger" title="Удалить строку" aria-label="Удалить строку">
                    ×
                </button>
            </form>
        </div>
    </td>
</tr>