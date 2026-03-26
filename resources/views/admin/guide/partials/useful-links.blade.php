{{-- روابط مفيدة --}}
<div class="d-section" id="quick-links">
    <div class="d-section-title">
        <i class="bi bi-link-45deg"></i>
        وصول سريع
    </div>

    <div class="d-links-grid">
        @if($user->isCompanyOwner())
        <div class="d-links-group">
            <h6><i class="bi bi-building text-primary"></i> الصفحات الأساسية</h6>
            <ul>
                <li><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> لوحة التحكم</a></li>
                <li><a href="{{ route('admin.operators.profile') }}"><i class="bi bi-person-badge"></i> ملف المشغل</a></li>
                <li><a href="{{ route('admin.generation-units.index') }}"><i class="bi bi-building"></i> وحدات التوليد</a></li>
                <li><a href="{{ route('admin.generators.index') }}"><i class="bi bi-gear"></i> المولدات</a></li>
                <li><a href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> فريق العمل</a></li>
            </ul>
        </div>
        <div class="d-links-group">
            <h6><i class="bi bi-journal-text text-success"></i> السجلات والتوثيق</h6>
            <ul>
                <li><a href="{{ route('admin.operation-logs.index') }}"><i class="bi bi-journal-check"></i> سجلات التشغيل</a></li>
                <li><a href="{{ route('admin.maintenance-records.index') }}"><i class="bi bi-tools"></i> سجلات الصيانة</a></li>
                <li><a href="{{ route('admin.compliance-safeties.index') }}"><i class="bi bi-shield-check"></i> الامتثال والسلامة</a></li>
                <li><a href="{{ route('admin.fuel-efficiencies.index') }}"><i class="bi bi-fuel-pump"></i> كفاءة الوقود</a></li>
                <li><a href="{{ route('admin.messages.index') }}"><i class="bi bi-envelope"></i> الرسائل</a></li>
            </ul>
        </div>
        @endif

        @if($user->isSuperAdmin() || $user->isEnergyAuthority())
        <div class="d-links-group">
            <h6><i class="bi bi-people text-danger"></i> المستخدمين والمشغلين</h6>
            <ul>
                <li><a href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> المستخدمون</a></li>
                <li><a href="{{ route('admin.operators.index') }}"><i class="bi bi-building"></i> المشغلون</a></li>
                <li><a href="{{ route('admin.operators.pending-approval') }}"><i class="bi bi-clock-history"></i> في انتظار الموافقة</a></li>
                <li><a href="{{ route('admin.authorized-phones.index') }}"><i class="bi bi-phone"></i> الأرقام المصرح بها</a></li>
            </ul>
        </div>
        <div class="d-links-group">
            <h6><i class="bi bi-shield-lock text-info"></i> النظام والصلاحيات</h6>
            <ul>
                <li><a href="{{ route('admin.roles.index') }}"><i class="bi bi-person-badge"></i> الأدوار</a></li>
                <li><a href="{{ route('admin.permissions.index') }}"><i class="bi bi-key"></i> الصلاحيات</a></li>
                @if($user->isSuperAdmin())
                <li><a href="{{ route('admin.settings.index') }}"><i class="bi bi-gear"></i> إعدادات الموقع</a></li>
                <li><a href="{{ route('admin.logs.index') }}"><i class="bi bi-file-text"></i> سجل الأخطاء</a></li>
                @endif
                <li><a href="{{ route('admin.activity-logs.index') }}"><i class="bi bi-journal-text"></i> سجل الأنشطة</a></li>
            </ul>
        </div>
        @endif

        @if($user->isEmployee() || $user->isTechnician())
        <div class="d-links-group">
            <h6><i class="bi bi-journal-text text-success"></i> السجلات</h6>
            <ul>
                <li><a href="{{ route('admin.operation-logs.index') }}"><i class="bi bi-journal-check"></i> سجلات التشغيل</a></li>
                <li><a href="{{ route('admin.maintenance-records.index') }}"><i class="bi bi-tools"></i> سجلات الصيانة</a></li>
                <li><a href="{{ route('admin.compliance-safeties.index') }}"><i class="bi bi-shield-check"></i> الامتثال والسلامة</a></li>
                <li><a href="{{ route('admin.messages.index') }}"><i class="bi bi-envelope"></i> الرسائل</a></li>
            </ul>
        </div>
        @endif

        <div class="d-links-group">
            <h6><i class="bi bi-person text-primary"></i> روابط عامة</h6>
            <ul>
                <li><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> لوحة التحكم</a></li>
                <li><a href="{{ route('admin.profile.show') }}"><i class="bi bi-person-circle"></i> الملف الشخصي</a></li>
                <li><a href="{{ route('admin.messages.index') }}"><i class="bi bi-envelope"></i> الرسائل</a></li>
                <li><a href="{{ route('admin.notifications.index') }}"><i class="bi bi-bell"></i> الإشعارات</a></li>
            </ul>
        </div>
    </div>
</div>
