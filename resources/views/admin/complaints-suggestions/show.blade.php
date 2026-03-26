@extends('layouts.admin')

@section('title', 'تفاصيل الشكوى/المقترح')

@php
    $breadcrumbTitle = 'تفاصيل الشكوى/المقترح';
    $breadcrumbParent = 'الشكاوى والمقترحات';
    $breadcrumbParentUrl = route('admin.complaints-suggestions.index');
    $isSuperAdmin = auth()->user()->isSuperAdmin();
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form title="تفاصيل الشكوى/المقترح" icon="bi-chat-left-text" :backRoute="route('admin.complaints-suggestions.index')">
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.complaints-suggestions.edit', $complaintSuggestion) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil me-1"></i>تعديل
                        </a>
                    @endif
                </x-admin.card-header-form>

                <div class="card-body p-4">
                    {{-- رمز التتبع + النوع + الحالة --}}
                    <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
                        <code style="background: #e9ecef; padding: 6px 12px; border-radius: 6px; font-size: 0.95rem; color: #0d6efd; font-weight: 700;">{{ $complaintSuggestion->tracking_code }}</code>
                        <span class="badge bg-{{ $complaintSuggestion->type == 'complaint' ? 'danger' : 'info' }}">{{ $complaintSuggestion->type_label }}</span>
                        <span class="badge bg-{{ ['pending' => 'warning', 'in_progress' => 'primary', 'resolved' => 'success', 'rejected' => 'danger'][$complaintSuggestion->status] ?? 'secondary' }}">{{ $complaintSuggestion->status_label }}</span>
                    </div>

                    {{-- معلومات المرسل --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <x-admin.field label="الاسم" :value="$complaintSuggestion->name" />
                        </div>
                        <div class="col-md-4">
                            <x-admin.field label="رقم الهاتف" :value="$complaintSuggestion->phone" />
                        </div>
                        @if($complaintSuggestion->email)
                            <div class="col-md-4">
                                <x-admin.field label="البريد الإلكتروني" :value="$complaintSuggestion->email" />
                            </div>
                        @endif
                    </div>

                    {{-- الموقع والمشغل --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <x-admin.field label="المحافظة" :value="$complaintSuggestion->getGovernorateLabel() ?? 'غير محدد'" />
                        </div>
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
                        @if($complaintSuggestion->operator)
                            <div class="col-md-4">
                                <x-admin.field label="المشغل" :value="$complaintSuggestion->operator->name" />
                            </div>
                        @endif
                    </div>

                    {{-- التواريخ --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <x-admin.field label="تاريخ الإرسال" :value="$complaintSuggestion->created_at->format('Y-m-d H:i')" />
                        </div>
                        @if($complaintSuggestion->responded_at)
                            <div class="col-md-4">
                                <x-admin.field label="تاريخ الرد" :value="$complaintSuggestion->responded_at->format('Y-m-d H:i')" />
                            </div>
                        @endif
                        @if($complaintSuggestion->responder)
                            <div class="col-md-4">
                                <x-admin.field label="الرد من" :value="$complaintSuggestion->responder->name" />
                            </div>
                        @endif
                    </div>

                    {{-- الرسالة --}}
                    <x-admin.section title="الرسالة" icon="bi-chat-dots" :boxed="true">
                        <div class="text-break" style="white-space: pre-wrap; line-height: 1.6; font-size: 0.95rem; color: var(--color-text-main, #1F2937);">{{ $complaintSuggestion->message }}</div>
                    </x-admin.section>

                    {{-- الصورة المرفقة --}}
                    @if($complaintSuggestion->image)
                        <x-admin.section title="الصورة المرفقة" icon="bi-image" :boxed="true">
                            <div class="text-center">
                                <a href="{{ asset('storage/' . $complaintSuggestion->image) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $complaintSuggestion->image) }}" alt="صورة مرفقة" class="img-fluid rounded" style="max-height: 400px;">
                                </a>
                            </div>
                        </x-admin.section>
                    @endif

                    {{-- الرد --}}
                    @if($complaintSuggestion->response)
                        <x-admin.section title="رد الإدارة" icon="bi-reply" :boxed="true">
                            <div class="text-break" style="white-space: pre-wrap; line-height: 1.6; font-size: 0.95rem; color: #1e3a8a;">{{ $complaintSuggestion->response }}</div>
                        </x-admin.section>
                    @else
                        <div class="mt-3 p-3 rounded-3" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2);">
                            <i class="bi bi-hourglass-split me-1 text-warning"></i>
                            <span style="color: #92400e;">الطلب قيد المراجعة. لم يتم الرد عليه بعد.</span>
                        </div>
                    @endif
                </div>
            </x-admin.card>

            {{-- نموذج الرد --}}
            @php
                $canRespond = !$complaintSuggestion->response ||
                              $isSuperAdmin ||
                              ($complaintSuggestion->responded_by && $complaintSuggestion->responded_by == auth()->id());
            @endphp

            @if($canRespond)
            <x-admin.card class="mt-3">
                <x-admin.card-header-form :title="$complaintSuggestion->response ? 'تعديل الرد' : 'الرد على الطلب'" icon="bi-reply" />
                <div class="card-body p-4">
                    <form action="{{ route('admin.complaints-suggestions.respond', $complaintSuggestion) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">الرد <span class="text-danger">*</span></label>
                                <textarea name="response" class="form-control" rows="5" placeholder="اكتب ردك هنا...">{{ old('response', $complaintSuggestion->response) }}</textarea>
                                @error('response')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تحديث الحالة <span class="text-danger">*</span></label>
                                <select name="status" class="form-select">
                                    <option value="pending" {{ old('status', $complaintSuggestion->status) == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="in_progress" {{ old('status', $complaintSuggestion->status) == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                                    <option value="resolved" {{ old('status', $complaintSuggestion->status) == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                    <option value="rejected" {{ old('status', $complaintSuggestion->status) == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                                @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                            <a href="{{ route('admin.complaints-suggestions.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>{{ $complaintSuggestion->response ? 'تحديث الرد' : 'إرسال الرد' }}
                            </button>
                        </div>
                    </form>
                </div>
            </x-admin.card>
            @endif
        </div>
    </div>
</div>
@endsection
