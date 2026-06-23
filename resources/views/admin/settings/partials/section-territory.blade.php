{{-- قسم النطاق الجغرافي ومنع التداخل --}}
<div class="settings-section">
    <h6 class="settings-section-title">
        <i class="bi bi-geo-alt"></i>
        النطاق الجغرافي
    </h6>

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label fw-semibold">
                <i class="bi bi-shield-exclamation me-1"></i>منع التداخل الجغرافي عند تسجيل البيانات
            </label>
            @php $overlapOn = \App\Models\OperatorTerritory::overlapEnforced(); @endphp
            <select name="settings[enforce_territory_overlap]" class="form-select">
                <option value="0" {{ ! $overlapOn ? 'selected' : '' }}>مُجمّد — السماح بالتداخل مؤقتاً (الوضع الحالي)</option>
                <option value="1" {{ $overlapOn ? 'selected' : '' }}>مفعّل — منع تسجيل وحدة ضمن نطاق مشغّل آخر</option>
            </select>
            <small class="text-muted">
                عند التفعيل يُمنع حفظ وحدة توليد متداخلة مع نطاق مشغّل آخر (أو قريبة جداً من وحدة أخرى).
                فعّله عند تطبيق آلية التلزيم ومناطق النفوذ. كشف التداخل وعرضه على الخريطة يبقى فعّالاً في كلتا الحالتين.
            </small>
        </div>
    </div>
</div>
