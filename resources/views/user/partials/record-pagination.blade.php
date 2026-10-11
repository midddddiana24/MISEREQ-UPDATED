@if($records->hasPages())
<nav class="record-pagination" aria-label="{{ $paginationLabel }}">
    @if($records->onFirstPage())
        <span class="record-pagination-disabled">Previous</span>
    @else
        <a href="{{ $records->previousPageUrl() }}" rel="prev">Previous</a>
    @endif

    <span class="record-pagination-current">Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</span>

    @if($records->hasMorePages())
        <a href="{{ $records->nextPageUrl() }}" rel="next">Next</a>
    @else
        <span class="record-pagination-disabled">Next</span>
    @endif
</nav>
@endif
