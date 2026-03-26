@php $user = auth()->user(); @endphp

@if(!$user->isCivilDefense() && !$user->isEnergyAuthority())
<div class="row g-3 mb-4">
    <div class="col-12">
        <x-admin.card>
            <x-admin.card-header title="تحليل الأداء" icon="bi-bar-chart-line" />
            <div class="p-3">
                {{-- Tabs --}}
                <ul class="nav nav-tabs nav-tabs-custom mb-3" id="performanceChartsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="energy-fuel-tab" data-bs-toggle="tab" data-bs-target="#energy-fuel-chart" type="button" role="tab">
                            <i class="bi bi-lightning-charge me-1"></i> الطاقة والوقود
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fuel-by-generator-tab" data-bs-toggle="tab" data-bs-target="#fuel-by-generator-chart" type="button" role="tab">
                            <i class="bi bi-fuel-pump me-1"></i> الوقود حسب المولد
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="load-tab" data-bs-toggle="tab" data-bs-target="#load-chart" type="button" role="tab">
                            <i class="bi bi-speedometer2 me-1"></i> نسبة التحميل
                        </button>
                    </li>
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content" id="performanceChartsContent">
                    <div class="tab-pane fade show active" id="energy-fuel-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="advancedEnergyFuelChart"></canvas>
                            <div id="advancedEnergyFuelChart-skeleton" class="skeleton-chart-area" style="position:absolute;inset:0;z-index:1;"></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="fuel-by-generator-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="fuelByGeneratorChart"></canvas>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="load-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="loadChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </x-admin.card>
    </div>
</div>
@endif
