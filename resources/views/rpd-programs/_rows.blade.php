@forelse ($rpdPrograms as $rpdProgram)
<tr>
    <td class="rpd-title-cell">
        <strong class="rpd-title-text">{{ $rpdProgram->title }}</strong>

        @if (filled($rpdProgram->smko_code))
        <div class="field-hint">
            СМКО: {{ $rpdProgram->smko_code }}
        </div>
        @endif
    </td>

    <td>
        {{ $rpdProgram->user?->name ?? '—' }}

        @if ($rpdProgram->user?->email)
        <div class="field-hint">
            {{ $rpdProgram->user->email }}
        </div>
        @endif
    </td>

    <td>
        {{ $rpdProgram->direction_label }}
    </td>

    <td>
        {{ $rpdProgram->year }}
    </td>

    <td>
        <span class="badge">
            {{ $rpdProgram->status_label }}
        </span>
    </td>

    <td>
        {{ $rpdProgram->created_at?->format('d.m.Y H:i') }}
    </td>

    <td>
        <div class="table-actions rpd-program-actions">
            <a
                href="{{ route('rpd-programs.show', $rpdProgram) }}"
                class="icon-action icon-action-edit"
                title="Открыть"
                aria-label="Открыть">
                <img src="{{ asset('icons/pencil.svg') }}" alt="">
            </a>

            @if (
            auth()->user()->role === 'admin'
            || ($rpdProgram->status === 'approved' && filled($rpdProgram->smko_code))
            )
            <a
                href="{{ route('rpd-programs.download-docx', $rpdProgram) }}"
                class="icon-action icon-action-download"
                title="Скачать DOCX"
                aria-label="Скачать DOCX">
                <img src="{{ asset('icons/download.png') }}" alt="">
            </a>
            @endif

            @if (auth()->user()->role === 'admin' || $rpdProgram->user_id === auth()->id())
            <form
                method="POST"
                action="{{ route('rpd-programs.destroy', $rpdProgram) }}"
                onsubmit="return confirm('Удалить эту РПД? Это действие нельзя отменить.');">
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="icon-action icon-action-delete"
                    title="Удалить"
                    aria-label="Удалить">
                    <img src="{{ asset('icons/remove.png') }}" alt="">
                </button>
            </form>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7">
        РПД не найдены.
    </td>
</tr>
@endforelse