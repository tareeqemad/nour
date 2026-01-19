@foreach($constants as $constant)
    <tr>
        <td><code>{{ $constant->constant_number }}</code></td>
        <td class="fw-semibold">{{ $constant->constant_name }}</td>
        <td class="d-none d-md-table-cell">
            <small class="text-muted">
                {{ Str::limit($constant->description, 50) }}
            </small>
        </td>
        <td class="text-center">
            <span class="badge-soft">{{ $constant->all_details_count ?? 0 }}</span>
        </td>
        <td class="text-center">
            @if($constant->is_active)
                <span class="badge-soft badge-active">
                    <i class="bi bi-check-circle me-1"></i>نشط
                </span>
            @else
                <span class="badge-soft badge-inactive">
                    <i class="bi bi-x-circle me-1"></i>غير نشط
                </span>
            @endif
        </td>
        <td class="text-center d-none d-lg-table-cell">
            <span class="badge-soft">{{ $constant->order }}</span>
        </td>
        <td>
            <div class="d-flex gap-2 justify-content-center">
                <a class="btn btn-light btn-icon" href="{{ route('admin.constants.show', $constant) }}" title="عرض">
                    <i class="bi bi-eye"></i>
                </a>
                @can('update', $constant)
                    <a class="btn btn-light btn-icon" href="{{ route('admin.constants.edit', $constant) }}" title="تعديل">
                        <i class="bi bi-pencil"></i>
                    </a>
                @endcan
                @can('delete', $constant)
                    <button type="button" class="btn btn-light btn-icon text-danger constant-delete-btn" 
                            data-constant-id="{{ $constant->id }}"
                            data-constant-name="{{ $constant->constant_name }}"
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteModal{{ $constant->id }}" 
                            title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                @endcan
            </div>
        </td>
    </tr>
@endforeach



