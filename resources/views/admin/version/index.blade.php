@extends('layouts.admin')

@section('title', 'إدارة الإصدارات')

@php
    $breadcrumbTitle = 'إدارة الإصدارات';
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="إدارة الإصدارات" icon="bi-boxes">
                    <x-slot:actions>
                        <a href="{{ route('admin.versions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            إصدار جديد
                        </a>
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body">
                    {{-- الإصدار الحالي --}}
                    @if($currentVersion)
                        <div style="background: var(--color-success-bg, #ECFDF5); border: 1px solid var(--color-success-border, #A7F3D0); border-radius: 10px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-check-circle-fill" style="color: var(--color-success, #10B981); font-size: 1.15rem;"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--color-success-text, #047857); font-size: 0.88rem;">
                                    الإصدار الحالي: v{{ $currentVersion->version }} — {{ $currentVersion->title }}
                                </div>
                                <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780);">
                                    {{ $currentVersion->release_date->format('Y/m/d') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div style="background: var(--color-warning-bg, #FFFBEB); border: 1px solid var(--color-warning-border, #FCD34D); border-radius: 10px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="bi bi-exclamation-triangle" style="color: var(--color-warning-text, #B45309); font-size: 1.25rem;"></i>
                            <div style="font-size: 0.88rem; color: var(--color-warning-text, #B45309);">
                                لم يتم تعيين إصدار حالي. <a href="{{ route('admin.versions.create') }}" style="color: var(--color-warning-text); font-weight: 700;">أضف إصداراً</a>
                            </div>
                        </div>
                    @endif

                    {{-- الجدول --}}
                    @if($versions->isEmpty())
                        <div class="text-center py-5" style="color: var(--color-text-muted, #5B6780);">
                            <i class="bi bi-box-seam" style="font-size: 2.5rem; opacity: .4;"></i>
                            <p class="mt-2 mb-3">لا توجد إصدارات</p>
                            <a href="{{ route('admin.versions.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>إنشاء إصدار
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 general-table">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">الإصدار</th>
                                        <th>العنوان</th>
                                        <th style="width: 100px;">النوع</th>
                                        <th style="width: 110px;">التاريخ</th>
                                        <th style="width: 90px;">الحالة</th>
                                        <th style="width: 160px;" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($versions as $version)
                                        <tr>
                                            <td>
                                                <span class="badge-role-system">v{{ $version->version }}</span>
                                            </td>
                                            <td>
                                                <div style="font-weight: 600; color: var(--color-text-main, #1F2937);">{{ $version->title }}</div>
                                                @if($version->description)
                                                    <small style="color: var(--color-text-muted, #5B6780);">{{ Str::limit($version->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge-neutral">{{ $version->getTypeLabel() }}</span>
                                            </td>
                                            <td style="font-size: 0.85rem; color: var(--color-text-secondary, #3B4863);">
                                                {{ $version->release_date->format('Y/m/d') }}
                                            </td>
                                            <td>
                                                @if($version->is_current)
                                                    <span class="badge-status-active">حالي</span>
                                                @else
                                                    <span class="badge-neutral">سابق</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="{{ route('admin.versions.show', $version) }}" class="btn btn-sm btn-outline-info" title="عرض">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.versions.print', $version) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="طباعة رسمية">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                    <a href="{{ route('admin.versions.edit', $version) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if(!$version->is_current)
                                                        <form action="{{ route('admin.versions.set-current', $version) }}" method="POST" class="d-inline set-current-form">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="تعيين كحالي">
                                                                <i class="bi bi-check-lg"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.versions.destroy', $version) }}" method="POST" class="d-inline delete-version-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <i class="bi bi-trash3"></i>
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

                        @if($versions->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $versions->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.set-current-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'تعيين كإصدار حالي',
            text: 'هل تريد تعيين هذا الإصدار كإصدار حالي؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#D1D5DB',
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i>تعيين',
            cancelButtonText: 'إلغاء',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });

    $('.delete-version-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'تأكيد الحذف',
            html: 'هل أنت متأكد من حذف هذا الإصدار؟<br><small style="color: #B91C1C;">هذا الإجراء لا يمكن التراجع عنه</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#D1D5DB',
            confirmButtonText: '<i class="bi bi-trash3 me-1"></i>حذف',
            cancelButtonText: 'إلغاء',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
