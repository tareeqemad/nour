@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'تقديم شكوى أو مقترح - ' . $siteName)

@push('styles')
<style>
    .complaints-create-page {
        padding: 3rem 0 5rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
        min-height: calc(100vh - 70px);
        margin: 0;
        width: 100%;
    }

    .complaints-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .complaints-header {
        text-align: center;
        margin-bottom: 3rem;
        padding: 0;
        background: transparent;
        border: none;
        box-shadow: none;
        position: relative;
    }

    .complaints-header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        display: inline-block;
        padding-bottom: 1.5rem;
    }
    
    .complaints-header h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 4px;
        background: linear-gradient(90deg, transparent 0%, #3b82f6 20%, #10b981 50%, #3b82f6 80%, transparent 100%);
        border-radius: 2px;
    }

    .complaints-header p {
        font-size: 1.1rem;
        color: #64748b;
        font-weight: 500;
        position: relative;
        padding-bottom: 2rem;
        margin-top: 1rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .complaints-header p::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        max-width: 600px;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, rgba(59, 130, 246, 0.2) 20%, rgba(16, 185, 129, 0.3) 50%, rgba(59, 130, 246, 0.2) 80%, transparent 100%);
    }

    .form-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        padding: 3rem;
    }


    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: #1e293b;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .form-label .required {
        color: #ef4444;
    }

    .type-section {
        margin-bottom: 2.5rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 16px;
        border: 2px dashed #cbd5e1;
    }
    
    .type-section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: #1e293b;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .type-section-title .required {
        color: #ef4444;
        font-size: 1.2rem;
    }
    
    .type-section-hint {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 1.25rem;
        padding-right: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .type-section-hint svg {
        width: 18px;
        height: 18px;
        color: #3b82f6;
        flex-shrink: 0;
    }

    .type-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .type-option {
        position: relative;
    }

    .type-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .type-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 1.75rem 1.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        font-weight: 600;
        color: #64748b;
        position: relative;
    }
    
    .type-option-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .type-option:nth-child(1) .type-option-icon {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    }
    
    .type-option:nth-child(1) .type-option-icon svg {
        color: #ef4444;
    }
    
    .type-option:nth-child(2) .type-option-icon {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    }
    
    .type-option:nth-child(2) .type-option-icon svg {
        color: #3b82f6;
    }

    .type-option input[type="radio"]:checked + label {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #3b82f6;
        font-weight: 700;
        box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
        transform: translateY(-2px);
    }
    
    .type-option:nth-child(1) input[type="radio"]:checked + label {
        border-color: #ef4444;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #ef4444;
        box-shadow: 0 4px 16px rgba(239, 68, 68, 0.2);
    }
    
    .type-option:nth-child(1) input[type="radio"]:checked + label .type-option-icon {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        transform: scale(1.1);
    }
    
    .type-option:nth-child(1) input[type="radio"]:checked + label .type-option-icon svg {
        color: white;
    }
    
    .type-option:nth-child(2) input[type="radio"]:checked + label .type-option-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        transform: scale(1.1);
    }
    
    .type-option:nth-child(2) input[type="radio"]:checked + label .type-option-icon svg {
        color: white;
    }
    
    .type-option-text {
        font-size: 1.1rem;
    }

    .form-input,
    .form-textarea,
    .form-select {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: 'Tajawal', sans-serif;
        background: white;
        color: #1e293b;
    }

    .form-select {
        cursor: pointer;
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .form-input[type="file"] {
        cursor: pointer;
    }

    .form-input[type="file"]::-webkit-file-upload-button {
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        margin-left: 10px;
        transition: all 0.3s ease;
    }

    .form-input[type="file"]::-webkit-file-upload-button:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .submit-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 0.5rem;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .alert {
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-right: 4px solid #10b981;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-right: 4px solid #ef4444;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    #image-preview {
        margin-top: 1rem;
    }

    #preview-img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
    }

    @media (max-width: 768px) {
        .complaints-create-page {
            padding: 2rem 0 4rem;
        }

        .form-card {
            padding: 2rem 1.5rem;
        }

        .type-selector {
            grid-template-columns: 1fr;
        }
        
        .type-section {
            padding: 1.25rem;
        }
        
        .type-section-title {
            font-size: 1rem;
        }
        
        .type-section-hint {
            font-size: 0.85rem;
        }
        
        .type-option label {
            padding: 1.5rem 1.25rem;
        }
        
        .type-option-icon {
            width: 45px;
            height: 45px;
        }
        
        .complaints-header {
            margin-bottom: 2rem;
        }
        
        .complaints-header h1 {
            font-size: 2rem;
        }
        
        .complaints-header p {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="complaints-create-page">
    <div class="complaints-container">
        <div class="complaints-header">
            <h1>تقديم شكوى أو مقترح</h1>
            <p>نقدر ملاحظاتك ونسعى لتحسين خدماتنا</p>
        </div>

        <div class="form-card">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <strong>⚠️ خطأ!</strong>
                    <ul style="margin-top: 8px; padding-right: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('complaints-suggestions.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="type-section">
                    <div class="type-section-title">
                        <span>نوع الطلب</span>
                        <span class="required">*</span>
                    </div>
                    <div class="type-section-hint">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>يرجى اختيار نوع الطلب قبل المتابعة</span>
                    </div>
                    <div class="type-selector">
                        <div class="type-option">
                            <input type="radio" id="complaint" name="type" value="complaint" {{ old('type') === 'complaint' ? 'checked' : '' }} required>
                            <label for="complaint">
                                <div class="type-option-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        <path d="M13 8H7"></path>
                                        <path d="M17 12H7"></path>
                                    </svg>
                                </div>
                                <span class="type-option-text">شكوى</span>
                            </label>
                        </div>
                        <div class="type-option">
                            <input type="radio" id="suggestion" name="type" value="suggestion" {{ old('type') === 'suggestion' ? 'checked' : '' }} required>
                            <label for="suggestion">
                                <div class="type-option-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                    </svg>
                                </div>
                                <span class="type-option-text">مقترح</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">
                        الاسم <span class="required">*</span>
                    </label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">
                        رقم الهاتف <span class="required">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone') }}" required>
                    @error('phone')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}">
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="governorate" class="form-label">
                        المحافظة <span class="required">*</span>
                    </label>
                    <select id="governorate" name="governorate" class="form-select" required>
                        <option value="">اختر المحافظة</option>
                        @foreach($governorates as $gov)
                            <option value="{{ $gov->value }}" {{ old('governorate') == $gov->value ? 'selected' : '' }}>
                                {{ $gov->label }}
                            </option>
                        @endforeach
                    </select>
                    @error('governorate')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="operator-group" style="display: none;">
                    <label for="operator_id" class="form-label">
                        المشغل <span class="required">*</span>
                    </label>
                    <select id="operator_id" name="operator_id" class="form-select">
                        <option value="">اختر المشغل</option>
                    </select>
                    @error('operator_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="generator-group" style="display: none;">
                    <label for="generator_id" class="form-label">
                        المولد <span class="required">*</span>
                    </label>
                    <select id="generator_id" name="generator_id" class="form-select">
                        <option value="">اختر المولد</option>
                    </select>
                    @error('generator_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">
                        الرسالة <span class="required">*</span>
                    </label>
                    <textarea id="message" name="message" class="form-textarea" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">
                        إرفاق صورة (اختياري)
                    </label>
                    <input type="file" id="image" name="image" class="form-input" accept="image/*">
                    @error('image')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <div id="image-preview" style="display: none;">
                        <img id="preview-img" src="" alt="معاينة الصورة">
                    </div>
                </div>

                <button type="submit" class="submit-btn">إرسال</button>
            </form>
        </div>
    </div>
</div>

<!-- General Helpers JS -->
<script src="{{ asset('assets/admin/js/general-helpers.js') }}"></script>

<script>
    // التحقق من اختيار نوع الطلب قبل الإرسال
    const form = document.querySelector('form');
    const typeInputs = document.querySelectorAll('input[name="type"]');
    const typeSection = document.querySelector('.type-section');
    
    form.addEventListener('submit', function(e) {
        let typeSelected = false;
        typeInputs.forEach(input => {
            if (input.checked) {
                typeSelected = true;
            }
        });
        
        if (!typeSelected) {
            e.preventDefault();
            
            // إضافة تأثير اهتزاز للقسم
            typeSection.style.animation = 'shake 0.5s';
            typeSection.style.borderColor = '#ef4444';
            typeSection.style.borderStyle = 'solid';
            
            // إضافة رسالة تحذير
            let alertDiv = typeSection.querySelector('.type-alert');
            if (!alertDiv) {
                alertDiv = document.createElement('div');
                alertDiv.className = 'type-alert';
                alertDiv.style.cssText = 'background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-top: 1rem; border-right: 4px solid #ef4444; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;';
                alertDiv.innerHTML = '<svg style="width: 18px; height: 18px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>يرجى اختيار نوع الطلب (شكوى أو مقترح) قبل المتابعة</span>';
                typeSection.appendChild(alertDiv);
            }
            
            // إزالة التأثير بعد ثانية
            setTimeout(() => {
                typeSection.style.animation = '';
            }, 500);
            
            // إزالة الرسالة بعد 5 ثوان
            setTimeout(() => {
                if (alertDiv) {
                    alertDiv.remove();
                }
            }, 5000);
            
            // التمرير إلى قسم النوع
            typeSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
    
    // إزالة التحذير عند اختيار نوع
    typeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const alertDiv = typeSection.querySelector('.type-alert');
            if (alertDiv) {
                alertDiv.remove();
            }
            typeSection.style.borderColor = '';
            typeSection.style.borderStyle = 'dashed';
        });
    });
    
    // إضافة CSS للاهتزاز
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);

    const governorateSelect = document.getElementById('governorate');
    const operatorGroup = document.getElementById('operator-group');
    const operatorSelect = document.getElementById('operator_id');
    const generatorGroup = document.getElementById('generator-group');
    const generatorSelect = document.getElementById('generator_id');

    // عند تغيير المحافظة
    governorateSelect.addEventListener('change', function() {
        const governorate = parseInt(this.value);

        // إخفاء حقول المشغل والمولد
        operatorGroup.style.display = 'none';
        operatorSelect.required = false;
        operatorSelect.innerHTML = '<option value="">اختر المشغل</option>';
        generatorGroup.style.display = 'none';
        generatorSelect.required = false;
        generatorSelect.innerHTML = '<option value="">اختر المولد</option>';

        if (governorate) {
            // إظهار حقل المشغل وملؤه بالمشغلين
            operatorGroup.style.display = 'block';
            operatorSelect.required = true;

            // مسح الخيارات السابقة
            operatorSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            operatorSelect.disabled = true;

            // جلب المشغلين من السيرفر
            const operatorsUrl = `{{ route('complaints-suggestions.operators-by-governorate', ':governorate') }}`.replace(':governorate', governorate);

            fetch(operatorsUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                operatorSelect.innerHTML = '<option value="">اختر المشغل</option>';
                operatorSelect.disabled = false;

                if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
                    data.data.forEach(operator => {
                        const option = document.createElement('option');
                        option.value = operator.id;
                        option.textContent = operator.name + (operator.city ? ` - ${operator.city}` : '');
                        @if(old('operator_id'))
                            if (operator.id == {{ old('operator_id') }}) {
                                option.selected = true;
                            }
                        @endif
                        operatorSelect.appendChild(option);
                    });

                    // trigger change event إذا كان هناك مشغل محدد مسبقاً
                    @if(old('operator_id'))
                        const oldOperatorId = {{ old('operator_id') }};
                        if (oldOperatorId) {
                            operatorSelect.value = oldOperatorId;
                            operatorSelect.dispatchEvent(new Event('change'));
                        }
                    @endif
                } else {
                    operatorSelect.innerHTML = '<option value="">لا توجد مشغلين في هذه المحافظة</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching operators:', error);
                operatorSelect.innerHTML = '<option value="">حدث خطأ في تحميل المشغلين</option>';
                operatorSelect.disabled = false;
            });
        }
    });

    // عند تغيير المشغل
    operatorSelect.addEventListener('change', function() {
        const operatorId = parseInt(this.value);

        // إخفاء حقل المولد
        generatorGroup.style.display = 'none';
        generatorSelect.required = false;
        generatorSelect.innerHTML = '<option value="">اختر المولد</option>';

        if (operatorId) {
            // إظهار حقل المولد
            generatorGroup.style.display = 'block';
            generatorSelect.required = true;

            // مسح الخيارات السابقة
            generatorSelect.innerHTML = '<option value="">جاري التحميل...</option>';

            // جلب المولدات من السيرفر حسب المشغل
            const url = `{{ route('complaints-suggestions.generators-by-operator') }}?operator_id=${encodeURIComponent(operatorId)}`;

            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                generatorSelect.innerHTML = '<option value="">اختر المولد</option>';

                if (data && Array.isArray(data) && data.length > 0) {
                    data.forEach(generator => {
                        const option = document.createElement('option');
                        option.value = generator.id;
                        option.textContent = generator.name;
                        @if(old('generator_id'))
                            if (generator.id == {{ old('generator_id') }}) {
                                option.selected = true;
                            }
                        @endif
                        generatorSelect.appendChild(option);
                    });
                } else {
                    generatorSelect.innerHTML = '<option value="">لا توجد مولدات لهذا المشغل</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching generators:', error);
                generatorSelect.innerHTML = '<option value="">حدث خطأ في تحميل المولدات</option>';
            });
        }
    });

    // إذا كانت المحافظة محددة مسبقاً (عند وجود أخطاء في النموذج)
    @if(old('governorate'))
        // انتظر تحميل GeneralHelpers ثم قم بملء المشغلين
        if (typeof GeneralHelpers !== 'undefined') {
            governorateSelect.dispatchEvent(new Event('change'));
        } else {
            // انتظر تحميل GeneralHelpers
            const checkGeneralHelpers = setInterval(function() {
                if (typeof GeneralHelpers !== 'undefined') {
                    clearInterval(checkGeneralHelpers);
                    governorateSelect.dispatchEvent(new Event('change'));
                }
            }, 100);

            // timeout بعد 5 ثوان
            setTimeout(function() {
                clearInterval(checkGeneralHelpers);
            }, 5000);
        }
    @endif

    // معاينة الصورة
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');

    if (imageInput && imagePreview && previewImg) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    }
</script>
@endsection
