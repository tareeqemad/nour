<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = \App\Models\Setting::get('site_name', 'نور');
    @endphp
    <title>تسجيل الدخول - {{ $siteName }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 50%, #2563eb 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            position: relative;
            overflow: hidden;
        }

        @media (max-width: 1024px) {
            body {
                overflow: auto;
                align-items: flex-start;
            }
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 0;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
            overflow: hidden;
            width: 100%;
            height: 100vh;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            position: relative;
        }

        @media (max-width: 1024px) {
            .login-container {
                overflow: visible;
                height: auto;
            }
        }

        .login-form-section {
            padding: 40px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9ff 100%);
            position: relative;
            overflow: hidden;
        }

        @media (max-width: 1024px) {
            .login-form-section {
                overflow: visible;
                justify-content: flex-start;
            }
        }

        .login-form-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background:
                linear-gradient(135deg, rgba(30, 64, 175, 0.03) 0%, transparent 50%),
                radial-gradient(circle at top right, rgba(30, 58, 138, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .form-content {
            position: relative;
            z-index: 1;
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
        }

        @media (min-width: 1025px) {
            .form-content {
                max-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }
        .logo-mini {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            animation: fadeInDown 0.6s ease;
        }

        .logo-mini img {
            max-height: 250px;
            width: auto;
        }

        .logo-mini-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
            font-weight: 800;
            box-shadow:
                0 10px 25px rgba(30, 64, 175, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .logo-mini-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.2) 0%, transparent 50%);
            pointer-events: none;
        }

        .logo-mini-icon::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: shine 3s ease-in-out infinite;
        }

        @keyframes shine {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
            50% { transform: translate(-50%, -50%) rotate(180deg); }
        }

        .logo-mini:hover .logo-mini-icon {
            transform: scale(1.05) rotate(5deg);
            box-shadow:
                0 15px 35px rgba(30, 64, 175, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.2) inset;
        }


        .welcome-text {
            font-size: 32px;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 10px;
            line-height: 1.2;
            animation: fadeInDown 0.6s ease 0.1s both;
        }

        .welcome-subtitle {
            color: #64748b;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.6;
            animation: fadeInDown 0.6s ease 0.2s both;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 20px;
            animation: fadeInUp 0.6s ease both;
        }

        .form-group:nth-child(1) { animation-delay: 0.3s; }
        .form-group:nth-child(2) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #1e293b;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: -0.3px;
        }

        .input-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: color 0.2s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #1e40af;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            color: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-input.password-input {
            padding-left: 50px;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow:
                0 0 0 4px rgba(30, 64, 175, 0.1),
                0 4px 12px rgba(30, 64, 175, 0.15);
            transform: translateY(-1px);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            animation: fadeInUp 0.6s ease 0.5s both;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .remember-me input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #1e40af;
            border-radius: 4px;
        }

        .remember-me label {
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
            animation: fadeInUp 0.6s ease 0.6s both;
        }

        .login-button:hover {
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.4);
            transform: translateY(-1px);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 12px;
            font-weight: 600;
            border-right: 4px solid #dc2626;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .login-image-section {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 50%, #2563eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 60px;
        }

        .login-image-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background:
                url('data:image/svg+xml,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>'),
                radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0.15) 0%, transparent 50%);
            animation: move 25s linear infinite;
            opacity: 0.4;
        }

        @keyframes move {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(100px, 100px) rotate(360deg); }
        }

        .brand-content {
            text-align: center;
            z-index: 1;
            position: relative;
            animation: fadeInScale 1s ease;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .brand-logo {
            font-size: 96px;
            font-weight: 800;
            color: white;
            text-shadow:
                0 4px 20px rgba(0, 0, 0, 0.3),
                0 0 40px rgba(255, 255, 255, 0.2);
            margin-bottom: 16px;
            letter-spacing: -2px;
            background: linear-gradient(135deg, #ffffff 0%, rgba(255, 255, 255, 0.9) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.95);
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 40px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            padding: 0 20px;
        }

        .product-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .product-item:hover {
            transform: translateY(-8px) scale(1.05);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .product-icon {
            font-size: 36px;
            margin-bottom: 10px;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .product-icon svg {
            transition: all 0.3s ease;
        }

        .product-item:hover .product-icon {
            background: rgba(255, 255, 255, 0.35);
            transform: scale(1.15) rotate(8deg);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* ألوان الأيقونات */
        .icon-energy svg {
            stroke: #fbbf24;
            fill: rgba(251, 191, 36, 0.1);
        }

        .icon-generators svg {
            stroke: #60a5fa;
            fill: rgba(96, 165, 250, 0.1);
        }

        .icon-batteries svg {
            stroke: #34d399;
            fill: rgba(52, 211, 153, 0.1);
        }

        .icon-network svg {
            stroke: #fb923c;
            fill: rgba(251, 146, 60, 0.1);
        }

        .icon-location svg {
            stroke: #f87171;
            fill: rgba(248, 113, 113, 0.1);
        }

        .icon-solar svg {
            stroke: #fbbf24;
            fill: rgba(251, 191, 36, 0.1);
        }

        .product-item:hover .icon-energy svg {
            stroke: #f59e0b;
            filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.6));
        }

        .product-item:hover .icon-generators svg {
            stroke: #3b82f6;
            filter: drop-shadow(0 0 8px rgba(96, 165, 250, 0.6));
        }

        .product-item:hover .icon-batteries svg {
            stroke: #10b981;
            filter: drop-shadow(0 0 8px rgba(52, 211, 153, 0.6));
        }

        .product-item:hover .icon-network svg {
            stroke: #f97316;
            filter: drop-shadow(0 0 8px rgba(251, 146, 60, 0.6));
        }

        .product-item:hover .icon-location svg {
            stroke: #ef4444;
            filter: drop-shadow(0 0 8px rgba(248, 113, 113, 0.6));
        }

        .product-item:hover .icon-solar svg {
            stroke: #f59e0b;
            filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.6));
        }

        .product-name {
            color: white;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .brand-description {
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            line-height: 1.8;
            max-width: 400px;
            margin: 16px auto 0;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 1024px) {
            .login-container {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
            }

            .login-image-section {
                display: none;
            }

            .login-form-section {
                padding: 40px 30px;
                min-height: 100vh;
                overflow: visible;
            }

            .form-content {
                padding-bottom: 40px;
            }
        }

        @media (max-width: 768px) {
            .login-form-section {
                padding: 30px 20px;
            }

            .form-content {
                max-width: 100%;
            }

            .logo-mini {
                margin-bottom: 24px;
            }

            .logo-mini img {
                max-height: 180px;
            }

            .welcome-text {
                font-size: 26px;
                margin-bottom: 8px;
            }

            .welcome-subtitle {
                font-size: 13px;
                margin-bottom: 24px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-label {
                font-size: 12px;
                margin-bottom: 6px;
            }

            .form-input {
                padding: 11px 14px;
                font-size: 14px;
            }

            .form-input.password-input {
                padding-left: 45px;
            }

            .password-toggle {
                left: 14px;
                padding: 6px;
            }

            .password-toggle svg {
                width: 18px;
                height: 18px;
            }

            .remember-forgot {
                margin-bottom: 20px;
            }

            .remember-me label {
                font-size: 11px;
            }

            .login-button {
                padding: 11px;
                font-size: 13px;
            }

            .error-message {
                padding: 10px 14px;
                font-size: 11px;
                margin-bottom: 18px;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                padding: 0 10px;
            }

            .product-item {
                padding: 20px 15px;
            }

            .product-icon {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }

            .product-icon svg {
                width: 32px;
                height: 32px;
            }

            .product-name {
                font-size: 11px;
            }

            .brand-logo {
                font-size: 64px;
            }

            .brand-subtitle {
                font-size: 16px;
                margin-bottom: 30px;
            }

            .brand-description {
                font-size: 12px;
                max-width: 100%;
                padding: 0 20px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
            }

            .login-container {
                border-radius: 0;
            }

            .login-form-section {
                padding: 24px 16px;
            }

            .logo-mini {
                margin-bottom: 20px;
            }

            .logo-mini img {
                max-height: 150px;
            }

            .logo-mini-icon {
                width: 60px;
                height: 60px;
                font-size: 32px;
            }

            .logo-mini-icon svg {
                width: 40px;
                height: 40px;
            }

            .welcome-text {
                font-size: 22px;
                margin-bottom: 6px;
            }

            .welcome-subtitle {
                font-size: 12px;
                margin-bottom: 20px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            .form-label {
                font-size: 11px;
                margin-bottom: 5px;
            }

            .form-input {
                padding: 10px 12px;
                font-size: 13px;
                border-radius: 8px;
            }

            .form-input.password-input {
                padding-left: 40px;
            }

            .password-toggle {
                left: 12px;
                padding: 5px;
            }

            .password-toggle svg {
                width: 16px;
                height: 16px;
            }

            .remember-forgot {
                margin-bottom: 18px;
            }

            .remember-me input[type="checkbox"] {
                width: 18px;
                height: 18px;
            }

            .remember-me label {
                font-size: 10px;
            }

            .login-button {
                padding: 10px;
                font-size: 12px;
                border-radius: 8px;
            }

            .error-message {
                padding: 9px 12px;
                font-size: 10px;
                margin-bottom: 16px;
                border-radius: 8px;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                padding: 0 8px;
            }

            .product-item {
                padding: 16px 12px;
                border-radius: 12px;
            }

            .product-icon {
                width: 50px;
                height: 50px;
                font-size: 26px;
            }

            .product-icon svg {
                width: 28px;
                height: 28px;
            }

            .product-name {
                font-size: 10px;
            }

            .brand-logo {
                font-size: 48px;
                margin-bottom: 12px;
            }

            .brand-subtitle {
                font-size: 14px;
                margin-bottom: 24px;
            }

            .brand-description {
                font-size: 11px;
                line-height: 1.6;
                padding: 0 16px;
            }
        }

        @media (max-width: 360px) {
            .login-form-section {
                padding: 20px 12px;
            }

            .logo-mini img {
                max-height: 120px;
            }

            .welcome-text {
                font-size: 20px;
            }

            .welcome-subtitle {
                font-size: 11px;
            }

            .products-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .product-item {
                padding: 14px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form-section">
            <div class="form-content">
                <div class="logo-mini">
                    @php
                        $logo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png');
                        $logoUrl = str_starts_with($logo, 'http') ? $logo : asset($logo);
                    @endphp
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'نور') }}" style="max-height: 250px; width: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="logo-mini-icon" style="display: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 60px; height: 60px;">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                        </svg>
                    </div>
                </div>

                <h1 class="welcome-text">مرحباً بك</h1>
                <p class="welcome-subtitle">سجل دخولك للوصول إلى منصة {{ \App\Models\Setting::get('site_name', 'نور') }}</p>

                @if ($errors->any())
                    <div class="error-message">
                        <strong>⚠️ خطأ!</strong>
                        @foreach ($errors->all() as $error)
                            <div style="margin-top: 8px;">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    {{-- Honeypot field to prevent bots --}}
                    <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">

                    <div class="form-group">
                        <label for="username" class="form-label">اسم المستخدم</label>
                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                value="{{ old('username') }}"
                                placeholder="أدخل اسم المستخدم"
                                required
                                autofocus
                                autocomplete="username"
                                maxlength="255"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <div class="input-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input password-input"
                                placeholder="أدخل كلمة المرور"
                                required
                                autocomplete="current-password"
                                maxlength="255"
                            >
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="إظهار كلمة المرور">
                                <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eyeOffIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">تذكرني</label>
                        </div>
                        <a href="#" id="forgotPasswordLink" style="color: #1e40af; text-decoration: none; font-size: 12px; font-weight: 600;">نسيت كلمة المرور؟</a>
                    </div>

                    <button type="submit" class="login-button" id="loginButton">
                        <span id="loginButtonText">تسجيل الدخول</span>
                        <span id="loginButtonSpinner" style="display: none;">
                            <svg style="width: 20px; height: 20px; animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke-opacity="0.75"></path>
                            </svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <div class="login-image-section">
            <div class="brand-content">
                <div class="brand-logo">{{ \App\Models\Setting::get('site_name', 'نور') }}</div>
                <div class="brand-subtitle">منصة رقمية لإدارة سوق الطاقة</div>

                <div class="products-grid">
                    <div class="product-item">
                        <div class="product-icon icon-energy">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 40px; height: 40px;">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                            </svg>
                        </div>
                        <div class="product-name">طاقة كهربائية</div>
                    </div>
                    <div class="product-item">
                        <div class="product-icon icon-generators">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 40px; height: 40px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="product-name">مولدات</div>
                    </div>
                    <div class="product-item">
                        <div class="product-icon icon-batteries">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 40px; height: 40px;">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <div class="product-name">بطاريات</div>
                    </div>
                    <div class="product-item">
                        <div class="product-icon icon-network">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 40px; height: 40px;">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </div>
                        <div class="product-name">شبكة توزيع</div>
                    </div>
                    <div class="product-item">
                        <div class="product-icon icon-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 40px; height: 40px;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="product-name">نقاط توزيع</div>
                    </div>
                    <div class="product-item">
                        <div class="product-icon icon-solar">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 40px; height: 40px;">
                                <line x1="12" y1="2" x2="12" y2="6"></line>
                                <line x1="12" y1="18" x2="12" y2="22"></line>
                                <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                <line x1="2" y1="12" x2="6" y2="12"></line>
                                <line x1="18" y1="12" x2="22" y2="12"></line>
                                <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <div class="product-name">طاقة شمسية</div>
                    </div>
                </div>

                <div class="brand-description">
                    منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة.
                </div>
            </div>
        </div>
    </div>

    <script>
        // إظهار/إخفاء كلمة المرور
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'text') {
                    eyeIcon.style.display = 'none';
                    eyeOffIcon.style.display = 'block';
                    passwordToggle.setAttribute('aria-label', 'إخفاء كلمة المرور');
                } else {
                    eyeIcon.style.display = 'block';
                    eyeOffIcon.style.display = 'none';
                    passwordToggle.setAttribute('aria-label', 'إظهار كلمة المرور');
                }
            });
        }

        // Disable button and show spinner on form submit
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginButtonText = document.getElementById('loginButtonText');
        const loginButtonSpinner = document.getElementById('loginButtonSpinner');

        if (loginForm && loginButton) {
            // تحديث CSRF token من API قبل إرسال النموذج
            function refreshCSRFToken() {
                return fetch('{{ route("login.csrf-token") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const tokenInput = loginForm.querySelector('input[name="_token"]');
                    const metaToken = document.querySelector('meta[name="csrf-token"]');
                    
                    if (tokenInput && data.token) {
                        tokenInput.value = data.token;
                    }
                    
                    if (metaToken && data.token) {
                        metaToken.setAttribute('content', data.token);
                    }
                    
                    return true;
                })
                .catch(error => {
                    console.error('خطأ في تحديث CSRF token:', error);
                    return false;
                });
            }

            loginForm.addEventListener('submit', function(e) {
                // Check honeypot field (if filled, it's a bot)
                const honeypot = loginForm.querySelector('input[name="website"]');
                if (honeypot && honeypot.value !== '') {
                    e.preventDefault();
                    return false;
                }

                // منع الإرسال المباشر
                e.preventDefault();
                
                // تحديث CSRF token قبل الإرسال
                refreshCSRFToken().then((success) => {
                    // Disable button and show spinner
                    loginButton.disabled = true;
                    loginButtonText.style.display = 'none';
                    loginButtonSpinner.style.display = 'inline-block';
                    
                    // Prevent double submission
                    if (loginForm.submitDisabled) {
                        return false;
                    }
                    loginForm.submitDisabled = true;
                    
                    // إرسال النموذج بعد تحديث الـ token
                    loginForm.submit();
                }).catch(() => {
                    // إذا فشل التحديث، أرسل النموذج كما هو
                    loginButton.disabled = true;
                    loginButtonText.style.display = 'none';
                    loginButtonSpinner.style.display = 'inline-block';
                    loginForm.submitDisabled = true;
                    loginForm.submit();
                });
            });

            // Re-enable button if form validation fails (after page reload with errors)
            window.addEventListener('load', function() {
                if (loginForm.querySelector('.error-message')) {
                    loginButton.disabled = false;
                    loginButtonText.style.display = 'inline';
                    loginButtonSpinner.style.display = 'none';
                }
            });
        }

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

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

            // Open modal
            if (forgotPasswordLink) {
                forgotPasswordLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (resetModal && resetModalBackdrop) {
                        resetModal.style.display = 'flex';
                        resetModalBackdrop.style.display = 'block';
                        if (phoneInput) {
                            setTimeout(() => phoneInput.focus(), 100);
                        }
                    }
                });
            } else {
                console.error('forgotPasswordLink not found');
            }

            // Close modal
            function closeModal() {
                if (resetModal && resetModalBackdrop) {
                    resetModal.style.display = 'none';
                    resetModalBackdrop.style.display = 'none';
                    if (resetPasswordForm) resetPasswordForm.reset();
                    if (resetFormErrors) resetFormErrors.innerHTML = '';
                    if (otpSection) otpSection.style.display = 'none';
                    if (sendOTPButton) sendOTPButton.style.display = 'block';
                    if (verifyOTPButton) verifyOTPButton.style.display = 'none';
                    if (phoneInput) phoneInput.disabled = false;
                }
            }

            if (closeResetModal) {
                closeResetModal.addEventListener('click', closeModal);
            }

            if (resetModalBackdrop) {
                resetModalBackdrop.addEventListener('click', closeModal);
            }

            // Format phone input
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    let value = this.value.replace(/[^0-9]/g, '');
                    if (value.length > 0 && !value.startsWith('0')) {
                        if (value.startsWith('59') || value.startsWith('56')) {
                            value = '0' + value;
                        }
                    }
                    if (value.length > 10) {
                        value = value.substring(0, 10);
                    }
                    this.value = value;
                });
            }

            // Format OTP input (numbers only, 6 digits)
            if (otpInput) {
                otpInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
                });
            }

            // Send OTP
            if (sendOTPButton && phoneInput && resetFormErrors) {
                sendOTPButton.addEventListener('click', function() {
                    const phone = phoneInput.value.trim();
                    
                    if (!phone || !/^0(59|56)\d{7}$/.test(phone)) {
                        resetFormErrors.innerHTML = '<div style="color: #dc2626; font-size: 12px; margin-top: 8px;">يرجى إدخال رقم جوال صحيح (يبدأ بـ 059 أو 056)</div>';
                        return;
                    }

                    resetFormErrors.innerHTML = '';
                    sendOTPButton.disabled = true;
                    sendOTPButton.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite;"></span> جاري الإرسال...';

                    fetch('{{ route("password.reset.send-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ phone: phone })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success || !data.errors) {
                            resetFormErrors.innerHTML = '<div style="color: #10b981; font-size: 12px; margin-top: 8px;">' + (data.message || 'تم إرسال رمز التحقق بنجاح') + '</div>';
                            if (otpSection) otpSection.style.display = 'block';
                            sendOTPButton.style.display = 'none';
                            if (verifyOTPButton) verifyOTPButton.style.display = 'block';
                            phoneInput.disabled = true;
                            if (otpInput) otpInput.focus();
                        } else {
                            const errors = data.errors || {};
                            let errorMsg = '';
                            if (errors.phone) {
                                errorMsg = errors.phone[0];
                            } else if (data.message) {
                                errorMsg = data.message;
                            } else {
                                errorMsg = 'حدث خطأ. يرجى المحاولة مرة أخرى.';
                            }
                            resetFormErrors.innerHTML = '<div style="color: #dc2626; font-size: 12px; margin-top: 8px;">' + errorMsg + '</div>';
                            sendOTPButton.disabled = false;
                            sendOTPButton.innerHTML = 'إرسال رمز التحقق';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resetFormErrors.innerHTML = '<div style="color: #dc2626; font-size: 12px; margin-top: 8px;">حدث خطأ. يرجى المحاولة مرة أخرى.</div>';
                        sendOTPButton.disabled = false;
                        sendOTPButton.innerHTML = 'إرسال رمز التحقق';
                    });
                });
            }

            // Verify OTP and reset password
            if (verifyOTPButton && phoneInput && otpInput && resetFormErrors) {
                verifyOTPButton.addEventListener('click', function() {
                    const phone = phoneInput.value.trim();
                    const otp = otpInput.value.trim();

                    if (!otp || otp.length !== 6) {
                        resetFormErrors.innerHTML = '<div style="color: #dc2626; font-size: 12px; margin-top: 8px;">يرجى إدخال رمز التحقق (6 أرقام)</div>';
                        return;
                    }

                    resetFormErrors.innerHTML = '';
                    verifyOTPButton.disabled = true;
                    verifyOTPButton.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite;"></span> جاري التحقق...';

                    fetch('{{ route("password.reset.verify-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ phone: phone, otp: otp })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success || !data.errors) {
                            resetFormErrors.innerHTML = '<div style="color: #10b981; font-size: 12px; margin-top: 8px;">' + (data.message || 'تم إعادة تعيين كلمة المرور بنجاح') + '</div>';
                            setTimeout(() => {
                                closeModal();
                                window.location.href = '{{ route("login") }}';
                            }, 2000);
                        } else {
                            const errors = data.errors || {};
                            let errorMsg = '';
                            if (errors.otp) {
                                errorMsg = errors.otp[0];
                            } else if (errors.phone) {
                                errorMsg = errors.phone[0];
                            } else if (data.message) {
                                errorMsg = data.message;
                            } else {
                                errorMsg = 'رمز التحقق غير صحيح. يرجى المحاولة مرة أخرى.';
                            }
                            resetFormErrors.innerHTML = '<div style="color: #dc2626; font-size: 12px; margin-top: 8px;">' + errorMsg + '</div>';
                            verifyOTPButton.disabled = false;
                            verifyOTPButton.innerHTML = 'تحقق وإعادة تعيين';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (resetFormErrors) {
                            resetFormErrors.innerHTML = '<div style="color: #dc2626; font-size: 12px; margin-top: 8px;">حدث خطأ. يرجى المحاولة مرة أخرى.</div>';
                        }
                        if (verifyOTPButton) {
                            verifyOTPButton.disabled = false;
                            verifyOTPButton.innerHTML = 'تحقق وإعادة تعيين';
                        }
                    });
                });
            }
        });
    </script>

    <!-- Password Reset Modal -->
    <div id="resetModalBackdrop" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 9998; backdrop-filter: blur(4px);"></div>
    <div id="resetPasswordModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: white; border-radius: 16px; padding: 32px; max-width: 450px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; max-height: 90vh; overflow-y: auto;">
            <button id="closeResetModal" style="position: absolute; top: 16px; left: 16px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#1e293b';" onmouseout="this.style.background='none'; this.style.color='#64748b';">
                ×
            </button>
            
            <h2 style="font-size: 24px; font-weight: 800; color: #1a202c; margin-bottom: 8px; text-align: center;">استعادة كلمة المرور</h2>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 24px; text-align: center;">أدخل رقم الجوال المسجل لإرسال رمز التحقق</p>

            <form id="resetPasswordForm">
                <div id="resetFormErrors"></div>

                <div style="margin-bottom: 20px;">
                    <label for="resetPhone" style="display: block; margin-bottom: 8px; color: #1e293b; font-weight: 700; font-size: 13px;">رقم الجوال</label>
                    <input
                        type="text"
                        id="resetPhone"
                        class="form-input"
                        placeholder="0591234567 أو 0561234567"
                        required
                        maxlength="10"
                        style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s;"
                        onfocus="this.style.borderColor='#1e40af'; this.style.boxShadow='0 0 0 4px rgba(30, 64, 175, 0.1)';"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                    >
                </div>

                <div id="otpSection" style="display: none; margin-bottom: 20px;">
                    <label for="resetOTP" style="display: block; margin-bottom: 8px; color: #1e293b; font-weight: 700; font-size: 13px;">رمز التحقق</label>
                    <input
                        type="text"
                        id="resetOTP"
                        class="form-input"
                        placeholder="أدخل الرمز المكون من 6 أرقام"
                        required
                        maxlength="6"
                        style="width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; text-align: center; letter-spacing: 8px; font-size: 18px; font-weight: 700;"
                        onfocus="this.style.borderColor='#1e40af'; this.style.boxShadow='0 0 0 4px rgba(30, 64, 175, 0.1)';"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                    >
                    <small style="color: #64748b; font-size: 12px; margin-top: 4px; display: block;">الرمز صالح لمدة 10 دقائق فقط</small>
                </div>

                <button
                    type="button"
                    id="sendOTPButton"
                    class="login-button"
                    style="width: 100%; padding: 12px; background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);"
                    onmouseover="this.style.boxShadow='0 6px 16px rgba(30, 64, 175, 0.4)'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.boxShadow='0 4px 12px rgba(30, 64, 175, 0.3)'; this.style.transform='translateY(0)';"
                >
                    إرسال رمز التحقق
                </button>

                <button
                    type="button"
                    id="verifyOTPButton"
                    class="login-button"
                    style="display: none; width: 100%; padding: 12px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); margin-top: 12px;"
                    onmouseover="this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.4)'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.3)'; this.style.transform='translateY(0)';"
                >
                    تحقق وإعادة تعيين
                </button>
            </form>
        </div>
    </div>
</body>
</html>

