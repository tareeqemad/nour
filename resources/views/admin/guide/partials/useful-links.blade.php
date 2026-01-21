{{-- روابط مفيدة --}}
<div class="guide-section">
    <div class="general-card">
        <div class="section-header">
            <h5>
                <i class="bi bi-link-45deg"></i>
                روابط مفيدة ووصول سريع
            </h5>
        </div>
        <div class="alert alert-primary mb-4">
            <h6 class="alert-heading">
                <i class="bi bi-compass"></i>
                التنقل السريع
            </h6>
            <p class="mb-0">
                استخدم هذه الروابط للوصول السريع إلى الصفحات المهمة في النظام. 
                تم تنظيم الروابط حسب دورك في النظام.
            </p>
        </div>

        <div class="row g-4">
            @if($user->isCompanyOwner())
            <div class="col-md-6 links-section">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-building text-primary"></i>
                    للمشغل - الصفحات الأساسية:
                </h6>
                <ul>
                    <li>
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            لوحة التحكم
                        </a>
                        <small class="text-muted d-block ms-5">عرض الإحصائيات الشاملة والملخص</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.operators.profile') }}">
                            <i class="bi bi-person-badge"></i>
                            ملف المشغل
                        </a>
                        <small class="text-muted d-block ms-5">إدارة بيانات المشغل الأساسية</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.generation-units.index') }}">
                            <i class="bi bi-building"></i>
                            وحدات التوليد
                        </a>
                        <small class="text-muted d-block ms-5">إدارة وحدات التوليد والمناطق الجغرافية</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.generators.index') }}">
                            <i class="bi bi-gear"></i>
                            المولدات
                        </a>
                        <small class="text-muted d-block ms-5">إدارة جميع المولدات التابعة لك</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i>
                            فريق العمل
                        </a>
                        <small class="text-muted d-block ms-5">إدارة أعضاء فريق العمل (فني مشغل، محاسب)</small>
                    </li>
                </ul>
            </div>
            <div class="col-md-6 links-section">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-journal-text text-success"></i>
                    للمشغل - السجلات والتوثيق:
                </h6>
                <ul>
                    <li>
                        <a href="{{ route('admin.operation-logs.index') }}">
                            <i class="bi bi-journal-check"></i>
                            سجلات التشغيل
                        </a>
                        <small class="text-muted d-block ms-5">تسجيل وعرض سجلات التشغيل اليومية</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.maintenance-records.index') }}">
                            <i class="bi bi-tools"></i>
                            سجلات الصيانة
                        </a>
                        <small class="text-muted d-block ms-5">تسجيل أعمال الصيانة الدورية والطارئة</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.compliance-safeties.index') }}">
                            <i class="bi bi-shield-check"></i>
                            الامتثال والسلامة
                        </a>
                        <small class="text-muted d-block ms-5">تسجيل سجلات الامتثال والسلامة</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.fuel-efficiencies.index') }}">
                            <i class="bi bi-fuel-pump"></i>
                            كفاءة الوقود
                        </a>
                        <small class="text-muted d-block ms-5">مراقبة وتحليل كفاءة استهلاك الوقود</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.messages.index') }}">
                            <i class="bi bi-envelope"></i>
                            الرسائل
                        </a>
                        <small class="text-muted d-block ms-5">الرسائل الداخلية والتواصل</small>
                    </li>
                </ul>
            </div>
            @endif

            @if($user->isSuperAdmin() || $user->isEnergyAuthority())
            <div class="col-md-6 links-section">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-people text-danger"></i>
                    إدارة المستخدمين والمشغلين:
                </h6>
                <ul>
                    <li>
                        <a href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i>
                            المستخدمون
                        </a>
                        <small class="text-muted d-block ms-5">إدارة جميع المستخدمين في النظام</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.operators.index') }}">
                            <i class="bi bi-building"></i>
                            المشغلون
                        </a>
                        <small class="text-muted d-block ms-5">إدارة المشغلين واعتماد الطلبات</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.operators.pending-approval') }}">
                            <i class="bi bi-clock-history"></i>
                            في انتظار الموافقة
                        </a>
                        <small class="text-muted d-block ms-5">مراجعة واعتماد طلبات الانضمام</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.authorized-phones.index') }}">
                            <i class="bi bi-phone"></i>
                            الأرقام المصرح بها
                        </a>
                        <small class="text-muted d-block ms-5">إدارة الأرقام المسموح لها بالانضمام</small>
                    </li>
                </ul>
            </div>
            <div class="col-md-6 links-section">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-shield-lock text-info"></i>
                    إدارة النظام والصلاحيات:
                </h6>
                <ul>
                    <li>
                        <a href="{{ route('admin.roles.index') }}">
                            <i class="bi bi-person-badge"></i>
                            الأدوار
                        </a>
                        <small class="text-muted d-block ms-5">إدارة الأدوار في النظام</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.permissions.index') }}">
                            <i class="bi bi-key"></i>
                            الصلاحيات
                        </a>
                        <small class="text-muted d-block ms-5">إدارة شجرة الصلاحيات وتعيينها</small>
                    </li>
                    @if($user->isSuperAdmin())
                    <li>
                        <a href="{{ route('admin.settings.index') }}">
                            <i class="bi bi-gear"></i>
                            إعدادات الموقع
                        </a>
                        <small class="text-muted d-block ms-5">إدارة إعدادات الموقع (الاسم، اللوجو، إلخ)</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.logs.index') }}">
                            <i class="bi bi-file-text"></i>
                            سجل الأخطاء
                        </a>
                        <small class="text-muted d-block ms-5">عرض ومراقبة أخطاء النظام</small>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('admin.activity-logs.index') }}">
                            <i class="bi bi-journal-text"></i>
                            سجل الأنشطة
                        </a>
                        <small class="text-muted d-block ms-5">عرض جميع العمليات المهمة في النظام</small>
                    </li>
                </ul>
            </div>
            @endif

            @if($user->isEmployee() || $user->isTechnician())
            <div class="col-md-6 links-section">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-journal-text text-success"></i>
                    السجلات والتوثيق:
                </h6>
                <ul>
                    <li>
                        <a href="{{ route('admin.operation-logs.index') }}">
                            <i class="bi bi-journal-check"></i>
                            سجلات التشغيل
                        </a>
                        <small class="text-muted d-block ms-5">تسجيل وعرض سجلات التشغيل (حسب الصلاحيات)</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.maintenance-records.index') }}">
                            <i class="bi bi-tools"></i>
                            سجلات الصيانة
                        </a>
                        <small class="text-muted d-block ms-5">تسجيل أعمال الصيانة (حسب الصلاحيات)</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.compliance-safeties.index') }}">
                            <i class="bi bi-shield-check"></i>
                            الامتثال والسلامة
                        </a>
                        <small class="text-muted d-block ms-5">تسجيل سجلات الامتثال (حسب الصلاحيات)</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.messages.index') }}">
                            <i class="bi bi-envelope"></i>
                            الرسائل
                        </a>
                        <small class="text-muted d-block ms-5">الرسائل الداخلية</small>
                    </li>
                </ul>
            </div>
            @endif

            {{-- روابط مشتركة لجميع المستخدمين --}}
            <div class="col-md-6 links-section">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-person text-primary"></i>
                    روابط مشتركة:
                </h6>
                <ul>
                    <li>
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            لوحة التحكم
                        </a>
                        <small class="text-muted d-block ms-5">الصفحة الرئيسية والإحصائيات</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.profile.show') }}">
                            <i class="bi bi-person-circle"></i>
                            الملف الشخصي
                        </a>
                        <small class="text-muted d-block ms-5">عرض بياناتك الشخصية وتغيير كلمة المرور</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.messages.index') }}">
                            <i class="bi bi-envelope"></i>
                            الرسائل
                        </a>
                        <small class="text-muted d-block ms-5">الرسائل الداخلية والتواصل</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.notifications.index') }}">
                            <i class="bi bi-bell"></i>
                            الإشعارات
                        </a>
                        <small class="text-muted d-block ms-5">عرض الإشعارات والتنبيهات</small>
                    </li>
                    <li>
                        <a href="{{ route('admin.guide.index') }}">
                            <i class="bi bi-book"></i>
                            الدليل الإرشادي
                        </a>
                        <small class="text-muted d-block ms-5">هذا الدليل - شرح شامل للنظام</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
