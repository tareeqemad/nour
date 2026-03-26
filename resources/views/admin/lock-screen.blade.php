<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = \App\Models\Setting::get('site_name', 'نور');
        $favicon = \App\Models\Setting::get('site_favicon', 'assets/admin/images/brand-logos/favicon.ico');
        $faviconUrl = str_starts_with($favicon, 'http') ? $favicon : asset($favicon);
        $logo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png');
        $logoUrl = str_starts_with($logo, 'http') ? $logo : asset($logo);
    @endphp
    <title>قفل الشاشة - {{ $siteName }}</title>
    <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">
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
            --border: #E5E7EB;
            --border-input: #D9E0EA;
            --success: #10B981;
            --danger: #EF4444;
            --radius: 0.75rem;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Tajawal', sans-serif;
            min-height: 100vh;
            background: var(--primary-deep);
            overflow-x: hidden;
        }

        .lock-scene {
            display: flex;
            min-height: 100vh;
        }

        /* ---- Brand Half ---- */
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
            width: 500px; height: 500px;
            background: #4F6AFF;
            top: -10%; right: -10%;
        }

        .brand-half::after {
            width: 400px; height: 400px;
            background: #FBBF24;
            bottom: -15%; left: -5%;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -30px) scale(1.05); }
        }

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
            width: 160px; height: 160px;
            border-radius: 36px;
            object-fit: contain;
            background: rgba(255,255,255,0.95);
            padding: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.1);
            margin: 0 auto 2.5rem;
            display: block;
        }

        .brand-title {
            font-size: 3.5rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .brand-subtitle {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.75);
            font-weight: 500;
            line-height: 1.8;
        }

        /* ---- Form Half ---- */
        .form-half {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F8FAFC;
            padding: 2rem;
        }

        .form-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: var(--radius);
            padding: 2.5rem 2rem;
            border: 1px solid var(--border);
            border-top: 2.5px solid var(--primary);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.02);
            text-align: center;
        }

        /* Lock icon */
        .lock-badge {
            width: 56px; height: 56px;
            background: var(--primary-soft);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .lock-badge i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        /* Avatar */
        .avatar {
            width: 90px; height: 90px;
            border-radius: 50%;
            border: 3px solid var(--primary-soft);
            object-fit: cover;
            margin: 0 auto 1rem;
            display: block;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .avatar-fallback {
            width: 90px; height: 90px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            font-size: 2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 12px rgba(36,48,143,0.2);
        }

        .user-name {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.2rem;
        }

        .user-role {
            font-size: 0.92rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .lock-msg {
            color: var(--text-label);
            font-size: 0.9rem;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        /* Error */
        .alert-box {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: var(--radius);
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            color: var(--danger);
            font-size: 0.92rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-align: right;
        }

        /* Field */
        .field { margin-bottom: 1.25rem; text-align: right; }

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
            padding-right: 44px;
            padding-left: 44px;
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

        .input-box input::placeholder { color: var(--text-label); font-weight: 400; }

        .input-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(36,48,143,0.08);
        }

        .input-box input:focus ~ .icon { color: var(--primary); }

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

        /* Buttons */
        .btn-unlock {
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
            margin-bottom: 0.75rem;
        }

        .btn-unlock:hover {
            background: var(--primary-hover);
            box-shadow: 0 4px 12px rgba(36,48,143,0.25);
            transform: translateY(-1px);
        }

        .btn-unlock:active { transform: translateY(0); }

        .btn-logout {
            width: 100%;
            height: 46px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            background: #fff;
            color: var(--text-muted);
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            border-color: var(--danger);
            color: var(--danger);
        }

        .spinner {
            width: 20px; height: 20px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .input-error input {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.08);
        }

        .lock-time {
            text-align: center;
            color: var(--text-label);
            font-size: 0.82rem;
            margin-top: 1.25rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .lock-scene { flex-direction: column; }
            .brand-half { display: none; }
            .form-half { min-height: 100vh; }
        }

        @media (max-width: 480px) {
            .form-half { padding: 1.5rem 1rem; }
            .form-card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="lock-scene">
        {{-- ===== Brand Half ===== --}}
        <div class="brand-half">
            <div class="brand-grid"></div>
            <div class="brand-content">
                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="brand-logo"
                     onerror="this.style.display='none'">
                <h1 class="brand-title">{{ $siteName }}</h1>
                <p class="brand-subtitle">منصة رقمية متكاملة لإدارة ومراقبة سوق الطاقة</p>
            </div>
        </div>

        {{-- ===== Form Half ===== --}}
        <div class="form-half">
            <div class="form-card">
                <div class="lock-badge">
                    <i class="bi bi-lock-fill"></i>
                </div>

                @if($user->avatar_url && !str_contains($user->avatar_url, 'default'))
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="avatar"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="avatar-fallback" style="display:none;">{{ mb_substr($user->name, 0, 1) }}</div>
                @else
                    <div class="avatar-fallback">{{ mb_substr($user->name, 0, 1) }}</div>
                @endif

                <h2 class="user-name">{{ $user->name }}</h2>
                <p class="user-role">{{ $user->role_name }}</p>

                <p class="lock-msg">
                    <i class="bi bi-shield-lock"></i>
                    تم قفل الشاشة للحفاظ على خصوصيتك
                </p>

                <div id="alertBox" class="alert-box" style="display:none;">
                    <i class="bi bi-exclamation-circle"></i>
                    <span id="alertMsg"></span>
                </div>

                <div class="field">
                    <label for="password" class="field-label">كلمة المرور</label>
                    <div class="input-box">
                        <input type="password" id="password" name="password"
                               placeholder="أدخل كلمة المرور لفتح القفل"
                               autofocus maxlength="255">
                        <i class="bi bi-lock icon"></i>
                        <button type="button" class="toggle-pw" id="toggleBtn" aria-label="إظهار كلمة المرور">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                            <i class="bi bi-eye-slash" id="eyeOffIcon" style="display:none;"></i>
                        </button>
                    </div>
                </div>

                <button type="button" class="btn-unlock" id="unlockBtn">
                    <i class="bi bi-unlock-fill" id="unlockIcon"></i>
                    <span id="unlockText">فتح القفل</span>
                    <span id="unlockSpinner" class="spinner" style="display:none;"></span>
                </button>

                <button type="button" class="btn-logout" id="logoutBtn">
                    <i class="bi bi-box-arrow-right"></i>
                    تسجيل الخروج
                </button>

                @if(session('locked_at'))
                    <p class="lock-time">
                        تم القفل منذ {{ \Carbon\Carbon::parse(session('locked_at'))->diffForHumans() }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('toggleBtn');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');
        const unlockBtn = document.getElementById('unlockBtn');
        const unlockText = document.getElementById('unlockText');
        const unlockIcon = document.getElementById('unlockIcon');
        const unlockSpinner = document.getElementById('unlockSpinner');
        const logoutBtn = document.getElementById('logoutBtn');
        const alertBox = document.getElementById('alertBox');
        const alertMsg = document.getElementById('alertMsg');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Focus
        passwordInput.focus();

        // Toggle password
        toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.style.display = isPassword ? 'none' : 'inline';
            eyeOffIcon.style.display = isPassword ? 'inline' : 'none';
        });

        // Show error
        function showError(msg) {
            alertMsg.textContent = msg;
            alertBox.style.display = 'flex';
            passwordInput.closest('.input-box').classList.add('input-error');
        }

        // Clear error
        function clearError() {
            alertBox.style.display = 'none';
            passwordInput.closest('.input-box').classList.remove('input-error');
        }

        // Set loading
        function setLoading(loading) {
            unlockBtn.disabled = loading;
            unlockText.style.display = loading ? 'none' : 'inline';
            unlockIcon.style.display = loading ? 'none' : 'inline';
            unlockSpinner.style.display = loading ? 'inline-block' : 'none';
        }

        // Unlock via AJAX
        function doUnlock() {
            clearError();
            const password = passwordInput.value.trim();

            if (!password) {
                showError('يرجى إدخال كلمة المرور');
                passwordInput.focus();
                return;
            }

            setLoading(true);

            fetch('{{ route("admin.lock-screen.unlock") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: password })
            })
            .then(function(response) {
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                return response.json().then(function(data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function(result) {
                if (!result) return;

                if (result.status === 200 && (result.data.success || result.data.redirect)) {
                    window.location.href = result.data.redirect || '{{ route("admin.dashboard") }}';
                } else {
                    var msg = 'كلمة المرور غير صحيحة';
                    if (result.data.errors && result.data.errors.password) {
                        msg = result.data.errors.password[0];
                    } else if (result.data.message) {
                        msg = result.data.message;
                    }
                    showError(msg);
                    setLoading(false);
                    passwordInput.value = '';
                    passwordInput.focus();
                }
            })
            .catch(function() {
                showError('حدث خطأ. يرجى المحاولة مرة أخرى.');
                setLoading(false);
            });
        }

        // Unlock button click
        unlockBtn.addEventListener('click', doUnlock);

        // Enter key
        passwordInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doUnlock();
            }
        });

        // Clear error on typing
        passwordInput.addEventListener('input', clearError);

        // Logout via AJAX
        logoutBtn.addEventListener('click', function() {
            fetch('{{ route("logout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function() {
                window.location.href = '{{ route("login") }}';
            })
            .catch(function() {
                window.location.href = '{{ route("login") }}';
            });
        });
    });
    </script>
</body>
</html>
