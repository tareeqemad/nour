{{-- السوبر أدمن --}}
<div class="role-section">
    <div class="role-header">
        <div class="role-title">
            <i class="bi bi-shield-check"></i>
            <span>السوبر أدمن (SuperAdmin)</span>
            <span class="role-badge bg-danger ms-2">مدير النظام</span>
        </div>
    </div>
    
    <div class="alert alert-danger mb-4">
        <h6 class="alert-heading">
            <i class="bi bi-exclamation-triangle"></i>
            أعلى مستوى من الصلاحيات
        </h6>
        <p class="mb-0">
            السوبر أدمن لديه صلاحيات كاملة على جميع جوانب النظام. يمكنه إدارة جميع المستخدمين، 
            المشغلين، الإعدادات، والأدوار. هذا الدور محجوز للمسؤولين الرئيسيين عن النظام.
        </p>
    </div>

    <div class="info-box">
        <h4><i class="bi bi-key"></i>الصلاحيات الكاملة:</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">إدارة المستخدمين والأدوار:</h6>
                <ul>
                    <li>✅ إنشاء مستخدمين من <strong>جميع الأدوار</strong> (SuperAdmin, Admin, EnergyAuthority, CompanyOwner, Employee, Technician)</li>
                    <li>✅ تعديل أو حذف أي مستخدم في النظام</li>
                    <li>✅ إدارة الأدوار والصلاحيات بشكل كامل</li>
                    <li>✅ تعيين أو إلغاء تعيين الصلاحيات لأي مستخدم</li>
                    <li>✅ عرض سجل تغييرات الصلاحيات (Permission Audit Logs)</li>
                    <li>✅ إدارة الأدوار المخصصة للمشغلين</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">إدارة المشغلين والوحدات:</h6>
                <ul>
                    <li>✅ إنشاء وتعديل وحذف المشغلين</li>
                    <li>✅ اعتماد أو رفض حسابات المشغلين</li>
                    <li>✅ تفعيل أو تعطيل حسابات المشغلين</li>
                    <li>✅ عرض وإدارة جميع وحدات التوليد والمولدات</li>
                    <li>✅ نقل المولدات بين المشغلين</li>
                    <li>✅ عرض جميع المناطق الجغرافية</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">إدارة النظام والإعدادات:</h6>
                <ul>
                    <li>✅ إدارة إعدادات الموقع (اسم الموقع، اللوجو، الفافيكون)</li>
                    <li>✅ إدارة الثوابت (المحافظات، المدن، الأنواع)</li>
                    <li>✅ عرض سجل الأخطاء (Error Logs)</li>
                    <li>✅ عرض سجل الأنشطة (Activity Logs)</li>
                    <li>✅ إدارة الأرقام المصرح بها</li>
                    <li>✅ مسح سجلات الأخطاء</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">التواصل والإشعارات:</h6>
                <ul>
                    <li>✅ إرسال رسائل لجميع المستخدمين أو لمشغل معين</li>
                    <li>✅ إرسال رسائل لجميع المشغلين</li>
                    <li>✅ عرض جميع الرسائل في النظام</li>
                    <li>✅ إدارة الإشعارات</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="info-box">
        <h4><i class="bi bi-gear"></i>آلية العمل التفصيلية:</h4>
        <ol class="mb-0">
            <li><strong>إنشاء المستخدمين:</strong>
                <ul>
                    <li>اذهب إلى <strong>المستخدمون → إضافة مستخدم جديد</strong></li>
                    <li>يمكنك إنشاء مستخدمين من أي دور في النظام</li>
                    <li><strong>البيانات المطلوبة:</strong>
                        <ul>
                            <li>الاسم بالعربي</li>
                            <li>الاسم بالإنجليزي (للتوليد التلقائي لـ username)</li>
                            <li>رقم الجوال</li>
                            <li>البريد الإلكتروني</li>
                            <li>الدور (اختر من القائمة)</li>
                        </ul>
                    </li>
                    <li>سيتم توليد username و password تلقائياً</li>
                    <li>سيتم إرسال SMS تلقائياً بالبيانات</li>
                </ul>
            </li>
            <li><strong>إدارة المشغلين:</strong>
                <ul>
                    <li>اذهب إلى <strong>المشغلون</strong> لعرض جميع المشغلين</li>
                    <li>يمكنك إنشاء مشغل جديد أو تعديل مشغل موجود</li>
                    <li>يمكنك اعتماد أو رفض طلبات الانضمام</li>
                    <li>يمكنك تفعيل أو تعطيل حسابات المشغلين</li>
                </ul>
            </li>
            <li><strong>إدارة الإعدادات:</strong>
                <ul>
                    <li>اذهب إلى <strong>الإعدادات</strong> لإدارة إعدادات الموقع</li>
                    <li>يمكنك تغيير اسم الموقع، اللوجو، الفافيكون</li>
                    <li>يمكنك إدارة الثوابت (المحافظات، المدن، إلخ)</li>
                </ul>
            </li>
            <li><strong>مراقبة النظام:</strong>
                <ul>
                    <li>استخدم <strong>سجل الأخطاء</strong> لمراقبة أخطاء النظام</li>
                    <li>استخدم <strong>سجل الأنشطة</strong> لمراقبة جميع العمليات المهمة</li>
                    <li>استخدم <strong>المستخدمون النشطون</strong> لرؤية من يستخدم النظام حالياً</li>
                </ul>
            </li>
        </ol>
    </div>

    <div class="alert alert-warning mt-3 mb-0">
        <h6 class="alert-heading">
            <i class="bi bi-shield-exclamation"></i>
            تحذير مهم
        </h6>
        <p class="mb-0">
            السوبر أدمن لديه صلاحيات كاملة على النظام. تأكد من استخدام هذه الصلاحيات بحذر 
            ووفقاً للسياسات المحددة. جميع عملياتك يتم تسجيلها في سجل الأنشطة.
        </p>
    </div>
</div>
