@if($generationUnits->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle table-striped">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold">
                        <i class="bi bi-hash me-1"></i>
                        كود الوحدة
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-lightning-charge me-1"></i>
                        اسم الوحدة
                    </th>
                    <th class="fw-semibold">
                        <i class="bi bi-building me-1"></i>
                        المشغل
                    </th>
                    <th class="text-center fw-semibold">
                        <i class="bi bi-gear-wide-connected me-1"></i>
                        عدد المولدات
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
                @foreach($generationUnits as $unit)
                    <tr>
                        <td>
                            <code class="text-primary fw-semibold">{{ $unit->unit_code }}</code>
                            @if($unit->qr_code_generated_at)
                                <span class="badge bg-success ms-2" title="تم توليد QR Code في {{ $unit->qr_code_generated_at->format('Y-m-d H:i') }}">
                                    <i class="bi bi-check-circle"></i> QR
                                </span>
                            @else
                                <span class="badge bg-secondary ms-2" title="لم يتم توليد QR Code بعد">
                                    <i class="bi bi-x-circle"></i> بدون QR
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-medium">{{ $unit->name }}</span>
                        </td>
                        <td>
                            <i class="bi bi-building text-muted me-1"></i>
                            <span>{{ $unit->operator->name }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                // العدد الفعلي للمولدات المرتبطة بالوحدة (من قاعدة البيانات - جدول generators)
                                $actualCount = $unit->actual_generators_count ?? $unit->generators()->count();
                                // العدد المطلوب/المدخل في حقل generators_count (من جدول generation_units)
                                $requiredCount = $unit->generators_count;
                            @endphp
                            <span class="badge {{ $actualCount >= $requiredCount ? 'bg-success' : 'bg-warning' }} px-3 py-2" title="العدد الفعلي: {{ $actualCount }} | المطلوب: {{ $requiredCount }}">
                                <i class="bi bi-gear-wide me-1"></i>
                                {{ $actualCount }} / {{ $requiredCount }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($unit->statusDetail)
                                <span class="badge {{ $unit->statusDetail->code === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                    <i class="bi bi-{{ $unit->statusDetail->code === 'ACTIVE' ? 'check-circle' : 'x-circle' }} me-1"></i>
                                    {{ $unit->statusDetail->label }}
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
                                @can('view', $unit)
                                    <a href="{{ route('admin.generation-units.show', $unit) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('generateQrCode', $unit)
                                    <a href="{{ route('admin.generation-units.qr-code', $unit) }}" target="_blank" class="btn btn-sm btn-success" title="طباعة QR Code">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                @endcan
                                @can('update', $unit)
                                    <a href="{{ route('admin.generation-units.edit', $unit) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('delete', $unit)
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-generation-unit-btn" 
                                            data-id="{{ $unit->id }}" 
                                            data-name="{{ $unit->name }}"
                                            title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endcan
                                @can('create', App\Models\Generator::class)
                                    <a href="{{ route('admin.generators.create', ['generation_unit_id' => $unit->id]) }}" class="btn btn-sm btn-success" title="إضافة مولد">
                                        <i class="bi bi-plus-circle"></i> مولد
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($generationUnits->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $generationUnits->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
        <p class="text-muted mt-3">لا توجد وحدات توليد.</p>
    </div>
@endif

