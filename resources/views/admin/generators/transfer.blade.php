@extends('layouts.admin')

@section('title', 'نقل المولد')

@php
    $breadcrumbTitle = 'نقل المولد';
    $breadcrumbParent = 'إدارة المولدات';
    $breadcrumbParentUrl = route('admin.generators.index');
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="'نقل: ' . $generator->name" icon="bi-arrow-left-right" :backRoute="route('admin.generators.show', $generator)">
                </x-admin.card-header-form>

                <div class="card-body">
                    {{-- معلومات المولد الحالية --}}
                    <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
                        <div class="row g-3">
                            @foreach([
                                ['label' => 'المولد', 'value' => $generator->name, 'icon' => 'bi-lightning-charge'],
                                ['label' => 'الرقم', 'value' => $generator->generator_number ?? '—', 'icon' => 'bi-hash'],
                                ['label' => 'المشغل الحالي', 'value' => $generator->operator->name ?? '—', 'icon' => 'bi-building'],
                                ['label' => 'وحدة التوليد', 'value' => $generator->generationUnit->name ?? '—', 'icon' => 'bi-diagram-3'],
                            ] as $field)
                            <div class="col-md-3 col-6">
                                <div style="font-size: 0.75rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">
                                    <i class="{{ $field['icon'] }} me-1" style="opacity: .6;"></i>{{ $field['label'] }}
                                </div>
                                <div style="font-size: 0.9rem; color: var(--color-text-main, #1F2937); font-weight: 600;">{{ $field['value'] }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- نموذج النقل --}}
                    <form action="{{ route('admin.generators.transfer.store', $generator) }}" method="POST" id="transferForm">
                        @csrf

                        <div class="invoice-section">
                            <div class="invoice-section-header">
                                <i class="bi bi-arrow-right-circle"></i>
                                تفاصيل النقل
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المشغل الهدف <span class="text-danger">*</span></label>
                                    <select name="target_operator_id" id="target_operator_id"
                                            class="form-select @error('target_operator_id') is-invalid @enderror" required>
                                        <option value="">اختر المشغل الهدف</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}" {{ old('target_operator_id') == $operator->id ? 'selected' : '' }}>
                                                {{ $operator->name }}@if($operator->owner_name) - {{ $operator->owner_name }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('target_operator_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">وحدة التوليد الهدف <span class="text-danger">*</span></label>
                                    <select name="target_generation_unit_id" id="target_generation_unit_id"
                                            class="form-select @error('target_generation_unit_id') is-invalid @enderror" required disabled>
                                        <option value="">اختر المشغل أولاً</option>
                                    </select>
                                    @error('target_generation_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">سبب النقل</label>
                                    <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                                              rows="2" maxlength="500" placeholder="اختياري — بيع، نقل ملكية، إعادة توزيع...">{{ old('reason') }}</textarea>
                                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div style="background: var(--color-warning-bg, #FFFBEB); border: 1px solid var(--color-warning-border, #FCD34D); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: var(--color-warning-text, #B45309);">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>تنبيه:</strong> هذه العملية لا يمكن التراجع عنها. السجلات القديمة تبقى مرتبطة بالمشغل السابق.
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-3 pt-3" style="border-top: 1px solid var(--color-border-soft, #EDF1F5);">
                            <a href="{{ route('admin.generators.show', $generator) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-danger" id="transferBtn">
                                <i class="bi bi-arrow-left-right me-1"></i>تأكيد النقل
                            </button>
                        </div>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const $opSelect = $('#target_operator_id');
    const $unitSelect = $('#target_generation_unit_id');

    $opSelect.on('change', function() {
        const opId = $(this).val();
        if (!opId) {
            $unitSelect.html('<option value="">اختر المشغل أولاً</option>').prop('disabled', true);
            return;
        }
        $unitSelect.html('<option value="">جاري التحميل...</option>').prop('disabled', true);

        $.ajax({
            url: `{{ route('admin.generators.transfer.generation-units', ['generator' => $generator->id, 'operator' => ':id']) }}`.replace(':id', opId),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(data) {
                if (data.success && data.generation_units && data.generation_units.length) {
                    let html = '<option value="">اختر وحدة التوليد</option>';
                    data.generation_units.forEach(u => { html += `<option value="${u.id}">${u.name}${u.unit_code ? ' ('+u.unit_code+')' : ''}</option>`; });
                    $unitSelect.html(html).prop('disabled', false);
                } else {
                    $unitSelect.html('<option value="">لا توجد وحدات متاحة</option>');
                    if (window.adminNotifications) window.adminNotifications.warning('لا توجد وحدات توليد لهذا المشغل');
                }
            },
            error: function() {
                $unitSelect.html('<option value="">خطأ في التحميل</option>');
            }
        });
    });

    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'تأكيد النقل',
            html: 'هل أنت متأكد من نقل المولد؟<br><small style="color: var(--color-danger-text, #B91C1C);">لا يمكن التراجع عن هذه العملية</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#D1D5DB',
            confirmButtonText: '<i class="bi bi-arrow-left-right me-1"></i>نقل',
            cancelButtonText: 'إلغاء',
            reverseButtons: true,
        }).then((result) => { if (result.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
@endsection
