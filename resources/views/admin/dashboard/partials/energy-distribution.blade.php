@php $user = auth()->user(); @endphp

@if(!$user->isCivilDefense() && !$user->isEnergyAuthority())
<div class="row g-3 mb-4">
    <div class="col-12">
        <x-admin.card>
            <x-admin.card-header title="توزيع الطاقة المنتجة" icon="bi-pie-chart" />
            <div class="p-3">
                {{-- Tabs --}}
                <ul class="nav nav-tabs nav-tabs-custom mb-3" id="distributionChartsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="generators-pie-tab" data-bs-toggle="tab" data-bs-target="#generators-pie-chart" type="button" role="tab">
                            <i class="bi bi-lightning-charge me-1"></i> حسب المولدات
                        </button>
                    </li>
                    @if(!$user->isCompanyOwner())
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="operators-pie-tab" data-bs-toggle="tab" data-bs-target="#operators-pie-chart" type="button" role="tab">
                            <i class="bi bi-building me-1"></i> حسب المشغلين
                        </button>
                    </li>
                    @endif
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="governorates-pie-tab" data-bs-toggle="tab" data-bs-target="#governorates-pie-chart" type="button" role="tab">
                            <i class="bi bi-geo-alt me-1"></i> حسب المحافظات
                        </button>
                    </li>
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content" id="distributionChartsContent">
                    <div class="tab-pane fade show active" id="generators-pie-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="generatorsPieChart"></canvas>
                            <div id="generatorsPieChart-skeleton" class="skeleton-chart-area" style="position:absolute;inset:0;z-index:1;"></div>
                        </div>
                    </div>
                    @if(!$user->isCompanyOwner())
                    <div class="tab-pane fade" id="operators-pie-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="operatorsPieChart"></canvas>
                        </div>
                    </div>
                    @endif
                    <div class="tab-pane fade" id="governorates-pie-chart" role="tabpanel">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="governoratesPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </x-admin.card>
    </div>
</div>
@endif
