@extends('layouts.admin')

@section('title', 'إضافة رقم مصرح به')

@php
    $breadcrumbTitle = 'إضافة رقم مصرح به';
    $breadcrumbParent = 'إدارة الأرقام المصرح بها';
    $breadcrumbParentUrl = route('admin.authorized-phones.index');
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
                            <i class="bi bi-phone-plus me-2"></i>
                            إضافة رقم مصرح به
                        </h5>
                        <div class="general-subtitle">
                            قم بإدخال بيانات الرقم المصرح به
                        </div>
                    </div>
                    <a href="{{ route('admin.authorized-phones.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-right me-2"></i>
                        رجوع
                    </a>
                </div>

                <form action="{{ route('admin.authorized-phones.store') }}" method="POST" id="phoneForm">
                    @csrf

                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                بيانات الرقم الأساسية
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم الجوال <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="phone" 
                                           id="phoneInput"
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           placeholder="0591234567 أو 0561234567"
                                           value="{{ old('phone') }}"
                                           required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">يجب أن يبدأ الرقم بـ 059 أو 056</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">الاسم</label>
                                    <input type="text" 
                                           name="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           placeholder="اسم صاحب الرقم (اختياري)"
                                           value="{{ old('name') }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">اسم صاحب الرقم أو اسم المشروع</small>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">الملاحظات</label>
                                    <textarea name="notes" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3"
                                              placeholder="ملاحظات إضافية (اختياري)">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="is_active" 
                                               value="1" 
                                               id="is_active"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            تفعيل الرقم
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">الرقم المفعل فقط يمكن استخدامه للتسجيل</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <a href="{{ route('admin.authorized-phones.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-lg me-2"></i>
                                حفظ الرقم
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function($) {
        $(document).ready(function() {
            const $form = $('#phoneForm');
            const $submitBtn = $('#submitBtn');
            const $phoneInput = $('#phoneInput');

            // Format phone number on input
            $phoneInput.on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                if (value.length > 0 && !value.startsWith('0')) {
                    if (value.startsWith('59') || value.startsWith('56')) {
                        value = '0' + value;
                    }
                }
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                $(this).val(value);
            });

            $form.on('submit', function(e) {
                if (!$form[0].checkValidity()) {
                    $form[0].reportValidity();
                    return false;
                }

                $submitBtn.prop('disabled', true);
                const originalText = $submitBtn.html();
                $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');
            });
        });
    })(jQuery);
</script>
@endpush
