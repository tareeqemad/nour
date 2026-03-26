{{-- سلطة الطاقة --}}
<div class="d-accordion-item">
    <button class="d-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEnergyAuth" aria-expanded="true">
        <i class="bi bi-building icon"></i>
        سلطة الطاقة
        <span class="d-accordion-badge badge-energy">سلطة الطاقة</span>
        <i class="bi bi-chevron-down chevron"></i>
    </button>
    <div class="collapse show" id="collapseEnergyAuth">
        <div class="d-accordion-body">

            <div class="d-callout d-callout-info mb-3">
                <p>سلطة الطاقة هي الجهة الرسمية المسؤولة عن إدارة ومراقبة قطاع الطاقة. لديك صلاحيات واسعة لإدارة المشغلين والمستخدمين ومراقبة العمليات، لكن لا يمكنك تعديل إعدادات النظام الأساسية.</p>
            </div>

            <div class="d-workflow-title"><i class="bi bi-key"></i> الصلاحيات المتاحة</div>
            <div class="d-perms-grid">
                <div class="d-perms-group">
                    <h6>إدارة المستخدمين</h6>
                    <ul>
                        <li>إنشاء مستخدمين (Admin, سلطة الطاقة, مشغل, موظف, فني)</li>
                        <li>تعديل أو حذف المستخدمين الذين أنشأتهم</li>
                        <li>إعادة تعيين كلمات المرور</li>
                        <li>تفعيل أو تعطيل الحسابات</li>
                    </ul>
                </div>
                <div class="d-perms-group">
                    <h6>إدارة المشغلين</h6>
                    <ul>
                        <li>إنشاء مشغلين جدد</li>
                        <li>اعتماد أو رفض طلبات الانضمام</li>
                        <li>تفعيل أو تعطيل الحسابات</li>
                        <li>إدارة وحدات التوليد والمولدات</li>
                        <li>نقل المولدات بين المشغلين</li>
                    </ul>
                </div>
                <div class="d-perms-group">
                    <h6>إدارة الصلاحيات</h6>
                    <ul>
                        <li>إدارة شجرة الصلاحيات</li>
                        <li>تعيين الصلاحيات للمستخدمين والأدوار</li>
                        <li>عرض سجل تغييرات الصلاحيات</li>
                    </ul>
                </div>
                <div class="d-perms-group">
                    <h6>المراقبة والتواصل</h6>
                    <ul>
                        <li>عرض سجل الأخطاء والأنشطة</li>
                        <li>إرسال رسائل للمستخدمين والمشغلين</li>
                        <li>إدارة الأرقام المصرح بها</li>
                    </ul>
                </div>
            </div>

            <div class="d-restrictions">
                <h6>القيود:</h6>
                <ul>
                    <li>&#10060; لا يمكنك إنشاء مستخدمين من أدوار إدارية عليا</li>
                    <li>&#10060; لا يمكنك إدارة إعدادات الموقع</li>
                    <li>&#10060; لا يمكنك حذف أو تعديل مستخدمين من أدوار إدارية عليا</li>
                </ul>
            </div>

            <div class="d-workflow-title"><i class="bi bi-gear"></i> آلية العمل التفصيلية</div>
            <ol>
                <li><strong>اعتماد المشغلين الجدد:</strong> المشغلون &rarr; في انتظار الموافقة &rarr; راجع البيانات &rarr; اعتماد أو رفض</li>
                <li><strong>إنشاء مستخدمين:</strong> المستخدمون &rarr; إضافة مستخدم جديد &rarr; أدخل البيانات &rarr; يتم توليد username + password + SMS تلقائياً</li>
                <li><strong>الأرقام المصرح بها:</strong> أضف أرقام جوال للمشغلين المسموح لهم بالانضمام (يمكنك الاستيراد من Excel)</li>
                <li><strong>مراقبة العمليات:</strong> سجل الأخطاء + سجل الأنشطة + المستخدمون النشطون</li>
            </ol>

        </div>
    </div>
</div>
