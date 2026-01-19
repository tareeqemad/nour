@extends('layouts.admin')

@section('title', 'المهام')

@php
    $breadcrumbTitle = 'المهام';
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $isAdmin = auth()->user()->isAdmin();
    $isEnergyAuthority = auth()->user()->isEnergyAuthority();
    $canCreate = $isSuperAdmin || $isAdmin || $isEnergyAuthority;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <style>
        .badge-type-maintenance {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-type-safety_inspection {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-status-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
@endpush

@section('content')
<div class="general-page" id="tasksPage" data-index-url="{{ route('admin.tasks.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-clipboard-check me-2"></i>
                            إدارة المهام
                        </h5>
                        <div class="general-subtitle">
                            إدارة مهام الصيانة وفحص السلامة. العدد: <span id="tasksCount">{{ $tasks->total() }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @if($canCreate)
                            <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                تكليف مهمة جديدة
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body pb-4">
                    @if(auth()->user()->isTechnician() || auth()->user()->isCivilDefense())
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            أنت ترى فقط المهام المكلف بها.
                        </div>
                    @endif

                    {{-- Statistics Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">إجمالي</h6>
                                    <h4 class="mb-0 text-primary" id="statTotal">{{ number_format($stats['total'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">قيد الانتظار</h6>
                                    <h4 class="mb-0 text-warning" id="statPending">{{ number_format($stats['pending'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">قيد التنفيذ</h6>
                                    <h4 class="mb-0 text-info" id="statInProgress">{{ number_format($stats['in_progress'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">مكتملة</h6>
                                    <h4 class="mb-0 text-success" id="statCompleted">{{ number_format($stats['completed'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filter Card --}}
                    <div class="filter-card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <i class="bi bi-funnel me-2"></i>
                                فلاتر البحث
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- البحث --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-search me-1"></i>
                                        البحث
                                    </label>
                                    <input
                                        type="text"
                                        id="searchInput"
                                        class="form-control"
                                        placeholder="ابحث عن مهمة..."
                                        value="{{ request('search', '') }}"
                                    >
                                </div>

                                {{-- النوع --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tag me-1"></i>
                                        نوع المهمة
                                    </label>
                                    <select id="typeFilter" class="form-select">
                                        <option value="">كل الأنواع</option>
                                        <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>صيانة</option>
                                        <option value="safety_inspection" {{ request('type') == 'safety_inspection' ? 'selected' : '' }}>فحص سلامة</option>
                                    </select>
                                </div>

                                {{-- الحالة --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-flag me-1"></i>
                                        الحالة
                                    </label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">كل الحالات</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                                    </select>
                                </div>

                                {{-- المكلف (SuperAdmin, Admin, EnergyAuthority فقط) --}}
                                @if($canCreate && isset($technicians) && isset($civilDefense))
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person me-1"></i>
                                            المكلف
                                        </label>
                                        <select id="assignedToFilter" class="form-select">
                                            <option value="">كل المكلفين</option>
                                            <optgroup label="فنيون">
                                                @foreach($technicians as $tech)
                                                    <option value="{{ $tech->id }}" {{ request('assigned_to') == $tech->id ? 'selected' : '' }}>
                                                        {{ $tech->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="دفاع مدني">
                                                @foreach($civilDefense as $cd)
                                                    <option value="{{ $cd->id }}" {{ request('assigned_to') == $cd->id ? 'selected' : '' }}>
                                                        {{ $cd->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>
                                @endif

                                {{-- أزرار البحث --}}
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button class="btn btn-primary flex-fill" type="button" id="searchBtn" title="بحث">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <button
                                            class="btn btn-outline-secondary flex-fill {{ request('search') || request('type') || request('status') || request('assigned_to') ? '' : 'd-none' }}"
                                            type="button"
                                            id="clearSearchBtn"
                                            title="تفريغ"
                                        >
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>نوع المهمة</th>
                                    <th>المكلف</th>
                                    <th>المشغل</th>
                                    <th>وحدة التوليد</th>
                                    <th>المولد</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الاستحقاق</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="tasksTableBody">
                                @include('admin.tasks.partials.tbody-rows')
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div id="tasksPagination" class="mt-3">
                        @include('admin.tasks.partials.pagination')
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
    const indexUrl = document.getElementById('tasksPage').dataset.indexUrl;
    const searchBtn = document.getElementById('searchBtn');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const assignedToFilter = document.getElementById('assignedToFilter');

    function performSearch() {
        const params = new URLSearchParams();
        if (searchInput.value) params.append('search', searchInput.value);
        if (typeFilter.value) params.append('type', typeFilter.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (assignedToFilter && assignedToFilter.value) params.append('assigned_to', assignedToFilter.value);

        window.location.href = indexUrl + (params.toString() ? '?' + params.toString() : '');
    }

    searchBtn.addEventListener('click', performSearch);
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            window.location.href = indexUrl;
        });
    }

    // Enter key search
    [searchInput, typeFilter, statusFilter, assignedToFilter].forEach(el => {
        if (el) {
            el.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') performSearch();
            });
        }
    });

    // Pagination
    document.querySelectorAll('#tasksPagination .page-link[data-page]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            const params = new URLSearchParams(window.location.search);
            params.set('page', page);
            window.location.href = indexUrl + '?' + params.toString();
        });
    });

    // Delete task
    document.querySelectorAll('.task-delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const taskId = this.dataset.taskId;
            const taskType = this.dataset.taskType;
            if (confirm(`هل أنت متأكد من حذف مهمة ${taskType}؟`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('admin.tasks.index') }}/${taskId}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
