{{-- روابط مفيدة --}}
<div class="guide-section">
    <div class="general-card">
        <div class="section-header">
            <h5>
                <i class="bi bi-link-45deg"></i>
                روابط مفيدة
            </h5>
        </div>
        <div class="row">
            @if($user->isCompanyOwner())
            <div class="col-md-6 links-section">
                <h6 class="fw-semibold mb-3">للمشغل:</h6>
                <ul>
                    <li><a href="{{ route('admin.operators.profile') }}"><i class="bi bi-arrow-left"></i> ملف المشغل</a></li>
                    <li><a href="{{ route('admin.generation-units.index') }}"><i class="bi bi-arrow-left"></i> وحدات التوليد</a></li>
                    <li><a href="{{ route('admin.generators.index') }}"><i class="bi bi-arrow-left"></i> المولدات</a></li>
                    <li><a href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left"></i> فريق العمل</a></li>
                </ul>
            </div>
            @endif
            @if($user->isSuperAdmin() || $user->isEnergyAuthority())
            <div class="col-md-6 links-section">
                <h6 class="fw-semibold mb-3">للسوبر أدمن وسلطة الطاقة:</h6>
                <ul>
                    <li><a href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left"></i> المستخدمون</a></li>
                    <li><a href="{{ route('admin.operators.index') }}"><i class="bi bi-arrow-left"></i> المشغلون</a></li>
                    <li><a href="{{ route('admin.roles.index') }}"><i class="bi bi-arrow-left"></i> الأدوار</a></li>
                    <li><a href="{{ route('admin.permissions.index') }}"><i class="bi bi-arrow-left"></i> الصلاحيات</a></li>
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
