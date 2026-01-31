@extends('layouts.admin')

@section('title', 'إدارة بيانات المشتركين')

@php
    $breadcrumbTitle = 'إدارة بيانات المشتركين';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page" id="subscribersPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-people me-2"></i>
                                إدارة بيانات المشتركين
                            </h5>
                            <div class="general-subtitle">
                                البحث والفلترة وإدارة بيانات المشتركين. العدد: <span id="subscribersCount">{{ $subscribers->total() }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('create', App\Models\Subscriber::class)
                                <a href="{{ route('admin.subscribers.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة مشترك جديد
                                </a>
                            @endcan
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
                                    @php
                                        $user = auth()->user();
                                        $isCompanyOwner = $user->isCompanyOwner();
                                        $isEmployeeOrTechnician = $user->isEmployee() || $user->isTechnician();
                                        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
                                    @endphp

                                    {{-- فلتر المشغل --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building me-1"></i>
                                            المشغل
                                        </label>
                                        @if($canSelectOperator && isset($operators) && $operators->count() > 0)
                                            <select id="operatorFilter" class="form-select">
                                                <option value="">كل المشغلين</option>
                                                @foreach($operators as $op)
                                                    <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                        {{ $op->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif(($isCompanyOwner || $isEmployeeOrTechnician) && isset($currentOperator))
                                            <select id="operatorFilter" class="form-select" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                                <option value="{{ $currentOperator->id }}" selected>{{ $currentOperator->name }}</option>
                                            </select>
                                            <input type="hidden" name="operator_id" value="{{ $currentOperator->id }}">
                                        @endif
                                    </div>

                                    {{-- فلتر حالة الاشتراك --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-funnel me-1"></i>
                                            حالة الاشتراك
                                        </label>
                                        <select id="subscriptionStatusFilter" class="form-select">
                                            <option value="">الكل</option>
                                            <option value="1" {{ request('subscription_status') == '1' ? 'selected' : '' }}>نشط</option>
                                            <option value="2" {{ request('subscription_status') == '2' ? 'selected' : '' }}>موقوف</option>
                                            <option value="3" {{ request('subscription_status') == '3' ? 'selected' : '' }}>مغلق</option>
                                        </select>
                                    </div>

                                    {{-- البحث --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            البحث
                                        </label>
                                        <input type="text" id="searchInput" class="form-control" placeholder="ابحث برقم الاشتراك، رقم الهوية، الاسم، الجوال، أو رقم العداد..." value="{{ request('search') }}">
                                    </div>
                                </div>

                                {{-- صف جديد لزر البحث --}}
                                <div class="row g-3 mt-2">
                                    <div class="col-12 d-flex justify-content-center gap-2">
                                        <button class="btn btn-primary" type="button" id="searchBtn">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button
                                            class="btn btn-outline-secondary {{ request('operator_id') || request('subscription_status') || request('search') ? '' : 'd-none' }}"
                                            type="button"
                                            id="clearBtn"
                                            title="تفريغ الحقول"
                                        >
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            تفريغ الحقول
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div id="subscribersListWrap" class="position-relative">
                            {{-- Loading overlay --}}
                            <div id="subLoading" class="data-table-loading d-none">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                                </div>
                            </div>

                            @include('admin.subscribers.partials.list', ['subscribers' => $subscribers])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const listUrl = @json(route('admin.subscribers.index'));
            const $wrap = $('#subscribersListWrap');
            let $loading = $('#subLoading');
            const $operatorFilter = $('#operatorFilter');
            const $subscriptionStatusFilter = $('#subscriptionStatusFilter');
            const $searchInput = $('#searchInput');
            const $searchBtn = $('#searchBtn');
            const $clearBtn = $('#clearBtn');

            function setLoading(on) {
                if (on) {
                    $loading.removeClass('d-none');
                    $wrap.find('.table, .pagination, .card').hide();
                } else {
                    $loading.addClass('d-none');
                    $wrap.find('.table, .pagination, .card').show();
                }
            }

            function currentParams(extra = {}) {
                let operatorId = $operatorFilter.val() || '';
                if ($operatorFilter.prop('disabled')) {
                    operatorId = $('input[name="operator_id"]').val() || '';
                }
                
                return Object.assign({
                    operator_id: operatorId,
                    subscription_status: $subscriptionStatusFilter.val() || '',
                    search: $searchInput.val() || '',
                }, extra);
            }

            function loadSubscribers() {
                setLoading(true);
                const params = currentParams();
                
                $.ajax({
                    url: listUrl,
                    method: 'GET',
                    data: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    success: function(response) {
                        if (response.success) {
                            $wrap.html(response.html);
                            $('#subscribersCount').text(response.count);
                            
                            // إظهار/إخفاء زر التفريغ
                            if (params.operator_id || params.subscription_status || params.search) {
                                $clearBtn.removeClass('d-none');
                            } else {
                                $clearBtn.addClass('d-none');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading subscribers:', xhr);
                        alert('حدث خطأ أثناء تحميل البيانات');
                    },
                    complete: function() {
                        setLoading(false);
                    }
                });
            }

            $searchBtn.on('click', loadSubscribers);
            $operatorFilter.on('change', loadSubscribers);
            $subscriptionStatusFilter.on('change', loadSubscribers);
            $searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    loadSubscribers();
                }
            });

            $clearBtn.on('click', function() {
                $operatorFilter.val('').trigger('change');
                $subscriptionStatusFilter.val('').trigger('change');
                $searchInput.val('');
                loadSubscribers();
            });
        })();
    </script>
@endpush

