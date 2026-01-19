@extends('layouts.admin')

@section('title', 'المشغلين الذين يحتاجون إلى اعتماد')
@php
    $breadcrumbTitle = 'المشغلين الذين يحتاجون إلى اعتماد';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/operators.css') }}">
@endpush

@section('content')
<div class="operators-page">
    <div class="general-page" id="operatorsPendingApprovalPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-hourglass-split me-2 text-warning"></i>
                                المشغلين الذين يحتاجون إلى اعتماد
                            </h5>
                            <div class="general-subtitle">
                                قائمة المشغلين الذين ينتظرون اعتماد من سلطة الطاقة.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.operators.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>
                                جميع المشغلين
                            </a>
                        </div>
                    </div>

                    <div class="card-body pb-4">
                        {{-- كارد واحد للفلاتر --}}
                        <div class="filter-card">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <i class="bi bi-funnel me-2"></i>
                                    فلاتر البحث
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building me-1"></i>
                                            اسم المشغل
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="filterName" 
                                               name="name" 
                                               value="{{ request('name') }}" 
                                               placeholder="ابحث بالاسم...">
                                    </div>

                                    <div class="col-lg-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-toggle-on me-1"></i>
                                            الحالة
                                        </label>
                                        <select class="form-select" id="filterStatus" name="status">
                                            <option value="">جميع الحالات</option>
                                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>فعّال</option>
                                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير فعّال</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-5 d-flex align-items-end gap-2">
                                        <button type="button" class="btn btn-primary" id="applyFilters">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            إعادة تعيين
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- قائمة المشغلين --}}
                        <div id="operatorsListContainer">
                            @include('admin.operators.partials.pending-list')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const applyFiltersBtn = document.getElementById('applyFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const filterName = document.getElementById('filterName');
    const filterStatus = document.getElementById('filterStatus');
    const operatorsListContainer = document.getElementById('operatorsListContainer');

    // تطبيق الفلاتر
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (filterName.value.trim()) {
            params.append('name', filterName.value.trim());
        }
        
        if (filterStatus.value) {
            params.append('status', filterStatus.value);
        }

        const url = '{{ route("admin.operators.pending-approval") }}' + (params.toString() ? '?' + params.toString() : '');
        
        // تحديث URL بدون إعادة تحميل الصفحة
        window.history.pushState({}, '', url);

        // AJAX request
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.html) {
                operatorsListContainer.innerHTML = data.html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback: reload page
            window.location.href = url;
        });
    }

    // إعادة تعيين الفلاتر
    function resetFilters() {
        filterName.value = '';
        filterStatus.value = '';
        window.location.href = '{{ route("admin.operators.pending-approval") }}';
    }

    applyFiltersBtn.addEventListener('click', applyFilters);
    resetFiltersBtn.addEventListener('click', resetFilters);

    // Enter key في حقل البحث
    filterName.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    // معالجة الاعتماد
    document.addEventListener('click', function(e) {
        // منع إغلاق dropdown عند النقر على عنصر داخله
        if (e.target.closest('.dropdown-menu')) {
            e.stopPropagation();
        }

        if (e.target.closest('.approve-operator-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.closest('.approve-operator-btn');
            const operatorId = btn.dataset.operatorId;
            const operatorName = btn.dataset.operatorName || 'المشغل';

            if (confirm(`هل أنت متأكد من اعتماد "${operatorName}"؟`)) {
                // إغلاق dropdown
                const dropdown = btn.closest('.btn-group');
                if (dropdown) {
                    const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.querySelector('.dropdown-toggle'));
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/operators/${operatorId}/toggle-approval`;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // معالجة حظر/إلغاء حظر المشغل
        if (e.target.closest('.toggle-status-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.closest('.toggle-status-btn');
            const operatorId = btn.dataset.operatorId;
            const operatorName = btn.dataset.operatorName || 'المشغل';
            const currentStatus = btn.dataset.currentStatus;
            const employeesCount = parseInt(btn.dataset.employeesCount || 0);
            const action = currentStatus === 'active' ? 'حظر' : 'إلغاء حظر';
            
            let message = `هل أنت متأكد من ${action} "${operatorName}"؟\n\n`;
            if (currentStatus === 'active') {
                message += `⚠️ تحذير: سيتم إيقاف المشغل وجميع الموظفين التابعين له (${employeesCount} موظف).\n\n`;
                message += `هذا سيؤثر على:\n`;
                message += `- المشغل (${operatorName})\n`;
                message += `- جميع الموظفين التابعين له (${employeesCount} موظف)\n\n`;
                message += `لن يتمكنوا من الوصول للنظام حتى يتم إلغاء الحظر.`;
            } else {
                message += `سيتم تفعيل المشغل وجميع الموظفين التابعين له (${employeesCount} موظف).`;
            }

            if (confirm(message)) {
                // إغلاق dropdown
                const dropdown = btn.closest('.btn-group');
                if (dropdown) {
                    const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.querySelector('.dropdown-toggle'));
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/operators/${operatorId}/toggle-status`;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    });
});
</script>
@endpush
