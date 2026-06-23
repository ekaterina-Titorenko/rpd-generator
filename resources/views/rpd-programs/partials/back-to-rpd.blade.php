@php
    $sectionAnchor = $sectionAnchor ?? null;
    $backUrl = route('rpd-programs.show', $rpdProgram) . ($sectionAnchor ? '#section-' . $sectionAnchor : '');
@endphp

<div class="form-actions form-actions-bottom">
    <a href="{{ $backUrl }}" class="btn btn-primary">
        К редактированию РПД
    </a>
</div>
