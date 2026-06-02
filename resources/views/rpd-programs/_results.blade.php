<tbody data-rpd-programs-tbody>
    @include('rpd-programs._rows')
</tbody>

<div class="pagination-wrap" data-rpd-programs-pagination>
    {{ $rpdPrograms->links() }}
</div>