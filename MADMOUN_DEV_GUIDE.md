# دليل مبرمج منصة مضمون — كل شي تحتاجه

منصة **مضمون** تعمل داخل تطبيق نور (Laravel 12): نفس المستخدم، نفس الدخول،
نفس التصميم، نفس قاعدة البيانات — لكن بمساحة كود معزولة لك بالكامل.

التزم بهذا الدليل وستشتغل بحرّية دون لمس كود نور. مثال حيّ جاهز أمامك:
**"بوالص الضمان"** (Policies) — افتحه وقلّده.

---

## 0) القاعدة الذهبية (احفظها)

| العنصر | القاعدة | مثال |
|--------|---------|------|
| اسم الجدول | يبدأ بـ `madmoun_` | `madmoun_policies` |
| اسم الصلاحية | يبدأ بـ `madmoun.` | `madmoun.policies.view` |
| اسم الراوت | يبدأ بـ `madmoun.` | `madmoun.policies.index` |
| الـ namespace | تحت `Madmoun` | `App\Http\Controllers\Madmoun\...` |

هذا يضمن **صفر تعارض** مع نور.

---

## 1) أين تكتب كل شيء (مساحتك)

```
routes/madmoun.php                      ← الراوتس
app/Http/Controllers/Madmoun/           ← الكنترولرات
app/Models/Madmoun/                     ← الموديلات (ورّثها من MadmounModel)
app/Services/Madmoun/                   ← خدماتك (أنشئه عند الحاجة)
app/Http/Requests/Madmoun/              ← Form Requests (اختياري)
database/migrations/madmoun/            ← المايقريشن (جداول madmoun_)
resources/views/madmoun/                ← الفيوهات (تُستدعى madmoun::)
app/Http/Middleware/Madmoun/            ← بوابة MadmounAccess
```

🔴 **لا تلمس:** `app/Models/User.php` · `routes/admin.php` · `routes/web.php` ·
`app/Http/Controllers/Admin/` · `resources/views/admin/` ·
`resources/views/layouts/admin.blade.php` · `bootstrap/`.

---

## 2) بناء ميزة كاملة — خطوة بخطوة

سننشئ ميزة "مطالبات" (Claims) كمثال جديد. كرّر نفس النمط لأي ميزة.

### الخطوة 1 — أنشئ الجدول (Migration)

```bash
php artisan make:migration create_madmoun_claims_table --path=database/migrations/madmoun
```

> ⚠️ مهم: استخدم `--path=database/migrations/madmoun` دائماً، وإلا سيُنشأ الملف
> في مجلد نور الافتراضي. أما عند التشغيل فأمر `php artisan migrate` العادي يكفي
> (مجلد مضمون مُسجّل تلقائياً).

عدّل الملف الناتج:

```php
public function up(): void
{
    Schema::create('madmoun_claims', function (Blueprint $table) {
        $table->id();
        // ربط بنواة نور — نفس قاعدة البيانات، مفتاح أجنبي مباشر:
        $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
        $table->string('title');
        $table->decimal('amount', 12, 2)->default(0);
        $table->string('status')->default('open');
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('madmoun_claims');
}
```

ثم شغّلها:

```bash
php artisan migrate
```

### الخطوة 2 — أنشئ الموديل

```bash
php artisan make:model Madmoun/Claim
```

غيّر الأب من `Model` إلى `MadmounModel` (يشتق اسم الجدول `madmoun_claims` تلقائياً):

```php
<?php

namespace App\Models\Madmoun;

use App\Models\Operator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends MadmounModel
{
    use SoftDeletes;

    protected $fillable = ['operator_id', 'title', 'amount', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    // ربط بنواة نور:
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
```

### الخطوة 3 — أنشئ الكنترولر

```bash
php artisan make:controller Madmoun/ClaimController
```

```php
<?php

namespace App\Http\Controllers\Madmoun;

use App\Http\Controllers\Controller;
use App\Models\Madmoun\Claim;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index()
    {
        $claims = Claim::latest()->get();

        return view('madmoun::claims.index', compact('claims'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'  => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $data['operator_id'] = $request->user()->getAffiliatedOperator()?->id;

        Claim::create($data);

        return redirect()->route('madmoun.claims.index')
            ->with('success', 'تم الحفظ ✅');
    }
}
```

### الخطوة 4 — أضف الراوتس

في `routes/madmoun.php`، داخل المجموعة المحمية الموجودة:

```php
use App\Http\Controllers\Madmoun\ClaimController;

Route::get('/claims',  [ClaimController::class, 'index'])->name('claims.index');
Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
```

> البادئة `/madmoun` والاسم `madmoun.` يُضافان تلقائياً، فالرابط النهائي
> `/madmoun/claims` واسمه `madmoun.claims.index`.

### الخطوة 5 — أنشئ الفيو

ملف `resources/views/madmoun/claims/index.blade.php`:

```blade
@extends('madmoun::layouts.app')
@section('title', 'المطالبات')

@section('content')
    <div class="card custom-card">
        <div class="card-header"><div class="card-title">المطالبات</div></div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('madmoun.claims.store') }}" class="mb-3">
                @csrf
                <input type="text" name="title" class="form-control mb-2" placeholder="العنوان">
                <input type="number" step="0.01" name="amount" class="form-control mb-2" placeholder="القيمة">
                <button class="btn btn-primary">حفظ</button>
            </form>

            <table class="table">
                <thead><tr><th>#</th><th>العنوان</th><th>المشغّل</th><th>القيمة</th></tr></thead>
                <tbody>
                    @foreach ($claims as $c)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $c->title }}</td>
                            <td>{{ $c->operator?->name ?? '—' }}</td>
                            <td>{{ number_format($c->amount, 2) }} ₪</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
```

### الخطوة 6 — أضف رابطاً في السايدبار

في `resources/views/madmoun/partials/sidebar.blade.php`، أضف عنصراً:

```blade
<li class="slide {{ $isActive('madmoun.claims.*') }}">
    <a href="{{ route('madmoun.claims.index') }}" class="side-menu__item">
        <i class="side-menu__icon bi bi-file-text"></i>
        <span class="side-menu__label">المطالبات</span>
    </a>
</li>
```

**خلص.** افتح `/madmoun/claims` بالمتصفح.

---

## 3) الصلاحيات (طبقتان)

مضمون تستخدم نظام صلاحيات نور نفسه، لكن بصلاحيات بادئتها `madmoun.*`:

- **طبقة الدور (RBAC):** تحقّق داخل الكنترولر أو الفيو:
  ```php
  if (! auth()->user()->hasPermission('madmoun.claims.view')) abort(403);
  ```
  في الفيو:
  ```blade
  @if (auth()->user()->hasPermission('madmoun.claims.create')) ... @endif
  ```

- **طبقة الاشتراك (هل المشغّل دافع؟):** منطقها المركزي في
  `app/Http/Middleware/Madmoun/MadmounAccess.php`. طوّرها هناك (جداول
  `madmoun_subscriptions` ... إلخ من تصميمك).

لإضافة صلاحيات مضمون لقاعدة البيانات، أنشئ Seeder خاصاً بك يضيف صفوفاً
لجدول `permissions` بأسماء `madmoun.*` (لا تعدّل seeders نور).

---

## 4) الربط بنواة نور (متاح لك مباشرة)

```php
$user     = auth()->user();              // App\Models\User
$operator = $user->getAffiliatedOperator(); // مشغّل المستخدم الحالي
$user->hasPermission('madmoun.x.view');  // فحص صلاحية
$user->isSuperAdmin();                    // مدير النظام؟

// الثوابت المشتركة:
\App\Models\ConstantMaster::...
\App\Models\ConstantDetail::...
```

في موديلاتك اربط بـ `App\Models\Operator` / `App\Models\Subscriber` ... إلخ
عبر مفاتيح أجنبية مباشرة (نفس قاعدة البيانات).

---

## 5) ورقة الأوامر السريعة

```bash
# إنشاء العناصر (لاحظ بادئة Madmoun/ و --path للمايقريشن)
php artisan make:migration create_madmoun_X_table --path=database/migrations/madmoun
php artisan make:model      Madmoun/X          # ثم غيّر الأب إلى MadmounModel
php artisan make:controller Madmoun/XController
php artisan make:request    Madmoun/XRequest   # اختياري

# قاعدة البيانات
php artisan migrate                 # يشغّل مايقريشن مضمون تلقائياً
php artisan migrate:rollback        # تراجع آخر دفعة
php artisan migrate:status          # حالة المايقريشن

# تنظيف الكاش بعد التعديلات
php artisan optimize:clear          # يمسح cache/config/route/view

# الفحص
php artisan route:list --name=madmoun   # كل راوتس مضمون
php artisan tinker                       # تجربة الموديلات تفاعلياً

# التشغيل المحلي
php artisan serve                    # http://127.0.0.1:8000
```

---

## 6) قواعد سريعة لتجنّب الأخطاء

- بعد إضافة/تعديل راوت أو فيو لأول مرة: `php artisan optimize:clear`.
- كل جداولك تبدأ بـ `madmoun_`، وكل صلاحياتك بـ `madmoun.`.
- لا تنشئ نسخة من User أو الثوابت — استخدم موديلات نور كما هي.
- صفحاتك دائماً `@extends('madmoun::layouts.app')`.
- تحتاج تعديل نواة نور؟ نسّق مع فريق نور — لا تعدّلها مباشرة.

---

## مرجع حيّ جاهز (قلّده)

| الطبقة | الملف |
|--------|------|
| مايقريشن | `database/migrations/madmoun/2026_07_01_120000_create_madmoun_policies_table.php` |
| موديل | `app/Models/Madmoun/Policy.php` |
| كنترولر | `app/Http/Controllers/Madmoun/PolicyController.php` |
| راوت | `routes/madmoun.php` |
| فيو | `resources/views/madmoun/policies/index.blade.php` |
| سايدبار | `resources/views/madmoun/partials/sidebar.blade.php` |

افتحه على `/madmoun/policies` وابدأ منه.
