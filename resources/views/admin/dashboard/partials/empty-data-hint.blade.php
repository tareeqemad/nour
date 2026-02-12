{{-- توضيح لسلطة الطاقة: عندما توجد أصول (مشغلون/وحدات/مولدات) لكن لا توجد سجلات تشغيل أو صيانة أو شهادات بعد --}}
@if(auth()->user()->isEnergyAuthority() && !empty($showEmptyDataHint))
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info border-0 d-flex align-items-center mb-0" role="alert">
            <i class="bi bi-info-circle-fill fs-4 me-3 flex-shrink-0"></i>
            <div>
                <strong>توضيح:</strong>
                الأرقام أعلاه (الطاقة المنتجة، الوقود، الكفاءة، الصيانة، شهادات السلامة) تظهر من <strong>السجلات المسجلة في النظام</strong>.
                عند ظهورها كصفر فهذا يعني أنه لم يُدخل بعد سجلات تشغيل أو صيانة أو شهادات سلامة للمشغلين الحاليين — وستتحدث تلقائياً عند إدخال البيانات.
            </div>
        </div>
    </div>
</div>
@endif
