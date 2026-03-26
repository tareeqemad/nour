@extends('layouts.admin')

@section('title', 'تعديل الطلب')

@php
    $breadcrumbTitle = 'تعديل الطلب';
    $breadcrumbParent = 'الشكاوى والمقترحات';
    $breadcrumbParentUrl = route('admin.complaints-suggestions.index');
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form :title="'رمز التتبع: ' . $complaintSuggestion->tracking_code" icon="bi-chat-left-text" :backRoute="route('admin.complaints-suggestions.show', $complaintSuggestion)" />
                <div class="card-body p-4">
                    <form action="{{ route('admin.complaints-suggestions.update', $complaintSuggestion) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Section: تحديث الحالة --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">تحديث الحالة والرد</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الحالة <span class="text-danger">*</span></label>
                                <select name="status" class="form-select">
                                    <option value="pending" {{ old('status', $complaintSuggestion->status) == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="in_progress" {{ old('status', $complaintSuggestion->status) == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                                    <option value="resolved" {{ old('status', $complaintSuggestion->status) == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                    <option value="rejected" {{ old('status', $complaintSuggestion->status) == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                                @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">النوع</label>
                                <input type="text" class="form-control" value="{{ $complaintSuggestion->type_label }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">الرد</label>
                                <textarea name="response" class="form-control" rows="6" placeholder="اكتب ردك هنا...">{{ old('response', $complaintSuggestion->response) }}</textarea>
                                @error('response')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Section: معلومات الطلب (للاطلاع) --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">معلومات الطلب</h6>
                        <x-admin.section :boxed="true" title="">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <x-admin.field label="الاسم" :value="$complaintSuggestion->name" />
                                </div>
                                <div class="col-md-4">
                                    <x-admin.field label="الهاتف" :value="$complaintSuggestion->phone" />
                                </div>
                                @if($complaintSuggestion->email)
                                    <div class="col-md-4">
                                        <x-admin.field label="البريد" :value="$complaintSuggestion->email" />
                                    </div>
                                @endif
                                @if($complaintSuggestion->generator)
                                    <div class="col-md-4">
                                        <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">المولد</div>
                                        <div style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); font-weight: 500;">
                                            {{ $complaintSuggestion->generator->name ?? 'مولد محذوف' }}
                                            @if($complaintSuggestion->generator->trashed())
                                                <span class="badge bg-secondary ms-1">محذوف</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @php
                                    $operator = null;
                                    if ($complaintSuggestion->generator && $complaintSuggestion->generator->generationUnit) {
                                        $operator = $complaintSuggestion->generator->generationUnit->operator;
                                    }
                                @endphp
                                @if($operator)
                                    <div class="col-md-4">
                                        <x-admin.field label="المشغل" :value="$operator->name" />
                                    </div>
                                @endif
                                <div class="col-12">
                                    <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">الرسالة</div>
                                    <div class="text-break" style="font-size: 0.92rem; color: var(--color-text-main, #1F2937); white-space: pre-wrap; line-height: 1.6;">{{ $complaintSuggestion->message }}</div>
                                </div>
                            </div>
                        </x-admin.section>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top mt-4">
                            <a href="{{ route('admin.complaints-suggestions.show', $complaintSuggestion) }}" class="btn btn-outline-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection
