@if($generators->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold">
                        <i class="bi bi-hash me-1"></i>
                        رقم المولد
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-lightning-charge me-1"></i>
                        اسم المولد
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-building me-1"></i>
                        المشغل
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-lightning-charge me-1"></i>
                        وحدة التوليد
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-speedometer2 me-1"></i>
                        القدرة (KVA)
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
        @foreach($generators as $generator)
                    <tr>
                        <td>
                            <code class="text-primary fw-semibold">{{ $generator->generator_number }}</code>
                                @if($generator->qr_code_generated_at)
                                <span class="badge bg-success ms-2 px-3 py-2" title="تم توليد QR Code في {{ $generator->qr_code_generated_at->format('Y-m-d H:i') }}">
                                        <i class="bi bi-check-circle"></i> QR
                                    </span>
                                @else
                                <span class="badge bg-secondary ms-2 px-3 py-2" title="لم يتم توليد QR Code بعد">
                                        <i class="bi bi-x-circle"></i> بدون QR
                                    </span>
                                @endif
                        </td>
                        <td>
                            <span class="fw-medium">{{ $generator->name }}</span>
                        </td>
                        <td>
                            <i class="bi bi-building text-muted me-1"></i>
                            <span>{{ $generator->operator->name ?? 'غير محدد' }}</span>
                        </td>
                        <td>
                            <i class="bi bi-lightning-charge text-muted me-1"></i>
                            <span>{{ $generator->generationUnit->name ?? 'غير محدد' }}</span>
                            @if($generator->generationUnit && $generator->generationUnit->unit_code)
                                <small class="text-muted">({{ $generator->generationUnit->unit_code }})</small>
                                @endif
                        </td>
                        <td class="text-center">
                                @if($generator->capacity_kva)
                                <span class="badge bg-info px-3 py-2">
                                    <i class="bi bi-speedometer2 me-1"></i>
                                    {{ number_format($generator->capacity_kva, 0) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                                @endif
                        </td>
                        <td class="text-center">
                            @if($generator->statusDetail)
                                <span class="badge {{ $generator->statusDetail->code === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                    <i class="bi bi-{{ $generator->statusDetail->code === 'ACTIVE' ? 'check-circle' : 'x-circle' }} me-1"></i>
                                    {{ $generator->statusDetail->label }}
                                </span>
                            @else
                                <span class="badge bg-secondary px-3 py-2">
                                    <i class="bi bi-question-circle me-1"></i>
                                    غير محدد
                                </span>
                                @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                @can('view', $generator)
                                    <a href="{{ route('admin.generators.show', $generator) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                        @can('view', $generator)
                            <a href="{{ route('admin.generators.qr-code', $generator) }}" target="_blank" class="btn btn-sm btn-success" title="طباعة QR Code">
                                <i class="bi bi-qr-code"></i>
                            </a>
                        @endcan
                        @can('update', $generator)
                            <a href="{{ route('admin.generators.edit', $generator) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endcan
                        @can('delete', $generator)
                            <button type="button" class="btn btn-sm btn-outline-danger gen-delete-btn" 
                                    data-generator-id="{{ $generator->id }}"
                                    data-generator-name="{{ $generator->name }}"
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

    @if($generators->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $generators->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
        <p class="text-muted mt-3">لا توجد مولدات.</p>
        @can('create', App\Models\Generator::class)
            <a href="{{ route('admin.generators.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>
                إضافة مولد جديد
            </a>
        @endcan
    </div>
@endif






