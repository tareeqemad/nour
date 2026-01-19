{{-- Partial لسلطة الطاقة --}}
{{-- يعرض: سلطة الطاقة، مشغل، وباقي الأدوار --}}
{{-- إذا اختار مشغل - يظهر المشغل والمستخدمين تحت المشغل --}}

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

<div class="mb-3" id="operatorSelectWrapper" style="display:none;">
    <label class="form-label fw-semibold">
        <i class="bi bi-building me-1"></i>
        المشغل
    </label>
    <select id="operatorSelect" class="form-select"></select>
    <div class="form-text">اختر المشغل لعرض المستخدمين التابعين له.</div>
</div>

<div class="mb-3" id="userSelectWrapper" style="display:none;">
    <label class="form-label fw-semibold">
        <i class="bi bi-person-badge me-1"></i>
        المستخدم
    </label>
    <select id="userSelect" class="form-select" disabled></select>
    <div class="form-text">ابحث عن المستخدمين في النظام حسب الدور والمشغل المحدد.</div>
</div>
