{{-- الأدوار المخصصة للمشغل --}}
<div class="role-section">
    <div class="role-header">
        <div class="role-title">
            <i class="bi bi-people"></i>
            <span>الأدوار المخصصة للمشغل</span>
            <span class="role-badge bg-primary ms-2">فني مشغل ومحاسب</span>
        </div>
    </div>
    
    <div class="alert alert-primary mb-4">
        <h6 class="alert-heading">
            <i class="bi bi-people-fill"></i>
            فريق العمل المخصص
        </h6>
        <p class="mb-0">
            عند تسجيل مشغلك في النظام، يتم إنشاء دورين مخصصين تلقائياً: <strong>فني مشغل</strong> و<strong>محاسب</strong>. 
            يمكنك إضافة أعضاء فريق العمل وإسنادهم لهذه الأدوار، ثم تخصيص صلاحيات كل عضو حسب احتياجاتك.
        </p>
    </div>

    <div class="info-box">
        <h4><i class="bi bi-list-check"></i>الأدوار المتاحة:</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="alert alert-info mb-0">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-tools"></i>
                        فني مشغل
                    </h6>
                    <p class="mb-2">يمكنه:</p>
                    <ul class="mb-0">
                        <li>تسجيل سجلات الصيانة (دورية، طارئة، وقائية)</li>
                        <li>تسجيل سجلات الامتثال والسلامة</li>
                        <li>تسجيل كفاءة الوقود</li>
                        <li>عرض بيانات المولدات ووحدات التوليد</li>
                        <li>تسجيل الأعطال والمشاكل</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success mb-0">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-calculator"></i>
                        محاسب
                    </h6>
                    <p class="mb-2">يمكنه:</p>
                    <ul class="mb-0">
                        <li>إدارة أسعار التعرفة الكهربائية</li>
                        <li>عرض السجلات المالية</li>
                        <li>تسجيل سجلات التشغيل (حسب الصلاحيات)</li>
                        <li>عرض إحصائيات الطاقة المنتجة</li>
                        <li>عرض إحصائيات استهلاك الوقود</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="info-box">
        <h4><i class="bi bi-gear"></i>آلية إضافة وإدارة فريق العمل:</h4>
        <ol class="mb-0">
            <li><strong>إضافة عضو فريق عمل جديد:</strong>
                <ul>
                    <li>اذهب إلى <strong>فريق العمل → إضافة مستخدم جديد</strong></li>
                    <li>أدخل البيانات الأساسية:
                        <ul>
                            <li><strong>الاسم بالعربي:</strong> اسم العضو باللغة العربية</li>
                            <li><strong>الاسم بالإنجليزي:</strong> اسم العضو بالإنجليزية (يستخدم لتوليد username)</li>
                            <li><strong>رقم الجوال:</strong> (اختياري) لإرسال SMS بالبيانات</li>
                        </ul>
                    </li>
                    <li>اختر <strong>الدور</strong> من القائمة:
                        <ul>
                            <li><strong>فني مشغل:</strong> للفنيين المتخصصين في الصيانة</li>
                            <li><strong>محاسب:</strong> للمحاسبين المسؤولين عن الشؤون المالية</li>
                        </ul>
                    </li>
                    <li>انقر على <strong>حفظ</strong></li>
                    <li>سيتم توليد <code>username</code> و <code>password</code> تلقائياً</li>
                    <li>إذا أدخلت رقم جوال، سيتم إرسال SMS تلقائياً يحتوي على:
                        <ul>
                            <li>اسم المستخدم (username)</li>
                            <li>كلمة المرور (password)</li>
                            <li>رابط الدخول للمنصة</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><strong>إدارة صلاحيات فريق العمل:</strong>
                <ul>
                    <li>اذهب إلى <strong>الصلاحيات</strong></li>
                    <li>ابحث عن العضو أو اختره من القائمة</li>
                    <li>يمكنك تعيين أو إلغاء تعيين الصلاحيات لكل عضو بشكل دقيق</li>
                    <li>مثال على الصلاحيات:
                        <ul>
                            <li>إضافة سجلات التشغيل</li>
                            <li>تعديل سجلات التشغيل</li>
                            <li>حذف سجلات التشغيل</li>
                            <li>إضافة سجلات الصيانة</li>
                            <li>إدارة أسعار التعرفة</li>
                            <li>وغيرها...</li>
                        </ul>
                    </li>
                    <li>احفظ التغييرات</li>
                </ul>
            </li>
            <li><strong>تعديل أو حذف أعضاء فريق العمل:</strong>
                <ul>
                    <li>اذهب إلى <strong>فريق العمل</strong></li>
                    <li>ابحث عن العضو المطلوب</li>
                    <li>يمكنك <strong>تعديل</strong> بيانات العضو (الاسم، رقم الجوال، إلخ)</li>
                    <li>يمكنك <strong>حذف</strong> العضو إذا لم يعد يعمل معك</li>
                    <li>يمكنك <strong>تعطيل</strong> حساب العضو مؤقتاً بدلاً من حذفه</li>
                </ul>
            </li>
        </ol>
    </div>

    <div class="info-box">
        <h4><i class="bi bi-shield-check"></i>نظام الصلاحيات المتقدم:</h4>
        <p>
            يمكنك تخصيص صلاحيات كل عضو في فريق العمل بشكل دقيق جداً. على سبيل المثال:
        </p>
        <ul>
            <li><strong>فني مشغل:</strong> يمكنك منحه صلاحيات تسجيل الصيانة فقط، أو إضافة صلاحيات أخرى</li>
            <li><strong>محاسب:</strong> يمكنك منحه صلاحيات إدارة التعرفة فقط، أو إضافة صلاحيات تسجيل التشغيل</li>
            <li>يمكنك منع أي عضو من الوصول إلى صفحات معينة</li>
            <li>يمكنك السماح بالقراءة فقط لبعض الصفحات</li>
        </ul>
        <p class="mb-0 mt-3">
            هذا النظام المرن يسمح لك بتخصيص صلاحيات كل عضو حسب احتياجات عملك بالضبط.
        </p>
    </div>

    <div class="alert alert-info mb-0 mt-3">
        <h6 class="alert-heading">
            <i class="bi bi-info-circle"></i>
            ملاحظة مهمة
        </h6>
        <p class="mb-0">
            الأدوار المخصصة (فني مشغل ومحاسب) تم إنشاؤها تلقائياً عند تسجيل مشغلك في النظام. 
            لا يمكنك حذف هذه الأدوار، لكن يمكنك تخصيص صلاحياتها كما تشاء.
        </p>
    </div>
</div>
