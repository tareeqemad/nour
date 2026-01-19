@extends('layouts.admin')

@section('title', 'تعديل المستخدم')

@php
    $authUser = auth()->user();
    $breadcrumbTitle = 'تعديل المستخدم';
    $breadcrumbParent = 'إدارة المستخدمين';
    $breadcrumbParentUrl = route('admin.users.index');

    $mode = 'edit';
    
    // تحديد نوع المستخدم للعرض
    $userTypeLabel = '';
    if ($user->isCompanyOwner()) {
        $userTypeLabel = 'مشغل';
    } elseif ($user->isEmployee()) {
        $userTypeLabel = 'موظف';
    } elseif ($user->isTechnician()) {
        $userTypeLabel = 'فني';
    } elseif ($user->isAdmin()) {
        $userTypeLabel = 'مدير';
    } elseif ($user->isEnergyAuthority()) {
        $userTypeLabel = 'سلطة الطاقة';
    } elseif ($user->isSuperAdmin()) {
        $userTypeLabel = 'مدير النظام';
    }
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/users.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-person-gear me-2"></i>
                                تعديل المستخدم
                            </h5>
                            <div class="general-subtitle">
                                تعديل بيانات: <span class="fw-bold">{{ $user->name }}</span>
                                @if($userTypeLabel)
                                    <span class="badge bg-primary ms-2">{{ $userTypeLabel }}</span>
                                @endif
                                @if($authUser->isEnergyAuthority() && !$user->isCompanyOwner())
                                    <span class="badge bg-danger ms-2">لا يمكنك تعديل هذا المستخدم</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            رجوع
                        </a>
                    </div>

                    <form action="{{ route('admin.users.update', $user) }}" method="POST" id="editUserForm">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            @include('admin.users.partials.form', [
                                'mode' => $mode,
                                'user' => $user,
                                'defaultRole' => '',
                                'operatorFieldName' => 'operator_id',
                            ])

                            <hr class="my-4">

                            @if($user->phone)
                                <div class="mb-3">
                                    <button type="button" class="btn btn-warning" id="resetPasswordBtn">
                                        <i class="bi bi-key me-2"></i>
                                        إعادة تعيين كلمة المرور
                                    </button>
                                    <small class="form-text text-muted d-block mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        سيتم تعيين كلمة مرور جديدة وإرسالها عبر SMS إلى رقم الجوال: <strong>{{ $user->phone }}</strong>
                                    </small>
                                </div>
                                <hr class="my-4">
                            @endif

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span class="btn-text">
                                        <i class="bi bi-check-lg me-1"></i> حفظ التغييرات
                                    </span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        جاري الحفظ...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('roleSelect');
            const operatorField = document.getElementById('operatorField');
            const editUserForm = document.getElementById('editUserForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnSpinner = submitBtn.querySelector('.btn-spinner');
            const cancelBtn = document.querySelector('a[href="{{ route('admin.users.index') }}"]');

            // التحقق من الصلاحيات: Energy Authority يمكنه تعديل المشغلين فقط
            @if($authUser->isEnergyAuthority() && !$user->isCompanyOwner())
                // تعطيل النموذج إذا كان المستخدم ليس مشغل
                if (editUserForm) {
                    editUserForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        alert('لا يمكنك تعديل هذا المستخدم. يمكنك تعديل المشغلين فقط.');
                        return false;
                    });
                    
                    // تعطيل جميع الحقول
                    const formInputs = editUserForm.querySelectorAll('input, select, textarea, button[type="submit"]');
                    formInputs.forEach(input => {
                        input.disabled = true;
                    });
                }
            @endif

            // Toggle operator field based on role
            if (roleSelect && operatorField) {
                function toggleOperator() {
                    const val = roleSelect.value;
                    const needOp = (val === '{{ \App\Role::Employee->value }}' || val === '{{ \App\Role::Technician->value }}');

                    operatorField.style.display = needOp ? '' : 'none';
                    const star = document.getElementById('opReqStar');
                    if (star) star.style.display = needOp ? '' : 'none';
                    
                    // Clear operator selection if not needed
                    if (!needOp) {
                        const opSelect = document.getElementById('operatorSelect');
                        if (opSelect) opSelect.value = '';
                    }
                }

                roleSelect.addEventListener('change', toggleOperator);
                toggleOperator();
            }

            {{-- username يتم توليده تلقائياً في الـ backend - لا حاجة لـ JavaScript --}}

            // زر إعادة تعيين كلمة المرور
            const resetPasswordBtn = document.getElementById('resetPasswordBtn');
            if (resetPasswordBtn) {
                resetPasswordBtn.addEventListener('click', function() {
                    if (!confirm('هل أنت متأكد من إعادة تعيين كلمة المرور؟ سيتم إرسال كلمة المرور الجديدة عبر SMS.')) {
                        return;
                    }
                    
                    resetPasswordBtn.disabled = true;
                    resetPasswordBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري المعالجة...';
                    
                    fetch('{{ route('admin.users.reset-password', $user) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.ok) {
                            alert('تم إعادة تعيين كلمة المرور بنجاح. تم إرسال كلمة المرور الجديدة عبر SMS.');
                        } else {
                            alert('حدث خطأ: ' + (data.message || 'فشل إعادة تعيين كلمة المرور'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ أثناء إعادة تعيين كلمة المرور');
                    })
                    .finally(() => {
                        resetPasswordBtn.disabled = false;
                        resetPasswordBtn.innerHTML = '<i class="bi bi-key me-2"></i>إعادة تعيين كلمة المرور';
                    });
                });
            }

            // Disable submit button on form submission
            if (editUserForm && submitBtn) {
                let isSubmitting = false;
                
                editUserForm.addEventListener('submit', function(e) {
                    @if($authUser->isEnergyAuthority() && !$user->isCompanyOwner())
                        e.preventDefault();
                        return false;
                    @endif
                    
                    // منع الإرسال المتكرر
                    if (isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    
                    isSubmitting = true;
                    
                    // تعطيل الزر فوراً وإظهار spinner
                    submitBtn.disabled = true;
                    submitBtn.style.pointerEvents = 'none';
                    submitBtn.style.opacity = '0.6';
                    if (cancelBtn) {
                        cancelBtn.style.pointerEvents = 'none';
                        cancelBtn.style.opacity = '0.6';
                    }
                    if (btnText) btnText.classList.add('d-none');
                    if (btnSpinner) btnSpinner.classList.remove('d-none');

                    // إعادة تفعيل الزر بعد 30 ثانية كحد أقصى (في حالة أخطاء الشبكة)
                    setTimeout(() => {
                        isSubmitting = false;
                        submitBtn.disabled = false;
                        submitBtn.style.pointerEvents = 'auto';
                        submitBtn.style.opacity = '1';
                        if (cancelBtn) {
                            cancelBtn.style.pointerEvents = 'auto';
                            cancelBtn.style.opacity = '1';
                        }
                        if (btnText) btnText.classList.remove('d-none');
                        if (btnSpinner) btnSpinner.classList.add('d-none');
                    }, 30000);
                });
            }
        });
    </script>
@endpush
