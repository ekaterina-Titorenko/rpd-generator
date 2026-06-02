@php
    $sortLink = function (string $key) use ($sort, $direction) {
        $nextDirection = $sort === $key && $direction === 'asc' ? 'desc' : 'asc';

        return request()->fullUrlWithQuery([
            'sort' => $key,
            'direction' => $nextDirection,
        ]);
    };

    $sortMark = function (string $key) use ($sort, $direction) {
        if ($sort !== $key) {
            return '';
        }

        return $direction === 'asc' ? ' ↑' : ' ↓';
    };
@endphp
<div class="table-scroll">
    <table class="table rpd-programs-table">
        <thead>
            <tr>
                <th>
                    <a href="{{ $sortLink('title') }}">
                        Название{{ $sortMark('title') }}
                    </a>
                </th>
                <th>
                    <a href="{{ $sortLink('teacher') }}">
                        Создал{{ $sortMark('teacher') }}
                    </a>
                </th>
                <th>
                    <a href="{{ $sortLink('direction') }}">
                        Направленность{{ $sortMark('direction') }}
                    </a>
                </th>
                <th>
                    <a href="{{ $sortLink('year') }}">
                        Год{{ $sortMark('year') }}
                    </a>
                </th>
                <th>
                    <a href="{{ $sortLink('status') }}">
                        Статус{{ $sortMark('status') }}
                    </a>
                </th>
                <th>
                    <a href="{{ $sortLink('created_at') }}">
                        Создана{{ $sortMark('created_at') }}
                    </a>
                </th>
                <th>Действия</th>
            </tr>
        </thead>

        <tbody>
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

                    <td>{{ $rpdProgram->direction_label }}</td>
                    <td>{{ $rpdProgram->year }}</td>

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
                    <td colspan="7">РПД не найдены.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-wrap">
    {{ $rpdPrograms->links() }}
</div>