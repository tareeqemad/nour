@extends('layouts.admin')

@section('title', 'رسالة جديدة')

@php
    $breadcrumbTitle = 'رسالة جديدة';
    $breadcrumbParent = 'الرسائل';
    $breadcrumbParentUrl = route('admin.messages.index');
    $user = auth()->user();
    $isSuperAdmin = $user->isSuperAdmin();
    $isAdmin = $user->isAdmin();
    $isCompanyOwner = $user->isCompanyOwner();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header-form title="إرسال رسالة جديدة" icon="bi-envelope-plus" :backRoute="route('admin.messages.index')" />

                <div class="card-body p-4">
                    <form action="{{ route('admin.messages.store') }}" method="POST" id="messageForm" enctype="multipart/form-data">
                        @csrf

                        {{-- Section: المرسل إليه --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">المرسل إليه</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">إرسال إلى <span class="text-danger">*</span></label>
                                <select name="send_to" id="sendTo" class="form-select @error('send_to') is-invalid @enderror" required>
                                    <option value="">اختر نوع المرسل إليه</option>
                                    @if($isSuperAdmin || $isAdmin)
                                        <option value="all_operators" {{ old('send_to') == 'all_operators' ? 'selected' : '' }}>جميع المشغلين</option>
                                        <option value="operator" {{ old('send_to') == 'operator' ? 'selected' : '' }}>مشغل محدد</option>
                                        <option value="user" {{ old('send_to') == 'user' ? 'selected' : '' }}>مستخدم محدد</option>
                                    @elseif($isCompanyOwner)
                                        <option value="my_staff" {{ old('send_to') == 'my_staff' ? 'selected' : '' }}>جميع موظفي المشغل</option>
                                        <option value="operator" {{ old('send_to') == 'operator' ? 'selected' : '' }}>مشغل آخر</option>
                                        <option value="user" {{ old('send_to') == 'user' ? 'selected' : '' }}>مستخدم محدد</option>
                                    @endif
                                </select>
                                @error('send_to')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 d-none" id="operatorField">
                                <label class="form-label fw-semibold">المشغل <span class="text-danger">*</span></label>
                                <select name="operator_id" id="operatorId" class="form-select select2 @error('operator_id') is-invalid @enderror">
                                    <option value="">اختر المشغل</option>
                                    @foreach($operators as $operator)
                                        <option value="{{ $operator->id }}" {{ old('operator_id') == $operator->id ? 'selected' : '' }}>
                                            {{ $operator->unit_number ? $operator->unit_number . ' - ' : '' }}{{ $operator->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('operator_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 d-none" id="userField">
                                <label class="form-label fw-semibold">المستخدم <span class="text-danger">*</span></label>
                                <select name="receiver_id" id="receiverId" class="form-select select2 @error('receiver_id') is-invalid @enderror">
                                    <option value="">اختر المستخدم</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ old('receiver_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->role_name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('receiver_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Section: محتوى الرسالة --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">محتوى الرسالة</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">الموضوع <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror"
                                       value="{{ old('subject') }}" required maxlength="255" placeholder="أدخل موضوع الرسالة">
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">محتوى الرسالة <span class="text-danger">*</span></label>
                                <textarea name="body" id="body" class="form-control @error('body') is-invalid @enderror" rows="8"
                                          required maxlength="5000" placeholder="أدخل محتوى الرسالة...">{{ old('body') }}</textarea>
                                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">الحد الأقصى: 5000 حرف</small>
                                    <small class="text-muted" id="charCount">0 / 5000</small>
                                </div>
                            </div>
                        </div>

                        {{-- Section: المرفقات --}}
                        <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">المرفقات (اختياري)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">صورة مرفقة</label>
                                <input type="file" name="attachment" id="attachment"
                                       class="form-control @error('attachment') is-invalid @enderror"
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="text-muted">JPEG, PNG, GIF, WEBP | الحد الأقصى: 10 ميجابايت</small>
                            </div>
                            <div class="col-md-6">
                                <div id="attachmentPreview" class="d-none">
                                    <img id="attachmentPreviewImg" src="" alt="معاينة" class="img-thumbnail" style="max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-block" id="removeAttachment">
                                        <i class="bi bi-x-circle me-1"></i>إزالة
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                            <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send me-2"></i>إرسال الرسالة
                            </button>
                        </div>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script>
(function() {
    'use strict';

    $(document).ready(function() {
        // Select2
        $('.select2').select2({ dir: 'rtl', language: { noResults: function() { return "لا توجد نتائج"; } }, placeholder: "اختر من القائمة", allowClear: true });

        // Character counter
        const $body = $('#body'), $charCount = $('#charCount');
        function updateCharCount() {
            const len = $body.val().length;
            $charCount.text(len + ' / 5000').toggleClass('text-danger', len > 5000);
        }
        $body.on('input', updateCharCount);
        updateCharCount();

        // File preview
        const $attachment = $('#attachment'), $preview = $('#attachmentPreview'), $previewImg = $('#attachmentPreviewImg');

        $attachment.on('change', function(e) {
            const file = e.target.files[0];
            if (!file) { $preview.addClass('d-none'); return; }
            if (file.size > 10 * 1024 * 1024) {
                AdminCRUD.notify('error', 'حجم الصورة يجب ألا يتجاوز 10 ميجابايت');
                $(this).val('');
                $preview.addClass('d-none');
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) { $previewImg.attr('src', e.target.result); $preview.removeClass('d-none'); };
            reader.readAsDataURL(file);
        });

        $('#removeAttachment').on('click', function() { $attachment.val(''); $preview.addClass('d-none'); $previewImg.attr('src', ''); });

        // Conditional fields
        $('#sendTo').on('change', function() {
            const val = $(this).val();
            $('#operatorField, #userField').addClass('d-none');
            $('#operatorId').prop('required', false).val('').trigger('change');
            $('#receiverId').prop('required', false).val('').trigger('change');

            if (val === 'operator') { $('#operatorField').removeClass('d-none'); $('#operatorId').prop('required', true); }
            else if (val === 'user') { $('#userField').removeClass('d-none'); $('#receiverId').prop('required', true); }
        });

        if ($('#sendTo').val()) $('#sendTo').trigger('change');

        // Submit validation
        $('#messageForm').on('submit', function(e) {
            const sendTo = $('#sendTo').val();
            if (!sendTo) { e.preventDefault(); AdminCRUD.notify('error', 'يرجى اختيار نوع المرسل إليه'); return false; }
            if (sendTo === 'operator' && !$('#operatorId').val()) { e.preventDefault(); AdminCRUD.notify('error', 'يرجى اختيار المشغل'); return false; }
            if (sendTo === 'user' && !$('#receiverId').val()) { e.preventDefault(); AdminCRUD.notify('error', 'يرجى اختيار المستخدم'); return false; }
            $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...');
        });
    });
})();
</script>
@endpush
