<tbody data-rpd-programs-tbody>
    @forelse ($rpdPrograms as $rpdProgram)
        <tr>
            <td>
                <strong>{{ $rpdProgram->title }}</strong>
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
                        class="btn btn-secondary btn-compact"
                    >
                        Открыть
                    </a>

                    @if (auth()->user()->role === 'admin' || $rpdProgram->user_id === auth()->id())
                        <a
                            href="{{ route('rpd-programs.edit', $rpdProgram) }}"
                            class="btn btn-secondary btn-compact"
                        >
                            Редактировать
                        </a>

                        <form
                            method="POST"
                            action="{{ route('rpd-programs.destroy', $rpdProgram) }}"
                            onsubmit="return confirm('Удалить эту РПД? Это действие нельзя отменить.');"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger btn-compact">
                                Удалить
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
</tbody>

<div class="pagination-wrap" data-rpd-programs-pagination>
    {{ $rpdPrograms->links() }}
</div>