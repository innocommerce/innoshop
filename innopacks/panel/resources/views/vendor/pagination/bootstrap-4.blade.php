@if ($paginator->hasPages())
<div class="d-flex align-items-center mb-3 mt-4 pagination-wrap">
  <nav class="me-2">
      <ul class="pagination mb-0">
          {{-- Previous Page Link --}}
          @if ($paginator->onFirstPage())
              <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                  <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
              </li>
          @else
              <li class="page-item">
                  <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><i class="bi bi-chevron-left"></i></a>
              </li>
          @endif

          {{-- Pagination Elements --}}
          @foreach ($elements as $element)
              {{-- "Three Dots" Separator --}}
              @if (is_string($element))
                  <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
              @endif

              {{-- Array Of Links --}}
              @if (is_array($element))
                  @foreach ($element as $page => $url)
                      @if ($page == $paginator->currentPage())
                          <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                      @else
                          <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                      @endif
                  @endforeach
              @endif
          @endforeach

          {{-- Next Page Link --}}
          @if ($paginator->hasMorePages())
              <li class="page-item">
                  <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"><i class="bi bi-chevron-right"></i></a>
              </li>
          @else
              <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                  <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
              </li>
          @endif
      </ul>
  </nav>
  <div class="text-secondary">共{{ $paginator->lastPage() }}页，</div>
  <div class="text-secondary">共{{ $paginator->total() }}项</div>
</div>
@endif
