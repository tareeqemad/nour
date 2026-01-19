{{-- resources/views/admin/complaints-suggestions/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'تفاصيل الشكوى/المقترح')

@php
    $breadcrumbTitle = 'تفاصيل الشكوى/المقترح';
    $breadcrumbParent = 'الشكاوى والمقترحات';
    $breadcrumbParentUrl = route('admin.complaints-suggestions.index');
    $isSuperAdmin = auth()->user()->isSuperAdmin();
@endphp

@push('styles')
    <style>
        .info-item {
            margin-bottom: 1.5rem;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }
        .info-value {
            font-size: 0.95rem;
            color: #1f2937;
            font-weight: 500;
        }
        .info-value code {
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.95em;
            color: #0d6efd;
            font-weight: 600;
        }
        .message-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-right: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 1.25rem;
            margin-top: 1.5rem;
        }
        .message-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .message-content {
            font-size: 0.95rem;
            color: #1f2937;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .response-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-right: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 1.25rem;
            margin-top: 1.5rem;
        }
        .response-label {
            font-size: 0.875rem;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .response-content {
            font-size: 0.95rem;
            color: #1e3a8a;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .attached-image {
            max-width: 100%;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            margin-top: 0.75rem;
        }
    </style>
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            {{-- معلومات الشكوى/المقترح --}}
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-chat-left-text me-2"></i>
                            تفاصيل الشكوى/المقترح
                        </h5>
                        <div class="general-subtitle">
                            <span class="badge-status badge-status-{{ $complaintSuggestion->status }}">
                                {{ $complaintSuggestion->status_label }}
                            </span>
                            <span class="badge bg-{{ $complaintSuggestion->type === 'complaint' ? 'danger' : 'primary' }} ms-2">
                                {{ $complaintSuggestion->type_label }}
                            </span>
                            @if($complaintSuggestion->operator)
                            <span class="badge bg-info ms-2">
                                {{ $complaintSuggestion->operator->name }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.complaints-suggestions.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة
                        </a>
                        @if($isSuperAdmin)
                            <a href="{{ route('admin.complaints-suggestions.edit', $complaintSuggestion) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body pb-4">
                    {{-- رمز التتبع --}}
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-hash me-1"></i>
                            رمز التتبع
                        </div>
                        <div class="info-value">
                            <code>{{ $complaintSuggestion->tracking_code }}</code>
                        </div>
                    </div>

                    {{-- معلومات المرسل --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-person me-1"></i>
                                    الاسم
                                </div>
                                <div class="info-value">{{ $complaintSuggestion->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-telephone me-1"></i>
                                    رقم الهاتف
                                </div>
                                <div class="info-value">{{ $complaintSuggestion->phone }}</div>
                            </div>
                        </div>
                        @if($complaintSuggestion->email)
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-envelope me-1"></i>
                                    البريد الإلكتروني
                                </div>
                                <div class="info-value">{{ $complaintSuggestion->email }}</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- الموقع والمشغل --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    المحافظة
                                </div>
                                <div class="info-value">{{ $complaintSuggestion->getGovernorateLabel() ?? 'غير محدد' }}</div>
                            </div>
                        </div>
                        @if($complaintSuggestion->generator)
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-lightning-charge me-1"></i>
                                    المولد
                                </div>
                                <div class="info-value">
                                    {{ $complaintSuggestion->generator->name ?? 'مولد محذوف' }}
                                    @if($complaintSuggestion->generator && $complaintSuggestion->generator->trashed())
                                        <span class="badge bg-secondary ms-1" title="مولد محذوف">محذوف</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($complaintSuggestion->operator)
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-building me-1"></i>
                                    المشغل
                                </div>
                                <div class="info-value">{{ $complaintSuggestion->operator->name }}</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- التواريخ --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-calendar me-1"></i>
                                    تاريخ الإرسال
                                </div>
                                <div class="info-value">{{ $complaintSuggestion->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                        @if($complaintSuggestion->responded_at)
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-clock-history me-1"></i>
                                    تاريخ الرد
                                </div>
                                <div class="info-value">{{ $complaintSuggestion->responded_at->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($complaintSuggestion->responder)
                        <div class="col-md-4">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-person-check me-1"></i>
                                    الرد من
                                </div>
                                <div class="info-value">{{ $complaintSuggestion->responder->name }}</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- الرسالة --}}
                    <div class="message-box">
                        <div class="message-label">
                            <i class="bi bi-chat-dots me-1"></i>
                            الرسالة
                        </div>
                        <div class="message-content">{{ $complaintSuggestion->message }}</div>
                    </div>

                    {{-- الصورة المرفقة --}}
                    @if($complaintSuggestion->image)
                    <div class="message-box">
                        <div class="message-label">
                            <i class="bi bi-image me-1"></i>
                            الصورة المرفقة
                        </div>
                        <div>
                            <img src="{{ asset('storage/' . $complaintSuggestion->image) }}" 
                                 alt="صورة مرفقة" 
                                 class="attached-image">
                        </div>
                    </div>
                    @endif

                    {{-- الرد --}}
                    @if($complaintSuggestion->response)
                        <div class="response-box">
                            <div class="response-label">
                                <i class="bi bi-reply me-1"></i>
                                رد الإدارة
                            </div>
                            <div class="response-content">{{ $complaintSuggestion->response }}</div>
                        </div>
                    @else
                        <div class="message-box" style="background: #fef3c7; border-right-color: #f59e0b;">
                            <div style="font-size: 0.95rem; color: #92400e;">
                                <i class="bi bi-hourglass-split me-1"></i>
                                الطلب قيد المراجعة. لم يتم الرد عليه بعد.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- نموذج الرد --}}
            @php
                $canRespond = !$complaintSuggestion->response || 
                              $isSuperAdmin || 
                              ($complaintSuggestion->responded_by && $complaintSuggestion->responded_by == auth()->id());
            @endphp

            @if($canRespond)
            <div class="general-card mt-3">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-reply me-2"></i>
                            {{ $complaintSuggestion->response ? 'تعديل الرد' : 'الرد على الطلب' }}
                        </h5>
                    </div>
                </div>
                <div class="card-body pb-4">
                    <form action="{{ route('admin.complaints-suggestions.respond', $complaintSuggestion) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-chat-text me-1"></i>
                                الرد <span class="text-danger">*</span>
                            </label>
                            <textarea name="response" class="form-control" rows="6" 
                                      placeholder="اكتب ردك هنا...">{{ old('response', $complaintSuggestion->response) }}</textarea>
                            @error('response')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-tag me-1"></i>
                                تحديث الحالة <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ old('status', $complaintSuggestion->status) == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="in_progress" {{ old('status', $complaintSuggestion->status) == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                                <option value="resolved" {{ old('status', $complaintSuggestion->status) == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                <option value="rejected" {{ old('status', $complaintSuggestion->status) == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            </select>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>
                                {{ $complaintSuggestion->response ? 'تحديث الرد' : 'إرسال الرد' }}
                            </button>
                            <a href="{{ route('admin.complaints-suggestions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x me-1"></i>
                                إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
