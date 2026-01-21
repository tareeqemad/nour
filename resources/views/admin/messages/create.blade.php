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
    <link rel="stylesheet" href="{{ asset('assets/admin/css/messages-create.css') }}">
@endpush

@section('content')
<div class="general-page messages-create-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-envelope-plus me-2"></i>
                            إرسال رسالة جديدة
                        </h5>
                        <div class="general-subtitle">
                            قم بإدخال بيانات الرسالة وإرسالها للمستلمين
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <form action="{{ route('admin.messages.store') }}" method="POST" id="messageForm" enctype="multipart/form-data">
                        @csrf

                        {{-- قسم المرسل إليه --}}
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="bi bi-person-check"></i>
                                المرسل إليه
                            </h6>
                            
                            <div class="field-group">
                                <label class="form-label">
                                    <i class="bi bi-send"></i>
                                    إرسال إلى <span class="text-danger">*</span>
                                </label>
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
                                @error('send_to')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    اختر نوع المرسل إليه للرسالة
                                </small>
                            </div>

                            {{-- المشغل (يظهر عند اختيار "مشغل محدد") --}}
                            <div class="field-group conditional-field hidden" id="operatorField">
                                <label class="form-label">
                                    <i class="bi bi-building"></i>
                                    المشغل <span class="text-danger">*</span>
                                </label>
                                <select name="operator_id" id="operatorId" class="form-select select2 @error('operator_id') is-invalid @enderror">
                                    <option value="">اختر المشغل</option>
                                    @foreach($operators as $operator)
                                        <option value="{{ $operator->id }}" {{ old('operator_id') == $operator->id ? 'selected' : '' }}>
                                            {{ $operator->unit_number ? $operator->unit_number . ' - ' : '' }}{{ $operator->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('operator_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    اختر المشغل المراد إرسال الرسالة إليه
                                </small>
                            </div>

                            {{-- المستخدم (يظهر عند اختيار "مستخدم محدد") --}}
                            <div class="field-group conditional-field hidden" id="userField">
                                <label class="form-label">
                                    <i class="bi bi-person"></i>
                                    المستخدم <span class="text-danger">*</span>
                                </label>
                                <select name="receiver_id" id="receiverId" class="form-select select2 @error('receiver_id') is-invalid @enderror">
                                    <option value="">اختر المستخدم</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ old('receiver_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->role_name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('receiver_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    اختر المستخدم المراد إرسال الرسالة إليه
                                </small>
                            </div>
                        </div>

                        {{-- قسم محتوى الرسالة --}}
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="bi bi-envelope"></i>
                                محتوى الرسالة
                            </h6>

                            <div class="field-group">
                                <label class="form-label">
                                    <i class="bi bi-chat-text"></i>
                                    الموضوع <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" 
                                       value="{{ old('subject') }}" required maxlength="255" 
                                       placeholder="أدخل موضوع الرسالة">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    الحد الأقصى: 255 حرف
                                </small>
                            </div>

                            <div class="field-group">
                                <label class="form-label">
                                    <i class="bi bi-file-text"></i>
                                    محتوى الرسالة <span class="text-danger">*</span>
                                </label>
                                <textarea name="body" id="body" class="form-control @error('body') is-invalid @enderror" rows="12" 
                                          required maxlength="5000" 
                                          placeholder="أدخل محتوى الرسالة...">{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="char-counter">
                                    <small class="form-text">
                                        <i class="bi bi-info-circle"></i>
                                        الحد الأقصى: 5000 حرف
                                    </small>
                                    <small class="char-count" id="charCount">0 / 5000</small>
                                </div>
                            </div>
                        </div>

                        {{-- قسم المرفقات --}}
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="bi bi-paperclip"></i>
                                المرفقات (اختياري)
                            </h6>

                            <div class="field-group">
                                <label class="form-label">
                                    <i class="bi bi-image"></i>
                                    صورة مرفقة
                                </label>
                                <div class="file-upload-wrapper" id="fileUploadWrapper">
                                    <i class="bi bi-cloud-upload file-upload-icon"></i>
                                    <div class="file-upload-text">انقر لاختيار صورة أو اسحبها هنا</div>
                                    <div class="file-upload-hint">الصيغ المدعومة: JPEG, JPG, PNG, GIF, WEBP | الحد الأقصى: 10 ميجابايت</div>
                                    <input type="file" name="attachment" id="attachment" 
                                           class="file-input-hidden @error('attachment') is-invalid @enderror" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                </div>
                                @error('attachment')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                                
                                <div id="attachmentPreview" class="attachment-preview" style="display: none;">
                                    <img id="attachmentPreviewImg" src="" alt="معاينة الصورة" class="attachment-preview-img">
                                    <div class="attachment-preview-actions">
                                        <button type="button" class="btn btn-outline-danger" id="removeAttachment">
                                            <i class="bi bi-x-circle me-1"></i>
                                            إزالة الصورة
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- أزرار الإجراءات --}}
                        <div class="form-actions">
                            <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send"></i>
                                إرسال الرسالة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
        // Initialize Select2
        $('.select2').select2({
            dir: 'rtl',
            language: {
                noResults: function() {
                    return "لا توجد نتائج";
                },
                searching: function() {
                    return "جاري البحث...";
                }
            },
            placeholder: "اختر من القائمة",
            allowClear: true
        });

        // Character counter for body textarea
        const $body = $('#body');
        const $charCount = $('#charCount');
        
        function updateCharCount() {
            const length = $body.val().length;
            $charCount.text(`${length} / 5000`);
            if (length > 5000) {
                $charCount.addClass('text-danger');
            } else {
                $charCount.removeClass('text-danger');
            }
        }
        
        $body.on('input', updateCharCount);
        updateCharCount(); // Initial count

        // File upload enhancement
        const $fileWrapper = $('#fileUploadWrapper');
        const $attachment = $('#attachment');
        const $preview = $('#attachmentPreview');
        const $previewImg = $('#attachmentPreviewImg');
        const $removeBtn = $('#removeAttachment');

        // Click on wrapper to trigger file input
        $fileWrapper.on('click', function(e) {
            if (!$(e.target).is('input')) {
                $attachment.click();
            }
        });

        $attachment.on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    AdminCRUD.notify('error', 'حجم الصورة يجب ألا يتجاوز 10 ميجابايت');
                    $(this).val('');
                    $preview.hide();
                    $fileWrapper.removeClass('has-file');
                    return;
                }
                
                $fileWrapper.addClass('has-file');
                const reader = new FileReader();
                reader.onload = function(e) {
                    $previewImg.attr('src', e.target.result);
                    $preview.slideDown(300);
                };
                reader.readAsDataURL(file);
            } else {
                $preview.slideUp(300);
                $fileWrapper.removeClass('has-file');
            }
        });

        $removeBtn.on('click', function() {
            $attachment.val('');
            $preview.slideUp(300);
            $previewImg.attr('src', '');
            $fileWrapper.removeClass('has-file');
        });

        // Show/hide fields based on send_to selection
        $('#sendTo').on('change', function() {
            const sendTo = $(this).val();
            
            // Hide all fields first
            $('#operatorField').addClass('hidden');
            $('#userField').addClass('hidden');
            $('#operatorId').prop('required', false).val('').trigger('change');
            $('#receiverId').prop('required', false).val('').trigger('change');
            
            if (sendTo === 'operator') {
                $('#operatorField').removeClass('hidden');
                $('#operatorId').prop('required', true);
            } else if (sendTo === 'user') {
                $('#userField').removeClass('hidden');
                $('#receiverId').prop('required', true);
            }
        });

        // Trigger change on load if there's an old value
        if ($('#sendTo').val()) {
            $('#sendTo').trigger('change');
        }

        // Form submission validation
        $('#messageForm').on('submit', function(e) {
            const sendTo = $('#sendTo').val();
            
            if (!sendTo) {
                e.preventDefault();
                AdminCRUD.notify('error', 'يرجى اختيار نوع المرسل إليه');
                $('#sendTo').focus();
                return false;
            }
            
            if (sendTo === 'operator' && !$('#operatorId').val()) {
                e.preventDefault();
                AdminCRUD.notify('error', 'يرجى اختيار المشغل');
                $('#operatorId').focus();
                return false;
            }
            
            if (sendTo === 'user' && !$('#receiverId').val()) {
                e.preventDefault();
                AdminCRUD.notify('error', 'يرجى اختيار المستخدم');
                $('#receiverId').focus();
                return false;
            }

            // Show loading
            const $submitBtn = $('#submitBtn');
            $submitBtn.addClass('btn-loading').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...');
        });

        // تحديث لوحة الرسائل بعد الإرسال الناجح
        $(document).on('message:sent', function() {
            if (window.MessagesPanel) {
                window.MessagesPanel.refresh();
            }
        });
    });
})();
</script>
@endpush
