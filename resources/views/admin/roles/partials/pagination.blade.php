@if($roles->hasPages())
    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
        <div class="small text-muted">
            @if($roles->total() > 0)
                عرض {{ $roles->firstItem() }} - {{ $roles->lastItem() }} من {{ $roles->total() }}
            @else
                —
            @endif
        </div>
        <div>
            {{ $roles->links() }}
        </div>
    </div>
@endif
