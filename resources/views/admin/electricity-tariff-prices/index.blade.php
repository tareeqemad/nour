@extends('layouts.admin')

@section('title', 'أسعار التعرفة الكهربائية')

@php
    $breadcrumbTitle = 'أسعار التعرفة الكهربائية';
    if ($operator) {
        $breadcrumbParent = $operator->name;
        $breadcrumbParentUrl = route('admin.operators.show', $operator);
    } else {
        $breadcrumbParent = 'إدارة التعرفة';
        $breadcrumbParentUrl = route('admin.dashboard');
    }
@endphp

@push('styles')
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header :title="$operator ? 'أسعار التعرفة الكهربائية - ' . $operator->name : 'أسعار التعرفة الكهربائية العامة'" icon="bi-currency-exchange">
                        <x-slot:actions>
                            @can('create', \App\Models\ElectricityTariffPrice::class)
                                <a href="{{ route('admin.electricity-tariff-prices.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    إضافة سعر جديد
                                </a>
                            @endcan

                            @if($operator)
                                <a href="{{ route('admin.operators.show', $operator) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    العودة للمشغل
                                </a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    العودة
                                </a>
                            @endif
                        </x-slot:actions>
                    </x-admin.card-header>

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
                                            <th>أضافه</th>
                                            <th>ملاحظات</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tariffPrices as $tariffPrice)
                                            <tr>
                                                <td>{{ $tariffPrice->start_date->format('Y-m-d') }}</td>
                                                <td>{{ $tariffPrice->end_date ? $tariffPrice->end_date->format('Y-m-d') : '—' }}</td>
                                                <td><strong>{{ number_format($tariffPrice->price_per_kwh, 2) }}</strong> ₪/kWh</td>
                                                <td>
                                                    @if($tariffPrice->is_active)
                                                        <x-admin.badge type="success">نشط</x-admin.badge>
                                                    @else
                                                        <x-admin.badge type="neutral">غير نشط</x-admin.badge>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($tariffPrice->creator)
                                                        <div class="small">
                                                            <i class="bi bi-person-circle me-1"></i>
                                                            {{ $tariffPrice->creator->name }}
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                            {{ $tariffPrice->created_at->format('Y-m-d H:i') }}
                                                        </div>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $tariffPrice->notes ?? '—' }}</td>
                                                <td>
                                                    @can('update', $tariffPrice)
                                                        <a href="{{ route('admin.electricity-tariff-prices.edit', $tariffPrice) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $tariffPrice)
                                                        <form action="{{ route('admin.electricity-tariff-prices.destroy', $tariffPrice) }}" 
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
                            <x-admin.empty-state icon="bi-currency-exchange" message="لا توجد أسعار تعرفة مسجلة">
                                @can('create', \App\Models\ElectricityTariffPrice::class)
                                    <a href="{{ route('admin.electricity-tariff-prices.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        إضافة سعر جديد
                                    </a>
                                @endcan
                            </x-admin.empty-state>
                        @endif
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection
