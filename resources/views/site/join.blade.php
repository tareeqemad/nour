@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'طلب الانضمام - ' . $siteName)

@push('styles')
<style>
/* ===== Join Page — Nour Design System ===== */

.jn-hero {
    background: linear-gradient(155deg, #1a2478 0%, #11186B 40%, #0c1050 100%);
    padding: 2.5rem 0 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.jn-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(circle at 25% 35%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 75% 65%, rgba(255,255,255,0.04) 0%, transparent 50%);
    pointer-events: none;
}

.jn-hero::after {
    content: '';
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
}

.jn-hero-inner {
    position: relative; z-index: 1;
    max-width: 600px; margin: 0 auto; padding: 0 2rem;
}

.jn-hero h1 {
    font-size: 2rem; font-weight: 900; color: #fff; margin-bottom: 0.4rem;
}

.jn-hero h1 i { color: #FBBF24; margin-left: 0.4rem; }

.jn-hero p {
    font-size: 1rem; color: rgba(255,255,255,0.7); font-weight: 500; margin: 0;
}

/* Form container */
.jn-wrap {
    max-width: 720px;
    margin: -1.5rem auto 3rem;
    padding: 0 1.5rem;
    position: relative;
    z-index: 2;
}

.jn-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #E5E7EB;
    border-top: 2.5px solid #24308F;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 2rem;
}

/* Alert */
.jn-alert {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: 0.5rem;
    padding: 0.85rem 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    color: #EF4444;
    font-size: 0.88rem;
    font-weight: 600;
}

.jn-alert i { font-size: 1.1rem; margin-top: 2px; flex-shrink: 0; }
.jn-alert ul { list-style: none; margin: 0; padding: 0; }
.jn-alert li { margin-bottom: 0.25rem; }
.jn-alert li:last-child { margin-bottom: 0; }

/* Section headers */
.jn-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.05rem;
    font-weight: 800;
    color: #1F2937;
    margin-bottom: 1.25rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid #E5E7EB;
}

.jn-section-title i { color: #24308F; font-size: 1.1rem; }
.jn-section-title.green i { color: #10B981; }

.jn-divider {
    height: 1px;
    background: #E5E7EB;
    margin: 1.75rem 0;
}

/* Form fields */
.jn-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.jn-field {
    margin-bottom: 1rem;
}

.jn-label {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: #3B4863;
    margin-bottom: 0.35rem;
}

.jn-label i { color: #98A2B3; font-size: 0.9rem; }
.jn-label .req { color: #EF4444; }

.jn-input {
    width: 100%;
    height: 42px;
    padding: 0 0.75rem;
    border: 1.5px solid #D9E0EA;
    border-radius: 0.5rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.92rem;
    font-weight: 500;
    color: #1F2937;
    background: #fff;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.jn-input::placeholder { color: #98A2B3; font-weight: 400; }

.jn-input:focus {
    border-color: #24308F;
    box-shadow: 0 0 0 3px rgba(36,48,143,0.08);
}

.jn-hint {
    font-size: 0.75rem;
    color: #98A2B3;
    font-weight: 500;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.jn-hint i { font-size: 0.72rem; }

/* Checkbox */
.jn-check {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin: 1.5rem 0;
    cursor: pointer;
}

.jn-check input {
    width: 18px; height: 18px;
    accent-color: #24308F;
    cursor: pointer;
    margin-top: 2px;
    flex-shrink: 0;
}

.jn-check span {
    font-size: 0.88rem;
    font-weight: 600;
    color: #3B4863;
    line-height: 1.5;
}

.jn-check .req { color: #EF4444; }

/* Submit */
.jn-submit {
    width: 100%;
    height: 48px;
    background: #24308F;
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    font-family: 'Tajawal', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(36,48,143,0.2);
}

.jn-submit:hover {
    background: #2330B3;
    box-shadow: 0 4px 14px rgba(36,48,143,0.3);
}

.jn-submit:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* Toast */
.jn-toast {
    position: fixed;
    top: 5rem; left: 50%;
    transform: translateX(-50%);
    background: #10B981;
    color: #fff;
    padding: 0.85rem 1.5rem;
    border-radius: 0.6rem;
    font-weight: 700;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 8px 24px rgba(16,185,129,0.3);
    z-index: 9999;
    animation: jnToastIn 0.3s ease;
}

@keyframes jnToastIn { from { opacity: 0; transform: translateX(-50%) translateY(-10px); } }

/* Spinner */
.jn-spinner {
    width: 18px; height: 18px;
    border: 2.5px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: jnSpin 0.7s linear infinite;
}

@keyframes jnSpin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 640px) {
    .jn-hero h1 { font-size: 1.5rem; }
    .jn-row { grid-template-columns: 1fr; }
    .jn-card { padding: 1.5rem 1.25rem; }
}
</style>
@endpush

@section('content')

    {{-- Hero --}}
    <section class="jn-hero">
        <div class="jn-hero-inner">
            <h1><i class="bi bi-person-plus-fill"></i> طلب الانضمام</h1>
            <p>تقدم بطلب انضمام كمشغل وحدة توليد لمنصة {{ $siteName }}</p>
        </div>
    </section>

    {{-- Form --}}
    <div class="jn-wrap">
        <div class="jn-card">

            @if($errors->any())
                <div class="jn-alert">
                    <i class="bi bi-exclamation-circle"></i>
                    <div>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('front.join.store') }}" method="POST" id="joinForm">
                @csrf

                {{-- بيانات المالك --}}
                <div class="jn-section-title">
                    <i class="bi bi-person-badge"></i> بيانات المالك
                </div>

                <div class="jn-row">
                    <div class="jn-field">
                        <label class="jn-label"><i class="bi bi-person"></i> اسم المالك بالعربية <span class="req">*</span></label>
                        <input type="text" name="owner_name" class="jn-input" placeholder="الاسم رباعي بالعربية" value="{{ old('owner_name') }}" required>
                    </div>
                    <div class="jn-field">
                        <label class="jn-label"><i class="bi bi-person"></i> اسم المالك بالإنجليزية <span class="req">*</span></label>
                        <input type="text" name="owner_name_en" class="jn-input" placeholder="Full name in English" value="{{ old('owner_name_en') }}" required>
                    </div>
                </div>

                <div class="jn-field">
                    <label class="jn-label"><i class="bi bi-card-heading"></i> رقم هوية المالك <span class="req">*</span></label>
                    <input type="text" id="owner_id_number" name="owner_id_number" class="jn-input" placeholder="9 أرقام" value="{{ old('owner_id_number') }}" maxlength="9" required>
                    <div class="jn-hint"><i class="bi bi-info-circle"></i> يجب أن يكون 9 أرقام</div>
                </div>

                <div class="jn-divider"></div>

                {{-- بيانات المشغل --}}
                <div class="jn-section-title green">
                    <i class="bi bi-building"></i> بيانات المشغل
                </div>

                <div class="jn-row">
                    <div class="jn-field">
                        <label class="jn-label"><i class="bi bi-building"></i> اسم المشغل بالعربية <span class="req">*</span></label>
                        <input type="text" name="operator_name" class="jn-input" placeholder="اسم المشغل/الشركة" value="{{ old('operator_name') }}" required>
                    </div>
                    <div class="jn-field">
                        <label class="jn-label"><i class="bi bi-building"></i> اسم المشغل بالإنجليزية</label>
                        <input type="text" name="operator_name_en" class="jn-input" placeholder="Operator name (optional)" value="{{ old('operator_name_en') }}">
                    </div>
                </div>

                <div class="jn-row">
                    <div class="jn-field">
                        <label class="jn-label"><i class="bi bi-card-text"></i> رقم هوية المشغل</label>
                        <input type="text" id="operator_id_number" name="operator_id_number" class="jn-input" placeholder="9 أرقام (اختياري)" value="{{ old('operator_id_number') }}" maxlength="9">
                    </div>
                    <div class="jn-field">
                        <label class="jn-label"><i class="bi bi-phone"></i> رقم الموبايل <span class="req">*</span></label>
                        <input type="tel" id="phone" name="phone" class="jn-input" placeholder="0591234567" value="{{ old('phone') }}" maxlength="10" required>
                        <div class="jn-hint"><i class="bi bi-info-circle"></i> يبدأ بـ 059 أو 056</div>
                    </div>
                </div>

                <div class="jn-field">
                    <label class="jn-label"><i class="bi bi-envelope"></i> البريد الإلكتروني</label>
                    <input type="email" name="email" class="jn-input" placeholder="example@email.com" value="{{ old('email') }}">
                </div>

                <div class="jn-divider"></div>

                <label class="jn-check">
                    <input type="checkbox" name="data_accuracy" value="1" required>
                    <span>أقر بصحة جميع البيانات المقدمة في هذا الطلب <span class="req">*</span></span>
                </label>

                <button type="submit" class="jn-submit" id="submitBtn">
                    <i class="bi bi-check-circle"></i>
                    <span id="submitText">إرسال الطلب</span>
                </button>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function validatePalID(t) {
        var s = String(t).trim();
        if (!/^\d{9}$/.test(s) || Number(s) === 0) return false;
        var fn = 0;
        for (var x = 1; x <= 8; x++) {
            var y = Number(s[x - 1]);
            if (x % 2 === 1) { fn += y; }
            else { var sn = y * 2; if (sn >= 10) sn = Math.floor(sn / 10) + (sn % 10); fn += sn; }
        }
        return Number(s[8]) === (10 - (fn % 10)) % 10;
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            var toast = document.createElement('div');
            toast.className = 'jn-toast';
            toast.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + {!! json_encode(session('success'), JSON_UNESCAPED_UNICODE) !!};
            document.body.appendChild(toast);
            setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(function() { toast.remove(); }, 300); }, 5000);
        @endif

        var form = document.getElementById('joinForm');
        var submitBtn = document.getElementById('submitBtn');
        var submitText = document.getElementById('submitText');
        var ownerIdInput = document.getElementById('owner_id_number');
        var operatorIdInput = document.getElementById('operator_id_number');
        var phoneInput = document.getElementById('phone');

        // Numeric only for ID fields
        [ownerIdInput, operatorIdInput].forEach(function(el) {
            el.addEventListener('input', function() { this.value = this.value.replace(/\D/g, '').substring(0, 9); });
        });

        // Phone validation
        phoneInput.addEventListener('input', function() {
            var v = this.value.replace(/\D/g, '');
            if (v.length > 0 && !v.startsWith('0')) v = '0' + v;
            if (v.length >= 3) {
                var p = v.substring(0, 3);
                if (p !== '059' && p !== '056') {
                    if (v.startsWith('05') && v.length > 2 && v[2] !== '9' && v[2] !== '6') v = v.substring(0, 2);
                    else if (v.startsWith('0') && v.length > 1 && v[1] !== '5') v = '0';
                }
            }
            this.value = v.substring(0, 10);
        });

        // Form submit validation
        form.addEventListener('submit', function(e) {
            if (!validatePalID(ownerIdInput.value.trim())) {
                e.preventDefault();
                alert('رقم هوية المالك غير صحيح. تأكد أنه 9 أرقام صحيحة.');
                ownerIdInput.focus();
                return;
            }
            var opId = operatorIdInput.value.trim();
            if (opId.length > 0 && !validatePalID(opId)) {
                e.preventDefault();
                alert('رقم هوية المشغل غير صحيح. تأكد أنه 9 أرقام صحيحة أو اتركه فارغاً.');
                operatorIdInput.focus();
                return;
            }
            submitBtn.disabled = true;
            submitText.textContent = 'جاري الإرسال...';
        });
    });
</script>
@endpush
