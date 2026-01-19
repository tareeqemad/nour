@extends('layouts.admin')

@section('title', 'عرض الرسالة')

@php
    $breadcrumbTitle = 'عرض الرسالة';
    $breadcrumbParent = 'الرسائل';
    $breadcrumbParentUrl = route('admin.messages.index');
@endphp

@push('styles')
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-envelope-open me-2"></i>
                            عرض الرسالة
                        </h5>
                        <div class="general-subtitle">
                            تفاصيل الرسالة المرسلة
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة
                        </a>
                        @can('delete', $message)
                            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="d-inline" id="deleteForm">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger" onclick="if(confirm('هل أنت متأكد من حذف هذه الرسالة؟')) { document.getElementById('deleteForm').submit(); }">
                                    <i class="bi bi-trash me-1"></i>
                                    حذف
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    {{-- معلومات الرسالة --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            معلومات الرسالة
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المرسل:</label>
                                <div>
                                    <span class="badge bg-primary">{{ $message->sender_display_name }}</span>
                                    @if(!$message->isSystemMessage() && $message->sender)
                                        <small class="text-muted ms-2">{{ $message->sender->role_name }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المستقبل:</label>
                                <div>
                                    @if($message->receiver)
                                        <span class="badge bg-info">{{ $message->receiver->name }}</span>
                                        <small class="text-muted ms-2">{{ $message->receiver->role_name }}</small>
                                    @elseif($message->operator)
                                        <span class="badge bg-info">{{ $message->operator->name }}</span>
                                        <small class="text-muted ms-2">مشغل</small>
                                    @else
                                        <span class="badge bg-secondary">
                                            @if($message->type === 'admin_to_all')
                                                جميع المشغلين
                                            @elseif($message->type === 'operator_to_staff')
                                                جميع موظفي المشغل
                                            @else
                                                -
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">النوع:</label>
                                <div>
                                    @php
                                        $typeLabels = [
                                            'operator_to_operator' => ['label' => 'مشغل لمشغل', 'badge' => 'bg-primary'],
                                            'operator_to_staff' => ['label' => 'مشغل لموظفين', 'badge' => 'bg-success'],
                                            'admin_to_operator' => ['label' => 'أدمن لمشغل', 'badge' => 'bg-warning'],
                                            'admin_to_all' => ['label' => 'أدمن للجميع', 'badge' => 'bg-danger'],
                                        ];
                                        $typeInfo = $typeLabels[$message->type] ?? ['label' => $message->type, 'badge' => 'bg-secondary'];
                                    @endphp
                                    <span class="badge {{ $typeInfo['badge'] }}">
                                        {{ $typeInfo['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">التاريخ:</label>
                                <div>
                                    <span class="text-muted">{{ $message->created_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                            </div>
                            @if($message->is_read && $message->read_at)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ القراءة:</label>
                                    <div>
                                        <span class="text-muted">{{ $message->read_at->format('Y-m-d H:i:s') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- الموضوع --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-tag text-primary me-2"></i>
                            الموضوع
                        </h6>
                        <div class="p-3 bg-light rounded border">
                            <h5 class="mb-0">{{ $message->subject }}</h5>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- المحتوى --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-file-text text-primary me-2"></i>
                            محتوى الرسالة
                        </h6>
                        <div class="p-4 bg-light rounded border" style="min-height: 200px; white-space: pre-wrap; line-height: 1.8;">
                            {{ $message->body }}
                        </div>
                    </div>

                    {{-- الصورة المرفقة --}}
                    @if($message->hasAttachment())
                        <hr class="my-4">
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-image text-primary me-2"></i>
                                الصورة المرفقة
                            </h6>
                            <div class="p-3 bg-light rounded border">
                                <a href="{{ $message->attachment_url }}" target="_blank" class="d-inline-block">
                                    <img src="{{ $message->attachment_url }}" alt="الصورة المرفقة" 
                                         class="img-fluid rounded shadow-sm" style="max-width: 600px; max-height: 600px; cursor: pointer;">
                                </a>
                                <div class="mt-3">
                                    <a href="{{ $message->attachment_url }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>
                                        فتح الصورة في نافذة جديدة
                                    </a>
                                    <a href="{{ $message->attachment_url }}" download class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>
                                        تحميل الصورة
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    // تحديث عدد الرسائل غير المقروءة عند قراءة رسالة
    if (window.MessagesPanel) {
        window.MessagesPanel.refresh();
    }
})();
</script>
@endpush
