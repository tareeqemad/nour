@php
    /** @var \App\Models\Operator $operator */
    $isEdit = isset($mode) && $mode === 'edit';
@endphp

<div data-modal-title="{{ $isEdit ? 'تعديل مشغل' : 'إضافة مشغل جديد' }}"></div>

<form
    data-ajax-form="operator"
    action="{{ $isEdit ? route('admin.operators.update', $operator) : route('admin.operators.store') }}"
    method="POST"
>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    @if(!$isEdit)
        {{-- CREATE --}}
        <div class="row g-3">
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    سيتم إنشاء حساب مشغل، وبعدها المشغل يكمل بياناته من صفحة "ملف المشغل".
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">اسم المستخدم <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" placeholder="مثال: op_ahmed">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">كلمة المرور <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password" id="op_password" class="form-control" minlength="8">
                    <button class="btn btn-outline-secondary" type="button" data-toggle-pass>
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="form-text">8 أحرف على الأقل.</div>
            </div>

            <div class="col-md-8">
                <label class="form-label fw-semibold">البريد الإلكتروني (اختياري)</label>
                <input type="email" name="email" id="op_email" class="form-control" placeholder="name@domain.com">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">إرسال الإيميل</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="send_email" id="op_send_email" value="1">
                    <label class="form-check-label" for="op_send_email">إرسال بيانات الدخول</label>
                </div>
                <div class="form-text">يتفعل فقط إذا أدخلت إيميل.</div>
            </div>
        </div>
    @else
        {{-- EDIT --}}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">اسم المشغل <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $operator->name }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">البريد</label>
                <input type="email" name="email" class="form-control" value="{{ $operator->email }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">الهاتف</label>
                <input type="text" name="phone" class="form-control" value="{{ $operator->phone }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">العنوان</label>
                <input type="text" name="address" class="form-control" value="{{ $operator->address }}">
            </div>

            @if($operator->owner)
                <div class="col-12">
                    <hr class="my-2">
                    <div class="alert alert-warning mb-0">
                        تعديل بيانات الدخول (اختياري). اترك كلمة المرور فارغة إذا ما بدك تغيرها.
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">اسم المستخدم</label>
                    @if(auth()->user()->isSuperAdmin())
                        <input type="text" name="username" class="form-control" value="{{ $operator->owner->username }}">
                    @else
                        <input type="text" class="form-control" value="{{ $operator->owner->username }}" disabled readonly>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">بريد المستخدم</label>
                    <input type="email" name="user_email" class="form-control" value="{{ $operator->owner->email }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" minlength="8">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="8">
                </div>
            @endif
        </div>
    @endif

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary" data-submit-btn>
            <i class="bi bi-check-lg me-1"></i>
            حفظ
        </button>
    </div>
</form>
