<!DOCTYPE html>
<html lang="ar" dir="rtl" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>قفل الشاشة - {{ \App\Models\Setting::get('site_name', 'نور') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @php
        $favicon = \App\Models\Setting::get('site_favicon', 'assets/admin/images/brand-logos/favicon.ico');
        $faviconUrl = str_starts_with($favicon, 'http') ? $favicon : asset($favicon);
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/bootstrap/css/bootstrap.rtl.min.css') }}">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/admin/icon-fonts/bootstrap-icons/bootstrap-icons.css') }}">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/styles.rtl.min.css') }}">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .lock-screen-container {
            max-width: 450px;
            width: 100%;
            padding: 0 1rem;
        }
        
        .lock-screen-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem 2rem;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            margin: 0 auto 1.5rem;
            display: block;
            object-fit: cover;
        }
        
        .lock-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #fff;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .user-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
            text-align: center;
        }
        
        .user-role {
            font-size: 0.95rem;
            color: #718096;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .locked-message {
            text-align: center;
            color: #4a5568;
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }
        
        .form-control {
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-unlock {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-unlock:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: #fff;
        }
        
        .btn-unlock:active {
            transform: translateY(0);
        }
        
        .btn-logout {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #718096;
            width: 100%;
            margin-top: 0.75rem;
            transition: all 0.2s;
        }
        
        .btn-logout:hover {
            border-color: #dc2626;
            color: #dc2626;
            background: #fff;
        }
        
        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            color: #991b1b;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .locked-time {
            text-align: center;
            color: #a0aec0;
            font-size: 0.875rem;
            margin-top: 1.5rem;
        }
        
        .input-group-text {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-left: none;
            border-radius: 0 0.75rem 0.75rem 0;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0.75rem 0 0 0.75rem;
        }
        
        .input-group .form-control:focus {
            border-left: none;
        }
        
        .input-group .form-control:focus + .input-group-text {
            border-color: #667eea;
        }
        
        .password-toggle {
            cursor: pointer;
            user-select: none;
            background: transparent;
            border: none;
            color: #718096;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
    </style>
</head>

<body>
    <div class="lock-screen-container">
        <div class="lock-screen-card">
            <!-- Lock Icon -->
            <div class="lock-icon">
                <i class="bi bi-lock-fill"></i>
            </div>
            
            <!-- User Avatar -->
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="user-avatar">
            
            <!-- User Info -->
            <h2 class="user-name">{{ $user->name }}</h2>
            <p class="user-role">{{ $user->role_name }}</p>
            
            <!-- Locked Message -->
            <p class="locked-message">
                <i class="bi bi-shield-lock me-1"></i>
                تم قفل الشاشة للحفاظ على خصوصيتك
            </p>
            
            <!-- Unlock Form -->
            <form method="POST" action="{{ route('admin.lock-screen.unlock') }}">
                @csrf
                
                @if($errors->has('password'))
                    <div class="error-message">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ $errors->first('password') }}</span>
                    </div>
                @endif
                
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">كلمة المرور</label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="أدخل كلمة المرور لفتح القفل"
                               autofocus
                               required>
                        <span class="input-group-text">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-unlock">
                    <i class="bi bi-unlock-fill me-2"></i>
                    فتح القفل
                </button>
            </form>
            
            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-logout">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    تسجيل الخروج
                </button>
            </form>
            
            <!-- Locked Time -->
            @if(session('locked_at'))
            <p class="locked-time">
                تم القفل منذ {{ \Carbon\Carbon::parse(session('locked_at'))->diffForHumans() }}
            </p>
            @endif
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
        
        // Auto-focus on password input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('password').focus();
        });
        
        // Allow Enter key to submit
        document.getElementById('password').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.form.submit();
            }
        });
    </script>
</body>
</html>

