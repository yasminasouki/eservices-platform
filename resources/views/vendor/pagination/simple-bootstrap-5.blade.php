@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination">
        <ul class="pagination justify-content-center mb-0">
            @if (!$paginator->onFirstPage())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ $paginator->currentPage() - 1 }}</a>
                </li>
            @endif

            <li class="page-item active" aria-current="page">
                <span class="page-link">{{ $paginator->currentPage() }}</span>
            </li>

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ $paginator->currentPage() + 1 }}</a>
                </li>
            @endif
        </ul>
    </nav>
@endif
