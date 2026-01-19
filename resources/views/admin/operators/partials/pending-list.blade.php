<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 op-table">
            <thead class="table-light">
            <tr>
                <th>المشغل</th>
                <th class="d-none d-lg-table-cell">المالك</th>
                <th class="text-center">الموظفون</th>
                <th class="text-center">وحدات التوليد</th>
                <th class="text-center d-none d-md-table-cell">الحالة</th>
                <th class="d-none d-xl-table-cell">تاريخ الإنشاء</th>
                <th class="text-nowrap">إجراءات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($operators as $operator)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $operator->name }}</div>
                        @if($operator->owner_name)
                            <div class="text-muted small">{{ $operator->owner_name }}</div>
                        @endif
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
                            @if($operator->status === 'active')
                                <span class="badge bg-info text-white">فعّال</span>
                            @else
                                <span class="badge bg-secondary">غير فعّال</span>
                            @endif
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-hourglass-split me-1"></i>
                                في انتظار الاعتماد
                            </span>
                        </div>
                    </td>

                    <td class="d-none d-xl-table-cell">
                        <div class="text-muted small">
                            {{ $operator->created_at->format('Y-m-d') }}
                        </div>
                        <div class="text-muted small">
                            {{ $operator->created_at->diffForHumans() }}
                        </div>
                    </td>

                    <td class="text-nowrap">
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.operators.show', $operator) }}" 
                               class="btn btn-sm btn-outline-primary" 
                               title="عرض التفاصيل">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false"
                                    title="المزيد من الإجراءات">
                                <span class="visually-hidden">إجراءات</span>
                            </button>
                            
                            <ul class="dropdown-menu dropdown-menu-end">
                                @can('approve', $operator)
                                    <li>
                                        <form action="{{ route('admin.operators.toggle-approval', $operator) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="dropdown-item text-success approve-operator-btn" 
                                                    data-operator-id="{{ $operator->id }}"
                                                    data-operator-name="{{ $operator->name }}">
                                                <i class="bi bi-check-circle me-2"></i>
                                                اعتماد المشغل
                                            </button>
                                        </form>
                                    </li>
                                @endcan

                                @can('update', $operator)
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.operators.toggle-status', $operator) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="dropdown-item text-{{ $operator->status === 'active' ? 'warning' : 'secondary' }} toggle-status-btn" 
                                                        data-operator-id="{{ $operator->id }}"
                                                        data-operator-name="{{ $operator->name }}"
                                                        data-current-status="{{ $operator->status }}"
                                                        data-employees-count="{{ $operator->employees_count ?? 0 }}">
                                                    <i class="bi bi-{{ $operator->status === 'active' ? 'ban' : 'check-circle' }} me-2"></i>
                                                    {{ $operator->status === 'active' ? 'حظر المشغل' : 'إلغاء حظر المشغل' }}
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                @endcan
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <p class="mb-0">لا توجد مشغلين في انتظار الاعتماد</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($operators->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    عرض {{ $operators->firstItem() ?? 0 }} إلى {{ $operators->lastItem() ?? 0 }} من {{ $operators->total() }} مشغل
                </div>
                <div>
                    {{ $operators->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
