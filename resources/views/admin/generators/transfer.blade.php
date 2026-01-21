@extends('layouts.admin')

@section('title', 'نقل المولد')

@php
    $breadcrumbTitle = 'نقل المولد';
    $breadcrumbParent = 'إدارة المولدات';
    $breadcrumbParentUrl = route('admin.generators.index');
@endphp

@section('content')
<div class="general-page">
    <div class="row">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-arrow-left-right me-2"></i>
                            نقل المولد
                        </h5>
                        <div class="general-subtitle">
                            نقل المولد "{{ $generator->name }}" من مشغل لآخر
                        </div>
                    </div>
                </div>
                <div class="general-card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-building me-2"></i>
                                        المشغل الحالي
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-tag text-primary"></i>
                                            اسم المشغل
                                        </div>
                                        <div class="info-value">{{ $generator->operator->name ?? 'غير محدد' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-lightning-charge text-primary"></i>
                                            وحدة التوليد الحالية
                                        </div>
                                        <div class="info-value">{{ $generator->generationUnit->name ?? 'غير محدد' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-success mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-gear me-2"></i>
                                        معلومات المولد
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-tag text-success"></i>
                                            اسم المولد
                                        </div>
                                        <div class="info-value">{{ $generator->name }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="bi bi-hash text-success"></i>
                                            رقم المولد
                                        </div>
                                        <div class="info-value">{{ $generator->generator_number ?? 'غير محدد' }}</div>
                                    </div>
                                    @if($generator->capacity_kva)
                                        <div class="info-item">
                                            <div class="info-label">
                                                <i class="bi bi-lightning text-success"></i>
                                                السعة
                                            </div>
                                            <div class="info-value">{{ $generator->capacity_kva }} kVA</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.generators.transfer.store', $generator) }}" method="POST">
                        @csrf
                        
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-arrow-right-circle me-2"></i>
                                    تفاصيل النقل
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="target_operator_id" class="form-label fw-semibold">
                                        المشغل الهدف <span class="text-danger">*</span>
                                    </label>
                                    <select name="target_operator_id" id="target_operator_id" 
                                            class="form-select @error('target_operator_id') is-invalid @enderror" required>
                                        <option value="">-- اختر المشغل الهدف --</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->id }}" {{ old('target_operator_id') == $operator->id ? 'selected' : '' }}>
                                                {{ $operator->name }} 
                                                @if($operator->owner_name)
                                                    - {{ $operator->owner_name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('target_operator_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        اختر المشغل الذي تريد نقل المولد إليه
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="target_generation_unit_id" class="form-label fw-semibold">
                                        وحدة التوليد الهدف <span class="text-danger">*</span>
                                    </label>
                                    <select name="target_generation_unit_id" id="target_generation_unit_id" 
                                            class="form-select @error('target_generation_unit_id') is-invalid @enderror" required disabled>
                                        <option value="">-- اختر المشغل أولاً --</option>
                                    </select>
                                    @error('target_generation_unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        سيتم تحديث القائمة تلقائياً بعد اختيار المشغل الهدف
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="reason" class="form-label fw-semibold">
                                        سبب النقل (اختياري)
                                    </label>
                                    <textarea name="reason" id="reason" 
                                              class="form-control @error('reason') is-invalid @enderror" 
                                              rows="3" 
                                              maxlength="500"
                                              placeholder="أدخل سبب نقل المولد (مثال: بيع المولد، نقل الملكية، إعادة التوزيع...)">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        سيتم تسجيل سبب النقل في سجل التدقيق
                                    </small>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>ملاحظة مهمة:</strong> عند نقل المولد، ستكون السجلات القديمة (سجلات التشغيل والصيانة والسلامة) مرتبطة بالمشغل القديم وتبقى محفوظة في سجلاته التاريخية. السجلات الجديدة بعد النقل ستكون مرتبطة بالمشغل الجديد.
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>تحذير:</strong> هذه العملية لا يمكن التراجع عنها. سيتم نقل المولد إلى المشغل ووحدة التوليد المحددين.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('admin.generators.show', $generator) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-warning" onclick="return confirm('هل أنت متأكد من نقل المولد إلى المشغل ووحدة التوليد المحددين؟')">
                                <i class="bi bi-arrow-left-right me-1"></i>
                                تأكيد النقل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const operatorSelect = document.getElementById('target_operator_id');
        const generationUnitSelect = document.getElementById('target_generation_unit_id');
        const generatorId = {{ $generator->id }};

        operatorSelect.addEventListener('change', function() {
            const operatorId = this.value;
            
            if (!operatorId) {
                generationUnitSelect.innerHTML = '<option value="">-- اختر المشغل أولاً --</option>';
                generationUnitSelect.disabled = true;
                return;
            }

            // Show loading
            generationUnitSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            generationUnitSelect.disabled = true;

            // Fetch generation units for selected operator
            fetch(`{{ route('admin.generators.transfer.generation-units', ['generator' => $generator->id, 'operator' => ':operatorId']) }}`.replace(':operatorId', operatorId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.generation_units) {
                    generationUnitSelect.innerHTML = '<option value="">-- اختر وحدة التوليد --</option>';
                    data.generation_units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.id;
                        option.textContent = unit.name + (unit.unit_code ? ' (' + unit.unit_code + ')' : '');
                        generationUnitSelect.appendChild(option);
                    });
                    generationUnitSelect.disabled = false;
                } else {
                    generationUnitSelect.innerHTML = '<option value="">لا توجد وحدات توليد متاحة</option>';
                    generationUnitSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error loading generation units:', error);
                generationUnitSelect.innerHTML = '<option value="">حدث خطأ أثناء التحميل</option>';
                generationUnitSelect.disabled = true;
            });
        });
    });
</script>
@endpush
@endsection
