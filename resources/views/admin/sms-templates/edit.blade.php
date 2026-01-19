@extends('layouts.admin')

@section('title', 'تعديل قالب SMS')

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-pencil me-2"></i>
                            تعديل قالب SMS
                        </h5>
                        <div class="general-subtitle">
                            {{ $smsTemplate->name }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.sms-templates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>

                <div class="card-body pb-4">
                    <form action="{{ route('admin.sms-templates.update', $smsTemplate) }}" method="POST" id="smsTemplateForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $smsTemplate->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">القالب <span class="text-danger">*</span></label>
                                <textarea name="template" class="form-control @error('template') is-invalid @enderror" 
                                          rows="10" required id="templateInput">{{ old('template', $smsTemplate->template) }}</textarea>
                                @error('template')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    المتغيرات المتاحة: <code>{name}</code>, <code>{username}</code>, <code>{password}</code>, <code>{role}</code>, <code>{login_url}</code>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        عدد الأحرف: <span id="charCount">0</span> / <span id="maxLength">{{ $smsTemplate->max_length }}</span>
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الحد الأقصى لطول الرسالة <span class="text-danger">*</span></label>
                                <input type="number" name="max_length" class="form-control @error('max_length') is-invalid @enderror" 
                                       value="{{ old('max_length', $smsTemplate->max_length) }}" min="100" max="160" required>
                                @error('max_length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">الحد الأقصى المسموح به لطول رسالة SMS (160 حرف لرسائل SMS القصيرة)</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الحالة</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                           id="isActive" {{ old('is_active', $smsTemplate->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">
                                        نشط
                                    </label>
                                </div>
                                <div class="form-text">إذا كان غير نشط، لن يتم استخدام هذا القالب</div>
                            </div>

                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">معاينة الرسالة</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="template-preview" id="templatePreview">
                                            {{ $smsTemplate->template }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.sms-templates.index') }}" class="btn btn-outline-secondary">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateInput = document.getElementById('templateInput');
    const charCount = document.getElementById('charCount');
    const maxLength = document.getElementById('maxLength');
    const templatePreview = document.getElementById('templatePreview');
    const form = document.getElementById('smsTemplateForm');

    function updateCharCount() {
        const length = templateInput.value.length;
        charCount.textContent = length;
        
        if (length > parseInt(maxLength.textContent)) {
            charCount.classList.add('text-danger');
            charCount.classList.remove('text-success');
        } else {
            charCount.classList.remove('text-danger');
            charCount.classList.add('text-success');
        }
    }

    function updatePreview() {
        // استبدال المتغيرات بمثال
        let preview = templateInput.value
            .replace(/{name}/g, 'أحمد محمد')
            .replace(/{username}/g, 'ahmad_mohammed')
            .replace(/{password}/g, '********')
            .replace(/{role}/g, 'مشغل')
            .replace(/{login_url}/g, 'https://rased.ps/login');
        
        templatePreview.textContent = preview;
    }

    templateInput.addEventListener('input', function() {
        updateCharCount();
        updatePreview();
    });

    form.addEventListener('submit', function(e) {
        const length = templateInput.value.length;
        const max = parseInt(maxLength.value);
        
        if (length > max) {
            e.preventDefault();
            alert('القالب يتجاوز الحد الأقصى المسموح به (' + max + ' حرف)');
            return false;
        }
    });

    // تحديث عند التحميل
    updateCharCount();
    updatePreview();
});
</script>
@endpush

<style>
.template-preview {
    background: var(--tblr-bg-surface-secondary);
    border: 1px solid var(--tblr-border-color);
    border-radius: 0.5rem;
    padding: 1rem;
    font-family: monospace;
    font-size: 0.875rem;
    white-space: pre-wrap;
    min-height: 100px;
    max-height: 300px;
    overflow-y: auto;
}
</style>
@endsection

