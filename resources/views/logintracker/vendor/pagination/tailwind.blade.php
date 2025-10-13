@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="logintracker-pagination">
        {{-- Add CSS Styles --}}
        <style>
            .logintracker-pagination {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 1rem 0;
            }
            
            .logintracker-pagination .pagination-wrapper {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .logintracker-pagination a,
            .logintracker-pagination span.disabled,
            .logintracker-pagination span.active {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 0.75rem;
                text-decoration: none;
                color: #6b7280;
                border: 1px solid #e5e7eb;
                background-color: white;
                font-size: 0.875rem;
                line-height: 1.25rem;
                transition: all 0.15s ease-in-out;
                min-width: 2.5rem;
                height: 2.5rem;
            }
            
            .logintracker-pagination a:hover {
                background-color: #f3f4f6;
                color: #374151;
                border-color: #d1d5db;
            }
            
            .logintracker-pagination span.active {
                background-color: #3b82f6;
                color: white;
                border-color: #3b82f6;
                font-weight: 600;
            }
            
            .logintracker-pagination span.disabled {
                background-color: #f9fafb;
                color: #d1d5db;
                cursor: not-allowed;
            }
            
            /* Arrow buttons specific styling */
            .logintracker-pagination .arrow-btn {
                padding: 0.5rem;
                min-width: 2.5rem;
            }
            
            .logintracker-pagination .arrow-btn svg {
                width: 1.25rem;
                height: 1.25rem;
            }
            
            /* Results text styling */
            .logintracker-pagination .results-text {
                color: #6b7280;
                font-size: 0.875rem;
                margin-right: 1.5rem;
            }
            
            /* Mobile styles */
            @media (max-width: 640px) {
                .logintracker-pagination .desktop-view {
                    display: none;
                }
                
                .logintracker-pagination .mobile-view {
                    display: flex;
                    justify-content: space-between;
                    width: 100%;
                    padding: 0 1rem;
                }
            }
            
            @media (min-width: 641px) {
                .logintracker-pagination .mobile-view {
                    display: none;
                }
                
                .logintracker-pagination .desktop-view {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    width: 100%;
                }
            }
        </style>
        
        {{-- Mobile View --}}
        <div class="mobile-view">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="disabled">← Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}">← Previous</a>
            @endif
            
            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">Next →</a>
            @else
                <span class="disabled">Next →</span>
            @endif
        </div>
        
        {{-- Desktop View --}}
        <div class="desktop-view">
            {{-- Results Summary --}}
            <div class="results-text">
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
            <div class="pagination-wrapper">
                {{-- Previous Arrow --}}
                @if ($paginator->onFirstPage())
                    <span class="disabled arrow-btn">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="arrow-btn" rel="prev">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                @endif
                
                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="disabled">{{ $element }}</span>
                    @endif
                    
                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                
                {{-- Next Arrow --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="arrow-btn" rel="next">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                @else
                    <span class="disabled arrow-btn">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif