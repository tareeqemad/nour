{{-- آلية إنشاء المستخدمين --}}
<div class="guide-section">
    <div class="general-card">
        <div class="section-header">
            <h5>
                <i class="bi bi-person-plus"></i>
                @if($user->isCompanyOwner())
                    آلية إضافة فريق العمل
                @else
                    آلية إنشاء المستخدمين
                @endif
            </h5>
        </div>
        @if($user->isCompanyOwner())
            {{-- للمشغل فقط --}}
            <div class="alert alert-primary mb-3">
                <h6 class="alert-heading mb-2">
                    <i class="bi bi-people-fill me-2"></i>
                    إضافة أعضاء فريق العمل
                </h6>
                <p class="mb-0">يمكنك إضافة أعضاء فريق العمل بسهولة من خلال الأدوار المخصصة لمشغلك</p>
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
            <div class="table-responsive">
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
                            <td>جميع الأدوار</td>
                            <td>
                                <strong>لـ SuperAdmin/Admin/EnergyAuthority/CompanyOwner:</strong><br>
                                الاسم، الاسم بالإنجليزي، رقم الجوال، البريد، الدور<br>
                                <strong>لـ Employee/Technician:</strong><br>
                                الاسم، المشغل
                            </td>
                            <td>
                                <code>sp_</code> للـ SuperAdmin<br>
                                <code>a_</code> للـ Admin<br>
                                <code>ea_</code> للـ EnergyAuthority<br>
                                <code>op_</code> للـ CompanyOwner<br>
                                <code>t_</code> للـ Technician<br>
                                <span class="d-block mt-1">النمط: <code>prefix</code> + أول حرف + اسم العائلة</span>
                            </td>
                            <td>✅ تلقائي</td>
                        </tr>
                        @endif
                        @if($user->isSuperAdmin() || $user->isEnergyAuthority())
                        <tr>
                            <td><span class="badge bg-info">EnergyAuthority</span></td>
                            <td>Admin, EnergyAuthority, CompanyOwner, Employee, Technician</td>
                            <td>الاسم، الاسم بالإنجليزي، رقم الجوال، البريد، الدور</td>
                            <td>نفس SuperAdmin</td>
                            <td>✅ تلقائي</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
        <div class="alert alert-info mt-3 mb-0">
            <h6 class="alert-heading mb-2">
                <i class="bi bi-info-circle me-2"></i>
                ملاحظات مهمة:
            </h6>
            <ul class="mb-0">
                <li>يتم توليد <code>username</code> و <code>password</code> تلقائياً عند إنشاء مستخدم جديد</li>
                @if($user->isCompanyOwner())
                <li>إذا أدخلت رقم جوال، سيتم إرسال SMS تلقائياً يحتوي على بيانات الدخول والرابط</li>
                <li>يتم ربط عضو فريق العمل تلقائياً بمشغلك</li>
                @else
                <li>يتم إرسال SMS تلقائياً يحتوي على بيانات الدخول والرابط</li>
                <li>يتم ربط الموظف/الفني تلقائياً بالمشغل</li>
                @endif
                <li>يتم توليد email تلقائياً إذا لم يُدخل: <code>username@{{ $siteDomain }}</code></li>
                @if(!$user->isCompanyOwner())
                <li>عند التسجيل عبر طلب الانضمام: يجب أن يكون الرقم ضمن الأرقام المصرح بها</li>
                @endif
            </ul>
        </div>
    </div>
</div>
