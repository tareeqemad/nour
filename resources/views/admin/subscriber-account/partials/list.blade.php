@if($subscribers->count() > 0)
    @if($search)
        <p class="text-muted small mb-3">
            <i class="bi bi-search me-1"></i>
            نتائج البحث عن: "<strong>{{ $search }}</strong>"
            <span class="badge bg-primary ms-1">{{ $subscribers->total() }} نتيجة</span>
        </p>
    @endif

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold text-center" style="width:50px;">#</th>
                    <th class="fw-semibold">
                        <i class="bi bi-person me-1"></i>المشترك
                    </th>
                    <th class="fw-semibold text-center">رقم الاشتراك</th>
                    <th class="fw-semibold text-center">
                        <i class="bi bi-telephone me-1"></i>الجوال
                    </th>
                    <th class="fw-semibold text-center">
                        <i class="bi bi-speedometer2 me-1"></i>العداد
                    </th>
                    <th class="fw-semibold text-center">التصنيف</th>
                    <th class="fw-semibold text-center">الحالة</th>
                    <th class="fw-semibold text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscribers as $sub)
                    @php
                        $statusClass = match($sub->subscription_status) {
                            1 => 'bg-success',
                            2 => 'bg-warning text-dark',
                            3 => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">
                                {{ ($subscribers->currentPage() - 1) * $subscribers->perPage() + $loop->iteration }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $sub->subscriber_name }}</div>
                            <small class="text-muted">{{ $sub->subscriber_id_number ?? '' }}</small>
                        </td>
                        <td class="text-center">
                            <code class="text-primary fw-semibold">{{ $sub->subscription_number }}</code>
                        </td>
                        <td class="text-center">{{ $sub->phone ?? '—' }}</td>
                        <td class="text-center">
                            @if($sub->meter_number)
                                <span>{{ $sub->meter_number }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $sub->subscription_category_name }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $statusClass }}">{{ $sub->subscription_status_name }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.subscriber-account.show', $sub) }}"
                               class="btn btn-sm btn-outline-primary" title="عرض الحساب">
                                <i class="bi bi-person-lines-fill me-1"></i>
                                عرض الحساب
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($subscribers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $subscribers->links() }}
        </div>
    @endif

@elseif($search)
    <div class="text-center py-5 empty-state">
        <i class="bi bi-search" style="font-size:3rem;color:#ccc;"></i>
        <p class="text-muted mt-3">لا توجد نتائج تطابق "<strong>{{ $search }}</strong>"</p>
    </div>
@else
    <div class="text-center py-5 empty-state">
        <i class="bi bi-person-lines-fill" style="font-size:3rem;color:#ccc;"></i>
        <p class="text-muted mt-3">ابحث عن مشترك أو اختره من القائمة أعلاه لعرض سجله المالي</p>
    </div>
@endif
