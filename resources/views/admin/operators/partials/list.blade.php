<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 general-table">
            <thead class="table-primary">
            <tr>
                <th style="min-width:200px;" class="text-nowrap">المشغل</th>
                <th style="min-width:150px;" class="d-none d-lg-table-cell text-nowrap">المالك</th>
                <th style="min-width:100px;" class="text-center text-nowrap">الموظفون</th>
                <th style="min-width:120px;" class="text-center text-nowrap">وحدات التوليد</th>
                <th style="min-width:150px;" class="text-center d-none d-md-table-cell text-nowrap">الحالة</th>
                <th style="min-width:120px;" class="d-none d-xl-table-cell text-nowrap">تاريخ الإنشاء</th>
                <th style="min-width:140px;" class="text-center text-nowrap">الإجراءات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($operators as $operator)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $operator->unit_name ?? $operator->name }}</div>
                    </td>

                    <td class="d-none d-lg-table-cell">
                        @if($operator->owner)
                            <span class="badge bg-primary">{{ $operator->owner->name }}</span>
                            <span class="text-muted small ms-1">({{ $operator->owner->username }})</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <span class="badge bg-success">
                            {{ $operator->employees_count ?? 0 }}
                        </span>
                    </td>

                    <td class="text-center">
                        <span class="badge bg-info">
                            {{ $operator->generation_units_count ?? 0 }}
                        </span>
                    </td>

                    <td class="text-center d-none d-md-table-cell">
                        <div class="d-flex flex-column gap-1 align-items-center">
                            @if($operator->status === 'active' && $operator->is_approved)
                                <span class="badge bg-success">فعّال ومعتمد</span>
                            @elseif($operator->status === 'active' && !$operator->is_approved)
                                <span class="badge bg-info text-white">فعّال وغير معتمد</span>
                            @elseif($operator->status === 'inactive' && $operator->is_approved)
                                <span class="badge bg-secondary">غير فعّال ومعتمد</span>
                            @else
                                <span class="badge bg-warning text-dark">غير فعّال وغير معتمد</span>
                            @endif
                        </div>
                    </td>

                    <td class="d-none d-xl-table-cell">
                        <span class="text-muted small">{{ optional($operator->created_at)->format('Y-m-d') }}</span>
                    </td>

                    <td class="text-center text-nowrap">
                        <div class="d-flex gap-2 justify-content-center">
                            @can('view', $operator)
                                <a href="{{ route('admin.operators.show', $operator) }}" 
                                   class="btn btn-sm btn-outline-info" 
                                   title="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endcan

                            @can('update', $operator)
                                <a href="{{ route('admin.operators.edit', $operator) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="تعديل"
                                   data-action="edit-operator"
                                   data-url="{{ route('admin.operators.edit', $operator) }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan

                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <button type="button"
                                        class="btn btn-sm btn-outline-{{ $operator->status === 'active' ? 'warning' : 'success' }}"
                                        title="{{ $operator->status === 'active' ? 'إيقاف' : 'تفعيل' }}"
                                        data-action="toggle-status-operator"
                                        data-status="{{ $operator->status }}"
                                        data-url="{{ route('admin.operators.toggle-status', $operator) }}">
                                    <i class="bi bi-{{ $operator->status === 'active' ? 'pause' : 'play' }}-fill"></i>
                                </button>
                                
                                <button type="button"
                                        class="btn btn-sm btn-outline-{{ $operator->is_approved ? 'danger' : 'primary' }}"
                                        title="{{ $operator->is_approved ? 'إلغاء الاعتماد' : 'اعتماد' }}"
                                        data-action="toggle-approval-operator"
                                        data-approved="{{ $operator->is_approved ? '1' : '0' }}"
                                        data-url="{{ route('admin.operators.toggle-approval', $operator) }}">
                                    <i class="bi bi-{{ $operator->is_approved ? 'x-circle' : 'check-circle' }}-fill"></i>
                                </button>
                            @endif

                            @can('delete', $operator)
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        title="حذف"
                                        data-action="delete-operator"
                                        data-name="{{ $operator->name }}"
                                        data-url="{{ route('admin.operators.destroy', $operator) }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        لا توجد نتائج
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($operators->hasPages())
    <div class="card-footer py-3">
        {{ $operators->links() }}
    </div>
@endif
