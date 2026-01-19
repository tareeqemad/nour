@php
    $operator = $operationLogs->first()->operator ?? null;
    $generationUnit = $operationLogs->first()->generator->generationUnit ?? null;
    $generator = $operationLogs->first()->generator ?? null;
    
    // استخدام الإحصائيات الإجمالية من Controller (جميع السجلات المطابقة للفلاتر)
    $totalStats = $totalStats ?? [
        'total_count' => $operationLogs->total(),
        'total_fuel' => 0,
        'total_energy' => 0,
        'total_duration' => 0,
    ];
    
    $totalFuel = $totalStats['total_fuel'] ?? 0;
    $totalEnergy = $totalStats['total_energy'] ?? 0;
    $totalDuration = $totalStats['total_duration'] ?? 0;
    
    $totalHours = floor($totalDuration / 60);
    $totalMinutes = $totalDuration % 60;
@endphp

@if($operator || $generationUnit || $generator)
    <div class="generator-info-card mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    {{-- معلومات المولد/وحدة التوليد --}}
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center gap-3">
                            <div class="generator-icon-wrapper">
                                <div class="generator-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                    @if($generationUnit)
                                        <i class="bi bi-grid-3x3 fs-3"></i>
                                    @elseif($generator)
                                        <i class="bi bi-lightning-charge fs-3"></i>
                                    @else
                                        <i class="bi bi-building fs-3"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                @if($generationUnit)
                                    <h4 class="mb-1 fw-bold">
                                        {{ $generationUnit->name }}
                                        <span class="badge bg-info ms-2">{{ $generationUnit->unit_code }}</span>
                                    </h4>
                                    @if($generator)
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-lightning-charge me-1"></i>
                                            {{ $generator->name }}
                                            <span class="badge bg-secondary ms-2">{{ $generator->generator_number }}</span>
                                        </p>
                                    @endif
                                @elseif($generator)
                                    <h4 class="mb-1 fw-bold">
                                        {{ $generator->name }}
                                        <span class="badge bg-secondary ms-2">{{ $generator->generator_number }}</span>
                                    </h4>
                                    <p class="text-muted mb-0">
                                        {{ $generator->capacity_kva ?? '-' }} KVA
                                    </p>
                                @endif
                                @if($operator)
                                    <p class="text-muted small mb-0 mt-1">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $operator->name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    {{-- الإحصائيات السريعة --}}
                    <div class="col-lg-6">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="stat-card text-center p-2 bg-light rounded">
                                    <div class="stat-icon text-primary mb-1">
                                        <i class="bi bi-journal-text fs-4"></i>
                                    </div>
                                    <div class="stat-value fw-bold">{{ $totalStats['total_count'] ?? $operationLogs->total() }}</div>
                                    <div class="stat-label text-muted small">عدد السجلات</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-card text-center p-2 bg-light rounded">
                                    <div class="stat-icon text-success mb-1">
                                        <i class="bi bi-fuel-pump fs-4"></i>
                                    </div>
                                    <div class="stat-value fw-bold">{{ number_format($totalFuel, 1) }}</div>
                                    <div class="stat-label text-muted small">لتر</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-card text-center p-2 bg-light rounded">
                                    <div class="stat-icon text-warning mb-1">
                                        <i class="bi bi-lightning fs-4"></i>
                                    </div>
                                    <div class="stat-value fw-bold">{{ number_format($totalEnergy, 1) }}</div>
                                    <div class="stat-label text-muted small">kWh</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-card text-center p-2 bg-light rounded">
                                    <div class="stat-icon text-info mb-1">
                                        <i class="bi bi-hourglass-split fs-4"></i>
                                    </div>
                                    <div class="stat-value fw-bold">
                                        @if($totalHours > 0){{ $totalHours }}س @endif
                                        @if($totalMinutes > 0){{ $totalMinutes }}د @endif
                                    </div>
                                    <div class="stat-label text-muted small">المدة</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

