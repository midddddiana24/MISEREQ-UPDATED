@if ($paginator->hasPages())
<nav class="admin-pagination {{ ($compact ?? false) ? 'admin-pagination-compact' : '' }}" role="navigation" aria-label="{{ $paginationLabel ?? 'Results pages' }}">
    <span class="admin-pagination-summary">Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results</span>
    <div class="admin-pagination-controls">
        @if ($paginator->onFirstPage())
            <span class="admin-pagination-disabled" aria-disabled="true">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif

        @if ($compact ?? false)
            <span class="admin-pagination-current">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @else
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="admin-pagination-ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="admin-pagination-page-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="admin-pagination-disabled" aria-disabled="true">Next</span>
        @endif
    </div>
</nav>
@endif
