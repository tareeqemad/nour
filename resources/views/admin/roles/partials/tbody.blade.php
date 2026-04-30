@php
    $isSuperAdmin = $isSuperAdmin ?? auth()->user()->isSuperAdmin();
    $isCompanyOwner = $isCompanyOwner ?? auth()->user()->isCompanyOwner();
@endphp

@forelse($roles as $index => $role)
    <tr class="{{ $role->is_system ? 'table-light' : '' }}">
        <td class="text-center text-nowrap">
            <span class="text-muted fw-semibold">{{ ($roles->currentPage() - 1) * $roles->perPage() + $index + 1 }}</span>
        </td>
        <td class="text-nowrap">
            <code class="text-primary fw-bold">{{ $role->name }}</code>
        </td>
        <td class="text-nowrap">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">{{ $role->label }}</span>
                @if($isCompanyOwner && $role->is_system)
                    <span class="badge bg-light text-dark border" title="دور نظامي - للقراءة فقط">
                        <i class="bi bi-lock-fill me-1"></i>
                        نظامي
                    </span>
                @endif
            </div>
        </td>
        <td class="d-none d-md-table-cell">
            <small class="text-muted">{{ $role->description ?? '-' }}</small>
        </td>
        @if($isSuperAdmin)
            <td class="text-center">
                @if($role->operator)
                    <span class="badge bg-secondary">{{ $role->operator->name }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
        @endif
        <td class="text-center">
            <span class="badge bg-info">{{ $role->users_count }} مستخدم</span>
        </td>
        <td class="text-center">
            <span class="badge bg-success">{{ $role->permissions_count }} صلاحية</span>
        </td>
        <td class="text-center">
            @if($role->is_system)
                <span class="badge badge-system" title="دور مدمج في النظام">
                    <i class="bi bi-shield-check me-1"></i>
                    نظامي
                </span>
            @elseif(is_null($role->operator_id))
                <span class="badge badge-general" title="دور عام يظهر لكل المشغلين">
                    <i class="bi bi-globe me-1"></i>
                    عام
                </span>
            @else
                <span class="badge badge-custom" title="دور خاص بمشغل واحد">
                    <i class="bi bi-gear me-1"></i>
                    مخصص
                </span>
            @endif
        </td>
        <td class="text-end">
            <div class="d-flex gap-1 justify-content-end">
                @can('view', $role)
                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-info" title="عرض">
                        <i class="bi bi-eye"></i>
                    </a>
                @endcan
                @can('update', $role)
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                        <i class="bi bi-pencil"></i>
                    </a>
                @endcan
                @can('delete', $role)
                    @if(!$role->is_system && $role->users_count == 0)
                        <button type="button" class="btn btn-sm btn-outline-danger" title="حذف"
                                data-bs-toggle="modal" data-bs-target="#deleteModal{{ $role->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endif
                @endcan
            </div>
        </td>
    </tr>

    @can('delete', $role)
        @if(!$role->is_system)
            <div class="modal fade" id="deleteModal{{ $role->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">تأكيد الحذف</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>هل أنت متأكد من حذف الدور <strong>{{ $role->label }}</strong>؟</p>
                            @if($role->users_count > 0)
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    <small>هذا الدور مرتبط بـ {{ $role->users_count }} مستخدم. لا يمكن حذفه.</small>
                                </div>
                            @else
                                <p class="text-danger mb-0"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            @if($role->users_count == 0)
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">حذف</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endcan
@empty
    <tr>
        <td colspan="{{ $isSuperAdmin ? '9' : '8' }}" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            @if(session('roles_filter.name') || session('roles_filter.label') || session('roles_filter.description'))
                لا توجد نتائج للبحث
            @else
                @if($isCompanyOwner)
                    لا توجد أدوار متاحة. يمكنك إنشاء أدوار مخصصة لمستخدمي مشغلك.
                @else
                    لا توجد أدوار
                @endif
            @endif
        </td>
    </tr>
@endforelse
