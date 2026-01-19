<!-- Pie Charts Section - للموظف والفني -->
@if(isset($pieChartData) && (count($pieChartData['generators']['data']) > 0 || count($pieChartData['operators']['data']) > 0 || count($pieChartData['governorates']['data']) > 0))
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="dashboard-card-title">
                        <i class="bi bi-pie-chart me-2"></i>
                        توزيع الطاقة المنتجة
                    </h5>
                    <p class="dashboard-card-subtitle">توزيع الطاقة المنتجة حسب المولدات والمشغلين والمحافظات</p>
                </div>
            </div>
            <div class="dashboard-card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-tabs-custom mb-3" id="pieChartsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="generators-pie-tab" data-bs-toggle="tab" data-bs-target="#generators-pie-chart" type="button" role="tab">
                            <i class="bi bi-lightning-charge me-1"></i>
                            حسب المولدات
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="operators-pie-tab" data-bs-toggle="tab" data-bs-target="#operators-pie-chart" type="button" role="tab">
                            <i class="bi bi-building me-1"></i>
                            حسب المشغلين
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="governorates-pie-tab" data-bs-toggle="tab" data-bs-target="#governorates-pie-chart" type="button" role="tab">
                            <i class="bi bi-geo-alt me-1"></i>
                            حسب المحافظات
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="pieChartsTabContent">
                    <!-- Generators Pie Chart -->
                    <div class="tab-pane fade show active" id="generators-pie-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="generatorsPieChart"></canvas>
                        </div>
                    </div>

                    <!-- Operators Pie Chart -->
                    <div class="tab-pane fade" id="operators-pie-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="operatorsPieChart"></canvas>
                        </div>
                    </div>

                    <!-- Governorates Pie Chart -->
                    <div class="tab-pane fade" id="governorates-pie-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="governoratesPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif




