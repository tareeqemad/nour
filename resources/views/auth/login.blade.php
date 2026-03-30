<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = \App\Models\Setting::get('site_name', 'نور');
        $logo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png');
        $logoUrl = str_starts_with($logo, 'http') ? $logo : asset($logo);
    @endphp
    <title>تسجيل الدخول - {{ $siteName }}</title>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tajawal-font.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #24308F;
            --primary-hover: #2330B3;
            --primary-deep: #11186B;
            --primary-soft: #EEF2FF;
            --text-main: #1F2937;
            --text-secondary: #3B4863;
            --text-muted: #5B6780;
            --text-label: #98A2B3;
            --bg-page: #F8FAFC;
            --bg-card: #FFFFFF;
            --border: #E5E7EB;
            --border-input: #D9E0EA;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --radius: 0.75rem;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Tajawal', sans-serif;
            min-height: 100vh;
            background: var(--primary-deep);
            overflow-x: hidden;
        }

        /* ===================== FULL-SCREEN LAYOUT ===================== */
        .login-scene {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* ---- Decorative Background (brand half) ---- */
        .brand-half {
            flex: 1;
            background: linear-gradient(155deg, #1a2478 0%, var(--primary-deep) 40%, #0c1050 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 3rem;
        }

        /* Animated floating orbs */
        .brand-half::before,
        .brand-half::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
        }

        .brand-half::before {
            width: 500px;
            height: 500px;
            background: #4F6AFF;
            top: -10%;
            right: -10%;
        }

        .brand-half::after {
            width: 400px;
            height: 400px;
            background: #FBBF24;
            bottom: -15%;
            left: -5%;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -30px) scale(1.05); }
        }

        /* Grid overlay */
        .brand-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 440px;
        }

        .brand-logo {
            width: 160px;
            height: 160px;
            border-radius: 36px;
            object-fit: contain;
            background: rgba(255,255,255,0.95);
            padding: 20px;
            box-shadow:
                0 20px 60px rgba(0,0,0,0.3),
                0 0 0 1px rgba(255,255,255,0.1);
            margin: 0 auto 2.5rem;
            display: block;
        }

        .brand-title {
            font-size: 3.5rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.75);
            font-weight: 500;
            line-height: 1.8;
            margin-bottom: 3rem;
        }

        /* Feature pills */
        .features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }

        .feat {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.2rem;
            border-radius: 2rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.85);
            font-size: 0.95rem;
            font-weight: 500;
            backdrop-filter: blur(6px);
            transition: all 0.25s;
        }

        .feat:hover {
            background: rgba(255,255,255,0.16);
            transform: translateY(-1px);
        }

        .feat i { font-size: 1rem; opacity: 0.75; }

        /* ---- Form Half ---- */
        .form-half {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F8FAFC;
            position: relative;
            padding: 2rem;
        }

        .form-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 0.75rem;
            padding: 2.5rem 2rem;
            border: 1px solid var(--border);
            border-top: 2.5px solid var(--primary);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.02);
        }

        /* Mobile logo */
        .mobile-logo {
            display: none;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .mobile-logo img {
            height: 56px;
            width: auto;
        }

        .form-welcome {
            margin-bottom: 2rem;
        }

        .form-welcome h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.3rem;
        }

        .form-welcome p {
            color: var(--text-muted);
            font-size: 1.05rem;
            font-weight: 500;
        }

        /* Alert */
        .alert-box {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: var(--radius);
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            color: var(--danger);
            font-size: 0.92rem;
            font-weight: 600;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .alert-box i { margin-top: 2px; flex-shrink: 0; }

        /* Field groups */
        .field {
            margin-bottom: 1.25rem;
        }

        .field-label {
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-box .icon {
            position: absolute;
            right: 14px;
            color: var(--text-label);
            font-size: 1.15rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-box input {
            width: 100%;
            height: 48px;
            padding: 0 14px 0 44px;
            padding-right: 44px;
            padding-left: 14px;
            border: 1.5px solid var(--border-input);
            border-radius: var(--radius);
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-main);
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .input-box input::placeholder {
            color: var(--text-label);
            font-weight: 400;
        }

        .input-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(36,48,143,0.08);
        }

        .input-box input:focus ~ .icon {
            color: var(--primary);
        }

        .input-box.has-toggle input {
            padding-left: 44px;
        }

        .toggle-pw {
            position: absolute;
            left: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-label);
            font-size: 1.2rem;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }

        .toggle-pw:hover { color: var(--primary); }

        /* Meta row */
        .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 0.92rem;
            font-weight: 600;
            user-select: none;
        }

        .remember input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .forgot {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.15s;
        }

        .forgot:hover { color: var(--primary-hover); }

        /* Submit button */
        .btn-submit {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: var(--radius);
            background: var(--primary);
            color: #fff;
            font-family: 'Tajawal', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(36,48,143,0.2);
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            box-shadow: 0 4px 12px rgba(36,48,143,0.25);
            transform: translateY(-1px);
        }

        .btn-submit:active { transform: translateY(0); }

        .btn-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Footer */
        .form-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-label);
            font-size: 0.82rem;
        }

        /* ===================== MODAL ===================== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 9998;
        }

        .modal-wrap {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }

        .modal-body {
            background: #fff;
            border-radius: var(--radius);
            padding: 2rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-x {
            position: absolute;
            top: 1rem;
            inset-inline-start: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius);
            transition: background 0.15s, color 0.15s;
        }

        .modal-x:hover { background: var(--bg-page); color: var(--text-main); }

        .modal-h { font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.35rem; text-align: center; }
        .modal-p { color: var(--text-muted); font-size: 0.92rem; margin-bottom: 1.5rem; text-align: center; }

        .btn-modal {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Tajawal', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn-modal-primary { background: var(--primary); color: #fff; box-shadow: 0 2px 8px rgba(36,48,143,0.25); }
        .btn-modal-primary:hover { background: var(--primary-hover); }
        .btn-modal-success { background: var(--success); color: #fff; box-shadow: 0 2px 8px rgba(16,185,129,0.25); margin-top: 0.75rem; }
        .btn-modal-success:hover { background: #059669; }

        .otp-input { text-align: center; letter-spacing: 6px; font-size: 1.35rem; font-weight: 700; }
        .otp-hint { color: var(--text-muted); font-size: 0.82rem; margin-top: 0.35rem; }
        .reset-msg { font-size: 0.88rem; margin-top: 0.5rem; font-weight: 600; }
        .reset-msg.error { color: var(--danger); }
        .reset-msg.success { color: var(--success); }

        /* ===================== RESPONSIVE ===================== */
        @media (max-width: 1024px) {
            .login-scene { flex-direction: column; }
            .brand-half { display: none; }
            .form-half { min-height: 100vh; }
            .mobile-logo { display: block; }
        }

        @media (max-width: 480px) {
            .form-half { padding: 1.5rem 1rem; }
            .form-welcome h1 { font-size: 1.6rem; }
        }

        @media (max-width: 360px) {
            .form-half { padding: 1.25rem 0.75rem; }
            .form-card { padding: 1.75rem 1rem; }
            .form-welcome h1 { font-size: 1.4rem; }
            .btn-submit { height: 44px; font-size: 1rem; }
            .field-label { font-size: 0.88rem; }
        }
    </style>
</head>
<body>
    <div class="login-scene">
        {{-- ===== Brand Half ===== --}}
        <div class="brand-half">
            <div class="brand-grid"></div>
            <div class="brand-content">
                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="brand-logo"
                     onerror="this.style.display='none'">
                <h1 class="brand-title">{{ $siteName }}</h1>
                <p class="brand-subtitle">منصة رقمية متكاملة لإدارة ومراقبة سوق الطاقة</p>

                <div class="features">
                    <span class="feat"><i class="bi bi-lightning-charge"></i> طاقة كهربائية</span>
                    <span class="feat"><i class="bi bi-gear"></i> مولدات</span>
                    <span class="feat"><i class="bi bi-geo-alt"></i> مناطق جغرافية</span>
                    <span class="feat"><i class="bi bi-people"></i> مشتركين</span>
                    <span class="feat"><i class="bi bi-shield-check"></i> امتثال وسلامة</span>
                    <span class="feat"><i class="bi bi-bar-chart"></i> تقارير وتحليلات</span>
                </div>
            </div>
        </div>

        {{-- ===== Form Half ===== --}}
        <div class="form-half">
            <div class="form-card">
                <div class="mobile-logo">
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" onerror="this.style.display='none'">
                </div>

                <div class="form-welcome">
                    <h1>مرحباً بك</h1>
                    <p>سجل دخولك للوصول إلى منصة {{ $siteName }}</p>
                </div>

                @if ($errors->any())
                    <div class="alert-box">
                        <i class="bi bi-exclamation-circle"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="on">
                    @csrf
                    <div style="position:absolute;left:-9999px;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                    <div class="field">
                        <label for="username" class="field-label">اسم المستخدم</label>
                        <div class="input-box">
                            <input type="text" id="username" name="username" value="{{ old('username') }}"
                                   placeholder="أدخل اسم المستخدم" required autofocus autocomplete="username" maxlength="255">
                            <i class="bi bi-person icon"></i>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password" class="field-label">كلمة المرور</label>
                        <div class="input-box has-toggle">
                            <input type="password" id="password" name="password"
                                   placeholder="أدخل كلمة المرور" required autocomplete="current-password" maxlength="255">
                            <i class="bi bi-lock icon"></i>
                            <button type="button" class="toggle-pw" id="passwordToggle" aria-label="إظهار كلمة المرور">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                                <i class="bi bi-eye-slash" id="eyeOffIcon" style="display:none;"></i>
                            </button>
                        </div>
                    </div>

                    <div class="meta">
                        <label class="remember">
                            <input type="checkbox" id="remember" name="remember"> تذكرني
                        </label>
                        <a href="#" id="forgotPasswordLink" class="forgot">نسيت كلمة المرور؟</a>
                    </div>

                    <button type="submit" class="btn-submit" id="loginButton">
                        <span id="loginButtonText">تسجيل الدخول</span>
                        <span id="loginButtonSpinner" style="display:none;" class="spinner"></span>
                    </button>
                </form>

                <div class="form-footer">
                    &copy; {{ date('Y') }} {{ $siteName }} — جميع الحقوق محفوظة
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Password Reset Modal ===== --}}
    <div class="modal-overlay" id="resetModalBackdrop"></div>
    <div class="modal-wrap" id="resetPasswordModal">
        <div class="modal-body">
            <button class="modal-x" id="closeResetModal">&times;</button>
            <h2 class="modal-h">استعادة كلمة المرور</h2>
            <p class="modal-p">أدخل رقم الجوال المسجل لإرسال رمز التحقق</p>

            <form id="resetPasswordForm">
                <div id="resetFormErrors"></div>

                <div class="field">
                    <label for="resetPhone" class="field-label">رقم الجوال</label>
                    <div class="input-box">
                        <input type="text" id="resetPhone" placeholder="0591234567 أو 0561234567" required maxlength="10">
                        <i class="bi bi-phone icon"></i>
                    </div>
                </div>

                <div class="field" id="otpSection" style="display:none;">
                    <label for="resetOTP" class="field-label">رمز التحقق</label>
                    <input type="text" id="resetOTP" class="otp-input" placeholder="------" required maxlength="6"
                           style="width:100%;height:48px;border:1.5px solid var(--border-input);border-radius:var(--radius);font-family:'Tajawal',sans-serif;color:var(--text-main);outline:none;">
                    <div class="otp-hint">الرمز صالح لمدة 10 دقائق فقط</div>
                </div>

                <button type="button" id="sendOTPButton" class="btn-modal btn-modal-primary">إرسال رمز التحقق</button>
                <button type="button" id="verifyOTPButton" class="btn-modal btn-modal-success" style="display:none;">تحقق وإعادة تعيين</button>
            </form>
        </div>
    </div>

    <script>
        // Password toggle
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                eyeIcon.style.display = isPassword ? 'none' : 'inline';
                eyeOffIcon.style.display = isPassword ? 'inline' : 'none';
                passwordToggle.setAttribute('aria-label', isPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            });
        }

        // Form submit with CSRF refresh
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginButtonText = document.getElementById('loginButtonText');
        const loginButtonSpinner = document.getElementById('loginButtonSpinner');

        if (loginForm && loginButton) {
            function refreshCSRFToken() {
                return fetch('{{ route("login.csrf-token") }}', {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    const tokenInput = loginForm.querySelector('input[name="_token"]');
                    const metaToken = document.querySelector('meta[name="csrf-token"]');
                    if (tokenInput && data.token) tokenInput.value = data.token;
                    if (metaToken && data.token) metaToken.setAttribute('content', data.token);
                    return true;
                })
                .catch(() => false);
            }

            loginForm.addEventListener('submit', function(e) {
                const honeypot = loginForm.querySelector('input[name="website"]');
                if (honeypot && honeypot.value !== '') { e.preventDefault(); return false; }

                e.preventDefault();
                refreshCSRFToken().then(() => {
                    loginButton.disabled = true;
                    loginButtonText.style.display = 'none';
                    loginButtonSpinner.style.display = 'inline-block';
                    if (loginForm.submitDisabled) return false;
                    loginForm.submitDisabled = true;
                    loginForm.submit();
                }).catch(() => {
                    loginButton.disabled = true;
                    loginButtonText.style.display = 'none';
                    loginButtonSpinner.style.display = 'inline-block';
                    loginForm.submitDisabled = true;
                    loginForm.submit();
                });
            });

            window.addEventListener('load', function() {
                if (document.querySelector('.alert-box')) {
                    loginButton.disabled = false;
                    loginButtonText.style.display = 'inline';
                    loginButtonSpinner.style.display = 'none';
                }
            });
        }

        if (window.history.replaceState) window.history.replaceState(null, null, window.location.href);

        // Password Reset Modal
        document.addEventListener('DOMContentLoaded', function() {
            const forgotPasswordLink = document.getElementById('forgotPasswordLink');
            const resetModal = document.getElementById('resetPasswordModal');
            const resetModalBackdrop = document.getElementById('resetModalBackdrop');
            const closeResetModal = document.getElementById('closeResetModal');
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            const otpSection = document.getElementById('otpSection');
            const phoneInput = document.getElementById('resetPhone');
            const otpInput = document.getElementById('resetOTP');
            const sendOTPButton = document.getElementById('sendOTPButton');
            const verifyOTPButton = document.getElementById('verifyOTPButton');
            const resetFormErrors = document.getElementById('resetFormErrors');

            function openModal() {
                if (resetModal && resetModalBackdrop) {
                    resetModal.style.display = 'flex';
                    resetModalBackdrop.style.display = 'block';
                    if (phoneInput) setTimeout(() => phoneInput.focus(), 100);
                }
            }

            function closeModal() {
                if (resetModal && resetModalBackdrop) {
                    resetModal.style.display = 'none';
                    resetModalBackdrop.style.display = 'none';
                    if (resetPasswordForm) resetPasswordForm.reset();
                    if (resetFormErrors) resetFormErrors.innerHTML = '';
                    if (otpSection) otpSection.style.display = 'none';
                    if (sendOTPButton) { sendOTPButton.style.display = 'flex'; sendOTPButton.disabled = false; sendOTPButton.textContent = 'إرسال رمز التحقق'; }
                    if (verifyOTPButton) { verifyOTPButton.style.display = 'none'; verifyOTPButton.disabled = false; verifyOTPButton.textContent = 'تحقق وإعادة تعيين'; }
                    if (phoneInput) phoneInput.disabled = false;
                }
            }

            function showMsg(el, msg, type) {
                if (el) el.innerHTML = '<div class="reset-msg ' + type + '">' + msg + '</div>';
            }

            if (forgotPasswordLink) forgotPasswordLink.addEventListener('click', function(e) { e.preventDefault(); openModal(); });
            if (closeResetModal) closeResetModal.addEventListener('click', closeModal);
            if (resetModalBackdrop) resetModalBackdrop.addEventListener('click', closeModal);

            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    let v = this.value.replace(/[^0-9]/g, '');
                    if (v.length > 0 && !v.startsWith('0') && (v.startsWith('59') || v.startsWith('56'))) v = '0' + v;
                    this.value = v.substring(0, 10);
                });
            }

            if (otpInput) {
                otpInput.addEventListener('input', function() { this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6); });
            }

            if (sendOTPButton) {
                sendOTPButton.addEventListener('click', function() {
                    const phone = phoneInput.value.trim();
                    if (!phone || !/^0(59|56)\d{7}$/.test(phone)) {
                        showMsg(resetFormErrors, 'يرجى إدخال رقم جوال صحيح (يبدأ بـ 059 أو 056)', 'error');
                        return;
                    }
                    resetFormErrors.innerHTML = '';
                    sendOTPButton.disabled = true;
                    sendOTPButton.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;"></span> جاري الإرسال...';

                    fetch('{{ route("password.reset.send-otp") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: JSON.stringify({ phone })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success || !data.errors) {
                            showMsg(resetFormErrors, data.message || 'تم إرسال رمز التحقق بنجاح', 'success');
                            otpSection.style.display = 'block';
                            sendOTPButton.style.display = 'none';
                            verifyOTPButton.style.display = 'flex';
                            phoneInput.disabled = true;
                            if (otpInput) otpInput.focus();
                        } else {
                            showMsg(resetFormErrors, (data.errors?.phone?.[0]) || data.message || 'حدث خطأ', 'error');
                            sendOTPButton.disabled = false;
                            sendOTPButton.textContent = 'إرسال رمز التحقق';
                        }
                    })
                    .catch(() => {
                        showMsg(resetFormErrors, 'حدث خطأ. يرجى المحاولة مرة أخرى.', 'error');
                        sendOTPButton.disabled = false;
                        sendOTPButton.textContent = 'إرسال رمز التحقق';
                    });
                });
            }

            if (verifyOTPButton) {
                verifyOTPButton.addEventListener('click', function() {
                    const phone = phoneInput.value.trim();
                    const otp = otpInput.value.trim();
                    if (!otp || otp.length !== 6) {
                        showMsg(resetFormErrors, 'يرجى إدخال رمز التحقق (6 أرقام)', 'error');
                        return;
                    }
                    resetFormErrors.innerHTML = '';
                    verifyOTPButton.disabled = true;
                    verifyOTPButton.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;"></span> جاري التحقق...';

                    fetch('{{ route("password.reset.verify-otp") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: JSON.stringify({ phone, otp })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success || !data.errors) {
                            showMsg(resetFormErrors, data.message || 'تم إعادة تعيين كلمة المرور بنجاح', 'success');
                            setTimeout(() => { closeModal(); window.location.href = '{{ route("login") }}'; }, 2000);
                        } else {
                            showMsg(resetFormErrors, (data.errors?.otp?.[0]) || (data.errors?.phone?.[0]) || data.message || 'رمز التحقق غير صحيح', 'error');
                            verifyOTPButton.disabled = false;
                            verifyOTPButton.textContent = 'تحقق وإعادة تعيين';
                        }
                    })
                    .catch(() => {
                        showMsg(resetFormErrors, 'حدث خطأ. يرجى المحاولة مرة أخرى.', 'error');
                        verifyOTPButton.disabled = false;
                        verifyOTPButton.textContent = 'تحقق وإعادة تعيين';
                    });
                });
            }
        });
    </script>
</body>
</html>
