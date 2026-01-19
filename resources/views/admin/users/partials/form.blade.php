@php
    $authUser = auth()->user();
    $isCreate = ($mode ?? 'create') === 'create';

    $roleMeta = [
        \App\Role::SuperAdmin->value   => ['label' => 'مدير النظام',      'badge' => 'danger', 'icon' => 'bi-shield-lock'],
        \App\Role::Admin->value        => ['label' => 'سلطة الطاقة',       'badge' => 'dark',   'icon' => 'bi-lightning-charge'],
        \App\Role::CompanyOwner->value => ['label' => 'مشغل',             'badge' => 'primary','icon' => 'bi-building'],
        \App\Role::Employee->value     => ['label' => 'موظف',             'badge' => 'success','icon' => 'bi-person-badge'],
        \App\Role::Technician->value   => ['label' => 'فني',              'badge' => 'warning','icon' => 'bi-tools'],
    ];

    $selectedRole = old('role');
    if ($selectedRole === null) {
        if (!$isCreate && isset($user)) {
            $selectedRole = $user->role?->value;
        } elseif (!empty($defaultRole)) {
            $selectedRole = $defaultRole;
        }
    }

    $isEmpOrTech = in_array($selectedRole, [\App\Role::Employee->value, \App\Role::Technician->value], true);
@endphp

<div class="mb-4">
    <h6 class="fw-bold mb-3">
        <i class="bi bi-person-vcard text-primary me-2"></i>
        بيانات المستخدم
    </h6>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">الاسم بالعربي <span class="text-danger">*</span></label>
            <input type="text" name="name" id="nameInput"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name ?? '') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">الاسم بالإنجليزي <span class="text-danger">*</span></label>
            <input type="text" name="name_en" id="nameEnInput"
                   class="form-control @error('name_en') is-invalid @enderror"
                   value="{{ old('name_en', $user->name_en ?? '') }}">
            <small class="form-text text-muted">
                <i class="bi bi-info-circle me-1"></i>
                سيتم توليد اسم المستخدم تلقائياً من الاسم الإنجليزي
            </small>
        </div>

        {{-- حقل username مخفي - يتم توليده تلقائياً في الـ backend --}}
        <input type="hidden" name="username" id="usernameInput" value="{{ old('username', $user->username ?? '') }}">

        <div class="col-md-6">
            <label class="form-label fw-semibold">رقم الجوال <span class="text-danger">*</span></label>
            <input type="text" name="phone" id="phoneInput"
                   class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $user->phone ?? '') }}"
                   placeholder="059xxxxxxx أو 056xxxxxxx"
                   maxlength="10">
            <small class="form-text text-muted">
                <i class="bi bi-info-circle me-1"></i>
                سيتم إرسال بيانات الدخول (اسم المستخدم وكلمة المرور) إلى هذا الرقم
            </small>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email ?? '') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">الدور <span class="text-danger">*</span></label>
            @php
                // Energy Authority: لا يمكنه تغيير دور المشغل
                $roleDisabled = false;
                $roleHelpText = 'المشغل يقدر يعمل موظف/فني فقط. السوبر أدمن يقدر يعمل مشغل/سلطة الطاقة/موظف/فني.';
                
                if (!$isCreate && isset($user) && $authUser->isEnergyAuthority() && $user->isCompanyOwner()) {
                    $roleDisabled = true;
                    $roleHelpText = 'لا يمكنك تغيير دور المشغل. يجب أن يبقى مشغل.';
                }
            @endphp
            <select name="role" id="roleSelect"
                    class="form-select @error('role') is-invalid @enderror"
                    @if($roleDisabled) disabled @endif>
                <option value="">اختر الدور</option>
                @foreach($roles as $r)
                    @php
                        $val = $r->value;
                        $meta = $roleMeta[$val] ?? ['label' => $val, 'badge' => 'secondary', 'icon' => 'bi-person'];
                    @endphp
                    <option value="{{ $val }}" {{ (string)old('role', $selectedRole) === (string)$val ? 'selected' : '' }}>
                        {{ $meta['label'] }}
                    </option>
                @endforeach
            </select>
            @if($roleDisabled)
                <input type="hidden" name="role" value="{{ \App\Role::CompanyOwner->value }}">
            @endif
            <small class="form-text text-muted">{{ $roleHelpText }}</small>
        </div>

        {{-- Operator binding --}}
        @if($authUser->isCompanyOwner())
            <div class="col-md-6">
                <label class="form-label fw-semibold">المشغل</label>
                <input type="text" class="form-control" value="{{ $operatorLocked?->name ?? 'غير مرتبط' }}" disabled>
                <small class="form-text text-muted">سيتم ربط المستخدم تلقائيًا بمشغلك.</small>
            </div>
        @else
            <div class="col-md-6" id="operatorField" style="{{ $isEmpOrTech ? '' : 'display:none;' }}">
                <label class="form-label fw-semibold">المشغل <span class="text-danger" id="opReqStar" style="{{ $isEmpOrTech ? '' : 'display:none;' }}">*</span></label>

                {{-- create: operator_id (scalar) / edit: operator_id[] (array) --}}
                @php
                    $opName = $operatorFieldName ?? ($isCreate ? 'operator_id' : 'operator_id[]');
                    $selectedOpId = old('operator_id');
                    
                    // Handle array case (when operator_id is sent as array)
                    if (is_array($selectedOpId)) {
                        $selectedOpId = !empty($selectedOpId) ? $selectedOpId[0] : null;
                    }
                    
                    // If no old value and editing, use user's operators
                    if (!$isCreate && isset($userOperators) && !empty($userOperators) && !$selectedOpId) {
                        $selectedOpId = $userOperators[0];
                    }
                    
                    // Ensure selectedOpId is a string or null for comparison
                    $selectedOpId = $selectedOpId !== null ? (string)$selectedOpId : null;
                @endphp

                <select name="{{ $opName }}" id="operatorSelect"
                        class="form-select @error('operator_id') is-invalid @enderror">
                    <option value="">اختر المشغل</option>
                    @foreach($operators as $op)
                        <option value="{{ $op->id }}" {{ $selectedOpId !== null && (string)$selectedOpId === (string)$op->id ? 'selected' : '' }}>
                            {{ $op->name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">للموظف/الفني: لازم يكون تابع لمشغل واحد فقط.</small>
            </div>
        @endif
    </div>
</div>

<hr class="my-4">

<div class="mb-4">
    <h6 class="fw-bold mb-3">
        <i class="bi bi-key text-primary me-2"></i>
        الأمان
    </h6>

    <div class="row g-3">
        @if($isCreate)
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    كلمة المرور <span class="text-danger">*</span>
                </label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       minlength="8">
                <small class="form-text text-muted">يجب أن تكون 8 أحرف على الأقل.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    تأكيد كلمة المرور <span class="text-danger">*</span>
                </label>
                <input type="password" name="password_confirmation"
                       class="form-control"
                       minlength="8">
            </div>
        @else
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>ملاحظة:</strong> لتغيير كلمة المرور، استخدم زر "إعادة تعيين كلمة المرور" أدناه.
                </div>
            </div>
        @endif
    </div>
</div>
