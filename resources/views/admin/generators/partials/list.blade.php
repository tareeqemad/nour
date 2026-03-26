@if($generators->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 general-table">
            <thead>
                <tr>
                    <th>رقم المولد</th>
                    <th>الاسم</th>
                    <th>المشغل</th>
                    <th>وحدة التوليد</th>
                    <th class="text-center">القدرة</th>
                    <th class="text-center">الحالة</th>
                    <th class="text-center" style="min-width: 140px;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($generators as $generator)
                <tr>
                    <td>
                        <code style="background: var(--badge-primary-bg, #EEF2FF); color: var(--badge-primary-text, #24308F); padding: 0.2rem 0.5rem; border-radius: 5px; font-weight: 600;">{{ $generator->generator_number }}</code>
                        @if($generator->qr_code_generated_at)
                            <span class="badge-status-active ms-1" style="font-size: 0.7rem;">QR</span>
                        @else
                            <span class="badge-neutral ms-1" style="font-size: 0.7rem;">بدون QR</span>
                        @endif
                    </td>
                    <td style="font-weight: 600; color: var(--color-text-main, #1F2937);">{{ $generator->name }}</td>
                    <td style="color: var(--color-text-secondary, #3B4863);">{{ $generator->operator->name ?? '—' }}</td>
                    <td style="color: var(--color-text-secondary, #3B4863);">
                        {{ $generator->generationUnit->name ?? '—' }}
                        @if($generator->generationUnit?->unit_code)
                            <small style="color: var(--color-text-muted, #5B6780);">({{ $generator->generationUnit->unit_code }})</small>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($generator->capacity_kva)
                            <span class="badge-neutral">{{ number_format($generator->capacity_kva, 0) }} KVA</span>
                        @else
                            <span style="color: var(--color-text-light, #98A2B3);">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($generator->statusDetail)
                            <span class="{{ $generator->statusDetail->code === 'ACTIVE' ? 'badge-status-active' : 'badge-neutral' }}">
                                {{ $generator->statusDetail->label }}
                            </span>
                        @else
                            <span class="badge-neutral">غير محدد</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            @can('view', $generator)
                                <a href="{{ route('admin.generators.show', $generator) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endcan
                            @can('update', $generator)
                                <a href="{{ route('admin.generators.edit', $generator) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan
                            @can('view', $generator)
                                <a href="{{ route('admin.generators.qr-code', $generator) }}" target="_blank" class="btn btn-sm btn-outline-success" title="QR">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                            @endcan
                            @can('delete', $generator)
                                <button type="button" class="btn btn-sm btn-outline-danger gen-delete-btn"
                                        data-generator-id="{{ $generator->id }}" data-generator-name="{{ $generator->name }}" title="حذف">
                                    <i class="bi bi-trash3"></i>
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
        <div class="d-flex justify-content-center mt-3">
            {{ $generators->links() }}
        </div>
    @endif
@else
    <div class="text-center py-5" style="color: var(--color-text-muted, #5B6780);">
        <i class="bi bi-lightning-charge" style="font-size: 2.5rem; opacity: .4;"></i>
        <p class="mt-2 mb-0">لا توجد مولدات</p>
    </div>
@endif
