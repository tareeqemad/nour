@if($complianceSafeties->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>اسم المشغل</th>
                    <th>حالة شهادة السلامة</th>
                    <th>تاريخ آخر زيارة</th>
                    <th>الجهة المنفذة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($complianceSafeties as $compliance)
                    <tr>
                        <td>{{ $compliance->id }}</td>
                        <td>
                            @if($compliance->operator)
                                <span class="badge bg-info">{{ $compliance->operator->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $compliance->safetyCertificateStatusDetail?->getBadgeColor() ?? 'secondary' }}">
                                {{ $compliance->safetyCertificateStatusDetail?->label ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @if($compliance->last_inspection_date)
                                {{ $compliance->last_inspection_date->format('Y-m-d') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $compliance->inspection_authority ?? '-' }}</td>
                        <td>
                            <div class="log-row-actions">
                                @can('view', $compliance)
                                    <a href="{{ route('admin.compliance-safeties.show', $compliance) }}" class="btn btn-xs btn-outline-info" title="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('update', $compliance)
                                    <a href="{{ route('admin.compliance-safeties.edit', $compliance) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('delete', $compliance)
                                    <button type="button" class="btn btn-xs btn-outline-danger compliance-safety-delete-btn" 
                                            data-compliance-safety-id="{{ $compliance->id }}"
                                            data-compliance-safety-name="سجل #{{ $compliance->id }}"
                                            title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($complianceSafeties->hasPages())
        <div class="log-pagination mt-4">
            {{ $complianceSafeties->links() }}
        </div>
    @endif
@else
    <div class="log-empty-state text-center py-5">
        <i class="bi bi-shield-check fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">لا توجد سجلات امتثال وسلامة</h5>
        <p class="text-muted">لم يتم العثور على سجلات امتثال وسلامة تطابق البحث</p>
    </div>
@endif


