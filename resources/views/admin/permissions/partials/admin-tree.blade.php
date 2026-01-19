{{-- Partial للأدمن --}}
{{-- يعرض الأدوار النظامية ما عدا السوبر أدمن --}}
{{-- لا يعطي صلاحية لموظف تابع لمشغل --}}

<div class="mb-3">
    <label class="form-label fw-semibold">
        <i class="bi bi-shield-check me-1"></i>
        الدور
    </label>
    <select id="roleSelect" class="form-select">
        <option value="">اختر الدور...</option>
        @foreach($systemRoles as $role)
            @if($role->name !== 'super_admin')
                <option value="{{ $role->name }}">{{ $role->label }}</option>
            @endif
        @endforeach
    </select>
    <div class="form-text">اختر الدور أولاً.</div>
</div>

<div class="mb-3" id="userSelectWrapper" style="display:none;">
    <label class="form-label fw-semibold">
        <i class="bi bi-person-badge me-1"></i>
        المستخدم
    </label>
    <select id="userSelect" class="form-select" disabled></select>
    <div class="form-text">ابحث عن المستخدمين في النظام حسب الدور المحدد.</div>
</div>
