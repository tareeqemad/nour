@if($generationUnits->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 general-table">
            <thead>
                <tr>
                    <th>كود الوحدة</th>
                    <th>اسم الوحدة</th>
                    <th>المشغل</th>
                    <th class="text-center">المولدات</th>
                    <th class="text-center">الحالة</th>
                    <th class="text-center" style="min-width: 160px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($generationUnits as $unit)
                    @php
                        $actualCount = $unit->actual_generators_count ?? $unit->generators()->count();
                        $requiredCount = $unit->generators_count;
                    @endphp
                    <tr>
                        <td>
                            <code style="background: var(--badge-primary-bg, #EEF2FF); color: var(--badge-primary-text, #24308F); padding: 0.2rem 0.5rem; border-radius: 5px; font-weight: 600;">{{ $unit->unit_code }}</code>
                            @if($unit->qr_code_generated_at)
                                <span class="badge-status-active ms-1" style="font-size: 0.7rem;">QR</span>
                            @else
                                <span class="badge-neutral ms-1" style="font-size: 0.7rem;">بدون QR</span>
                            @endif
                        </td>
                        <td style="font-weight: 600; color: var(--color-text-main, #1F2937);">{{ $unit->name }}</td>
                        <td style="color: var(--color-text-secondary, #3B4863);">{{ $unit->operator->name }}</td>
                        <td class="text-center">
                            <span class="{{ $actualCount >= $requiredCount ? 'badge-permissions-count' : 'badge-warning' }}">
                                {{ $actualCount }} / {{ $requiredCount }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($unit->statusDetail)
                                <span class="{{ $unit->statusDetail->code === 'ACTIVE' ? 'badge-status-active' : 'badge-neutral' }}">
                                    {{ $unit->statusDetail->label }}
                                </span>
                            @else
                                <span class="badge-neutral">غير محدد</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                @can('view', $unit)
                                    <a href="{{ route('admin.generation-units.show', $unit) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endcan
                                @can('update', $unit)
                                    <a href="{{ route('admin.generation-units.edit', $unit) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('generateQrCode', $unit)
                                    <a href="{{ route('admin.generation-units.qr-code', $unit) }}" target="_blank" class="btn btn-sm btn-outline-success" title="QR Code">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                @endcan
                                @can('delete', $unit)
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-generation-unit-btn"
                                            data-id="{{ $unit->id }}" data-name="{{ $unit->name }}" title="حذف">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                @endcan
                                @can('create', App\Models\Generator::class)
                                    <a href="{{ route('admin.generators.create', ['generation_unit_id' => $unit->id]) }}" class="btn btn-sm btn-primary" title="إضافة مولد">
                                        <i class="bi bi-plus-lg"></i>
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
        <div class="d-flex justify-content-center mt-3">
            {{ $generationUnits->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5" style="color: var(--color-text-muted, #5B6780);">
        <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: .4;"></i>
        <p class="mt-2 mb-0">لا توجد وحدات توليد</p>
    </div>
@endif
