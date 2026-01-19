# Data Table Loading Overlay - دليل الاستخدام

## الوصف
مكتبة عامة لإضافة loading overlay على جداول البيانات في صفحات الإدارة. يمكن استخدامها في أي صفحة تحتوي على جداول وبحث.

## الملفات المطلوبة

### CSS
```html
<link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
```

### JavaScript
```html
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
```

## طريقة الاستخدام

### 1. إضافة HTML الأساسي

أضف الكلاس `data-table-container` للـ container الرئيسي:

```html
<div class="card-body position-relative data-table-container">
    <!-- Loading Overlay -->
    <div class="data-table-loading-overlay">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <div class="mt-3 text-muted fw-semibold">جاري التحميل...</div>
        </div>
    </div>

    <!-- محتوى الجدول -->
    <div id="yourContentContainer">
        <!-- جدولك هنا -->
    </div>
</div>
```

### 2. استخدام JavaScript

```javascript
// الحصول على الـ container
const $container = $('.data-table-container');

// إظهار loading
window.DataTableLoading.show($container);

// إخفاء loading
window.DataTableLoading.hide($container);

// التحقق من حالة loading
const isVisible = window.DataTableLoading.isVisible($container);
```

### 3. مثال كامل مع AJAX

```javascript
function loadData() {
    const $container = $('.data-table-container');
    
    // إظهار loading
    window.DataTableLoading.show($container);
    
    $.ajax({
        url: '/your-route',
        method: 'GET',
        data: { /* your params */ }
    })
    .done(function(response) {
        // تحديث المحتوى
        $('#yourContentContainer').html(response.html);
    })
    .fail(function() {
        // معالجة الأخطاء
    })
    .always(function() {
        // إخفاء loading دائماً
        window.DataTableLoading.hide($container);
    });
}
```

## أمثلة من المشروع

### صفحة سجلات التشغيل (operation-logs)
انظر: `resources/views/admin/operation-logs/index.blade.php`

### للصفحات الأخرى
يمكن تطبيق نفس الطريقة على:
- سجلات الصيانة (maintenance-records)
- كفاءة الوقود (fuel-efficiencies)
- الامتثال والسلامة (compliance-safeties)
- أي صفحة أخرى تحتوي على جداول وبحث

## ملاحظات

- الـ overlay يتم إنشاؤه تلقائياً إذا لم يكن موجوداً في الـ HTML
- الـ container يجب أن يكون `position: relative`
- الـ z-index للـ overlay هو 100
- التأثيرات والحركات تتم بشكل سلس باستخدام jQuery animate


