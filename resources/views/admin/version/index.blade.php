@extends('layouts.admin')

@section('title', 'إدارة الإصدارات')

@php
    $breadcrumbTitle = 'إدارة الإصدارات';
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-boxes me-2"></i>
                            إدارة الإصدارات
                        </h5>
                        <div class="general-subtitle">
                            إدارة إصدارات المنصة وسجل التغييرات
                        </div>
                    </div>
                    <div class="general-card-actions">
                        <a href="{{ route('admin.versions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            إصدار جديد
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- الإصدار الحالي --}}
                    @if($currentVersion)
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                            <div>
                                <strong>الإصدار الحالي:</strong> 
                                v{{ $currentVersion->version }} - {{ $currentVersion->title }}
                                <small class="d-block text-muted">
                                    تاريخ الإصدار: {{ $currentVersion->release_date->format('Y/m/d') }}
                                </small>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                            <div>
                                <strong>تنبيه:</strong> لم يتم تعيين إصدار حالي بعد.
                                <a href="{{ route('admin.versions.create') }}" class="alert-link">أضف إصداراً جديداً</a>
                            </div>
                        </div>
                    @endif

                    {{-- جدول الإصدارات --}}
                    @if($versions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">لا توجد إصدارات</h5>
                            <p class="text-muted">ابدأ بإنشاء أول إصدار للمنصة</p>
                            <a href="{{ route('admin.versions.create') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-lg me-1"></i>
                                إنشاء إصدار
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">الإصدار</th>
                                        <th>العنوان</th>
                                        <th style="width: 120px;">النوع</th>
                                        <th style="width: 120px;">التاريخ</th>
                                        <th style="width: 100px;">الحالة</th>
                                        <th style="width: 180px;">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($versions as $version)
                                        <tr class="{{ $version->is_current ? 'table-success' : '' }}">
                                            <td>
                                                <span class="badge {{ $version->getTypeBadgeClass() }} fs-6">
                                                    v{{ $version->version }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $version->title }}</div>
                                                @if($version->description)
                                                    <small class="text-muted">{{ Str::limit($version->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $version->getTypeLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $version->release_date->format('Y/m/d') }}</small>
                                            </td>
                                            <td>
                                                @if($version->is_current)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        حالي
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">سابق</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.versions.show', $version) }}" 
                                                       class="btn btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.versions.edit', $version) }}" 
                                                       class="btn btn-outline-primary" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if(!$version->is_current)
                                                        <form action="{{ route('admin.versions.set-current', $version) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success" 
                                                                    title="تعيين كحالي"
                                                                    onclick="return confirm('هل تريد تعيين هذا الإصدار كإصدار حالي؟')">
                                                                <i class="bi bi-check-lg"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.versions.destroy', $version) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" 
                                                                    title="حذف"
                                                                    onclick="return confirm('هل أنت متأكد من حذف هذا الإصدار؟')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $versions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
