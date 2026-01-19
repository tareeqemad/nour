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
                <span class="badge badge-system">
                    <i class="bi bi-shield-check me-1"></i>
                    نظامي
                </span>
            @else
                <span class="badge badge-custom">
                    <i class="bi bi-gear me-1"></i>
                    مخصص
                </span>
            @endif
        </td>
        <td class="text-center text-nowrap">
            <div class="btn-group" role="group">
                <button type="button" 
                        class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false" 
                        title="إجراءات">
                    <span class="visually-hidden">إجراءات</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @can('view', $role)
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.roles.show', $role) }}">
                                <i class="bi bi-eye me-2 text-info"></i> عرض التفاصيل
                            </a>
                        </li>
                    @endcan
                    @can('update', $role)
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.roles.edit', $role) }}">
                                <i class="bi bi-pencil me-2 text-primary"></i> تعديل
                            </a>
                        </li>
                    @endcan
                    @can('delete', $role)
                        @if(!$role->is_system && $role->users_count == 0)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item text-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal{{ $role->id }}">
                                    <i class="bi bi-trash me-2"></i> حذف
                                </button>
                            </li>
                        @endif
                    @endcan
                </ul>
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
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
