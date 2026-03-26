{{-- آلية إنشاء المستخدمين --}}
<div class="guide-section">
    <x-admin.card>
        <div class="section-header">
            <h5>
                <i class="bi bi-person-plus"></i>
                @if($user->isCompanyOwner())
                    إضافة فريق العمل
                @else
                    إنشاء المستخدمين
                @endif
            </h5>
        </div>

        <div class="role-section">
            @if($user->isCompanyOwner())
                <div class="info-box">
                    <h4><i class="bi bi-people"></i>الأدوار المتاحة لك</h4>
                    <p>يمكنك إضافة فني مشغل أو محاسب. النظام يولّد بيانات الدخول تلقائياً ويرسلها عبر SMS.</p>
                    <h6 class="fw-bold mt-3 mb-2">البيانات المطلوبة:</h6>
                    <ul>
                        <li>الاسم بالعربي + الاسم بالإنجليزي</li>
                        <li>رقم الجوال (اختياري - لإرسال SMS)</li>
                    </ul>
                </div>
            @else
                <div class="info-box">
                    <h4><i class="bi bi-gear"></i>خطوات الإنشاء</h4>
                    <ol class="mb-0">
                        <li>المستخدمون ← إضافة مستخدم جديد</li>
                        <li>أدخل: الاسم بالعربي، الاسم بالإنجليزي، رقم الجوال، الدور</li>
                        <li>إذا اخترت موظف/فني → اختر المشغل</li>
                        <li>اضغط حفظ → يتم توليد username + password + إرسال SMS تلقائياً</li>
                    </ol>
                </div>

                <div class="info-box">
                    <h4><i class="bi bi-hash"></i>نظام Username</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Prefix لكل دور:</h6>
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
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">مثال:</h6>
                            <ul>
                                <li>أحمد محمد (مشغل) → <code>op_amohammed</code></li>
                                <li>Password يتولد تلقائياً</li>
                                <li>Email يتولد إذا لم يُدخل: <code>username&#64;{{ $siteDomain }}</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-admin.card>
</div>
