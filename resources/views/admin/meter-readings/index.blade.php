@extends('layouts.admin')

@section('title', 'قراءات العدادات')

@php
    $breadcrumbTitle = 'قراءات العدادات';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page" id="meterReadingsPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-speedometer2 me-2"></i>
                                قراءات العدادات
                            </h5>
                            <div class="general-subtitle">
                                البحث والفلترة وإدارة قراءات العدادات. العدد: <span id="meterReadingsCount">{{ $meterReadings->total() }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('create', App\Models\MeterReading::class)
                                <a href="{{ route('admin.meter-readings.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة قراءة جديدة
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

                                    {{-- فلتر المشترك --}}
                                    @if(isset($subscribers) && $subscribers->count() > 0)
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-person me-1"></i>
                                                المشترك
                                            </label>
                                            <select id="subscriberFilter" class="form-select">
                                                <option value="">كل المشتركين</option>
                                                @foreach($subscribers as $sub)
                                                    <option value="{{ $sub->id }}" {{ request('subscriber_id') == $sub->id ? 'selected' : '' }}>
                                                        {{ $sub->subscription_number }} - {{ $sub->subscriber_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- فلتر حالة القراءة --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-funnel me-1"></i>
                                            حالة القراءة
                                        </label>
                                        <select id="readingStatusFilter" class="form-select">
                                            <option value="">الكل</option>
                                            <option value="1" {{ request('reading_status') == '1' ? 'selected' : '' }}>طبيعية</option>
                                            <option value="2" {{ request('reading_status') == '2' ? 'selected' : '' }}>تقديرية</option>
                                        </select>
                                    </div>

                                    {{-- البحث --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            البحث
                                        </label>
                                        <input type="text" id="searchInput" class="form-control" placeholder="ابحث برقم القراءة، رقم العداد، أو اسم المشترك..." value="{{ request('search') }}">
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
                                            class="btn btn-outline-secondary {{ request('operator_id') || request('subscriber_id') || request('reading_status') || request('search') ? '' : 'd-none' }}"
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

                        <div id="meterReadingsListWrap" class="position-relative">
                            {{-- Loading overlay --}}
                            <div id="mrLoading" class="data-table-loading d-none">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                                </div>
                            </div>

                            @include('admin.meter-readings.partials.list', ['meterReadings' => $meterReadings])
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
            const listUrl = @json(route('admin.meter-readings.index'));
            const $wrap = $('#meterReadingsListWrap');
            let $loading = $('#mrLoading');
            const $operatorFilter = $('#operatorFilter');
            const $subscriberFilter = $('#subscriberFilter');
            const $readingStatusFilter = $('#readingStatusFilter');
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
                    subscriber_id: $subscriberFilter.val() || '',
                    reading_status: $readingStatusFilter.val() || '',
                    search: $searchInput.val() || '',
                }, extra);
            }

            function loadMeterReadings() {
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
                            $('#meterReadingsCount').text(response.count);
                            
                            if (params.operator_id || params.subscriber_id || params.reading_status || params.search) {
                                $clearBtn.removeClass('d-none');
                            } else {
                                $clearBtn.addClass('d-none');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading meter readings:', xhr);
                        alert('حدث خطأ أثناء تحميل البيانات');
                    },
                    complete: function() {
                        setLoading(false);
                    }
                });
            }

            $searchBtn.on('click', loadMeterReadings);
            $operatorFilter.on('change', loadMeterReadings);
            $subscriberFilter.on('change', loadMeterReadings);
            $readingStatusFilter.on('change', loadMeterReadings);
            $searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    loadMeterReadings();
                }
            });

            $clearBtn.on('click', function() {
                $operatorFilter.val('').trigger('change');
                $subscriberFilter.val('').trigger('change');
                $readingStatusFilter.val('').trigger('change');
                $searchInput.val('');
                loadMeterReadings();
            });
        })();
    </script>
@endpush

