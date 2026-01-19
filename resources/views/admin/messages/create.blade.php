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
    <link rel="stylesheet" href="{{ asset('assets/admin/css/messages.css') }}">
@endpush

@section('content')
<div class="general-page">
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

                        <div class="row g-3">
                            {{-- نوع المرسل إليه --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-check me-1"></i>
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
                                <small class="form-text text-muted">اختر نوع المرسل إليه للرسالة</small>
                            </div>

                            {{-- المشغل (يظهر عند اختيار "مشغل محدد") --}}
                            <div class="col-md-12" id="operatorField" style="display: none;">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1"></i>
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
                                <small class="form-text text-muted">اختر المشغل المراد إرسال الرسالة إليه</small>
                            </div>

                            {{-- المستخدم (يظهر عند اختيار "مستخدم محدد") --}}
                            <div class="col-md-12" id="userField" style="display: none;">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person me-1"></i>
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
                                <small class="form-text text-muted">اختر المستخدم المراد إرسال الرسالة إليه</small>
                            </div>

                            {{-- الموضوع --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-chat-text me-1"></i>
                                    الموضوع <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" 
                                       value="{{ old('subject') }}" required maxlength="255" 
                                       placeholder="أدخل موضوع الرسالة">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">الحد الأقصى: 255 حرف</small>
                            </div>

                            {{-- المحتوى --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-file-text me-1"></i>
                                    محتوى الرسالة <span class="text-danger">*</span>
                                </label>
                                <textarea name="body" id="body" class="form-control @error('body') is-invalid @enderror" rows="10" 
                                          required maxlength="5000" 
                                          placeholder="أدخل محتوى الرسالة">{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="form-text text-muted">الحد الأقصى: 5000 حرف</small>
                                    <small class="form-text text-muted" id="charCount">0 / 5000</small>
                                </div>
                            </div>

                            {{-- الصورة المرفقة --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-image me-1"></i>
                                    صورة مرفقة (اختياري)
                                </label>
                                <input type="file" name="attachment" id="attachment" 
                                       class="form-control @error('attachment') is-invalid @enderror" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                @error('attachment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    الصيغ المدعومة: JPEG, JPG, PNG, GIF, WEBP. الحد الأقصى: 10 ميجابايت
                                </small>
                                <div id="attachmentPreview" class="mt-3" style="display: none;">
                                    <img id="attachmentPreviewImg" src="" alt="معاينة الصورة" 
                                         class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeAttachment">
                                            <i class="bi bi-x-circle me-1"></i>
                                            إزالة الصورة
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>
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

        // Attachment preview
        const $attachment = $('#attachment');
        const $preview = $('#attachmentPreview');
        const $previewImg = $('#attachmentPreviewImg');
        const $removeBtn = $('#removeAttachment');

        $attachment.on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    AdminCRUD.notify('error', 'حجم الصورة يجب ألا يتجاوز 10 ميجابايت');
                    $(this).val('');
                    $preview.hide();
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    $previewImg.attr('src', e.target.result);
                    $preview.show();
                };
                reader.readAsDataURL(file);
            } else {
                $preview.hide();
            }
        });

        $removeBtn.on('click', function() {
            $attachment.val('');
            $preview.hide();
            $previewImg.attr('src', '');
        });

        // Show/hide fields based on send_to selection
        $('#sendTo').on('change', function() {
            const sendTo = $(this).val();
            
            // Hide all fields first
            $('#operatorField').slideUp(200);
            $('#userField').slideUp(200);
            $('#operatorId').prop('required', false).val('').trigger('change');
            $('#receiverId').prop('required', false).val('').trigger('change');
            
            if (sendTo === 'operator') {
                $('#operatorField').slideDown(200);
                $('#operatorId').prop('required', true);
            } else if (sendTo === 'user') {
                $('#userField').slideDown(200);
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
            const $submitBtn = $(this).find('button[type="submit"]');
            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...');
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
