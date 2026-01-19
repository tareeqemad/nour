<!-- Charts Section - للمشغل -->
@if(isset($chartData))
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-bar-chart-line me-2"></i>
                        تحليل الأداء
                    </h5>
                    <p class="dashboard-card-subtitle">مخططات تفصيلية لأداء المولدات (آخر 30 يوم)</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-tabs-custom mb-3" id="operationChartsTabOwner" role="tablist">
                    @if(isset($chartData['advanced_chart']) && !empty($chartData['advanced_chart']['labels']))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="energy-fuel-tab-owner" data-bs-toggle="tab" data-bs-target="#energy-fuel-chart-owner" type="button" role="tab">
                            <i class="bi bi-lightning-charge me-1"></i>
                            الطاقة والوقود
                        </button>
                    </li>
                    @endif
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fuel-by-generator-tab-owner" data-bs-toggle="tab" data-bs-target="#fuel-by-generator-chart-owner" type="button" role="tab">
                            <i class="bi bi-fuel-pump me-1"></i>
                            الوقود حسب المولد
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="load-tab-owner" data-bs-toggle="tab" data-bs-target="#load-chart-owner" type="button" role="tab">
                            <i class="bi bi-speedometer2 me-1"></i>
                            نسبة التحميل
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="operationChartsTabContentOwner">
                    @if(isset($chartData['advanced_chart']) && !empty($chartData['advanced_chart']['labels']))
                    <!-- Energy & Fuel Chart Tab -->
                    <div class="tab-pane fade show active" id="energy-fuel-chart-owner" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="advancedEnergyFuelChartOwner"></canvas>
                        </div>
                    </div>
                    @endif

                    <!-- Fuel by Generator Chart -->
                    <div class="tab-pane fade" id="fuel-by-generator-chart-owner" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="fuelByGeneratorChartOwner"></canvas>
                        </div>
                    </div>

                    <!-- Load Chart -->
                    <div class="tab-pane fade" id="load-chart-owner" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="loadChartOwner"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif


