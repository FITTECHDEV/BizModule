<style>
    .logintracker-pagination {
        font-size: 0.85rem;
        margin-top: 0.75rem;
    }

    /* Shared styles for links and spans */
    .logintracker-pagination a,
    .logintracker-pagination span {
        padding: 0.5rem 0.9rem;
        margin: 0 3px;
        border-radius: 9999px; /* pill shape */
        border: 1px solid #d1d5db;
        color: #1d4ed8;
        text-decoration: none;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-width: 2.25rem;
        height: 2.25rem;
        transition: all 0.2s ease-in-out;
    }

    /* Hover state */
    .logintracker-pagination a:hover {
        background-color: #f3f4f6;
        border-color: #cbd5e1;
    }

    /* Active page */
    .logintracker-pagination .active {
        background-color: #1d4ed8;
        color: #fff;
        font-weight: 600;
        border-color: #1d4ed8;
        box-shadow: 0 2px 6px rgba(29, 78, 216, 0.3);
    }

    /* Disabled state */
    .logintracker-pagination .disabled {
        color: #9ca3af;
        background-color: #f9fafb;
        border-color: #e5e7eb;
        cursor: not-allowed;
        opacity: 0.65;
    }

    /* Icons */
    .logintracker-pagination svg {
        width: 18px;
        height: 18px;
        vertical-align: middle;
    }

    /* Mobile layout */
    @media (max-width: 640px) {
        .logintracker-pagination {
            font-size: 0.8rem;
        }

        .logintracker-pagination a,
        .logintracker-pagination span {
            padding: 0.4rem 0.8rem;
            min-width: 2rem;
            height: 2rem;
        }
    }
</style>

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="logintracker-pagination">
        {{-- Mobile View --}}
        <div class="flex justify-between sm:hidden">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="disabled">← Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}">← Prev</a>
            @endif

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">Next →</a>
            @else
                <span class="disabled">Next →</span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between mt-2 w-full">
            {{-- Results Summary --}}
            <div class="text-gray-600">
                Showing 
                @if ($paginator->firstItem())
                    <span class="font-semibold">{{ $paginator->firstItem() }}</span> to 
                    <span class="font-semibold">{{ $paginator->lastItem() }}</span> of 
                    <span class="font-semibold">{{ $paginator->total() }}</span> results
                @else
                    {{ $paginator->count() }} results
                @endif
            </div>

            {{-- Pagination Links --}}
            <div class="flex items-center space-x-1">
                {{-- Previous Arrow --}}
                @if ($paginator->onFirstPage())
                    <span class="disabled">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"/>
                        </svg>
                    </a>
                @endif

                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="disabled">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Arrow --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                        </svg>
                    </a>
                @else
                    <span class="disabled">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
