@if($subscribers->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold text-center" style="width: 60px;">
                        <i class="bi bi-list-ol me-1"></i>
                        #
                    </th>
                    <th class="fw-semibold">
                        رقم الاشتراك
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-person me-1"></i>
                        اسم المشترك
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-credit-card me-1"></i>
                        رقم الهوية
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-telephone me-1"></i>
                        الجوال
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-lightning-charge me-1"></i>
                        وحدات التوليد
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-funnel me-1"></i>
                        الحالة
                    </th>
                    <th class="text-end fw-semibold">
                        <i class="bi bi-gear me-1"></i>
                        الإجراءات
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscribers as $subscriber)
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ ($subscribers->currentPage() - 1) * $subscribers->perPage() + $loop->iteration }}</span>
                        </td>
                        <td>
                            <code class="text-primary fw-semibold">{{ $subscriber->subscription_number }}</code>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $subscriber->subscriber_name }}</span>
                        </td>
                        <td>
                            <span>{{ $subscriber->subscriber_id_number }}</span>
                        </td>
                        <td>
                            @if($subscriber->phone)
                                <i class="bi bi-telephone text-muted me-1"></i>
                                <span>{{ $subscriber->phone }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($subscriber->generationUnits->count() > 0)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($subscriber->generationUnits->take(2) as $unit)
                                        <span class="badge bg-info">
                                            {{ $unit->name }}
                                        </span>
                                    @endforeach
                                    @if($subscriber->generationUnits->count() > 2)
                                        <span class="badge bg-secondary">
                                            +{{ $subscriber->generationUnits->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $statusClass = match($subscriber->subscription_status) {
                                    1 => 'bg-success',
                                    2 => 'bg-warning',
                                    3 => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} px-3 py-2">
                                {{ $subscriber->subscription_status_name }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                @can('view', $subscriber)
                                    <a href="{{ route('admin.subscribers.show', $subscriber) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('update', $subscriber)
                                    <a href="{{ route('admin.subscribers.edit', $subscriber) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @if(auth()->user()->isSuperAdmin())
                                    <form action="{{ route('admin.subscribers.destroy', $subscriber) }}" method="POST" class="d-inline delete-subscriber-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($subscribers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $subscribers->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
        <p class="text-muted mt-3">لا توجد بيانات مشتركين</p>
        @can('create', App\Models\Subscriber::class)
            <a href="{{ route('admin.subscribers.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-lg me-1"></i>
                إضافة مشترك جديد
            </a>
        @endcan
    </div>
@endif

