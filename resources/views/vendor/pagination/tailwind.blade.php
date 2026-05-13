@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-2">

        {{-- Result count --}}
        <p class="text-xs text-gray-500 whitespace-nowrap">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results
        </p>

        {{-- Page buttons --}}
        <div class="flex items-center gap-1">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-7 h-7 rounded text-xs text-gray-400 bg-gray-100 cursor-not-allowed" aria-disabled="true">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-7 h-7 rounded text-xs text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition" aria-label="{{ __('pagination.previous') }}">‹</a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-7 h-7 text-xs text-gray-400">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded text-xs font-semibold text-white bg-gray-900" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center w-7 h-7 rounded text-xs text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-7 h-7 rounded text-xs text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition" aria-label="{{ __('pagination.next') }}">›</a>
            @else
                <span class="inline-flex items-center justify-center w-7 h-7 rounded text-xs text-gray-400 bg-gray-100 cursor-not-allowed" aria-disabled="true">›</span>
            @endif

        </div>
    </nav>
@endif
