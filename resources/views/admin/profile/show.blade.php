@extends('layouts.admin')

@section('title', 'الملف الشخصي')
@php
    $breadcrumbTitle = 'الملف الشخصي';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/profile.css') }}">
@endpush

@section('content')
<div class="profile-page">
    <div class="row g-4">
        {{-- معلومات المستخدم --}}
        <div class="col-12">
            <div class="profile-header-card">
                <div class="profile-header-bg">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar">
                            @if($user->avatar && file_exists(storage_path('app/public/'.$user->avatar)))
                                <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}">
                            @else
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'profile-avatar-initials\'>{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>'">
                            @endif
                        </div>
                    </div>
                    <div class="profile-name">{{ $user->name }}</div>
                    <div class="profile-role">{{ $user->role_name }}</div>
                </div>

                <div class="profile-info-section">
                    <div class="profile-info-grid">
                        <div class="profile-info-item">
                            <div class="profile-info-label">
                                <i class="bi bi-person-badge"></i>
                                اسم المستخدم
                            </div>
                            <div class="profile-info-value">{{ $user->username ?? 'غير محدد' }}</div>
                        </div>

                        <div class="profile-info-item">
                            <div class="profile-info-label">
                                <i class="bi bi-envelope"></i>
                                البريد الإلكتروني
                            </div>
                            <div class="profile-info-value">{{ $user->email ?? 'غير محدد' }}</div>
                        </div>

                        @if($user->phone)
                        <div class="profile-info-item">
                            <div class="profile-info-label">
                                <i class="bi bi-phone"></i>
                                رقم الموبايل
                            </div>
                            <div class="profile-info-value">{{ $user->phone }}</div>
                        </div>
                        @endif

                        <div class="profile-info-item">
                            <div class="profile-info-label">
                                <i class="bi bi-shield-check"></i>
                                الدور
                            </div>
                            <div class="profile-info-value">{{ $user->role_name }}</div>
                        </div>

                        <div class="profile-info-item">
                            <div class="profile-info-label">
                                <i class="bi bi-circle-fill"></i>
                                الحالة
                            </div>
                            <div class="profile-info-value">
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $user->status === 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- تغيير كلمة المرور --}}
        <div class="col-12">
            <div class="password-change-card position-relative">
                <div class="password-change-header">
                    <h5 class="password-change-title">
                        <i class="bi bi-shield-lock"></i>
                        تغيير كلمة المرور
                    </h5>
                </div>

                <div class="password-change-body">
                    <form id="changePasswordForm" action="{{ route('admin.profile.change-password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="password-form-group">
                                    <label class="password-form-label">
                                        <i class="bi bi-key"></i>
                                        كلمة المرور الحالية
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password" name="current_password" id="current_password" class="form-control"
                                               placeholder="أدخل كلمة المرور الحالية" required>
                                        <button class="password-toggle-btn" type="button" onclick="togglePasswordVisibility('current_password', this)">
                                            <i class="bi bi-eye" id="eye-icon-current"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="current_password_error"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="password-form-group">
                                    <label class="password-form-label">
                                        <i class="bi bi-key-fill"></i>
                                        كلمة المرور الجديدة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password" name="new_password" id="new_password" class="form-control"
                                               placeholder="أدخل كلمة المرور الجديدة" required minlength="6">
                                        <button class="password-toggle-btn" type="button" onclick="togglePasswordVisibility('new_password', this)">
                                            <i class="bi bi-eye" id="eye-icon-new"></i>
                                        </button>
                                    </div>
                                    <div class="password-form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        يجب أن تكون كلمة المرور 6 أحرف على الأقل
                                    </div>
                                    <div class="invalid-feedback" id="new_password_error"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="password-form-group">
                                    <label class="password-form-label">
                                        <i class="bi bi-key-fill"></i>
                                        تأكيد كلمة المرور الجديدة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control"
                                               placeholder="أعد إدخال كلمة المرور الجديدة" required minlength="6">
                                        <button class="password-toggle-btn" type="button" onclick="togglePasswordVisibility('new_password_confirmation', this)">
                                            <i class="bi bi-eye" id="eye-icon-confirm"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="new_password_confirmation_error"></div>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary password-submit-btn" id="changePasswordBtn">
                                    <i class="bi bi-key"></i>
                                    تغيير كلمة المرور
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="password-loading-overlay d-none" id="passwordLoading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted mt-2">جاري التحديث...</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        alert(msg);
    }

    const passwordForm = document.getElementById('changePasswordForm');
    const passwordBtn = document.getElementById('changePasswordBtn');
    const passwordLoading = document.getElementById('passwordLoading');

    if (!passwordForm || !passwordBtn) return;

    function setPasswordLoading(on) {
        passwordLoading.classList.toggle('d-none', !on);
        passwordBtn.disabled = on;
    }

    function clearPasswordErrors() {
        passwordForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        passwordForm.querySelectorAll('.invalid-feedback').forEach(el => {
            if (el.id && el.id.endsWith('_error')) {
                el.textContent = '';
            } else {
                el.remove();
            }
        });
    }

    function showPasswordErrors(errors) {
        clearPasswordErrors();
        
        Object.keys(errors || {}).forEach(field => {
            const input = passwordForm.querySelector(`[name="${CSS.escape(field)}"]`);
            const errorDiv = document.getElementById(field + '_error');
            
            if (input) {
                input.classList.add('is-invalid');
            }
            
            if (errorDiv) {
                errorDiv.textContent = errors[field][0];
                errorDiv.style.display = 'block';
            } else if (input) {
                const div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = errors[field][0];
                input.parentElement.insertAdjacentElement('afterend', div);
            }
        });
    }

    async function submitPasswordChange() {
        clearPasswordErrors();
        
        const currentPassword = passwordForm.querySelector('[name="current_password"]').value;
        const newPassword = passwordForm.querySelector('[name="new_password"]').value;
        const confirmPassword = passwordForm.querySelector('[name="new_password_confirmation"]').value;

        // التحقق من تطابق كلمة المرور الجديدة
        if (newPassword !== confirmPassword) {
            showPasswordErrors({
                'new_password_confirmation': ['كلمة المرور الجديدة غير متطابقة']
            });
            return;
        }

        setPasswordLoading(true);

        try {
            const fd = new FormData(passwordForm);

            const res = await fetch(passwordForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd
            });

            const data = await res.json();

            if (res.status === 422) {
                showPasswordErrors(data.errors || {});
                notify('error', 'تحقق من الحقول المطلوبة');
                return;
            }

            if (data && data.success) {
                notify('success', data.message || 'تم تغيير كلمة المرور بنجاح');
                passwordForm.reset();
            } else {
                notify('error', (data && data.message) ? data.message : 'فشل تغيير كلمة المرور');
            }

        } catch (e) {
            notify('error', 'حدث خطأ أثناء تغيير كلمة المرور');
        } finally {
            setPasswordLoading(false);
        }
    }

    passwordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitPasswordChange();
    });

    // دالة لإظهار/إخفاء كلمة المرور
    window.togglePasswordVisibility = function(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    };
})();
</script>
@endpush
