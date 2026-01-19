@if($maintenanceRecords->hasPages())
    @php
        $current = $maintenanceRecords->currentPage();
        $last = $maintenanceRecords->lastPage();
    @endphp
    <ul class="pagination mb-0">
        {{-- Previous Page Link --}}
        @if ($maintenanceRecords->onFirstPage())
            <li class="page-item disabled"><span class="page-link">‹</span></li>
        @else
            <li class="page-item"><a class="page-link" href="#" data-page="{{ $current - 1 }}">‹</a></li>
        @endif

        {{-- Pagination Elements --}}
        @if($last <= 7)
            @for($p = 1; $p <= $last; $p++)
                @if($p == $current)
                    <li class="page-item active"><span class="page-link">{{ $p }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="#" data-page="{{ $p }}">{{ $p }}</a></li>
                @endif
            @endfor
        @else
            @if($current > 3)
                <li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>
                @if($current > 4)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
            @endif

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

            @if($current < $last - 2)
                @if($current < $last - 3)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
                <li class="page-item"><a class="page-link" href="#" data-page="{{ $last }}">{{ $last }}</a></li>
            @endif
        @endif

        {{-- Next Page Link --}}
        @if ($maintenanceRecords->hasMorePages())
            <li class="page-item"><a class="page-link" href="#" data-page="{{ $current + 1 }}">›</a></li>
        @else
            <li class="page-item disabled"><span class="page-link">›</span></li>
        @endif
    </ul>
@endif



