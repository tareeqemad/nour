{{-- آلية إنشاء المستخدمين --}}
<div class="d-section" id="user-creation">
    <div class="d-section-title">
        <i class="bi bi-person-plus"></i>
        @if($user->isCompanyOwner())
            إضافة فريق العمل
        @else
            إنشاء المستخدمين
        @endif
    </div>

    @if($user->isCompanyOwner())
        <div class="d-callout d-callout-info mb-2">
            <p>يمكنك إضافة فني مشغل أو محاسب. النظام يولّد بيانات الدخول تلقائياً ويرسلها عبر SMS.</p>
        </div>
        <div class="d-workflow-title"><i class="bi bi-clipboard-data"></i> البيانات المطلوبة</div>
        <ul style="padding-inline-start: 1.25rem; font-size: 0.85rem; color: var(--color-text-secondary, #3B4863);">
            <li>الاسم بالعربي + الاسم بالإنجليزي</li>
            <li>رقم الجوال (اختياري - لإرسال SMS)</li>
        </ul>
    @else
        <div class="d-workflow-title"><i class="bi bi-gear"></i> خطوات الإنشاء</div>
        <ol style="padding-inline-start: 1.25rem; font-size: 0.85rem; color: var(--color-text-secondary, #3B4863); line-height: 1.8;">
            <li>المستخدمون &rarr; إضافة مستخدم جديد</li>
            <li>أدخل: الاسم بالعربي، الاسم بالإنجليزي، رقم الجوال، الدور</li>
            <li>إذا اخترت موظف/فني &rarr; اختر المشغل</li>
            <li>حفظ &rarr; يتم توليد username + password + إرسال SMS تلقائياً</li>
        </ol>

        <div class="d-workflow-title"><i class="bi bi-hash"></i> نظام Username</div>
        <div class="d-perms-grid">
            <div class="d-perms-group">
                <h6>Prefix لكل دور</h6>
                <ul>
                    @if($user->isSuperAdmin())
                    <li><code>sp_</code> SuperAdmin</li>
                    @endif
                    <li><code>a_</code> Admin</li>
                    <li><code>ea_</code> سلطة الطاقة</li>
                    <li><code>op_</code> مشغل</li>
                    <li><code>e_</code> موظف</li>
                    <li><code>t_</code> فني</li>
                </ul>
            </div>
            <div class="d-perms-group">
                <h6>مثال</h6>
                <ul>
                    <li>أحمد محمد (مشغل) &rarr; <code>op_amohammed</code></li>
                    <li>Password يتولد تلقائياً</li>
                    <li>Email يتولد إذا لم يُدخل: <code>username&#64;{{ $siteDomain }}</code></li>
                </ul>
            </div>
        </div>
    @endif
</div>
