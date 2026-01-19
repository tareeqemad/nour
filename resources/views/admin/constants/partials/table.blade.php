<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-nowrap" style="width: 120px;">
                    <i class="bi bi-hash text-primary me-1"></i>
                    رقم الثابت
                </th>
                <th class="text-nowrap">
                    <i class="bi bi-tag text-info me-1"></i>
                    اسم الثابت
                </th>
                <th class="text-nowrap d-none d-md-table-cell">
                    <i class="bi bi-file-text text-secondary me-1"></i>
                    الوصف
                </th>
                <th class="text-nowrap text-center" style="width: 100px;">
                    <i class="bi bi-list-ul text-warning me-1"></i>
                    التفاصيل
                </th>
                <th class="text-nowrap text-center" style="width: 100px;">
                    <i class="bi bi-toggle-on text-success me-1"></i>
                    الحالة
                </th>
                <th class="text-nowrap text-center d-none d-lg-table-cell" style="width: 100px;">
                    <i class="bi bi-sort-numeric-down text-primary me-1"></i>
                    الترتيب
                </th>
                <th class="text-nowrap text-center" style="width: 150px;">
                    <i class="bi bi-gear text-dark me-1"></i>
                    الإجراءات
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($constants as $constant)
                <tr>
                    <td class="text-nowrap">
                        <code class="text-primary fw-bold">{{ $constant->constant_number }}</code>
                    </td>
                    <td class="text-nowrap">
                        <span class="fw-semibold text-dark">{{ $constant->constant_name }}</span>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <small class="text-muted">
                            {{ $constant->description ? Str::limit($constant->description, 50) : '-' }}
                        </small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                            <i class="bi bi-list-ul me-1"></i>
                            {{ $constant->all_details_count ?? 0 }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($constant->is_active)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                نشط
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                غير نشط
                            </span>
                        @endif
                    </td>
                    <td class="text-center d-none d-lg-table-cell">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                            {{ $constant->order }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            @can('view', $constant)
                                <a href="{{ route('admin.constants.show', $constant) }}" 
                                   class="btn btn-sm btn-outline-info" 
                                   title="عرض التفاصيل"
                                   data-bs-toggle="tooltip">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endcan
                            @can('update', $constant)
                                <a href="{{ route('admin.constants.edit', $constant) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="تعديل"
                                   data-bs-toggle="tooltip">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan
                            @can('delete', $constant)
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal{{ $constant->id }}" 
                                        title="حذف"
                                        data-bs-toggle="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5 class="text-muted mt-3 mb-2">
                                @if(request('search') || request('status'))
                                    لا توجد نتائج للبحث
                                @else
                                    لا توجد ثوابت
                                @endif
                            </h5>
                            <p class="text-muted mb-0">
                                @if(request('search') || request('status'))
                                    جرب البحث بكلمات مختلفة أو أزل الفلاتر
                                @else
                                    ابدأ بإضافة ثابت جديد
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
