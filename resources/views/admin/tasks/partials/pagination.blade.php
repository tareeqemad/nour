@if($tasks->hasPages())
    @php
        $current = $tasks->currentPage();
        $last = $tasks->lastPage();
    @endphp
    <ul class="pagination mb-0">
        {{-- Previous Page Link --}}
        @if ($tasks->onFirstPage())
            <li class="page-item disabled"><span class="page-link">‹</span></li>
        @else
            <li class="page-item"><a class="page-link" href="#" data-page="{{ $current - 1 }}">‹</a></li>
        @endif

        {{-- Pagination Elements --}}
        @if($last <= 7)
            {{-- Show all pages if 7 or less --}}
            @for($p = 1; $p <= $last; $p++)
                @if($p == $current)
                    <li class="page-item active"><span class="page-link">{{ $p }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="#" data-page="{{ $p }}">{{ $p }}</a></li>
                @endif
            @endfor
        @else
            {{-- Smart pagination for more than 7 pages --}}
            {{-- First page --}}
            @if($current > 3)
                <li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>
                @if($current > 4)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
            @endif

            {{-- Pages around current --}}
            @php
                $start = max(1, $current - 1);
                $end = min($last, $current + 1);
            @endphp
            @for($p = $start; $p <= $end; $p++)
                @if($p == $current)
                    <li class="page-item active"><span class="page-link">{{ $p }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="#" data-page="{{ $p }}">{{ $p }}</a></li>
                @endif
            @endfor

            {{-- Last page --}}
            @if($current < $last - 2)
                @if($current < $last - 3)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
                <li class="page-item"><a class="page-link" href="#" data-page="{{ $last }}">{{ $last }}</a></li>
            @endif
        @endif

        {{-- Next Page Link --}}
        @if ($tasks->hasMorePages())
            <li class="page-item"><a class="page-link" href="#" data-page="{{ $current + 1 }}">›</a></li>
        @else
            <li class="page-item disabled"><span class="page-link">›</span></li>
        @endif
    </ul>
@endif
