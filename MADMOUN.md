# منصة مضمون — دليل المطوّر

منصة **مضمون** تعمل **داخل** تطبيق نور: نفس المستخدم، نفس الدخول، نفس التصميم،
ونفس قاعدة البيانات — لكن بمساحة كود معزولة ومنظومة صلاحيات مستقلة.

هذا المستند هو العقد بينك (مبرمج مضمون) وبين نواة نور. التزم به وستشتغل بحرّية
كاملة دون لمس كود نور.

---

## 🟢 مساحتك (اشتغل هنا بحرّية)

| الغرض        | المسار                                   | ملاحظات                                    |
|--------------|-------------------------------------------|--------------------------------------------|
| الراوتس      | `routes/madmoun.php`                       | بادئة `/madmoun` ، أسماء `madmoun.*`        |
| الكنترولرات  | `app/Http/Controllers/Madmoun/`            | namespace `App\Http\Controllers\Madmoun`   |
| الموديلات    | `app/Models/Madmoun/`                      | ورّثها من `MadmounModel`                    |
| الخدمات      | `app/Services/Madmoun/`                    | (أنشئه عند الحاجة)                          |
| الفيوهات     | `resources/views/madmoun/`                 | تُستدعى بـ `madmoun::...`                   |
| المايقريشن   | `database/migrations/madmoun/`             | كل جدول يبدأ بـ `madmoun_`                  |
| الميدلوير    | `app/Http/Middleware/Madmoun/`             | بوابة `MadmounAccess`                       |

كل هذا مُسجّل من مكان واحد: `app/Providers/MadmounServiceProvider.php`.

## 🔴 لا تلمس (نواة نور)

`app/Models/User.php` · `routes/admin.php` · `routes/web.php` ·
`app/Http/Controllers/Admin/` · `resources/views/admin/` ·
`resources/views/layouts/admin.blade.php` · `bootstrap/`.

تحتاج تعديل النواة؟ نسّق مع فريق نور — لا تعدّلها مباشرة.

---

## القواعد الأساسية

1. **التسمية:** كل جدول = `madmoun_*` ، كل صلاحية = `madmoun.*` ، كل راوت = `madmoun.*`.
   هذا يضمن صفر تعارض مع نور.

2. **قاعدة بيانات واحدة:** اربط بجداول نور عبر مفاتيح أجنبية مباشرة
   (مثلاً `operator_id` ⟶ `operators`). لا تكرّر بيانات المستخدمين أو الثوابت.

3. **المستخدم والثوابت مشتركة:** استخدم `App\Models\User` و
   `App\Models\ConstantMaster`/`ConstantDetail` كما هي — لا تنشئ نسخاً منها.

4. **الصلاحيات (طبقتان):**
   - **الاشتراك (Entitlement):** هل المشغّل دافع؟ منطقه في `MadmounAccess`
     وفي جداولك (`madmoun_subscriptions` ... إلخ — أنشئها كما تراها).
   - **الدور (RBAC):** استخدم نظام نور الحالي عبر صلاحيات بادئتها `madmoun.*`:
     ```php
     if (! $user->hasPermission('madmoun.policies.view')) abort(403);
     ```
   نقطة الربط الوحيدة لكل ذلك: `app/Http/Middleware/Madmoun/MadmounAccess.php`.

5. **الواجهة:** كل صفحاتك تمتد من تخطيط مضمون:
   ```blade
   @extends('madmoun::layouts.app')
   @section('title', 'العنوان')
   @section('content') ... @endsection
   ```
   التصميم/الهيدر/الأصول موروثة من نور تلقائياً. قائمتك الجانبية في
   `resources/views/madmoun/partials/sidebar.blade.php`.

---

## البداية السريعة

```bash
# 1) صفحة مضمون شغّالة فوراً:
#    افتح /madmoun بالمتصفح بعد تسجيل الدخول.

# 2) أنشئ أول جدول:
cp database/migrations/madmoun/TEMPLATE_create_madmoun_table.php.stub \
   database/migrations/madmoun/2026_07_01_120000_create_madmoun_policies_table.php
php artisan migrate

# 3) أضف راوت في routes/madmoun.php، وكنترولر في app/Http/Controllers/Madmoun/،
#    وفيو تحت resources/views/madmoun/.
```

نقطة الدخول للمستخدم: رابط `/madmoun`. (إضافة رابط تنقّل من سايدبار نور إلى مضمون
خطوة منفصلة ينفّذها فريق نور عند الجاهزية.)
