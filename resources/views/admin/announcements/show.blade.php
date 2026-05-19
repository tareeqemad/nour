@extends('layouts.admin')

@section('title', 'تفاصيل الإعلان')

@php
    $breadcrumbTitle = 'تفاصيل الإعلان';
    $breadcrumbParent = 'الإعلانات';
    $breadcrumbParentUrl = route('admin.announcements.index');
    $today = now()->startOfDay();
    $isActive   = $announcement->is_visible && $announcement->start_date <= $today && $announcement->end_date >= $today;
    $isExpired  = $announcement->end_date < $today;
    $isUpcoming = $announcement->start_date > $today;
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header title="تفاصيل الإعلان" icon="bi-megaphone">
                        <x-slot:actions>
                            @can('update', $announcement)
                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-1"></i> تعديل
                                </a>
                            @endcan
                            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-1"></i> رجوع
                            </a>
                        </x-slot:actions>
                    </x-admin.card-header>

                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            @if($announcement->is_featured)
                                <span class="badge bg-warning text-dark fs-6"><i class="bi bi-star-fill me-1"></i> مميز</span>
                            @endif
                            @if(!$announcement->is_visible)
                                <span class="badge bg-secondary fs-6">مخفي</span>
                            @elseif($isExpired)
                                <span class="badge bg-danger fs-6">منتهي</span>
                            @elseif($isUpcoming)
                                <span class="badge bg-info fs-6">قادم</span>
                            @elseif($isActive)
                                <span class="badge bg-success fs-6">نشط</span>
                            @endif
                        </div>

                        <h4 class="fw-bold mb-3">{{ $announcement->title }}</h4>

                        <div class="mb-4 p-3 rounded" style="background:#FAFCFF;border:1px solid #EDF1F5;white-space:pre-wrap;line-height:1.8;">
                            {{ $announcement->description }}
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <div class="text-muted small">تاريخ الإعلان</div>
                                <div class="fw-semibold">{{ $announcement->announcement_date?->format('Y-m-d') }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">من تاريخ</div>
                                <div class="fw-semibold">{{ $announcement->start_date?->format('Y-m-d') }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">إلى تاريخ</div>
                                <div class="fw-semibold">{{ $announcement->end_date?->format('Y-m-d') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">أنشئ بواسطة</div>
                                <div class="fw-semibold">{{ $announcement->creator?->name ?? '—' }} <small class="text-muted">({{ $announcement->created_at?->format('Y-m-d H:i') }})</small></div>
                            </div>
                            @if($announcement->updater)
                            <div class="col-md-6">
                                <div class="text-muted small">آخر تعديل</div>
                                <div class="fw-semibold">{{ $announcement->updater?->name }} <small class="text-muted">({{ $announcement->updated_at?->format('Y-m-d H:i') }})</small></div>
                            </div>
                            @endif
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection
