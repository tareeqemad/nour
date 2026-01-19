{{-- resources/views/admin/complaints-suggestions/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'تعديل الطلب')

@php
    $breadcrumbTitle = 'تعديل الطلب';
    $breadcrumbParent = 'الشكاوى والمقترحات';
    $breadcrumbParentUrl = route('admin.complaints-suggestions.index');
@endphp

@push('styles')
    <style>
        .complaint-edit-page {
            --c-border: #e5e7eb;
            --c-border2: #eef2f7;
            --c-surface: #ffffff;
            --c-subtle: #f8fafc;
            --c-text: #0f172a;
            --c-muted: #64748b;
        }

        .complaint-edit-page .edit-card {
            border: 1px solid var(--c-border);
            border-radius: 14px;
            background: var(--c-surface);
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(15, 23, 42, .04);
        }

        .complaint-edit-page .edit-card-header {
            padding: 1.25rem;
            border-bottom: 2px solid var(--c-border2);
            background: var(--c-subtle);
        }

        .complaint-edit-page .edit-card-title {
            font-weight: 800;
            font-size: 1.1rem;
            margin: 0;
            color: var(--c-text);
        }

        .complaint-edit-page .edit-card-body {
            padding: 1.5rem;
        }
    </style>
@endpush

@section('content')
<div class="complaint-edit-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0">
                        <i class="bi bi-pencil me-2"></i>
                        تعديل الطلب
                    </h4>
                </div>
                <div>
                    <a href="{{ route('admin.complaints-suggestions.show', $complaintSuggestion) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-1"></i>
                        العودة
                    </a>
                </div>
            </div>

            <div class="edit-card">
                <div class="edit-card-header">
                    <h5 class="edit-card-title mb-0">
                        <i class="bi bi-chat-left-text me-2"></i>
                        رمز التتبع: <code>{{ $complaintSuggestion->tracking_code }}</code>
                    </h5>
                </div>
                <div class="edit-card-body">
                    <form action="{{ route('admin.complaints-suggestions.update', $complaintSuggestion) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الحالة <span class="text-danger">*</span></label>
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

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">النوع</label>
                                <input type="text" class="form-control" value="{{ $complaintSuggestion->type_label }}" disabled>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">الرد</label>
                                <textarea name="response" class="form-control" rows="8" 
                                          placeholder="اكتب ردك هنا...">{{ old('response', $complaintSuggestion->response) }}</textarea>
                                @error('response')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">معلومات الطلب</h6>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <small class="text-muted">الاسم:</small>
                                                <div class="fw-semibold">{{ $complaintSuggestion->name }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted">الهاتف:</small>
                                                <div class="fw-semibold">{{ $complaintSuggestion->phone }}</div>
                                            </div>
                                            @if($complaintSuggestion->email)
                                            <div class="col-md-4">
                                                <small class="text-muted">البريد:</small>
                                                <div class="fw-semibold">{{ $complaintSuggestion->email }}</div>
                                            </div>
                                            @endif
                                            @if($complaintSuggestion->generator)
                                            <div class="col-md-4">
                                                <small class="text-muted">المولد:</small>
                                                <div class="fw-semibold">
                                                    {{ $complaintSuggestion->generator->name ?? 'مولد محذوف' }}
                                                    @if($complaintSuggestion->generator && $complaintSuggestion->generator->trashed())
                                                        <span class="badge bg-secondary ms-1" title="مولد محذوف">محذوف</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                            @php
                                                $operator = null;
                                                // المولدات مرتبطة بالمشغل عبر وحدات التوليد فقط
                                                if ($complaintSuggestion->generator && $complaintSuggestion->generator->generationUnit) {
                                                    $operator = $complaintSuggestion->generator->generationUnit->operator;
                                                }
                                            @endphp
                                            @if($operator)
                                            <div class="col-md-4">
                                                <small class="text-muted">المشغل:</small>
                                                <div class="fw-semibold">{{ $operator->name }}</div>
                                            </div>
                                            @endif
                                        </div>
                                        <hr>
                                        <div>
                                            <small class="text-muted">الرسالة:</small>
                                            <div class="mt-2">{{ $complaintSuggestion->message }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                حفظ التغييرات
                            </button>
                            <a href="{{ route('admin.complaints-suggestions.show', $complaintSuggestion) }}" class="btn btn-outline-secondary">
                                إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





