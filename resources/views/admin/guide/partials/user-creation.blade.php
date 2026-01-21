{{-- آلية إنشاء المستخدمين --}}
<div class="guide-section">
    <div class="general-card">
        <div class="section-header">
            <h5>
                <i class="bi bi-person-plus"></i>
                @if($user->isCompanyOwner())
                    دليل إضافة وإدارة فريق العمل
                @else
                    دليل إنشاء وإدارة المستخدمين
                @endif
            </h5>
        </div>
        @if($user->isCompanyOwner())
            {{-- للمشغل فقط --}}
            <div class="alert alert-primary mb-4">
                <h6 class="alert-heading mb-2">
                    <i class="bi bi-people-fill me-2"></i>
                    إضافة أعضاء فريق العمل
                </h6>
                <p class="mb-0">
                    يمكنك إضافة أعضاء فريق العمل بسهولة من خلال الأدوار المخصصة لمشغلك (فني مشغل ومحاسب). 
                    النظام سيقوم بتوليد بيانات الدخول تلقائياً وإرسالها عبر SMS.
                </p>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>الدور</th>
                            <th>البيانات المطلوبة</th>
                            <th>توليد تلقائي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-primary">فني مشغل</span></td>
                            <td>
                                • الاسم بالعربي<br>
                                • الاسم بالإنجليزي<br>
                                • رقم الجوال (اختياري)
                            </td>
                            <td>
                                ✅ Username<br>
                                ✅ Password<br>
                                ✅ SMS (إذا أدخلت رقم الجوال)
                            </td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-success">محاسب</span></td>
                            <td>
                                • الاسم بالعربي<br>
                                • الاسم بالإنجليزي<br>
                                • رقم الجوال (اختياري)
                            </td>
                            <td>
                                ✅ Username<br>
                                ✅ Password<br>
                                ✅ SMS (إذا أدخلت رقم الجوال)
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            {{-- للسوبر أدمن وسلطة الطاقة --}}
            <div class="alert alert-info mb-4">
                <h6 class="alert-heading">
                    <i class="bi bi-person-plus"></i>
                    إنشاء المستخدمين
                </h6>
                <p class="mb-0">
                    يمكنك إنشاء مستخدمين جدد من الأدوار المختلفة. النظام سيقوم بتوليد بيانات الدخول تلقائياً 
                    وإرسالها عبر SMS. كل دور له prefix خاص في username.
                </p>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>الدور</th>
                            <th>يمكنه إنشاء</th>
                            <th>البيانات المطلوبة</th>
                            <th>توليد Username</th>
                            <th>إرسال SMS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($user->isSuperAdmin())
                        <tr>
                            <td><span class="badge bg-danger">SuperAdmin</span></td>
                            <td>
                                <strong>جميع الأدوار</strong><br>
                                @if($user->isSuperAdmin())
                                SuperAdmin, 
                                @endif
                                Admin, EnergyAuthority, CompanyOwner, Employee, Technician
                            </td>
                            <td>
                                @if($user->isSuperAdmin())
                                <strong>لـ SuperAdmin/Admin/EnergyAuthority/CompanyOwner:</strong><br>
                                • الاسم بالعربي<br>
                                • الاسم بالإنجليزي<br>
                                • رقم الجوال<br>
                                • البريد الإلكتروني<br>
                                • الدور<br><br>
                                @else
                                <strong>لـ Admin/EnergyAuthority/CompanyOwner:</strong><br>
                                • الاسم بالعربي<br>
                                • الاسم بالإنجليزي<br>
                                • رقم الجوال<br>
                                • البريد الإلكتروني<br>
                                • الدور<br><br>
                                @endif
                                <strong>لـ Employee/Technician:</strong><br>
                                • الاسم بالعربي<br>
                                • الاسم بالإنجليزي<br>
                                • المشغل (اختيار من القائمة)
                            </td>
                            <td>
                                @if($user->isSuperAdmin())
                                <code>sp_</code> للـ SuperAdmin<br>
                                @endif
                                <code>a_</code> للـ Admin<br>
                                <code>ea_</code> للـ EnergyAuthority<br>
                                <code>op_</code> للـ CompanyOwner<br>
                                <code>e_</code> للـ Employee<br>
                                <code>t_</code> للـ Technician<br><br>
                                <strong>النمط:</strong><br>
                                <code>prefix</code> + أول حرف من الاسم + اسم العائلة<br><br>
                                <strong>مثال:</strong><br>
                                أحمد محمد → <code>op_amohammed</code>
                            </td>
                            <td>✅ تلقائي<br>يتم إرسال SMS فوراً</td>
                        </tr>
                        @endif
                        @if($user->isSuperAdmin() || $user->isEnergyAuthority())
                        <tr>
                            <td><span class="badge bg-info">EnergyAuthority</span></td>
                            <td>Admin, EnergyAuthority, CompanyOwner, Employee, Technician</td>
                            <td>
                                • الاسم بالعربي<br>
                                • الاسم بالإنجليزي<br>
                                • رقم الجوال<br>
                                • البريد الإلكتروني<br>
                                • الدور
                            </td>
                            <td>
                                <code>a_</code> للـ Admin<br>
                                <code>ea_</code> للـ EnergyAuthority<br>
                                <code>op_</code> للـ CompanyOwner<br>
                                <code>e_</code> للـ Employee<br>
                                <code>t_</code> للـ Technician<br><br>
                                <strong>النمط:</strong><br>
                                <code>prefix</code> + أول حرف من الاسم + اسم العائلة
                            </td>
                            <td>✅ تلقائي<br>يتم إرسال SMS فوراً</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="info-box">
                <h4><i class="bi bi-gear"></i>خطوات إنشاء مستخدم جديد:</h4>
                <ol class="mb-0">
                    <li><strong>الانتقال إلى صفحة إضافة المستخدم:</strong>
                        <ul>
                            <li>اذهب إلى <strong>المستخدمون → إضافة مستخدم جديد</strong></li>
                        </ul>
                    </li>
                    <li><strong>إدخال البيانات الأساسية:</strong>
                        <ul>
                            <li>الاسم بالعربي (مطلوب)</li>
                            <li>الاسم بالإنجليزي (مطلوب - يستخدم لتوليد username)</li>
                            <li>رقم الجوال (مطلوب - لإرسال SMS)</li>
                            <li>البريد الإلكتروني (اختياري - إذا لم يُدخل، سيتم توليده تلقائياً)</li>
                        </ul>
                    </li>
                    <li><strong>اختيار الدور:</strong>
                        <ul>
                            <li>اختر الدور من القائمة المنسدلة</li>
                            <li>إذا اخترت Employee أو Technician، يجب اختيار المشغل</li>
                        </ul>
                    </li>
                    <li><strong>الحفظ:</strong>
                        <ul>
                            <li>انقر على <strong>حفظ</strong></li>
                            <li>سيتم توليد username و password تلقائياً</li>
                            <li>سيتم إرسال SMS تلقائياً يحتوي على:
                                <ul>
                                    <li>اسم المستخدم (username)</li>
                                    <li>كلمة المرور (password)</li>
                                    <li>رابط الدخول للمنصة</li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ol>
            </div>
        @endif

        <div class="info-box">
            <h4><i class="bi bi-info-circle"></i>معلومات مهمة عن نظام إنشاء المستخدمين:</h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">التوليد التلقائي:</h6>
                    <ul>
                        <li>✅ يتم توليد <code>username</code> تلقائياً بناءً على:
                            <ul>
                                <li>Prefix خاص بكل دور</li>
                                <li>أول حرف من الاسم</li>
                                <li>اسم العائلة</li>
                            </ul>
                        </li>
                        <li>✅ يتم توليد <code>password</code> تلقائياً (كلمة مرور قوية)</li>
                        <li>✅ يتم توليد <code>email</code> تلقائياً إذا لم يُدخل:
                            <ul>
                                <li>النمط: <code>username@{{ $siteDomain }}</code></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">الإشعارات:</h6>
                    <ul>
                        @if($user->isCompanyOwner())
                        <li>✅ إذا أدخلت رقم جوال، سيتم إرسال SMS تلقائياً</li>
                        <li>✅ يتم ربط عضو فريق العمل تلقائياً بمشغلك</li>
                        @else
                        <li>✅ يتم إرسال SMS تلقائياً لجميع المستخدمين الجدد</li>
                        <li>✅ يتم ربط الموظف/الفني تلقائياً بالمشغل المحدد</li>
                        @endif
                        <li>✅ يتم إرسال رسالة ترحيبية تلقائياً</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(!$user->isCompanyOwner())
        <div class="alert alert-warning mt-3 mb-0">
            <h6 class="alert-heading">
                <i class="bi bi-exclamation-triangle"></i>
                طلبات الانضمام
            </h6>
            <p class="mb-0">
                عند التسجيل عبر طلب الانضمام (من الصفحة العامة)، يجب أن يكون رقم الجوال 
                ضمن <strong>الأرقام المصرح بها</strong>. يمكنك إدارة الأرقام المصرح بها من 
                <strong>الأرقام المصرح بها</strong>.
            </p>
        </div>
        @endif
    </div>
</div>
