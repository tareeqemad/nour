@extends('layouts.admin')

@section('title', 'ملف المشغل')
@php
    $breadcrumbTitle = 'ملف المشغل';
@endphp

@push('styles')
<style>
    .section-divider-container {
        padding: 2rem 0;
        position: relative;
    }

    .section-divider {
        margin: 0;
        border: none;
        border-top: 2px solid transparent;
        background: linear-gradient(90deg, transparent 0%, rgba(59, 130, 246, 0.2) 30%, rgba(59, 130, 246, 0.4) 50%, rgba(59, 130, 246, 0.2) 70%, transparent 100%);
        height: 2px;
        position: relative;
        overflow: visible;
    }

    .section-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 16px;
        height: 16px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 2px solid #3b82f6;
        border-radius: 50%;
        z-index: 1;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }

    .section-divider::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: #3b82f6;
        border-radius: 50%;
        z-index: 2;
    }

    @media (max-width: 768px) {
        .section-divider-container {
            padding: 1.5rem 0;
        }

        .section-divider::before {
            width: 14px;
            height: 14px;
        }

        .section-divider::after {
            width: 6px;
            height: 6px;
        }
    }
</style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card position-relative" id="profileCard">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-ui-checks-grid me-2"></i>
                            بيانات المشغل
                        </h5>
                        <div class="general-subtitle d-flex align-items-center gap-2 flex-wrap">
                            <span>ملف المشغل</span>
                            @if($operator && $operator->is_approved !== null)
                                <span>|</span>
                                <span class="badge {{ $operator->is_approved ? 'bg-success' : 'bg-warning' }}">
                                    <i class="bi bi-{{ $operator->is_approved ? 'check-circle' : 'clock' }} me-1"></i>
                                    {{ $operator->is_approved ? 'معتمد' : 'في انتظار الاعتماد' }}
                                </span>
                            @elseif(!$operator)
                                <span>|</span>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-info-circle me-1"></i>
                                    لم يتم إنشاء المشغل بعد
                                </span>
                            @endif
                        </div>
                    </div>

                    <button class="btn btn-primary" id="saveProfileBtn" type="button">
                        <i class="bi bi-save me-1"></i>
                        حفظ
                    </button>
                </div>

                <div class="card-body">
                    <form id="operatorProfileForm" action="{{ route('admin.operators.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- بيانات المالك - أولاً --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">اسم المالك <span class="text-danger">*</span></label>
                                <input type="text" name="owner_name" id="owner_name" class="form-control"
                                       value="{{ old('owner_name', $operator->owner_name ?? '') }}"
                                       placeholder="اسم المالك"
                                       required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">رقم هوية المالك <span class="text-danger">*</span></label>
                                <input type="text" name="owner_id_number" id="owner_id_number" class="form-control"
                                       value="{{ old('owner_id_number', $operator->owner_id_number ?? '') }}"
                                       placeholder="رقم هوية المالك (9 أرقام)"
                                       maxlength="9"
                                       pattern="[0-9]{9}"
                                       required>
                                <small class="form-text text-muted">يجب أن يكون 9 أرقام فقط</small>
                            </div>
                            
                            {{-- بيانات المشغل - ثانياً --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">اسم المشغل <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                       value="{{ old('name', $operator->name ?? '') }}"
                                       placeholder="مثال: مشغل الطاقة النظيفة"
                                       required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">رقم هوية المشغل <span class="text-danger">*</span></label>
                                <input type="text" name="operator_id_number" id="operator_id_number" class="form-control"
                                       value="{{ old('operator_id_number', $operator->operator_id_number ?? '') }}"
                                       placeholder="رقم هوية المشغل (9 أرقام)"
                                       maxlength="9"
                                       pattern="[0-9]{9}"
                                       required>
                                <small class="form-text text-muted">يجب أن يكون 9 أرقام فقط</small>
                            </div>
                        </div>

                        <button type="submit" class="d-none" id="hiddenSubmitBtn"></button>
                    </form>
                </div>
            </div>

                <div class="op-loading d-none" id="profileLoading">
                    <div class="text-center">
                        <div class="spinner-border" role="status"></div>
                        <div class="mt-2 text-muted fw-semibold">جاري الحفظ...</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Divider between sections --}}
        <div class="col-12">
            <div class="section-divider-container">
                <hr class="section-divider">
            </div>
        </div>

        {{-- قسم وحدات التوليد --}}
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-lightning-charge me-2"></i>
                            وحدات التوليد
                        </h5>
                        <div class="general-subtitle">إدارة وحدات التوليد والمولدات التابعة لها</div>
                    </div>
                    @can('create', App\Models\GenerationUnit::class)
                        @php
                            $canAddUnit = $operator && 
                                         !empty($operator->name) && 
                                         !empty($operator->owner_name) && 
                                         !empty($operator->owner_id_number) && 
                                         !empty($operator->operator_id_number);
                        @endphp
                        @if($canAddUnit)
                            <a href="{{ route('admin.generation-units.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة وحدة توليد
                            </a>
                        @else
                            <button type="button" class="btn btn-primary" disabled title="يرجى إكمال بيانات المشغل أولاً">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة وحدة توليد
                            </button>
                        @endif
                    @endcan
                </div>
                <div class="card-body">
                    @if($generationUnits->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">لا توجد وحدات توليد. ابدأ بإضافة وحدة توليد جديدة.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>كود الوحدة</th>
                                        <th>اسم الوحدة</th>
                                        <th class="text-center">عدد المولدات المطلوبة</th>
                                        <th class="text-center">عدد المولدات الفعلي</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="text-end">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($generationUnits as $unit)
                                        <tr>
                                            <td>
                                                <code class="text-primary">{{ $unit->unit_code }}</code>
                                            </td>
                                            <td>{{ $unit->name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $unit->generators_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $actualCount = $unit->generators()->count();
                                                    $requiredCount = $unit->generators_count;
                                                @endphp
                                                <span class="badge {{ $actualCount >= $requiredCount ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $actualCount }} / {{ $requiredCount }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($unit->statusDetail)
                                                    <span class="badge {{ $unit->statusDetail->code === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $unit->statusDetail->label }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">غير محدد</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    @can('view', $unit)
                                                        <a href="{{ route('admin.generation-units.show', $unit) }}" class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    @endcan
                                                    @can('update', $unit)
                                                        <a href="{{ route('admin.generation-units.edit', $unit) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        alert(msg);
    }

    const form = document.getElementById('operatorProfileForm');
    const saveBtn = document.getElementById('saveProfileBtn');
    const loading = document.getElementById('profileLoading');

    // ====== منع إدخال غير الأرقام وحصر الإدخال بـ 9 أرقام فقط ======
    function setupIdNumberInputs() {
        const idInputs = ['owner_id_number', 'operator_id_number'];
        
        idInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                // منع إدخال غير الأرقام
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^0-9]/g, ''); // إزالة أي شيء غير الأرقام
                    if (value.length > 9) {
                        value = value.substring(0, 9); // حصر الإدخال بـ 9 أرقام
                    }
                    e.target.value = value;
                });

                // منع النسخ/اللصق لمحتوى غير صحيح
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const numbers = paste.replace(/[^0-9]/g, '').substring(0, 9);
                    e.target.value = numbers;
                });

                // منع إدخال المفاتيح غير الرقمية (مع السماح بالـ navigation keys)
                input.addEventListener('keydown', function(e) {
                    // السماح بـ: Backspace, Delete, Tab, Escape, Enter, Arrow keys
                    if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                        // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                        (e.keyCode === 65 && e.ctrlKey === true) ||
                        (e.keyCode === 67 && e.ctrlKey === true) ||
                        (e.keyCode === 86 && e.ctrlKey === true) ||
                        (e.keyCode === 88 && e.ctrlKey === true) ||
                        // Allow home, end
                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                        return;
                    }
                    // التأكد من أن المفت المضغوط هو رقم
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });
            }
        });
    }

    // تهيئة حقول أرقام الهوية عند تحميل الصفحة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupIdNumberInputs);
    } else {
        setupIdNumberInputs();
    }

    function setLoading(on) {
        loading.classList.toggle('d-none', !on);
        saveBtn.disabled = on;
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }

    function showErrors(errors) {
        const firstField = Object.keys(errors || {})[0];
        if (firstField) {
            const input = form.querySelector(`[name="${CSS.escape(firstField)}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = errors[firstField][0];
                input.insertAdjacentElement('afterend', div);
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        Object.keys(errors || {}).forEach(field => {
            const input = form.querySelector(`[name="${CSS.escape(field)}"]`);
            if (!input) return;
            input.classList.add('is-invalid');
            if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) return;
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = errors[field][0];
            input.insertAdjacentElement('afterend', div);
        });
    }

    // ====== AJAX submit ======
    async function submitProfile() {
        clearErrors();
        setLoading(true);

        try {
            const fd = new FormData(form);

            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            });

            const data = await res.json();

            if (res.status === 422) {
                showErrors(data.errors || {});
                notify('error', 'تحقق من الحقول المطلوبة');
                return;
            }

            if (data && data.success) {
                notify('success', data.message || 'تم الحفظ');
                
                // تحديث بيانات المشغل
                if (data.operator) {
                    if (data.operator.name) {
                        document.getElementById('name').value = data.operator.name || '';
                    }
                    if (data.operator.owner_name) {
                        document.getElementById('owner_name').value = data.operator.owner_name || '';
                    }
                    if (data.operator.owner_id_number) {
                        document.getElementById('owner_id_number').value = data.operator.owner_id_number || '';
                    }
                    if (data.operator.operator_id_number) {
                        document.getElementById('operator_id_number').value = data.operator.operator_id_number || '';
                    }
                    
                    // تحديث زر "إضافة وحدة توليد" بناءً على اكتمال البيانات
                    updateAddUnitButton();
                }
                
                // تحديث زر "إضافة وحدة توليد" بناءً على اكتمال البيانات
                updateAddUnitButton();
                
                // إعادة تحميل الصفحة بعد ثانية واحدة لتحديث جميع البيانات
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                notify('error', (data && data.message) ? data.message : 'فشل الحفظ');
            }

        } catch (e) {
            notify('error', 'حدث خطأ أثناء الحفظ');
        } finally {
            setLoading(false);
        }
    }

    saveBtn.addEventListener('click', submitProfile);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitProfile();
    });

    // ====== تحديث زر "إضافة وحدة توليد" بناءً على اكتمال البيانات ======
    function updateAddUnitButton() {
        const name = document.getElementById('name')?.value?.trim() || '';
        const ownerName = document.getElementById('owner_name')?.value?.trim() || '';
        const ownerIdNumber = document.getElementById('owner_id_number')?.value?.trim() || '';
        const operatorIdNumber = document.getElementById('operator_id_number')?.value?.trim() || '';
        
        const canAddUnit = name && ownerName && ownerIdNumber && operatorIdNumber;
        const addUnitBtn = document.querySelector('a[href*="generation-units.create"], button[data-add-unit]');
        
        if (addUnitBtn) {
            if (canAddUnit) {
                if (addUnitBtn.tagName === 'BUTTON') {
                    // تحويل button إلى link
                    const link = document.createElement('a');
                    link.href = '{{ route("admin.generation-units.create") }}';
                    link.className = 'btn btn-primary';
                    link.innerHTML = '<i class="bi bi-plus-lg me-1"></i> إضافة وحدة توليد';
                    addUnitBtn.parentNode.replaceChild(link, addUnitBtn);
                } else {
                    addUnitBtn.classList.remove('disabled');
                    addUnitBtn.removeAttribute('disabled');
                    addUnitBtn.removeAttribute('title');
                }
            } else {
                if (addUnitBtn.tagName === 'A') {
                    // تحويل link إلى button disabled
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-primary';
                    btn.disabled = true;
                    btn.setAttribute('title', 'يرجى إكمال بيانات المشغل أولاً');
                    btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> إضافة وحدة توليد';
                    addUnitBtn.parentNode.replaceChild(btn, addUnitBtn);
                } else {
                    addUnitBtn.disabled = true;
                    addUnitBtn.setAttribute('title', 'يرجى إكمال بيانات المشغل أولاً');
                }
            }
        }
    }

    // تحديث الزر عند تغيير أي حقل
    ['name', 'owner_name', 'owner_id_number', 'operator_id_number'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updateAddUnitButton);
            field.addEventListener('change', updateAddUnitButton);
        }
    });

    // تحديث الزر عند تحميل الصفحة
    updateAddUnitButton();

})();

// إدارة وحدات التوليد
(function() {
    const btnAddUnit = document.getElementById('btnAddGenerationUnit');
    if (btnAddUnit) {
        btnAddUnit.addEventListener('click', function() {
            // TODO: فتح modal لإضافة وحدة توليد جديدة
            // سيتم إضافتها لاحقاً عند إنشاء Controller وViews لوحدات التوليد
            alert('سيتم إضافة وظيفة إضافة وحدة توليد قريباً');
        });
    }
})();
</script>
@endpush

