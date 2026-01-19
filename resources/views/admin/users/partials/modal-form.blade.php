@php
    $authUser = auth()->user();
    $isCreate = ($mode ?? 'create') === 'create';

    $action = $isCreate
        ? route('admin.users.store')
        : route('admin.users.update', $user);

    $method = $isCreate ? 'POST' : 'PUT';

    $selectedRole = old('role', $isCreate ? ($defaultRole ?? '') : ($user->role?->value ?? ''));
@endphp

<form id="userAjaxForm" action="{{ $action }}" method="POST" data-method="{{ $method }}">
    @csrf
    @if(!$isCreate) @method('PUT') @endif

    <div class="row g-3">
        {{-- الدور - يجب أن يكون أولاً --}}
        <div class="col-12">
            <label class="form-label fw-semibold">
                <i class="bi bi-shield-check me-1"></i>
                الدور <span class="text-danger">*</span>
            </label>
            <select name="role" id="roleSelect" class="form-select" required>
                <option value="">اختر الدور</option>
                @foreach($roles as $r)
                    @php
                        // Handle both array format (from getRolesForCreate) and object format
                        $roleValue = is_array($r) ? ($r['value'] ?? '') : ($r->value ?? $r->name ?? '');
                        $roleLabel = is_array($r) ? ($r['label'] ?? $roleValue) : ($r->label ?? $r->label() ?? $roleValue);
                        $isCustom = is_array($r) ? ($r['is_custom'] ?? false) : false;
                    @endphp
                    <option value="{{ $roleValue }}" 
                            data-is-custom="{{ $isCustom ? '1' : '0' }}"
                            {{ (string)$selectedRole === (string)$roleValue ? 'selected' : '' }}>
                        {{ $roleLabel }}
                    </option>
                @endforeach
            </select>
            <div class="text-danger small mt-1 d-none" data-error-for="role"></div>
        </div>

        {{-- تحذير للمشغل إذا اختار CompanyOwner --}}
        <div class="col-12" id="companyOwnerWarning" style="display: none;">
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>تنبيه:</strong> لإضافة مشغل جديد، يجب إضافة رقم الجوال أولاً في 
                <a href="{{ route('admin.authorized-phones.index') }}" target="_blank" class="alert-link">صفحة الأرقام المصرح بها</a>
                ثم الرجوع إلى هذه الصفحة.
            </div>
        </div>

        {{-- الاسم بالعربي --}}
        <div class="col-md-6" id="nameField" style="display: none;">
            <label class="form-label fw-semibold">
                <i class="bi bi-person me-1"></i>
                الاسم بالعربي <span class="text-danger">*</span>
            </label>
            <input name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" placeholder="أدخل الاسم الكامل بالعربي" required>
            <div class="text-danger small mt-1 d-none" data-error-for="name"></div>
        </div>

        {{-- الاسم بالإنجليزي --}}
        <div class="col-md-6" id="nameEnField" style="display: none;">
            <label class="form-label fw-semibold">
                <i class="bi bi-person-badge me-1"></i>
                الاسم بالإنجليزي <span class="text-danger">*</span>
            </label>
            <input name="name_en" class="form-control" value="{{ old('name_en', $user->name_en ?? '') }}" placeholder="Enter full name in English" required>
            <div class="text-danger small mt-1 d-none" data-error-for="name_en"></div>
            <div class="help small text-muted mt-1">
                <i class="bi bi-info-circle me-1"></i>
                سيتم استخدامه لتوليد اسم المستخدم تلقائياً
            </div>
        </div>

        {{-- رقم الجوال --}}
        <div class="col-12" id="phoneField" style="display: none;">
            <label class="form-label fw-semibold">
                <i class="bi bi-phone me-1"></i>
                رقم الجوال <span class="text-danger">*</span>
            </label>
            <input name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}" placeholder="059xxxxxxx أو 056xxxxxxx" maxlength="10" required>
            <div class="text-danger small mt-1 d-none" data-error-for="phone"></div>
            <div class="help small text-muted mt-1">
                <i class="bi bi-info-circle me-1"></i>
                سيتم توليد اسم المستخدم وكلمة المرور تلقائياً وإرسالهما عبر SMS إلى هذا الرقم
            </div>
        </div>

        {{-- حقول مخفية: username, password, email --}}
        <input type="hidden" name="username" value="">
        <input type="hidden" name="password" value="">
        <input type="hidden" name="password_confirmation" value="">
        <input type="hidden" name="email" value="">

        {{-- المشغل (للسوبر أدمن فقط عند إضافة CompanyOwner) --}}
        @if(!$authUser->isCompanyOwner())
            <div class="col-12" id="operatorField" style="display: none;">
                <label class="form-label fw-semibold">
                    <i class="bi bi-building me-1"></i>
                    المشغل <span class="text-danger">*</span>
                </label>
                <select name="operator_id" id="operatorSelect" class="form-select js-operator-select">
                    <option value="">اختر المشغل</option>
                    @if(isset($selectedOperator) && $selectedOperator)
                        <option value="{{ $selectedOperator->id }}" selected>{{ $selectedOperator->name }}</option>
                    @endif
                </select>
                <div class="text-danger small mt-1 d-none" data-error-for="operator_id"></div>
                <div class="help small text-muted mt-1">
                    <i class="bi bi-info-circle me-1"></i>
                    يجب أن يكون رقم المشغل موجود في الأرقام المصرح بها
                </div>
            </div>
        @endif
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary" id="userModalSubmitBtn">
            <i class="bi bi-check-lg me-1"></i>
            حفظ
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const nameField = document.getElementById('nameField');
    const nameEnField = document.getElementById('nameEnField');
    const phoneField = document.getElementById('phoneField');
    const operatorField = document.getElementById('operatorField');
    const companyOwnerWarning = document.getElementById('companyOwnerWarning');
    const operatorSelect = document.getElementById('operatorSelect');
    
    const authUserIsSuperAdmin = {{ $authUser->isSuperAdmin() ? 'true' : 'false' }};
    const isCreate = {{ $isCreate ? 'true' : 'false' }};

    function updateFormFields() {
        const selectedRole = roleSelect ? roleSelect.value : '';
        const isCompanyOwnerRole = selectedRole === 'company_owner';
        
        if (!selectedRole) {
            // إذا لم يتم اختيار دور، إخفاء جميع الحقول
            if (nameField) nameField.style.display = 'none';
            if (nameEnField) nameEnField.style.display = 'none';
            if (phoneField) phoneField.style.display = 'none';
            if (operatorField) operatorField.style.display = 'none';
            if (companyOwnerWarning) companyOwnerWarning.style.display = 'none';
            return;
        }

        // إظهار الحقول المطلوبة
        if (nameField) nameField.style.display = '';
        if (nameEnField) nameEnField.style.display = '';
        if (phoneField) phoneField.style.display = '';

        // إظهار/إخفاء تحذير المشغل
        if (companyOwnerWarning) {
            companyOwnerWarning.style.display = isCompanyOwnerRole ? '' : 'none';
        }

        // إظهار/إخفاء حقل المشغل (للسوبر أدمن وسلطة الطاقة عند إضافة CompanyOwner)
        if (operatorField) {
            const shouldShowOperatorField = isCompanyOwnerRole && (authUserIsSuperAdmin || {{ $authUser->isEnergyAuthority() ? 'true' : 'false' }});
            operatorField.style.display = shouldShowOperatorField ? '' : 'none';
            if (operatorSelect) {
                operatorSelect.required = shouldShowOperatorField;
            }
        }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updateFormFields);
        // تحديث الحقول عند التحميل
        updateFormFields();
    }
});
</script>
