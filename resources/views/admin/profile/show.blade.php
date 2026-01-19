@extends('layouts.admin')

@section('title', 'الملف الشخصي')
@php
    $breadcrumbTitle = 'الملف الشخصي';
@endphp

@push('styles')
<style>
    .profile-page {
        padding: 1.5rem 0;
    }

    .profile-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .profile-header {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        margin: 0 auto 1rem;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #3b82f6;
        font-weight: 700;
    }

    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .profile-role {
        opacity: 0.9;
        font-size: 1rem;
    }

    .profile-body {
        padding: 2rem;
    }

    .info-row {
        display: flex;
        padding: 1rem 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #64748b;
        width: 200px;
        flex-shrink: 0;
    }

    .info-value {
        color: #1e293b;
        flex: 1;
    }

    .password-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        margin-top: 2rem;
    }

    .password-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }

    .password-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .password-body {
        padding: 2rem;
    }

    .password-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="profile-page">
    <div class="row g-4">
        {{-- معلومات المستخدم --}}
        <div class="col-12">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="profile-name">{{ $user->name }}</div>
                    <div class="profile-role">{{ $user->role_name }}</div>
                </div>

                <div class="profile-body">
                    <div class="info-row">
                        <div class="info-label">اسم المستخدم:</div>
                        <div class="info-value">{{ $user->username ?? 'غير محدد' }}</div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">البريد الإلكتروني:</div>
                        <div class="info-value">{{ $user->email ?? 'غير محدد' }}</div>
                    </div>

                    @if($user->phone)
                    <div class="info-row">
                        <div class="info-label">رقم الموبايل:</div>
                        <div class="info-value">{{ $user->phone }}</div>
                    </div>
                    @endif

                    <div class="info-row">
                        <div class="info-label">الدور:</div>
                        <div class="info-value">{{ $user->role_name }}</div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">الحالة:</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $user->status === 'active' ? 'نشط' : 'غير نشط' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- تغيير كلمة المرور --}}
        <div class="col-12">
            <div class="password-card position-relative">
                <div class="password-header">
                    <h5 class="password-title">
                        <i class="bi bi-shield-lock"></i>
                        تغيير كلمة المرور
                    </h5>
                </div>

                <div class="password-body">
                    <form id="changePasswordForm" action="{{ route('admin.profile.change-password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">كلمة المرور الحالية <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="current_password" id="current_password" class="form-control"
                                           placeholder="أدخل كلمة المرور الحالية" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('current_password', this)">
                                        <i class="bi bi-eye" id="eye-icon-current"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="current_password_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="new_password" id="new_password" class="form-control"
                                           placeholder="أدخل كلمة المرور الجديدة" required minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('new_password', this)">
                                        <i class="bi bi-eye" id="eye-icon-new"></i>
                                    </button>
                                </div>
                                <div class="form-text">يجب أن تكون كلمة المرور 6 أحرف على الأقل</div>
                                <div class="invalid-feedback" id="new_password_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تأكيد كلمة المرور الجديدة <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control"
                                           placeholder="أعد إدخال كلمة المرور الجديدة" required minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('new_password_confirmation', this)">
                                        <i class="bi bi-eye" id="eye-icon-confirm"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="new_password_confirmation_error"></div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                                    <i class="bi bi-key me-1"></i>
                                    تغيير كلمة المرور
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="password-loading d-none" id="passwordLoading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted fw-semibold">جاري التحديث...</div>
                    </div>
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
