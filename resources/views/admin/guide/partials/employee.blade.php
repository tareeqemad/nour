{{-- الموظف --}}
<div class="d-accordion-item">
    <button class="d-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmployee" aria-expanded="true">
        <i class="bi bi-person icon"></i>
        الموظف (Employee)
        <span class="d-accordion-badge badge-employee">موظف</span>
        <i class="bi bi-chevron-down chevron"></i>
    </button>
    <div class="collapse show" id="collapseEmployee">
        <div class="d-accordion-body">

            <div class="d-callout d-callout-success mb-3">
                <p>الموظف يعمل تحت إشراف المشغل ويمكنه تسجيل السجلات اليومية حسب الصلاحيات الممنوحة له. يمكن للمشغل تخصيص الصلاحيات لكل موظف.</p>
            </div>

            <div class="d-workflow-title"><i class="bi bi-key"></i> الصلاحيات الأساسية</div>
            <div class="d-perms-grid">
                <div class="d-perms-group">
                    <h6>عرض البيانات</h6>
                    <ul>
                        <li>عرض بيانات المشغل التابع له</li>
                        <li>عرض وحدات التوليد والمولدات</li>
                        <li>عرض لوحة التحكم والإحصائيات</li>
                    </ul>
                </div>
                <div class="d-perms-group">
                    <h6>تسجيل السجلات (حسب الصلاحيات)</h6>
                    <ul>
                        <li>سجلات التشغيل اليومية</li>
                        <li>سجلات الصيانة</li>
                        <li>كفاءة الوقود والامتثال</li>
                        <li>تعديل/حذف السجلات التي أنشأها</li>
                    </ul>
                </div>
            </div>

            <div class="d-restrictions">
                <h6>القيود:</h6>
                <ul>
                    <li>&#10060; لا يمكنه إدارة المستخدمين أو فريق العمل أو إعدادات المشغل</li>
                    <li>&#10060; لا يمكنه إضافة/تعديل وحدات التوليد أو المولدات أو التعرفة</li>
                </ul>
            </div>

            <div class="d-workflow-title"><i class="bi bi-gear"></i> كيفية العمل</div>
            <ol>
                <li><strong>سجلات التشغيل:</strong> سجلات التشغيل &rarr; إضافة سجل جديد &rarr; اختر الوحدة والمولد &rarr; أدخل البيانات &rarr; حفظ</li>
                <li><strong>سجلات الصيانة:</strong> سجلات الصيانة &rarr; إضافة سجل جديد &rarr; اختر النوع &rarr; أدخل التفاصيل &rarr; حفظ</li>
                <li><strong>المراقبة:</strong> استخدم لوحة التحكم لعرض الإحصائيات ومراقبة كفاءة الوقود</li>
            </ol>

            <div class="d-callout d-callout-warning mt-2">
                <div class="d-callout-title"><i class="bi bi-info-circle"></i> ملاحظة</div>
                <p>صلاحياتك تختلف حسب ما يحدده المشغل. إذا لم تتمكن من الوصول لصفحة معينة، تواصل مع المشغل لإضافة الصلاحية المطلوبة.</p>
            </div>

        </div>
    </div>
</div>
