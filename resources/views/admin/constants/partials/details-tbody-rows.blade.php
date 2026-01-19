@forelse($details as $detail)
    <tr>
        <td>{{ $detail->label }}</td>
        <td><code>{{ $detail->code ?? '-' }}</code></td>
        <td><code>{{ $detail->value ?? '-' }}</code></td>
        <td class="d-none d-md-table-cell">{{ $detail->notes ?? '-' }}</td>
        <td class="text-center">
            @if($detail->is_active)
                <span class="badge bg-success">نشط</span>
            @else
                <span class="badge bg-danger">غير نشط</span>
            @endif
        </td>
        <td class="text-center d-none d-lg-table-cell">{{ $detail->order }}</td>
        <td class="text-center">
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-primary edit-detail-btn" 
                        data-id="{{ $detail->id }}"
                        data-label="{{ $detail->label }}"
                        data-code="{{ $detail->code }}"
                        data-value="{{ $detail->value }}"
                        data-notes="{{ $detail->notes }}"
                        data-is-active="{{ $detail->is_active ? '1' : '0' }}"
                        data-order="{{ $detail->order }}"
                        title="تعديل">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger delete-detail-btn" 
                        data-id="{{ $detail->id }}"
                        title="حذف">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            لا توجد تفاصيل
        </td>
    </tr>
@endforelse


