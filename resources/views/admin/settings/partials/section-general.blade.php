{{-- قسم المعلومات العامة --}}
<div class="settings-section">
    <h6 class="settings-section-title">
        <i class="bi bi-info-circle"></i>
        المعلومات العامة
    </h6>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">اسم الموقع</label>
            <input type="text" name="settings[site_name]" class="form-control"
                   value="{{ \App\Models\Setting::get('site_name', 'نور') }}"
                   placeholder="اسم الموقع">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">وصف الموقع</label>
            <input type="text" name="settings[site_description]" class="form-control"
                   value="{{ \App\Models\Setting::get('site_description', '') }}"
                   placeholder="وصف مختصر للموقع">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">البريد الإلكتروني</label>
            <input type="email" name="settings[site_email]" class="form-control"
                   value="{{ \App\Models\Setting::get('site_email', '') }}"
                   placeholder="info@example.com">
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">رقم الهاتف</label>
            <input type="text" name="settings[site_phone]" class="form-control"
                   value="{{ \App\Models\Setting::get('site_phone', '') }}"
                   placeholder="+970 59 123 4567">
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">العنوان</label>
            <textarea name="settings[site_address]" class="form-control" rows="2"
                      placeholder="عنوان الموقع الكامل">{{ \App\Models\Setting::get('site_address', '') }}</textarea>
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">
                <i class="bi bi-play-circle me-1"></i>رابط الفيديو التعريفي (دليل التسجيل)
            </label>
            <input type="text" name="settings[intro_video_url]" class="form-control"
                   value="{{ \App\Models\Setting::get('intro_video_url', '') }}"
                   placeholder="رابط YouTube مثل https://youtu.be/XXXX أو رابط ملف MP4">
            <small class="text-muted">
                يظهر هذا الفيديو في صفحة «دليل التسجيل والمصطلحات». اتركه فارغاً لإخفاء الفيديو.
            </small>
        </div>
    </div>
</div>
