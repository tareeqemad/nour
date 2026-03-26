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
            background: rgba(217, 119, 6, 0.08);
            color: #92400e;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-type-safety_inspection {
            background: rgba(2, 132, 199, 0.08);
            color: #1e40af;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-status-pending {
            background: rgba(217, 119, 6, 0.08);
            color: #92400e;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-status-in_progress {
            background: rgba(2, 132, 199, 0.08);
            color: #1e40af;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-status-completed {
            background: rgba(22, 163, 74, 0.08);
            color: #065f46;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        .badge-status-cancelled {
            background: rgba(220, 38, 38, 0.08);
            color: #991b1b;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }
    </style>
@endpush

@section('content')
<div class="general-page" id="tasksPage" data-index-url="{{ route('admin.tasks.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="إدارة المهام" icon="bi-clipboard-check">
                    <x-slot:actions>
                        @if($canCreate)
                            <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                تكليف مهمة جديدة
                            </a>
                        @endif
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body pb-4">
                    @if(auth()->user()->isTechnician() || auth()->user()->isCivilDefense())
                        <x-admin.info-box type="info" icon="bi-info-circle">
                            أنت ترى فقط المهام المكلف بها.
                        </x-admin.info-box>
                    @endif

                    {{-- Statistics KPIs --}}
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6 col-6">
                            <x-admin.kpi icon="bi-clipboard-check" color="primary" :value="number_format($stats['total'] ?? 0)" label="إجمالي المهام" />
                        </div>
                        <div class="col-lg-3 col-md-6 col-6">
                            <x-admin.kpi icon="bi-hourglass-split" color="warning" :value="number_format($stats['pending'] ?? 0)" label="قيد الانتظار" />
                        </div>
                        <div class="col-lg-3 col-md-6 col-6">
                            <x-admin.kpi icon="bi-gear-wide-connected" color="info" :value="number_format($stats['in_progress'] ?? 0)" label="قيد التنفيذ" />
                        </div>
                        <div class="col-lg-3 col-md-6 col-6">
                            <x-admin.kpi icon="bi-check-circle" color="success" :value="number_format($stats['completed'] ?? 0)" label="مكتملة" />
                        </div>
                    </div>

                    {{-- فلاتر البحث --}}
                    <div class="row g-3 align-items-end">
                        {{-- البحث --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label fw-semibold" for="searchInput">
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
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label fw-semibold" for="typeFilter">
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
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label fw-semibold" for="statusFilter">
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
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="form-label fw-semibold" for="assignedToFilter">
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
                        <div class="col-12 col-lg-auto order-last order-lg-0 mt-2 mt-lg-0">
                            <label class="form-label fw-semibold d-none d-lg-block">&nbsp;</label>
                            <div class="d-flex flex-wrap gap-2 align-items-end">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="bi bi-search me-2"></i>
                                    بحث
                                </button>
                                <button class="btn btn-outline-secondary {{ request('search') || request('type') || request('status') || request('assigned_to') ? '' : 'd-none' }}" type="button" id="clearSearchBtn">
                                    <i class="bi bi-x me-2"></i>
                                    تفريغ
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive mt-4">
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
            </x-admin.card>
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
