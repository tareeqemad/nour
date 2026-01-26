# أفضل الممارسات (Best Practices) - مشروع نور

هذا الدليل يوضح أفضل الممارسات المطبقة في مشروع نور لضمان جودة الكود، الأداء، والأمان.

---

## 📋 جدول المحتويات

1. [بنية المشروع](#بنية-المشروع)
2. [التحقق من البيانات (Validation)](#التحقق-من-البيانات)
3. [المنطق التجاري (Business Logic)](#المنطق-التجاري)
4. [قواعد البيانات والأداء](#قواعد-البيانات-والأداء)
5. [الأمان](#الأمان)
6. [تنظيم الكود](#تنظيم-الكود)
7. [التعليقات والوثائق](#التعليقات-والوثائق)
8. [الأخطاء والاستثناءات](#الأخطاء-والاستثناءات)
9. [الاختبارات](#الاختبارات)
10. [Git و Version Control](#git-و-version-control)

---

## 🏗️ بنية المشروع

### ✅ الممارسات الجيدة

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Controllers للمنطقة الإدارية
│   │   ├── Api/            # API Controllers
│   │   └── Auth/           # Authentication Controllers
│   ├── Requests/           # Form Request Validation
│   └── Middleware/         # Custom Middleware
├── Models/                 # Eloquent Models
├── Policies/              # Authorization Policies
├── Services/              # Business Logic Services
├── Helpers/               # Helper Classes
└── Traits/                # Reusable Traits
```

### ❌ تجنب

- وضع كل المنطق في Controllers
- استخدام Controllers كـ Services
- خلط Validation مع Business Logic

---

## ✅ التحقق من البيانات (Validation)

### ✅ استخدم Form Requests

**بدلاً من:**
```php
// ❌ في Controller
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
    ]);
    
    if ($validator->fails()) {
        return back()->withErrors($validator);
    }
    // ...
}
```

**استخدم:**
```php
// ✅ Form Request
// app/Http/Requests/Admin/StoreComplaintRequest.php
class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // أو منطق التحقق من الصلاحيات
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
        ];
    }
}

// ✅ في Controller
public function store(StoreComplaintRequest $request)
{
    $validated = $request->validated();
    // البيانات محققة تلقائياً
}
```

### 📝 مثال عملي: تحويل ComplaintSuggestionController

**قبل:**
```php
// app/Http/Controllers/ComplaintSuggestionController.php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [...]);
    if ($validator->fails()) {
        return back()->withErrors($validator);
    }
}
```

**بعد:**
```php
// app/Http/Requests/StoreComplaintSuggestionRequest.php
class StoreComplaintSuggestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|in:complaint,suggestion',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'governorate' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (! Governorate::tryFrom($value)) {
                    $fail('يرجى اختيار محافظة صحيحة');
                }
            }],
            'generator_id' => 'nullable|exists:generators,id',
            'message' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }
}

// في Controller
public function store(StoreComplaintSuggestionRequest $request)
{
    $validated = $request->validated();
    // ...
}
```

---

## 💼 المنطق التجاري (Business Logic)

### ✅ استخدم Service Classes

**بدلاً من:**
```php
// ❌ كل المنطق في Controller
public function store(Request $request)
{
    // Validation
    // Business Logic
    // Database Operations
    // Notifications
    // SMS
    // Emails
    // ...
}
```

**استخدم:**
```php
// ✅ Service Class
// app/Services/ComplaintSuggestionService.php
class ComplaintSuggestionService
{
    public function __construct(
        private NotificationService $notificationService,
        private SmsService $smsService
    ) {}
    
    public function createComplaint(array $data): ComplaintSuggestion
    {
        DB::beginTransaction();
        try {
            $complaint = ComplaintSuggestion::create($data);
            $this->notifyRelevantUsers($complaint);
            $this->sendSmsIfNeeded($complaint);
            
            DB::commit();
            return $complaint;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    private function notifyRelevantUsers(ComplaintSuggestion $complaint): void
    {
        // منطق الإشعارات
    }
}

// ✅ في Controller
public function store(StoreComplaintSuggestionRequest $request)
{
    $complaint = $this->complaintService->createComplaint(
        $request->validated()
    );
    
    return redirect()->route('complaints.show', $complaint);
}
```

### 📝 مثال: TaskService

```php
// app/Services/TaskService.php
class TaskService
{
    public function createTask(array $data, User $creator): Task
    {
        $this->validateTaskAssignment($data);
        
        $task = Task::create([
            ...$data,
            'assigned_by' => $creator->id,
            'created_by' => $creator->id,
        ]);
        
        $this->sendTaskNotifications($task);
        
        return $task;
    }
    
    private function validateTaskAssignment(array $data): void
    {
        $assignedUser = User::findOrFail($data['assigned_to']);
        
        if (!$assignedUser->isTechnician() && !$assignedUser->isCivilDefense()) {
            throw ValidationException::withMessages([
                'assigned_to' => 'يجب اختيار فني أو دفاع مدني'
            ]);
        }
        
        // المزيد من التحقق...
    }
    
    private function sendTaskNotifications(Task $task): void
    {
        // منطق الإشعارات
    }
}
```

---

## 🗄️ قواعد البيانات والأداء

### ✅ Eager Loading

**❌ تجنب N+1 Query Problem:**
```php
// ❌ سيء - N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->operator->name; // Query لكل user!
}
```

**✅ استخدم Eager Loading:**
```php
// ✅ جيد - Query واحد فقط
$users = User::with('operator')->get();
foreach ($users as $user) {
    echo $user->operator->name; // لا يوجد queries إضافية
}

// ✅ Eager Loading متعدد
$users = User::with([
    'operator',
    'roleModel.permissions',
    'permissions'
])->get();

// ✅ Conditional Eager Loading
$users = User::with(['operator' => function ($query) {
    $query->where('status', 'active');
}])->get();
```

### ✅ استخدام Select المحدد

```php
// ❌ جلب كل الأعمدة
$users = User::all();

// ✅ جلب الأعمدة المطلوبة فقط
$users = User::select('id', 'name', 'email')->get();

// ✅ مع Relations
$users = User::with(['operator:id,name,owner_id'])
    ->select('id', 'name', 'operator_id')
    ->get();
```

### ✅ Caching

```php
// ✅ Cache للبيانات التي لا تتغير كثيراً
class ConstantsHelper
{
    public static function get(int $constantNumber): Collection
    {
        return Cache::remember("constant_{$constantNumber}", 3600, function () use ($constantNumber) {
            return ConstantDetail::getByConstantNumber($constantNumber);
        });
    }
}

// ✅ Cache للاستعلامات المعقدة
$operators = Cache::remember("operators_active", 1800, function () {
    return Operator::where('status', 'active')->get();
});
```

### ✅ Database Indexes

```php
// ✅ في Migration
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('phone')->index(); // للبحث السريع
    $table->foreignId('operator_id')->index();
    $table->timestamps();
    
    // Composite Index
    $table->index(['operator_id', 'status']);
});
```

### ✅ Query Optimization

```php
// ❌ سيء - Multiple Queries
$operators = Operator::all();
foreach ($operators as $operator) {
    $count = $operator->generators()->count(); // Query لكل operator!
}

// ✅ جيد - Single Query with Count
$operators = Operator::withCount('generators')->get();
foreach ($operators as $operator) {
    echo $operator->generators_count; // لا يوجد queries إضافية
}
```

---

## 🔒 الأمان

### ✅ Authorization

**استخدم Policies:**
```php
// app/Policies/OperatorPolicy.php
class OperatorPolicy
{
    public function view(User $user, Operator $operator): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isCompanyOwner()) {
            return $user->ownedOperators->contains($operator);
        }
        
        return false;
    }
}

// في Controller
public function show(Operator $operator)
{
    $this->authorize('view', $operator);
    // ...
}
```

### ✅ Input Sanitization

```php
// ✅ استخدم Trait للتنظيف
use App\Traits\SanitizesInput;

class MyController extends Controller
{
    use SanitizesInput;
    
    public function store(Request $request)
    {
        $cleanData = $this->sanitizeInput($request->all());
        // ...
    }
}
```

### ✅ SQL Injection Prevention

```php
// ❌ سيء - SQL Injection Risk
$users = DB::select("SELECT * FROM users WHERE name = '{$name}'");

// ✅ جيد - Parameter Binding
$users = DB::select("SELECT * FROM users WHERE name = ?", [$name]);

// ✅ أفضل - Query Builder
$users = User::where('name', $name)->get();
```

### ✅ CSRF Protection

Laravel يوفر CSRF protection تلقائياً. تأكد من وجود:
```blade
@csrf
```
في جميع النماذج.

---

## 📦 تنظيم الكود

### ✅ استخدام Traits

```php
// app/Traits/SanitizesInput.php
trait SanitizesInput
{
    protected function sanitizeInput(array $data): array
    {
        return AppServiceProvider::cleanInputArrayStatic($data);
    }
}

// استخدام في Controllers
class MyController extends Controller
{
    use SanitizesInput;
}
```

### ✅ Helper Classes

```php
// app/Helpers/UsernameHelper.php
class UsernameHelper
{
    public static function generate(string $name, ?string $idNumber = null): string
    {
        // منطق توليد Username
    }
    
    public static function transliterateArabicToLatin(string $text): string
    {
        // منطق التحويل
    }
}

// الاستخدام
$username = UsernameHelper::generate($name, $idNumber);
```

### ✅ Constants & Enums

```php
// ✅ استخدم Enums
enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case CompanyOwner = 'company_owner';
}

// الاستخدام
if ($user->role === Role::SuperAdmin) {
    // ...
}
```

---

## 💬 التعليقات والوثائق

### ✅ PHPDoc Comments

```php
/**
 * جلب الصلاحيات المتاحة للمستخدم المختار بناءً على دوره
 * 
 * @param User $actor المستخدم الذي يقوم بالتعديل
 * @param User $targetUser المستخدم المختار
 * @return array<int> مصفوفة من IDs الصلاحيات المتاحة
 */
private function getTargetUserAvailablePermissionIds(User $actor, User $targetUser): array
{
    // ...
}
```

### ✅ Inline Comments

```php
// ✅ تعليقات مفيدة
// المشغل مع الموظفين/الفنيين: فقط صلاحيات التشغيل والصيانة
if ($actor->isCompanyOwner() && ($targetUser->isEmployee() || $targetUser->isTechnician())) {
    return $this->filterEmployeeTechnicianPermissions($actorAvailableIds);
}

// ❌ تعليقات واضحة من الكود نفسه
// Check if actor is company owner and target is employee or technician
if ($actor->isCompanyOwner() && ($targetUser->isEmployee() || $targetUser->isTechnician())) {
    // ...
}
```

---

## ⚠️ الأخطاء والاستثناءات

### ✅ Exception Handling

```php
// ✅ في Controller
public function store(Request $request)
{
    try {
        DB::beginTransaction();
        
        $result = $this->service->create($request->validated());
        
        DB::commit();
        
        return redirect()->route('index')
            ->with('success', 'تم الحفظ بنجاح');
            
    } catch (ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->errors())->withInput();
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating record', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->with('error', 'حدث خطأ أثناء الحفظ')
            ->withInput();
    }
}
```

### ✅ Custom Exceptions

```php
// app/Exceptions/UnauthorizedActionException.php
class UnauthorizedActionException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'غير مصرح لك بهذا الإجراء'
        ], 403);
    }
}

// الاستخدام
if (!$user->can('manage', $operator)) {
    throw new UnauthorizedActionException();
}
```

---

## 🧪 الاختبارات

### ✅ Unit Tests

```php
// tests/Unit/UsernameHelperTest.php
class UsernameHelperTest extends TestCase
{
    public function test_generates_username_from_name()
    {
        $username = UsernameHelper::generate('أحمد محمد');
        
        $this->assertNotEmpty($username);
        $this->assertIsString($username);
    }
}
```

### ✅ Feature Tests

```php
// tests/Feature/ComplaintSuggestionTest.php
class ComplaintSuggestionTest extends TestCase
{
    public function test_user_can_submit_complaint()
    {
        $response = $this->post('/complaints', [
            'type' => 'complaint',
            'name' => 'أحمد',
            'phone' => '0591234567',
            'message' => 'هذه شكوى تجريبية',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('complaints_suggestions', [
            'name' => 'أحمد'
        ]);
    }
}
```

---

## 🔄 Git و Version Control

### ✅ Commit Messages

```
✅ جيد:
feat: إضافة نظام إدارة المهام
fix: إصلاح مشكلة N+1 queries في قائمة المستخدمين
refactor: تبسيط منطق الصلاحيات في PermissionsController
docs: تحديث README مع تعليمات التثبيت

❌ سيء:
update
fix bug
changes
```

### ✅ Branch Naming

```
feature/user-management
bugfix/permission-tree-loading
refactor/validation-requests
hotfix/security-patch
```

---

## 📊 Checklist للمراجعة

قبل إرسال Pull Request، تأكد من:

- [ ] ✅ استخدام Form Requests للتحقق من البيانات
- [ ] ✅ نقل Business Logic إلى Service Classes
- [ ] ✅ استخدام Eager Loading لتجنب N+1 queries
- [ ] ✅ إضافة Authorization Checks (Policies)
- [ ] ✅ تنظيف Input Data
- [ ] ✅ إضافة PHPDoc Comments
- [ ] ✅ معالجة الأخطاء بشكل صحيح
- [ ] ✅ اختبار الكود
- [ ] ✅ Commit Messages واضحة
- [ ] ✅ لا توجد TODO أو FIXME في الكود النهائي

---

## 📚 مراجع إضافية

- [Laravel Best Practices](https://laravel.com/docs/best-practices)
- [PSR Standards](https://www.php-fig.org/psr/)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)

---

**آخر تحديث:** 2025-01-24
