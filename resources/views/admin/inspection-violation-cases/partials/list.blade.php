@if($cases->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold text-center" style="width: 50px;">#</th>
                    <th class="fw-semibold">المحافظة</th>
                    <th class="fw-semibold">رقم الاشتراك</th>
                    <th class="fw-semibold">اسم المشترك</th>
                    <th class="fw-semibold">اسم المنتفع</th>
                    <th class="fw-semibold">تاريخ القضية</th>
                    <th class="fw-semibold text-center">حالة القضية</th>
                    <th class="fw-semibold">بيان القضية/التعدي</th>
                    <th class="text-end fw-semibold">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cases as $case)
                    @php
                        $rowClass = match($case->status) {
                            1 => 'table-danger',
                            2 => 'table-warning',
                            3 => 'table-success',
                            default => '',
                        };
                        $badgeClass = 'bg-' . \App\Models\InspectionViolationCase::statusCssClass($case->status);
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ ($cases->currentPage() - 1) * $cases->perPage() + $loop->iteration }}</span>
                        </td>
                        <td>{{ $case->governorateDetail?->label ?? '—' }}</td>
                        <td><code class="small">{{ $case->subscription_number ?? '—' }}</code></td>
                        <td>{{ $case->subscriber_name }}</td>
                        <td>{{ $case->beneficiary_name ?? '—' }}</td>
                        <td>{{ $case->case_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ \App\Models\InspectionViolationCase::statusLabels()[$case->status] ?? $case->status }}</span>
                        </td>
                        <td class="small text-muted">{{ Str::limit($case->statement, 40) ?: '—' }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @can('view', $case)
                                    <a href="{{ route('admin.inspection-violation-cases.show', $case) }}" class="btn btn-sm btn-outline-info d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('update', $case)
                                    <a href="{{ route('admin.inspection-violation-cases.edit', $case) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('delete', $case)
                                    <form action="{{ route('admin.inspection-violation-cases.destroy', $case) }}" method="POST" class="d-inline delete-case-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذه القضية؟');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($cases->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $cases->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bi bi-folder2-open" style="font-size: 3rem; color: #ccc;"></i>
        <p class="text-muted mt-3">لا توجد قضايا مسجلة</p>
        <p class="text-muted small">يمكنك الاستعلام باسم مالك الوحدة أو المشغل أو وحدة التوليد للتحقق من حالة القضية. منح الموافقة متاح عند عدم وجود قضايا أو عند وجود قضايا منتهية/محكومة فقط.</p>
        @can('create', App\Models\InspectionViolationCase::class)
            <a href="{{ route('admin.inspection-violation-cases.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-lg me-1"></i>
                إضافة قضية
            </a>
        @endcan
    </div>
@endif
