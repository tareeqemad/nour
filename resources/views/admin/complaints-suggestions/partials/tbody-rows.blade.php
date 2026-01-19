@forelse($complaintsSuggestions as $item)
    <tr>
        <td class="text-nowrap">
            <code class="text-primary fw-bold">{{ $item->tracking_code }}</code>
        </td>
        <td>
            <span class="badge-type-{{ $item->type }}">
                {{ $item->type_label }}
            </span>
        </td>
        <td class="text-nowrap">
            <span class="fw-semibold">{{ $item->name }}</span>
        </td>
        <td class="text-nowrap">{{ $item->phone }}</td>
        <td class="d-none d-md-table-cell">
            @if($item->generator)
                <span class="fw-semibold">
                    {{ $item->generator->name ?? 'مولد محذوف' }}
                    @if($item->generator->trashed())
                        <span class="badge bg-secondary ms-1" title="مولد محذوف">محذوف</span>
                    @endif
                </span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="d-none d-lg-table-cell">
            @if($item->operator)
                <span class="fw-semibold">{{ $item->operator->name }}</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="text-center">
            <span class="badge-status-{{ $item->status }}">
                {{ $item->status_label }}
            </span>
        </td>
        <td class="d-none d-xl-table-cell">
            <small class="text-muted">
                {{ $item->created_at->format('Y-m-d H:i') }}
            </small>
        </td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('admin.complaints-suggestions.show', $item) }}" 
                   class="btn btn-sm btn-outline-primary" title="عرض والرد">
                    <i class="bi bi-eye"></i>
                </a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.complaints-suggestions.edit', $item) }}" 
                       class="btn btn-sm btn-outline-warning" title="تعديل">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger complaint-delete-btn" 
                            data-complaint-id="{{ $item->id }}"
                            data-complaint-tracking="{{ $item->tracking_code }}"
                            title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            @if(request('search') || request('type') || request('status'))
                لا توجد نتائج للبحث
            @else
                لا توجد شكاوى أو مقترحات
            @endif
        </td>
    </tr>
@endforelse



