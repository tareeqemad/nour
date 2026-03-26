{{-- مودال إضافة إعداد جديد --}}
@if(isset($settings) && $settings->has('custom'))
<div class="modal fade" id="addSettingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-1"></i>
                        إضافة إعداد جديد
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">المفتاح (Key) <span class="text-danger">*</span></label>
                            <input type="text" name="key" class="form-control" placeholder="custom_setting_key" required>
                            <small class="form-text text-muted">أحرف إنجليزية وأرقام وشرطة سفلية</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">التسمية</label>
                            <input type="text" name="label" class="form-control" placeholder="اسم الإعداد بالعربية">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">النوع <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="text">نص</option>
                                <option value="textarea">نص طويل</option>
                                <option value="number">رقم</option>
                                <option value="email">بريد إلكتروني</option>
                                <option value="url">رابط</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">المجموعة <span class="text-danger">*</span></label>
                            <input type="text" name="group" class="form-control" value="custom" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">القيمة</label>
                            <input type="text" name="value" class="form-control" placeholder="القيمة الافتراضية">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">الوصف</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>إضافة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
