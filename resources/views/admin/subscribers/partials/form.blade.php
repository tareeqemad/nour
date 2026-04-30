@php
    $isEdit = isset($subscriber);
    $subscriptionCategories = \App\Helpers\ConstantsHelper::get(23);
    $phaseTypes             = \App\Helpers\ConstantsHelper::get(24);
    $subscriptionStatuses   = \App\Helpers\ConstantsHelper::get(25);
    $serviceTypes           = \App\Helpers\ConstantsHelper::get(26);
    $ampereOptions          = \App\Helpers\ConstantsHelper::get(31);
    $selectedUnitId         = old('generation_unit_id', $isEdit ? $subscriber->generationUnits->first()?->id : null);
@endphp

<div class="row g-3">

    {{-- ===== البيانات الأساسية ===== --}}
    <div class="col-12">
        <h6 class="fw-semibold mb-3" style="padding-bottom:0.5rem;border-bottom:2px solid var(--color-border,#E5E7EB);">
                <i class="bi bi-person me-2"></i>البيانات الأساسية
        </h6>
    </div>

    @if($isEdit)
        {{-- حقول للقراءة فقط في وضع التعديل --}}
        <div class="col-md-6">
            <label class="form-label fw-semibold">رقم الاشتراك</label>
            <input type="text" class="form-control" value="{{ $subscriber->subscription_number }}" readonly style="background-color:#f8f9fa;cursor:not-allowed;">
            <input type="hidden" name="subscription_number" value="{{ $subscriber->subscription_number }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">رقم هوية المشترك</label>
            <input type="text" class="form-control" value="{{ $subscriber->subscriber_id_number }}" readonly style="background-color:#f8f9fa;cursor:not-allowed;">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">اسم المشترك</label>
            <input type="text" class="form-control" value="{{ $subscriber->subscriber_name }}" readonly style="background-color:#f8f9fa;cursor:not-allowed;">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">تاريخ الاشتراك</label>
            <input type="text" class="form-control" value="{{ $subscriber->subscription_date?->format('Y-m-d') }}" readonly style="background-color:#f8f9fa;cursor:not-allowed;">
        </div>
    @else
        {{-- حقول قابلة للإدخال في وضع الإضافة --}}
        <div class="col-md-6">
            <label class="form-label fw-semibold">رقم هوية المشترك <span class="text-danger">*</span></label>
            <input type="text" name="subscriber_id_number" id="subscriber_id_number"
                   class="form-control @error('subscriber_id_number') is-invalid @enderror"
                   value="{{ old('subscriber_id_number') }}" maxlength="9" inputmode="numeric" required>
            @error('subscriber_id_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div id="idFeedback" class="form-text d-none"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">اسم المشترك <span class="text-danger">*</span></label>
            <input type="text" name="subscriber_name" class="form-control @error('subscriber_name') is-invalid @enderror"
                   value="{{ old('subscriber_name') }}" required>
            @error('subscriber_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif

    {{-- ===== بيانات الاتصال ===== --}}
    <div class="col-12 mt-4">
        <h6 class="fw-semibold mb-3" style="padding-bottom:0.5rem;border-bottom:2px solid var(--color-border,#E5E7EB);">
                <i class="bi bi-telephone me-2"></i>بيانات الاتصال والعنوان
        </h6>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">رقم الموبايل <span class="text-danger">*</span></label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $isEdit ? $subscriber->phone : '') }}" maxlength="10"
               placeholder="0591234567 أو 0561234567" required>
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">رقم جوال بديل</label>
        <input type="text" name="alt_phone" class="form-control @error('alt_phone') is-invalid @enderror"
               value="{{ old('alt_phone', $isEdit ? $subscriber->alt_phone : '') }}" maxlength="10"
               placeholder="0591234567 أو 0561234567">
        @error('alt_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">رقم الصندوق</label>
        <input type="text" name="box_number" class="form-control @error('box_number') is-invalid @enderror"
               value="{{ old('box_number', $isEdit ? $subscriber->box_number : '') }}" maxlength="4"
               placeholder="0000" inputmode="numeric">
        @error('box_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">عنوان المشترك <span class="text-danger">*</span></label>
        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address', $isEdit ? $subscriber->address : '') }}</textarea>
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ===== التصنيفات ===== --}}
    <div class="col-12 mt-4">
        <h6 class="fw-semibold mb-3" style="padding-bottom:0.5rem;border-bottom:2px solid var(--color-border,#E5E7EB);">
                <i class="bi bi-tags me-2"></i>التصنيفات
        </h6>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">تصنيف الاشتراك <span class="text-danger">*</span></label>
        <select name="subscription_category" class="form-select @error('subscription_category') is-invalid @enderror" required>
            <option value="">اختر التصنيف</option>
            @foreach($subscriptionCategories as $item)
                <option value="{{ $item->value }}" {{ old('subscription_category', $isEdit ? $subscriber->subscription_category : '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
            @endforeach
        </select>
        @error('subscription_category')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">نوع الفاز <span class="text-danger">*</span></label>
        <select name="phase_type" class="form-select @error('phase_type') is-invalid @enderror" required>
            <option value="">اختر النوع</option>
            @foreach($phaseTypes as $item)
                <option value="{{ $item->value }}" {{ old('phase_type', $isEdit ? $subscriber->phase_type : '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
            @endforeach
        </select>
        @error('phase_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">حالة الاشتراك <span class="text-danger">*</span></label>
        <select name="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror" required>
            <option value="">اختر الحالة</option>
            @foreach($subscriptionStatuses as $item)
                <option value="{{ $item->value }}" {{ old('subscription_status', $isEdit ? $subscriber->subscription_status : '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
            @endforeach
        </select>
        @error('subscription_status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">نوع الخدمة <span class="text-danger">*</span></label>
        <select name="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
            <option value="">اختر النوع</option>
            @foreach($serviceTypes as $item)
                <option value="{{ $item->value }}" {{ old('service_type', $isEdit ? $subscriber->service_type : '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
            @endforeach
        </select>
        @error('service_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ===== بيانات العداد ===== --}}
    <div class="col-12 mt-4">
        <h6 class="fw-semibold mb-3" style="padding-bottom:0.5rem;border-bottom:2px solid var(--color-border,#E5E7EB);">
                <i class="bi bi-speedometer2 me-2"></i>بيانات العداد والاشتراك
        </h6>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">رقم العداد</label>
        <input type="text" name="meter_number" class="form-control @error('meter_number') is-invalid @enderror"
               value="{{ old('meter_number', $isEdit ? $subscriber->meter_number : '') }}">
        @error('meter_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">قيمة أمبير الاشتراك</label>
        <select name="ampere" class="form-select @error('ampere') is-invalid @enderror">
            <option value="">-- اختر القيمة --</option>
            @foreach($ampereOptions as $item)
                <option value="{{ $item->value }}" {{ old('ampere', $isEdit ? $subscriber->ampere : '') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
            @endforeach
        </select>
        @error('ampere')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">قراءة العداد الافتتاحية</label>
        <input type="number" name="opening_reading" class="form-control @error('opening_reading') is-invalid @enderror"
               value="{{ old('opening_reading', $isEdit ? $subscriber->opening_reading : '') }}" min="0" step="0.01" placeholder="اختياري">
        @error('opening_reading')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check mt-3">
            <input type="hidden" name="is_employee_subscription" value="0">
            <input class="form-check-input" type="checkbox" name="is_employee_subscription" id="is_employee_subscription" value="1"
                   {{ old('is_employee_subscription', $isEdit ? $subscriber->is_employee_subscription : false) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="is_employee_subscription">
                <i class="bi bi-person-badge me-1"></i>اشتراك موظف الشركة
            </label>
            <div class="form-text text-muted">تحديد هذا الخيار يُطبّق نسبة الخصم المستحقة عند الفوترة</div>
        </div>
    </div>

    {{-- ===== التواريخ ===== --}}
    <div class="col-12 mt-4">
        <h6 class="fw-semibold mb-3" style="padding-bottom:0.5rem;border-bottom:2px solid var(--color-border,#E5E7EB);">
                <i class="bi bi-calendar3 me-2"></i>التواريخ
        </h6>
    </div>

    @if(!$isEdit)
        <div class="col-md-6">
            <label class="form-label fw-semibold">تاريخ الاشتراك</label>
            <input type="date" name="subscription_date" class="form-control @error('subscription_date') is-invalid @enderror"
                   value="{{ old('subscription_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
            @error('subscription_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div class="col-md-6">
        <label class="form-label fw-semibold">تاريخ الطلب</label>
        <input type="date" name="request_date" class="form-control @error('request_date') is-invalid @enderror"
               value="{{ old('request_date', $isEdit ? $subscriber->request_date?->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}">
        @error('request_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ===== ملاحظات ===== --}}
    <div class="col-12 mt-4">
        <h6 class="fw-semibold mb-3" style="padding-bottom:0.5rem;border-bottom:2px solid var(--color-border,#E5E7EB);">
                <i class="bi bi-chat-left-text me-2"></i>ملاحظات
        </h6>
    </div>

    <div class="col-12">
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                  placeholder="ملاحظات إضافية...">{{ old('notes', $isEdit ? $subscriber->notes : '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ===== المشغل ووحدة التوليد ===== --}}
    <div class="col-12 mt-4">
        <h6 class="fw-semibold mb-3" style="padding-bottom:0.5rem;border-bottom:2px solid var(--color-border,#E5E7EB);">
                <i class="bi bi-lightning-charge me-2"></i>المشغل ووحدة التوليد
        </h6>
    </div>

    <x-admin.operator-cascade
        :operators="$operators ?? collect()"
        :affiliatedOperator="$operator ?? null"
        :showGenerator="false"
        :showGenerationUnit="true"
        :operatorRequired="true"
        :generationUnitRequired="true"
        :selectedOperatorId="old('operator_id', $isEdit ? $subscriber->generationUnits->first()?->operator_id : null)"
        :selectedGenerationUnitId="old('generation_unit_id', $isEdit ? $subscriber->generationUnits->first()?->id : null)"
        :generationUnits="$generationUnits ?? collect()"
        colClass="col-md-6"
        :routes="[
            'generationUnits' => url('/admin/operators') . '/__OPERATOR__/generation-units-for-subscribers',
            'generators' => url('/admin/generation-units') . '/__UNIT__/generators-list',
        ]"
    />

    {{-- ===== أزرار الإجراء ===== --}}
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-end mt-3 pt-3" style="border-top:1px solid var(--color-border-soft,#EDF1F5);">
            <a href="{{ route('admin.subscribers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>إلغاء
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-lg me-1"></i>{{ $isEdit ? 'حفظ التعديلات' : 'حفظ' }}
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === التحقق من رقم الهوية الفلسطيني ===
    const idInput = document.getElementById('subscriber_id_number');
    const idFeedback = document.getElementById('idFeedback');

    if (idInput && idFeedback) {
        function validatePalID(id) {
            id = String(id).trim();
            if (id.length !== 9 || isNaN(id)) return false;
            let sum = 0;
            for (let i = 0; i < 9; i++) {
                let step = Number(id[i]) * ((i % 2) + 1);
                sum += (step > 9) ? (step - 9) : step;
            }
            return sum % 10 === 0;
        }

        function checkID() {
            const val = idInput.value.trim();

            if (val.length === 0) {
                idFeedback.classList.add('d-none');
                idInput.classList.remove('is-invalid', 'is-valid');
                return;
            }

            if (val.length === 9 && !isNaN(val)) {
                idFeedback.classList.remove('d-none');
                if (validatePalID(val)) {
                    idInput.classList.remove('is-invalid');
                    idInput.classList.add('is-valid');
                    idFeedback.className = 'form-text text-success';
                    idFeedback.innerHTML = '<i class="bi bi-check-circle me-1"></i>رقم هوية فلسطيني صحيح';
                } else {
                    idInput.classList.remove('is-valid');
                    idInput.classList.add('is-invalid');
                    idFeedback.className = 'form-text text-danger';
                    idFeedback.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>رقم الهوية مش فلسطيني';
                }
            } else if (val.length > 0 && val.length < 9) {
                idFeedback.classList.remove('d-none');
                idFeedback.className = 'form-text text-muted';
                idFeedback.innerHTML = '<i class="bi bi-info-circle me-1"></i>رقم الهوية يجب أن يكون 9 أرقام';
                idInput.classList.remove('is-valid', 'is-invalid');
            } else {
                idFeedback.classList.add('d-none');
                idInput.classList.remove('is-valid', 'is-invalid');
            }
        }

        idInput.addEventListener('input', checkID);
        idInput.addEventListener('blur', checkID);

        // تحقق عند تحميل الصفحة إذا كان هناك قيمة
        if (idInput.value.trim().length > 0) {
            checkID();
        }
    }
});
</script>
