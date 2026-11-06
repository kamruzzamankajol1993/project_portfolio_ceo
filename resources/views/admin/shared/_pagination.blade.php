@if($paginator->hasPages())
<nav class="ajax-pagination" aria-label="Pagination">
  <div class="page-count">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</div>
  <div class="pager-actions">
   <a class="page-step {{ $paginator->onFirstPage()?'disabled':'' }}" aria-disabled="{{ $paginator->onFirstPage()?'true':'false' }}" href="{{ $paginator->url(1) }}" aria-label="First page"><i class="bi bi-chevron-double-left" aria-hidden="true"></i> <span>First</span></a>
   <a class="page-step {{ $paginator->onFirstPage()?'disabled':'' }}" aria-disabled="{{ $paginator->onFirstPage()?'true':'false' }}" href="{{ $paginator->previousPageUrl() ?: '#' }}" aria-label="Previous page"><i class="bi bi-chevron-left" aria-hidden="true"></i> <span>Previous</span></a>
   <span class="current-page">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
   <a class="page-step {{ !$paginator->hasMorePages()?'disabled':'' }}" aria-disabled="{{ !$paginator->hasMorePages()?'true':'false' }}" href="{{ $paginator->nextPageUrl() ?: '#' }}" aria-label="Next page"><span>Next</span> <i class="bi bi-chevron-right" aria-hidden="true"></i></a>
   <a class="page-step {{ !$paginator->hasMorePages()?'disabled':'' }}" aria-disabled="{{ !$paginator->hasMorePages()?'true':'false' }}" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Last page"><span>Last</span> <i class="bi bi-chevron-double-right" aria-hidden="true"></i></a>
  </div>
</nav>
@endif
