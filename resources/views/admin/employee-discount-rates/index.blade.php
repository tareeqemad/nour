@extends('layouts.admin')

@section('title', 'نسب خصم الموظفين')

@php
    $breadcrumbTitle = 'نسب خصم الموظفين';
    $breadcrumbParent = 'إدارة الخصومات';
    $breadcrumbParentUrl = route('admin.dashboard');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/fuel-efficiencies.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="general-title">
                                    <i class="bi bi-percent me-2"></i>
                                    نسب خصم الموظفين
                                </div>
                                <div class="general-subtitle">
                                    إدارة نسب خصم موظفي الشركة (موظف + منزلي + 1 فاز). العدد: <span>{{ $discountRates->total() }}</span>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                @can('create', \App\Models\EmployeeDiscountRate::class)
                                    <a href="{{ route('admin.employee-discount-rates.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        إضافة نسبة جديدة
                                    </a>
                                @endcan

                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    العودة
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if($discountRates->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>تاريخ البدء</th>
                                            <th>تاريخ الانتهاء</th>
                                            <th>نسبة الخصم (%)</th>
                                            <th>الحالة</th>
                                            <th>أضافه</th>
                                            <th>ملاحظات</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($discountRates as $rate)
                                            <tr>
                                                <td>{{ $rate->start_date->format('Y-m-d') }}</td>
                                                <td>{{ $rate->end_date ? $rate->end_date->format('Y-m-d') : '—' }}</td>
                                                <td><strong>{{ number_format($rate->discount_rate, 2) }}</strong>%</td>
                                                <td>
                                                    @if($rate->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-secondary">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($rate->creator)
                                                        <div class="small">
                                                            <i class="bi bi-person-circle me-1"></i>
                                                            {{ $rate->creator->name }}
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                            {{ $rate->created_at->format('Y-m-d H:i') }}
                                                        </div>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $rate->notes ?? '—' }}</td>
                                                <td>
                                                    @can('update', $rate)
                                                        <a href="{{ route('admin.employee-discount-rates.edit', $rate) }}"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $rate)
                                                        <form action="{{ route('admin.employee-discount-rates.destroy', $rate) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('هل أنت متأكد من حذف هذه النسبة؟');">
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
                                {{ $discountRates->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3">لا توجد نسب خصم مسجلة</p>
                                @can('create', \App\Models\EmployeeDiscountRate::class)
                                    <a href="{{ route('admin.employee-discount-rates.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        إضافة نسبة جديدة
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
