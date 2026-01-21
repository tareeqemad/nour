{{-- سلطة الطاقة --}}
<div class="role-section">
    <div class="role-header">
        <div class="role-title">
            <i class="bi bi-building"></i>
            <span>سلطة الطاقة (EnergyAuthority)</span>
            <span class="role-badge bg-info ms-2">سلطة الطاقة</span>
        </div>
    </div>
    
    <div class="alert alert-info mb-4">
        <h6 class="alert-heading">
            <i class="bi bi-building-check"></i>
            دور إداري رئيسي
        </h6>
        <p class="mb-0">
            سلطة الطاقة هي الجهة الرسمية المسؤولة عن إدارة ومراقبة قطاع الطاقة. 
            لديك صلاحيات واسعة لإدارة المشغلين والمستخدمين ومراقبة العمليات، 
            لكن لا يمكنك تعديل إعدادات النظام الأساسية.
        </p>
    </div>

    <div class="info-box">
        <h4><i class="bi bi-key"></i>الصلاحيات المتاحة:</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">إدارة المستخدمين:</h6>
                <ul>
                    <li>✅ إنشاء مستخدمين من الأدوار التالية:
                        <ul>
                            <li>Admin (مدير)</li>
                            <li>EnergyAuthority (سلطة الطاقة)</li>
                            <li>CompanyOwner (مشغل)</li>
                            <li>Employee (موظف)</li>
                            <li>Technician (فني)</li>
                        </ul>
                    </li>
                    <li>✅ تعديل أو حذف المستخدمين الذين أنشأتهم</li>
                    <li>✅ إعادة تعيين كلمات المرور</li>
                    <li>✅ تفعيل أو تعطيل حسابات المستخدمين</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">إدارة المشغلين:</h6>
                <ul>
                    <li>✅ إنشاء مشغلين جدد</li>
                    <li>✅ اعتماد أو رفض طلبات الانضمام</li>
                    <li>✅ تفعيل أو تعطيل حسابات المشغلين</li>
                    <li>✅ عرض وإدارة جميع وحدات التوليد والمولدات</li>
                    <li>✅ نقل المولدات بين المشغلين</li>
                    <li>✅ عرض جميع المناطق الجغرافية</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">إدارة الصلاحيات:</h6>
                <ul>
                    <li>✅ إدارة شجرة الصلاحيات</li>
                    <li>✅ تعيين الصلاحيات للمستخدمين</li>
                    <li>✅ تعيين الصلاحيات للأدوار</li>
                    <li>✅ عرض سجل تغييرات الصلاحيات</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">المراقبة والتواصل:</h6>
                <ul>
                    <li>✅ عرض سجل الأخطاء</li>
                    <li>✅ عرض سجل الأنشطة</li>
                    <li>✅ إرسال رسائل لجميع المستخدمين أو لمشغل معين</li>
                    <li>✅ إدارة الأرقام المصرح بها</li>
                </ul>
            </div>
        </div>
        <div class="mt-3 pt-3 border-top">
            <h6 class="fw-bold text-danger mb-2">❌ القيود:</h6>
            <ul class="mb-0">
                <li>❌ لا يمكنك إنشاء مستخدمين من أدوار إدارية عليا</li>
                <li>❌ لا يمكنك إدارة إعدادات الموقع (اسم الموقع، اللوجو، إلخ)</li>
                <li>❌ لا يمكنك حذف أو تعديل مستخدمين من أدوار إدارية عليا</li>
            </ul>
        </div>
    </div>

    <div class="info-box">
        <h4><i class="bi bi-gear"></i>آلية العمل التفصيلية:</h4>
        <ol class="mb-0">
            <li><strong>اعتماد المشغلين الجدد:</strong>
                <ul>
                    <li>اذهب إلى <strong>المشغلون → في انتظار الموافقة</strong></li>
                    <li>راجع بيانات المشغل المقدم للانضمام</li>
                    <li>تحقق من أن رقم الجوال ضمن الأرقام المصرح بها</li>
                    <li>انقر على <strong>اعتماد</strong> للموافقة أو <strong>رفض</strong> للرفض</li>
                    <li>سيتم إرسال إشعار للمشغل بنتيجة الطلب</li>
                </ul>
            </li>
            <li><strong>إنشاء مستخدمين جدد:</strong>
                <ul>
                    <li>اذهب إلى <strong>المستخدمون → إضافة مستخدم جديد</strong></li>
                    <li>أدخل البيانات المطلوبة واختر الدور</li>
                    <li>سيتم توليد username و password تلقائياً</li>
                    <li>سيتم إرسال SMS بالبيانات تلقائياً</li>
                </ul>
            </li>
            <li><strong>إدارة الأرقام المصرح بها:</strong>
                <ul>
                    <li>اذهب إلى <strong>الأرقام المصرح بها</strong></li>
                    <li>أضف أرقام جوال جديدة للمشغلين المسموح لهم بالانضمام</li>
                    <li>يمكنك استيراد الأرقام من ملف Excel</li>
                    <li>يمكنك إرسال تذكير للمشغلين المعلقين</li>
                </ul>
            </li>
            <li><strong>مراقبة العمليات:</strong>
                <ul>
                    <li>استخدم <strong>سجل الأخطاء</strong> لمراقبة مشاكل النظام</li>
                    <li>استخدم <strong>سجل الأنشطة</strong> لمراقبة العمليات المهمة</li>
                    <li>استخدم <strong>المستخدمون النشطون</strong> لرؤية من يستخدم النظام</li>
                </ul>
            </li>
        </ol>
    </div>
</div>
