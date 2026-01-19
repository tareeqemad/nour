@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'طلب الانضمام للمنصة - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/icons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/front/css/join.css') }}">
@endpush

@section('content')
<div class="join-page">
    <div class="join-container">
        <div class="join-header">
            <h1>طلب الانضمام للمنصة</h1>
            <p>التقدم بطلب انضمام كـ مشغل وحدة توليد للمنصة الرقمية لإدارة سوق الطاقة</p>
        </div>

        <div class="form-card">

            @if($errors->any())
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px; flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; margin-bottom: 0.75rem; font-size: 1rem;">حدث خطأ</div>
                        <ul style="margin: 0; padding: 0; list-style: none;">
                            @foreach($errors->all() as $error)
                                <li style="margin-bottom: 0.5rem; padding-right: 0.5rem; position: relative;">
                                    <span style="position: absolute; right: 0; top: 0.5rem; width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
                                    <span style="display: block; padding-right: 1rem;">{!! e($error) !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('front.join.store') }}" method="POST" id="joinForm">
                @csrf

                <!-- بيانات المالك -->
                <h3 style="color: #1e293b; font-weight: 700; margin-bottom: 1.5rem; font-size: 1.3rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e2e8f0;">
                    <i class="bi bi-person-badge" style="margin-left: 0.5rem; color: #3b82f6;"></i>
                    بيانات المالك
                </h3>

                <!-- اسم المالك بالعربية والإنجليزية -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="owner_name" class="form-label">
                            <i class="bi bi-person" style="margin-left: 0.5rem; color: #3b82f6;"></i>
                            اسم المالك بالعربية <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="owner_name" 
                            name="owner_name" 
                            class="form-input" 
                            placeholder="أدخل اسم المالك رباعي بالعربية"
                            value="{{ e(old('owner_name', '')) }}"
                            required
                        >
                        <div class="form-hint">
                            <i class="bi bi-info-circle" style="margin-left: 0.25rem; color: #3b82f6;"></i>
                            اسم المالك القانوني
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="owner_name_en" class="form-label">
                            <i class="bi bi-person" style="margin-left: 0.5rem; color: #3b82f6;"></i>
                            اسم المالك بالإنجليزية <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="owner_name_en" 
                            name="owner_name_en" 
                            class="form-input" 
                            placeholder="Enter owner name in English"
                            value="{{ e(old('owner_name_en', '')) }}"
                            required
                        >
                    </div>
                </div>

                <!-- رقم هوية المالك -->
                <div class="form-group">
                    <label for="owner_id_number" class="form-label">
                        <i class="bi bi-card-heading" style="margin-left: 0.5rem; color: #3b82f6;"></i>
                        رقم هوية المالك <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="owner_id_number" 
                        name="owner_id_number" 
                        class="form-input" 
                        placeholder="أدخل رقم هوية المالك (9 أرقام)"
                        value="{{ e(old('owner_id_number', '')) }}"
                        maxlength="9"
                        required
                    >
                    <div class="form-hint">
                        <i class="bi bi-info-circle" style="margin-left: 0.25rem; color: #3b82f6;"></i>
                        يجب أن يكون 9 أرقام
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- بيانات المشغل -->
                <h3 style="color: #1e293b; font-weight: 700; margin-bottom: 1.5rem; margin-top: 2rem; font-size: 1.3rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e2e8f0;">
                    <i class="bi bi-building" style="margin-left: 0.5rem; color: #10b981;"></i>
                    بيانات المشغل
                </h3>

                <!-- اسم المشغل بالعربية والإنجليزية -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="operator_name" class="form-label">
                            <i class="bi bi-building" style="margin-left: 0.5rem; color: #10b981;"></i>
                            اسم المشغل بالعربية <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="operator_name" 
                            name="operator_name" 
                            class="form-input" 
                            placeholder="أدخل اسم المشغل/الشركة بالعربية"
                            value="{{ e(old('operator_name', '')) }}"
                            required
                        >
                        <div class="form-hint">
                            <i class="bi bi-info-circle" style="margin-left: 0.25rem; color: #10b981;"></i>
                            اسم المشغل أو الشركة
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="operator_name_en" class="form-label">
                            <i class="bi bi-building" style="margin-left: 0.5rem; color: #10b981;"></i>
                            اسم المشغل بالإنجليزية
                        </label>
                        <input 
                            type="text" 
                            id="operator_name_en" 
                            name="operator_name_en" 
                            class="form-input" 
                            placeholder="Enter operator/company name in English (optional)"
                            value="{{ e(old('operator_name_en', '')) }}"
                        >
                    </div>
                </div>

                <!-- رقم هوية المشغل ورقم الموبايل -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="operator_id_number" class="form-label">
                            <i class="bi bi-card-text" style="margin-left: 0.5rem; color: #10b981;"></i>
                            رقم هوية المشغل
                        </label>
                        <input 
                            type="text" 
                            id="operator_id_number" 
                            name="operator_id_number" 
                            class="form-input" 
                            placeholder="أدخل رقم هوية المشغل (9 أرقام) - اختياري"
                            value="{{ e(old('operator_id_number', '')) }}"
                            maxlength="9"
                        >
                        <div class="form-hint">
                            <i class="bi bi-info-circle" style="margin-left: 0.25rem; color: #10b981;"></i>
                            يجب أن يكون 9 أرقام (اختياري)
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="bi bi-phone" style="margin-left: 0.5rem; color: #3b82f6;"></i>
                            رقم الموبايل <span class="required">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-input" 
                            placeholder="0591234567 أو 0561234567"
                            value="{{ e(old('phone', '')) }}"
                            maxlength="10"
                            required
                        >
                        <div class="form-hint">
                            <i class="bi bi-info-circle" style="margin-left: 0.25rem; color: #3b82f6;"></i>
                            يجب أن يكون 10 أرقام ويبدأ بـ 059 أو 056
                        </div>
                    </div>
                </div>

                <!-- البريد الإلكتروني -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope" style="margin-left: 0.5rem; color: #3b82f6;"></i>
                        البريد الإلكتروني
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="example@email.com"
                        value="{{ e(old('email', '')) }}"
                    >
                </div>

                <div class="section-divider"></div>

                <!-- إقرار بصحة البيانات -->
                <div class="form-group">
                    <div class="checkbox-group">
                        <input 
                            type="checkbox" 
                            id="data_accuracy" 
                            name="data_accuracy" 
                            value="1"
                            required
                        >
                        <label for="data_accuracy">
                            أقر بصحة جميع البيانات المقدمة في هذا الطلب <span class="required">*</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    إرسال الطلب
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // عرض إشعار النجاح إذا كان موجوداً
        @if(session('success'))
            @php
                try {
                    $successMsg = session('success');
                    // Message is already cleaned by AppServiceProvider View Composer
                    // Just ensure it's safe for JSON encoding
                    if (!is_string($successMsg)) {
                        $successMsg = 'تم الإرسال بنجاح';
                    }
                    // Encode to JSON with UTF-8 support
                    $jsonMsg = json_encode($successMsg, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE | JSON_HEX_APOS | JSON_HEX_QUOT);
                    if ($jsonMsg === false || $jsonMsg === 'null') {
                        $jsonMsg = json_encode('تم الإرسال بنجاح', JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                    }
                } catch (\Exception $e) {
                    $jsonMsg = json_encode('تم الإرسال بنجاح', JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                }
            @endphp
            try {
                showToastNotification({!! $jsonMsg !!});
            } catch (e) {
                console.error('Error showing notification:', e);
            }
        @endif

        const form = document.getElementById('joinForm');
        const submitBtn = document.getElementById('submitBtn');
        const phoneInput = document.getElementById('phone');
        const ownerIdNumberInput = document.getElementById('owner_id_number');
        const operatorIdNumberInput = document.getElementById('operator_id_number');

        // دالة لعرض إشعار منبثق
        function showToastNotification(message) {
            // Clean message to ensure valid UTF-8
            if (typeof message !== 'string') {
                message = String(message || '');
            }
            // Remove any invalid UTF-8 characters
            message = message.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '');
            // Escape HTML to prevent XSS
            const messageText = document.createTextNode(message).textContent || message;
            
            // إزالة أي إشعار موجود مسبقاً
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) {
                existingToast.remove();
            }

            // إنشاء الإشعار
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            const titleElement = document.createElement('div');
            titleElement.className = 'toast-notification-title';
            titleElement.textContent = 'تم بنجاح!';
            const messageElement = document.createElement('div');
            messageElement.className = 'toast-notification-message';
            messageElement.textContent = messageText;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'toast-notification-content';
            contentDiv.appendChild(titleElement);
            contentDiv.appendChild(messageElement);
            
            const svgElement = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgElement.setAttribute('viewBox', '0 0 24 24');
            svgElement.setAttribute('fill', 'none');
            svgElement.setAttribute('stroke', 'currentColor');
            svgElement.setAttribute('stroke-width', '2.5');
            const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path1.setAttribute('d', 'M22 11.08V12a10 10 0 1 1-5.93-9.14');
            const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
            path2.setAttribute('points', '22 4 12 14.01 9 11.01');
            svgElement.appendChild(path1);
            svgElement.appendChild(path2);
            
            toast.appendChild(svgElement);
            toast.appendChild(contentDiv);

            // إضافة الإشعار للصفحة
            document.body.appendChild(toast);

            // إزالة الإشعار بعد 5 ثواني
            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s ease-in forwards';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }

        // تحقق من رقم هوية المالك (9 أرقام فقط)
        ownerIdNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            // حد أقصى 9 أرقام
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            e.target.value = value;
        });

        // تحقق من رقم هوية المشغل (9 أرقام فقط)
        operatorIdNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            // حد أقصى 9 أرقام
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            e.target.value = value;
        });

        // تحقق من رقم الموبايل (10 أرقام - يجب أن يبدأ بـ 059 أو 056)
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('0')) {
                value = '0' + value;
            }
            // التحقق من أن الرقم يبدأ بـ 059 أو 056
            if (value.length >= 3) {
                const prefix = value.substring(0, 3);
                if (prefix !== '059' && prefix !== '056') {
                    // إذا لم يبدأ بـ 059 أو 056، نصححه
                    if (value.startsWith('05')) {
                        // إذا بدأ بـ 05، نتركه للمستخدم لإكماله
                        if (value.length > 2 && value[2] !== '9' && value[2] !== '6') {
                            // إذا لم يكن 9 أو 6، نحذف الرقم الخاطئ
                            value = value.substring(0, 2);
                        }
                    } else if (value.startsWith('0') && value.length > 1 && value[1] !== '5') {
                        // إذا لم يبدأ بـ 05، نحذف الرقم
                        value = '0';
                    }
                }
            }
            // حد أقصى 10 أرقام
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });

        // منع الإرسال المزدوج
        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg style="width: 20px; height: 20px;" class="animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                جاري الإرسال...
            `;
        });
    });
</script>
@endpush

