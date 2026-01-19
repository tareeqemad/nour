@if($groupedLogs->isNotEmpty())
    @foreach($groupedLogs as $operatorId => $logs)
        @php
            $operator = $logs->first()->operator;
        @endphp
        
        @if($operator)
            {{-- Operator Header --}}
            <div class="log-generator-group mb-4">
                <div class="log-generator-header bg-light p-3 rounded-top border">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="log-generator-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-building fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">
                                    {{ $operator->name }}
                                    @if($operator->unit_number)
                                        <span class="badge bg-secondary ms-2">{{ $operator->unit_number }}</span>
                                    @endif
                                </h5>
                                @if($operator->address)
                                    <div class="text-muted small">
                                        {{ $operator->address }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-list-ul me-1"></i>
                            عدد السجلات: <strong>{{ $logs->count() }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Logs for this operator --}}
                <div class="log-list bg-white border border-top-0 rounded-bottom p-3">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>حالة شهادة السلامة</th>
                                    <th>تاريخ آخر زيارة</th>
                                    <th>الجهة المنفذة</th>
                                    <th>نتيجة التفتيش</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $compliance)
                                    <tr>
                                        <td>{{ $compliance->id }}</td>
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
                                        <td>{{ $compliance->inspection_result ?? '-' }}</td>
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
                </div>
            </div>
        @endif
    @endforeach

    {{-- Pagination for grouped view --}}
    @if(isset($complianceSafeties) && $complianceSafeties->hasPages())
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


