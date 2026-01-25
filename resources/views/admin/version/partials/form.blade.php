{{-- 
    Partial للنموذج المشترك بين create و edit
    المتغيرات المطلوبة:
    - $version (اختياري): الإصدار للتعديل
    - $nextVersions (اختياري): أرقام الإصدارات التالية للإنشاء
    - $isEdit: هل هذا تعديل أم إنشاء
--}}

@php
    $isEdit = isset($version) && $version->exists;
    $changes = $isEdit ? $version->getCategorizedChanges() : ['features' => [], 'fixes' => [], 'improvements' => [], 'security' => []];
@endphp

{{-- معلومات الإصدار الأساسية --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label fw-semibold">نوع الإصدار <span class="text-danger">*</span></label>
        <select name="type" id="versionType" class="form-select @error('type') is-invalid @enderror">
            @if(!$isEdit && isset($nextVersions))
                <option value="patch" {{ old('type', $isEdit ? $version->type : '') === 'patch' ? 'selected' : '' }}>
                    تحديث (Patch) - {{ $nextVersions['patch'] }}
                </option>
                <option value="minor" {{ old('type', $isEdit ? $version->type : '') === 'minor' ? 'selected' : '' }}>
                    إصدار فرعي (Minor) - {{ $nextVersions['minor'] }}
                </option>
                <option value="major" {{ old('type', $isEdit ? $version->type : '') === 'major' ? 'selected' : '' }}>
                    إصدار رئيسي (Major) - {{ $nextVersions['major'] }}
                </option>
            @else
                <option value="patch" {{ old('type', $isEdit ? $version->type : '') === 'patch' ? 'selected' : '' }}>
                    تحديث (Patch)
                </option>
                <option value="minor" {{ old('type', $isEdit ? $version->type : '') === 'minor' ? 'selected' : '' }}>
                    إصدار فرعي (Minor)
                </option>
                <option value="major" {{ old('type', $isEdit ? $version->type : '') === 'major' ? 'selected' : '' }}>
                    إصدار رئيسي (Major)
                </option>
            @endif
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">
            <strong>Patch:</strong> إصلاحات وتحسينات صغيرة |
            <strong>Minor:</strong> ميزات جديدة |
            <strong>Major:</strong> تغييرات كبيرة
        </small>
    </div>
    
    <div class="col-md-4">
        <label class="form-label fw-semibold">رقم الإصدار <span class="text-danger">*</span></label>
        <input type="text" name="version" id="versionNumber"
               class="form-control @error('version') is-invalid @enderror"
               value="{{ old('version', $isEdit ? $version->version : ($nextVersions['patch'] ?? '1.0.0')) }}"
               placeholder="1.0.0">
        @error('version')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-4">
        <label class="form-label fw-semibold">تاريخ الإصدار <span class="text-danger">*</span></label>
        <input type="date" name="release_date" 
               class="form-control @error('release_date') is-invalid @enderror"
               value="{{ old('release_date', $isEdit ? $version->release_date->format('Y-m-d') : date('Y-m-d')) }}">
        @error('release_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <label class="form-label fw-semibold">عنوان الإصدار <span class="text-danger">*</span></label>
        <input type="text" name="title" 
               class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $isEdit ? $version->title : '') }}"
               placeholder="مثال: تحسينات الأداء وإصلاحات">
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-4">
        <label class="form-label fw-semibold">تعيين كإصدار حالي</label>
        <div class="form-check form-switch mt-2">
            <input type="checkbox" name="is_current" value="1" 
                   class="form-check-input" id="isCurrent"
                   {{ old('is_current', $isEdit ? $version->is_current : false) ? 'checked' : '' }}>
            <label class="form-check-label" for="isCurrent">
                نعم، اجعله الإصدار الحالي
            </label>
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-semibold">وصف الإصدار</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
              rows="2" placeholder="وصف مختصر لهذا الإصدار...">{{ old('description', $isEdit ? $version->description : '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<hr class="my-4">

{{-- سجل التغييرات --}}
<h6 class="fw-bold mb-3">
    <i class="bi bi-list-check text-primary me-2"></i>
    سجل التغييرات
</h6>

{{-- الميزات الجديدة --}}
@include('admin.version.partials.changes-field', [
    'fieldName' => 'features',
    'label' => 'ميزات جديدة',
    'icon' => 'bi-stars',
    'colorClass' => 'success',
    'placeholder' => 'أضف ميزة جديدة...',
    'items' => $changes['features']
])

{{-- الإصلاحات --}}
@include('admin.version.partials.changes-field', [
    'fieldName' => 'fixes',
    'label' => 'إصلاحات الأخطاء',
    'icon' => 'bi-bug',
    'colorClass' => 'danger',
    'placeholder' => 'أضف إصلاحاً...',
    'items' => $changes['fixes']
])

{{-- التحسينات --}}
@include('admin.version.partials.changes-field', [
    'fieldName' => 'improvements',
    'label' => 'تحسينات',
    'icon' => 'bi-arrow-up-circle',
    'colorClass' => 'info',
    'placeholder' => 'أضف تحسيناً...',
    'items' => $changes['improvements']
])

{{-- الأمان --}}
@include('admin.version.partials.changes-field', [
    'fieldName' => 'security',
    'label' => 'تحديثات أمنية',
    'icon' => 'bi-shield-check',
    'colorClass' => 'warning',
    'placeholder' => 'أضف تحديثاً أمنياً...',
    'items' => $changes['security']
])

<hr class="my-4">

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.versions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i>
        إلغاء
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i>
        {{ $isEdit ? 'حفظ التعديلات' : 'حفظ الإصدار' }}
    </button>
</div>
