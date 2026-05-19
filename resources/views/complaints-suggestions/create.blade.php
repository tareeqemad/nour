@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'تقديم شكوى أو مقترح - ' . $siteName)

@push('styles')
<style>
/* ===== Complaints Create — Nour Design System ===== */

.cc-hero {
    background: linear-gradient(155deg, #1a2478 0%, #11186B 40%, #0c1050 100%);
    padding: 2.5rem 0 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cc-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(circle at 25% 35%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 75% 65%, rgba(255,255,255,0.04) 0%, transparent 50%);
    pointer-events: none;
}

.cc-hero::after {
    content: '';
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}

.cc-hero-inner { position: relative; z-index: 1; max-width: 600px; margin: 0 auto; padding: 0 2rem; }
.cc-hero h1 { font-size: 2rem; font-weight: 900; color: #fff; margin-bottom: 0.4rem; }
.cc-hero h1 i { color: #FBBF24; margin-left: 0.4rem; }
.cc-hero p { font-size: 1rem; color: rgba(255,255,255,0.7); font-weight: 500; margin: 0; }

/* Form wrap */
.cc-wrap { max-width: 720px; margin: -1.5rem auto 3rem; padding: 0 1.5rem; position: relative; z-index: 2; }

.cc-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    border-top: 2.5px solid #24308F;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 2rem;
}

/* Alerts */
.cc-success {
    background: #D1FAE5; border: 1px solid #A7F3D0; border-radius: 0.5rem;
    padding: 0.85rem 1rem; margin-bottom: 1.5rem; color: #065F46;
    font-size: 0.88rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;
}

.cc-error {
    background: #FEF2F2; border: 1px solid #FECACA; border-radius: 0.5rem;
    padding: 0.85rem 1rem; margin-bottom: 1.5rem; color: #EF4444;
    font-size: 0.88rem; font-weight: 600; display: flex; align-items: flex-start; gap: 0.5rem;
}

.cc-error i { margin-top: 2px; flex-shrink: 0; }
.cc-error ul { list-style: none; margin: 0; padding: 0; }
.cc-error li { margin-bottom: 0.2rem; }

/* Type selector */
.cc-type-section {
    margin-bottom: 1.75rem;
    padding: 1.25rem;
    background: #F8FAFC;
    border-radius: 0.6rem;
    border: 1px solid #E5E7EB;
}

.cc-type-title {
    font-size: 0.95rem; font-weight: 800; color: #1F2937;
    margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;
}

.cc-type-title .req { color: #EF4444; }

.cc-type-hint {
    font-size: 0.78rem; color: #98A2B3; margin-bottom: 1rem;
    display: flex; align-items: center; gap: 0.3rem;
}

.cc-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

.cc-type-opt { position: relative; }
.cc-type-opt input { position: absolute; opacity: 0; }

.cc-type-opt label {
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    padding: 1.25rem 1rem; border: 1.5px solid #D9E0EA; border-radius: 0.6rem;
    text-align: center; cursor: pointer; transition: all 0.2s;
    background: #fff; font-weight: 700; color: #5B6780; font-size: 0.95rem;
}

.cc-type-opt label:hover { border-color: #24308F; }

.cc-type-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s; font-size: 1.2rem;
}

.cc-type-opt:first-child .cc-type-icon { background: #FEE2E2; color: #EF4444; }
.cc-type-opt:last-child .cc-type-icon { background: #EEF2FF; color: #24308F; }

.cc-type-opt input:checked + label {
    border-color: #24308F; background: #EEF2FF; color: #24308F;
    box-shadow: 0 2px 8px rgba(36,48,143,0.1);
}

.cc-type-opt:first-child input:checked + label {
    border-color: #EF4444; background: #FEF2F2; color: #EF4444;
}

.cc-type-opt:first-child input:checked + label .cc-type-icon { background: #EF4444; color: #fff; }
.cc-type-opt:last-child input:checked + label .cc-type-icon { background: #24308F; color: #fff; }

/* Section title */
.cc-section { display: flex; align-items: center; gap: 0.5rem; font-size: 1rem; font-weight: 800; color: #1F2937; margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #E5E7EB; }
.cc-section i { color: #24308F; }

/* Fields */
.cc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.cc-field { margin-bottom: 1rem; }

.cc-label { display: flex; align-items: center; gap: 0.3rem; font-size: 0.88rem; font-weight: 700; color: #3B4863; margin-bottom: 0.35rem; }
.cc-label i { color: #98A2B3; font-size: 0.85rem; }
.cc-label .req { color: #EF4444; }

.cc-input, .cc-select, .cc-textarea {
    width: 100%; height: 42px; padding: 0 0.75rem;
    border: 1.5px solid #D9E0EA; border-radius: 0.5rem;
    font-family: 'Tajawal', sans-serif; font-size: 0.92rem; font-weight: 500;
    color: #1F2937; background: #fff; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.cc-input::placeholder, .cc-textarea::placeholder { color: #98A2B3; font-weight: 400; }
.cc-input:focus, .cc-select:focus, .cc-textarea:focus { border-color: #24308F; box-shadow: 0 0 0 3px rgba(36,48,143,0.08); }
.cc-select { cursor: pointer; }
.cc-textarea { height: auto; min-height: 110px; padding: 0.65rem 0.75rem; resize: vertical; }

.cc-input[type="file"] { padding: 6px 0.75rem; cursor: pointer; }
.cc-input[type="file"]::-webkit-file-upload-button {
    padding: 0.3rem 0.75rem; background: #24308F; color: #fff;
    border: none; border-radius: 0.4rem; cursor: pointer; font-weight: 600;
    margin-left: 8px; font-family: 'Tajawal', sans-serif; font-size: 0.82rem;
}

.cc-field-error { color: #EF4444; font-size: 0.78rem; font-weight: 600; margin-top: 0.25rem; }

#image-preview { margin-top: 0.5rem; }
#preview-img { max-width: 180px; max-height: 180px; border-radius: 0.5rem; border: 1px solid #E5E7EB; }

/* Submit */
.cc-submit {
    width: 100%; height: 48px; background: #24308F; color: #fff;
    border: none; border-radius: 0.5rem; font-family: 'Tajawal', sans-serif;
    font-size: 1rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    transition: all 0.2s; box-shadow: 0 2px 8px rgba(36,48,143,0.2); margin-top: 0.5rem;
}

.cc-submit:hover { background: #2330B3; box-shadow: 0 4px 14px rgba(36,48,143,0.3); }

@keyframes ccShake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
    20%, 40%, 60%, 80% { transform: translateX(4px); }
}

@media (max-width: 640px) {
    .cc-hero h1 { font-size: 1.5rem; }
    .cc-card { padding: 1.5rem 1.25rem; }
    .cc-row, .cc-type-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

    <section class="cc-hero">
        <div class="cc-hero-inner">
            <h1><i class="bi bi-chat-left-text-fill"></i> تقديم شكوى أو مقترح</h1>
            <p>نقدّر ملاحظاتك ونسعى لتحسين خدماتنا</p>
        </div>
    </section>

    <div class="cc-wrap">
        <div class="cc-card">

            @if(session('success'))
                <div class="cc-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="cc-error">
                    <i class="bi bi-exclamation-circle"></i>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('complaints-suggestions.store') }}" enctype="multipart/form-data" id="ccForm">
                @csrf

                {{-- نوع الطلب --}}
                <div class="cc-type-section" id="typeSection">
                    <div class="cc-type-title">نوع الطلب <span class="req">*</span></div>
                    <div class="cc-type-hint"><i class="bi bi-info-circle"></i> اختر نوع الطلب قبل المتابعة</div>
                    <div class="cc-type-grid">
                        <div class="cc-type-opt">
                            <input type="radio" id="complaint" name="type" value="complaint" {{ old('type') === 'complaint' ? 'checked' : '' }} required>
                            <label for="complaint">
                                <div class="cc-type-icon"><i class="bi bi-chat-left-dots"></i></div>
                                شكوى
                            </label>
                        </div>
                        <div class="cc-type-opt">
                            <input type="radio" id="suggestion" name="type" value="suggestion" {{ old('type') === 'suggestion' ? 'checked' : '' }} required>
                            <label for="suggestion">
                                <div class="cc-type-icon"><i class="bi bi-pencil-square"></i></div>
                                مقترح
                            </label>
                        </div>
                    </div>
                </div>

                {{-- بيانات مقدم الطلب --}}
                <div class="cc-section"><i class="bi bi-person"></i> بيانات مقدم الطلب</div>

                <div class="cc-row">
                    <div class="cc-field">
                        <label class="cc-label"><i class="bi bi-person"></i> الاسم <span class="req">*</span></label>
                        <input type="text" name="name" class="cc-input" value="{{ old('name') }}" required>
                        @error('name')<div class="cc-field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="cc-field">
                        <label class="cc-label"><i class="bi bi-phone"></i> رقم الهاتف <span class="req">*</span></label>
                        <input type="text" name="phone" class="cc-input" value="{{ old('phone') }}" required>
                        @error('phone')<div class="cc-field-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="cc-field">
                    <label class="cc-label"><i class="bi bi-envelope"></i> البريد الإلكتروني (اختياري)</label>
                    <input type="email" name="email" class="cc-input" value="{{ old('email') }}">
                    @error('email')<div class="cc-field-error">{{ $message }}</div>@enderror
                </div>

                {{-- تفاصيل الشكوى --}}
                <div class="cc-section"><i class="bi bi-building"></i> تفاصيل</div>

                <div class="cc-field">
                    <label class="cc-label"><i class="bi bi-geo-alt"></i> المحافظة <span class="req">*</span></label>
                    <select id="governorate" name="governorate" class="cc-select" required>
                        <option value="">اختر المحافظة</option>
                        @foreach($governorates as $gov)
                            <option value="{{ $gov->value }}" {{ old('governorate') == $gov->value ? 'selected' : '' }}>{{ $gov->label }}</option>
                        @endforeach
                    </select>
                    @error('governorate')<div class="cc-field-error">{{ $message }}</div>@enderror
                </div>

                <div class="cc-field" id="operator-group" style="display:none;">
                    <label class="cc-label"><i class="bi bi-building"></i> المشغل <span class="req">*</span></label>
                    <select id="operator_id" name="operator_id" class="cc-select">
                        <option value="">اختر المشغل</option>
                    </select>
                    @error('operator_id')<div class="cc-field-error">{{ $message }}</div>@enderror
                </div>

                <div class="cc-field" id="generator-group" style="display:none;">
                    <label class="cc-label"><i class="bi bi-lightning-charge"></i> المولد <span class="req">*</span></label>
                    <select id="generator_id" name="generator_id" class="cc-select">
                        <option value="">اختر المولد</option>
                    </select>
                    @error('generator_id')<div class="cc-field-error">{{ $message }}</div>@enderror
                </div>

                <div class="cc-field">
                    <label class="cc-label"><i class="bi bi-chat-text"></i> الرسالة <span class="req">*</span></label>
                    <textarea name="message" class="cc-textarea" required>{{ old('message') }}</textarea>
                    @error('message')<div class="cc-field-error">{{ $message }}</div>@enderror
                </div>

                <div class="cc-field">
                    <label class="cc-label"><i class="bi bi-image"></i> إرفاق صورة (اختياري)</label>
                    <input type="file" id="image" name="image" class="cc-input" accept="image/*">
                    @error('image')<div class="cc-field-error">{{ $message }}</div>@enderror
                    <div id="image-preview" style="display:none;">
                        <img id="preview-img" src="" alt="معاينة">
                    </div>
                </div>

                <button type="submit" class="cc-submit" id="submitBtn">
                    <i class="bi bi-send"></i> إرسال
                </button>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('ccForm');
    var typeSection = document.getElementById('typeSection');
    var typeInputs = document.querySelectorAll('input[name="type"]');
    var governorateSelect = document.getElementById('governorate');
    var operatorGroup = document.getElementById('operator-group');
    var operatorSelect = document.getElementById('operator_id');
    var generatorGroup = document.getElementById('generator-group');
    var generatorSelect = document.getElementById('generator_id');

    // Type validation
    form.addEventListener('submit', function(e) {
        var checked = false;
        typeInputs.forEach(function(i) { if (i.checked) checked = true; });
        if (!checked) {
            e.preventDefault();
            typeSection.style.animation = 'ccShake 0.5s';
            typeSection.style.borderColor = '#EF4444';
            setTimeout(function() { typeSection.style.animation = ''; }, 500);
            typeSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    typeInputs.forEach(function(i) {
        i.addEventListener('change', function() { typeSection.style.borderColor = '#E5E7EB'; });
    });

    // Governorate → Operator cascade
    governorateSelect.addEventListener('change', function() {
        var gov = parseInt(this.value);
        operatorGroup.style.display = 'none';
        operatorSelect.required = false;
        operatorSelect.innerHTML = '<option value="">اختر المشغل</option>';
        generatorGroup.style.display = 'none';
        generatorSelect.required = false;
        generatorSelect.innerHTML = '<option value="">اختر المولد</option>';

        if (gov) {
            operatorGroup.style.display = 'block';
            operatorSelect.required = true;
            operatorSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            operatorSelect.disabled = true;

            var url = '{{ route("complaints-suggestions.operators-by-governorate", ":gov") }}'.replace(':gov', gov);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    operatorSelect.innerHTML = '<option value="">اختر المشغل</option>';
                    operatorSelect.disabled = false;
                    if (data.success && data.data && data.data.length > 0) {
                        data.data.forEach(function(op) {
                            var opt = document.createElement('option');
                            opt.value = op.id;
                            opt.textContent = op.name + (op.city ? ' - ' + op.city : '');
                            @if(old('operator_id'))
                                if (op.id == {{ old('operator_id') }}) opt.selected = true;
                            @endif
                            operatorSelect.appendChild(opt);
                        });
                        @if(old('operator_id'))
                            operatorSelect.dispatchEvent(new Event('change'));
                        @endif
                    } else {
                        operatorSelect.innerHTML = '<option value="">لا توجد مشغلين</option>';
                    }
                })
                .catch(function() {
                    operatorSelect.innerHTML = '<option value="">حدث خطأ</option>';
                    operatorSelect.disabled = false;
                });
        }
    });

    // Operator → Generator cascade
    operatorSelect.addEventListener('change', function() {
        var opId = parseInt(this.value);
        generatorGroup.style.display = 'none';
        generatorSelect.required = false;
        generatorSelect.innerHTML = '<option value="">اختر المولد</option>';

        if (opId) {
            generatorGroup.style.display = 'block';
            generatorSelect.required = true;
            generatorSelect.innerHTML = '<option value="">جاري التحميل...</option>';

            fetch('{{ route("complaints-suggestions.generators-by-operator") }}?operator_id=' + opId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                generatorSelect.innerHTML = '<option value="">اختر المولد</option>';
                if (data && data.length > 0) {
                    data.forEach(function(g) {
                        var opt = document.createElement('option');
                        opt.value = g.id;
                        opt.textContent = g.name;
                        @if(old('generator_id'))
                            if (g.id == {{ old('generator_id') }}) opt.selected = true;
                        @endif
                        generatorSelect.appendChild(opt);
                    });
                } else {
                    generatorSelect.innerHTML = '<option value="">لا توجد مولدات</option>';
                }
            })
            .catch(function() { generatorSelect.innerHTML = '<option value="">حدث خطأ</option>'; });
        }
    });

    // Restore old values
    @if(old('governorate'))
        governorateSelect.dispatchEvent(new Event('change'));
    @endif

    // Image preview
    var imageInput = document.getElementById('image');
    var imagePreview = document.getElementById('image-preview');
    var previewImg = document.getElementById('preview-img');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(ev) { previewImg.src = ev.target.result; imagePreview.style.display = 'block'; };
                reader.readAsDataURL(file);
            } else { imagePreview.style.display = 'none'; }
        });
    }
});
</script>
@endpush
