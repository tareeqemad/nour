@extends('layouts.admin')

@section('title', 'أسعار التعرفة الكهربائية')

@php
    $breadcrumbTitle = 'أسعار التعرفة الكهربائية';
    $breadcrumbParent = $operator->name;
    $breadcrumbParentUrl = route('admin.operators.show', $operator);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/fuel-efficiencies.css') }}">
@endpush

@section('content')
    <div class="fuel-efficiencies-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header log-toolbar-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-currency-exchange me-2"></i>
                                    أسعار التعرفة الكهربائية - {{ $operator->name }}
                                </div>
                                <div class="log-subtitle">
                                    إدارة أسعار التعرفة الكهربائية للمشغل. العدد: <span>{{ $tariffPrices->total() }}</span>
                                </div>
                            </div>

                            @can('create', [\App\Models\ElectricityTariffPrice::class, $operator])
                                <a href="{{ route('admin.operators.tariff-prices.create', $operator) }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    إضافة سعر جديد
                                </a>
                            @endcan
                            
                            <a href="{{ route('admin.operators.show', $operator) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>
                                العودة للمشغل
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if($tariffPrices->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>تاريخ البدء</th>
                                            <th>تاريخ الانتهاء</th>
                                            <th>السعر (₪/kWh)</th>
                                            <th>الحالة</th>
                                            <th>ملاحظات</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tariffPrices as $tariffPrice)
                                            <tr>
                                                <td>{{ $tariffPrice->start_date->format('Y-m-d') }}</td>
                                                <td>{{ $tariffPrice->end_date ? $tariffPrice->end_date->format('Y-m-d') : '—' }}</td>
                                                <td><strong>{{ number_format($tariffPrice->price_per_kwh, 4) }}</strong> ₪/kWh</td>
                                                <td>
                                                    @if($tariffPrice->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-secondary">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>{{ $tariffPrice->notes ?? '—' }}</td>
                                                <td>
                                                    @can('update', $tariffPrice)
                                                        <a href="{{ route('admin.operators.tariff-prices.edit', [$operator, $tariffPrice]) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $tariffPrice)
                                                        <form action="{{ route('admin.operators.tariff-prices.destroy', [$operator, $tariffPrice]) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا السعر؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $tariffPrices->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3">لا توجد أسعار تعرفة مسجلة</p>
                                @can('create', [\App\Models\ElectricityTariffPrice::class, $operator])
                                    <a href="{{ route('admin.operators.tariff-prices.create', $operator) }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        إضافة سعر جديد
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

