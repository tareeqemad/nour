@extends('layouts.admin')

@section('title', 'النسخ الاحتياطي لقاعدة البيانات')

@php
    $breadcrumbTitle = 'النسخ الاحتياطي';

    $formatSize = function (int $bytes): string {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return number_format($bytes / 1024, 1) . ' KB';
        if ($bytes < 1024 * 1024 * 1024) return number_format($bytes / (1024 * 1024), 1) . ' MB';
        return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
    };
@endphp

@section('content')
<div class="general-page" id="backupsPage">
    <div class="row g-3">
        <div class="col-12">

            {{-- Header card --}}
            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1 fw-bold">
                            <i class="bi bi-cloud-arrow-down text-info me-1"></i>
                            النسخ الاحتياطي لقاعدة البيانات
                        </h5>
                        <small class="text-muted">
                            يتم حفظ كل نسخة كملف SQL في
                            <code>storage/app/backups</code>. يمكنك تنزيلها أو حذفها لاحقاً.
                        </small>
                    </div>
                    <form action="{{ route('admin.backups.store') }}" method="POST" class="m-0"
                          onsubmit="return confirm('سيتم إنشاء نسخة احتياطية جديدة الآن. قد تستغرق العملية عدة دقائق حسب حجم قاعدة البيانات. هل تريد المتابعة؟');">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>إنشاء نسخة احتياطية جديدة
                        </button>
                    </form>
                </div>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Backups table --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">النسخ المتوفرة ({{ count($backups) }})</h6>
                </div>
                <div class="card-body p-0">
                    @if(empty($backups))
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-archive fs-1 d-block mb-2 opacity-50"></i>
                            لا توجد نسخ احتياطية بعد. اضغط على زر "إنشاء نسخة احتياطية جديدة" لإنشاء أول نسخة.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>اسم الملف</th>
                                        <th>الحجم</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $idx => $b)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <i class="bi bi-file-earmark-zip text-secondary me-1"></i>
                                                <code>{{ $b['name'] }}</code>
                                            </td>
                                            <td>{{ $formatSize($b['size']) }}</td>
                                            <td>
                                                {{ $b['created_at']->format('Y-m-d H:i:s') }}
                                                <small class="text-muted d-block">{{ $b['created_at']->diffForHumans() }}</small>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.backups.download', $b['name']) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="تنزيل">
                                                    <i class="bi bi-download"></i> تنزيل
                                                </a>
                                                <form action="{{ route('admin.backups.destroy', $b['name']) }}"
                                                      method="POST" class="d-inline m-0"
                                                      onsubmit="return confirm('هل تريد حذف هذه النسخة الاحتياطية نهائياً؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                        <i class="bi bi-trash"></i> حذف
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
