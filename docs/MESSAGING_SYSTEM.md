# نظام الرسائل الداخلية - Internal Messaging System

## 📋 نظرة عامة

نظام الرسائل الداخلية في منصة راصد يسمح للمستخدمين بإرسال واستقبال الرسائل داخل النظام. يدعم النظام عدة أنواع من الرسائل حسب الدور والصلاحيات.

---

## 🎯 أنواع الرسائل

### 1. `admin_to_all`
**الوصف:** رسالة من السوبر أدمن أو سلطة الطاقة لجميع المشغلين في النظام

**الخصائص:**
- `sender_id`: ID المستخدم المرسل (SuperAdmin أو EnergyAuthority)
- `receiver_id`: `null` (لأنها موجهة للجميع)
- `operator_id`: `null`
- `type`: `admin_to_all`

**من يمكنه الإرسال:**
- SuperAdmin
- EnergyAuthority (Admin)

**من يمكنه الاستقبال:**
- جميع المشغلين (CompanyOwner)

**مثال:**
```php
Message::create([
    'sender_id' => $admin->id,
    'receiver_id' => null,
    'operator_id' => null,
    'subject' => 'إعلان هام',
    'body' => 'رسالة موجهة لجميع المشغلين',
    'type' => 'admin_to_all',
]);
```

---

### 2. `admin_to_operator`
**الوصف:** رسالة من السوبر أدمن أو سلطة الطاقة لمشغل معين

**الخصائص:**
- `sender_id`: ID المستخدم المرسل
- `receiver_id`: `null`
- `operator_id`: ID المشغل المستهدف
- `type`: `admin_to_operator`

**من يمكنه الإرسال:**
- SuperAdmin
- EnergyAuthority

**من يمكنه الاستقبال:**
- المشغل المحدد (CompanyOwner)

**مثال:**
```php
Message::create([
    'sender_id' => $admin->id,
    'receiver_id' => null,
    'operator_id' => $operator->id,
    'subject' => 'رسالة خاصة',
    'body' => 'رسالة موجهة لمشغل معين',
    'type' => 'admin_to_operator',
]);
```

---

### 3. `operator_to_operator`
**الوصف:** رسالة من مشغل لمشغل آخر أو من مشغل لمستخدم معين

**الخصائص:**
- `sender_id`: ID المشغل المرسل
- `receiver_id`: ID المستخدم المستقبل (أو null إذا كان لمشغل)
- `operator_id`: ID المشغل المستقبل (إذا كان المستقبل مشغل)
- `type`: `operator_to_operator`

**من يمكنه الإرسال:**
- CompanyOwner
- SuperAdmin
- EnergyAuthority

**من يمكنه الاستقبال:**
- المشغل المستقبل
- المستخدم المحدد

**مثال:**
```php
Message::create([
    'sender_id' => $operator1->owner_id,
    'receiver_id' => $operator2->owner_id,
    'operator_id' => null,
    'subject' => 'رسالة بين مشغلين',
    'body' => 'محتوى الرسالة',
    'type' => 'operator_to_operator',
]);
```

---

### 4. `operator_to_staff`
**الوصف:** رسالة من مشغل لجميع موظفيه

**الخصائص:**
- `sender_id`: ID المشغل المرسل
- `receiver_id`: `null` (لأنها موجهة لجميع الموظفين)
- `operator_id`: ID المشغل (للموظفين التابعين له)
- `type`: `operator_to_staff`

**من يمكنه الإرسال:**
- CompanyOwner

**من يمكنه الاستقبال:**
- جميع موظفي المشغل (Employee, Technician)
- المشغل نفسه (يمكنه رؤية الرسائل المرسلة لموظفيه)

**مثال:**
```php
Message::create([
    'sender_id' => $operator->owner_id,
    'receiver_id' => null,
    'operator_id' => $operator->id,
    'subject' => 'إعلان للموظفين',
    'body' => 'رسالة موجهة لجميع موظفي المشغل',
    'type' => 'operator_to_staff',
]);
```

---

### 5. `user_to_user`
**الوصف:** رسالة من مستخدم لمستخدم آخر (رسالة مباشرة)

**الخصائص:**
- `sender_id`: ID المستخدم المرسل
- `receiver_id`: ID المستخدم المستقبل
- `operator_id`: `null`
- `type`: `user_to_user`

**من يمكنه الإرسال:**
- أي مستخدم لديه صلاحية إرسال رسائل

**من يمكنه الاستقبال:**
- المستخدم المحدد

**مثال:**
```php
Message::create([
    'sender_id' => $user1->id,
    'receiver_id' => $user2->id,
    'operator_id' => null,
    'subject' => 'رسالة مباشرة',
    'body' => 'محتوى الرسالة',
    'type' => 'user_to_user',
]);
```

---

## 🔐 الصلاحيات حسب الدور

### SuperAdmin / EnergyAuthority (Admin)
**يمكنه:**
- إرسال رسائل لجميع المشغلين (`admin_to_all`)
- إرسال رسائل لمشغل معين (`admin_to_operator`)
- إرسال رسائل لمستخدم معين (`user_to_user`)
- رؤية جميع الرسائل في النظام (في المستقبل - حالياً يرى فقط رسائله المرسلة/المستقبلة)

**لا يمكنه:**
- إرسال رسائل لموظفين (`operator_to_staff`)

---

### CompanyOwner (المشغل)
**يمكنه:**
- إرسال رسائل لموظفيه (`operator_to_staff`)
- إرسال رسائل لمشغلين آخرين (`operator_to_operator`)
- إرسال رسائل لمستخدم معين (`user_to_user`)
- رؤية:
  - الرسائل المرسلة منه
  - الرسائل الموجهة له
  - الرسائل الموجهة لموظفيه
  - الرسائل الموجهة لمشغله من Admin

**لا يمكنه:**
- إرسال رسائل لجميع المشغلين (`admin_to_all`)

---

### Employee / Technician (الموظف/الفني)
**يمكنه:**
- إرسال رسائل للمشغل (صاحب المشغل)
- رؤية:
  - الرسائل الموجهة له مباشرة
  - الرسائل الموجهة لجميع موظفي المشغل (`operator_to_staff`)

**لا يمكنه:**
- إرسال رسائل لموظفين آخرين
- إرسال رسائل لمشغلين آخرين

---

## 📤 آلية الإرسال

### 1. إنشاء رسالة جديدة

```php
use App\Models\Message;
use Illuminate\Http\Request;

public function store(StoreMessageRequest $request)
{
    $user = auth()->user();
    $data = $request->validated();
    
    // تحديد نوع الرسالة تلقائياً
    $type = $this->determineMessageType($user, $data);
    
    // رفع المرفق (إن وجد)
    $attachmentPath = null;
    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $attachmentPath = $file->store('messages/attachments', 'public');
    }
    
    // إنشاء الرسالة
    $message = Message::create([
        'sender_id' => $user->id,
        'receiver_id' => $data['receiver_id'] ?? null,
        'operator_id' => $data['operator_id'] ?? null,
        'subject' => $data['subject'],
        'body' => $data['body'],
        'attachment' => $attachmentPath,
        'type' => $type,
    ]);
    
    return redirect()->route('admin.messages.index')
        ->with('success', 'تم إرسال الرسالة بنجاح');
}
```

### 2. تحديد نوع الرسالة تلقائياً

```php
private function determineMessageType($user, $data)
{
    // SuperAdmin أو EnergyAuthority
    if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
        if ($data['send_to'] === 'all_operators') {
            return 'admin_to_all';
        } elseif (isset($data['operator_id'])) {
            return 'admin_to_operator';
        } elseif (isset($data['receiver_id'])) {
            return 'operator_to_operator';
        }
    }
    
    // CompanyOwner
    if ($user->isCompanyOwner()) {
        if ($data['send_to'] === 'my_staff') {
            return 'operator_to_staff';
        } elseif (isset($data['operator_id'])) {
            return 'operator_to_operator';
        } elseif (isset($data['receiver_id'])) {
            return 'operator_to_operator';
        }
    }
    
    // Default
    return 'user_to_user';
}
```

---

## 📥 آلية الاستقبال والفلترة

### فلترة الرسائل حسب الدور

```php
public function index(Request $request)
{
    $user = auth()->user();
    $query = Message::with(['sender', 'receiver', 'operator']);
    
    // CompanyOwner
    if ($user->isCompanyOwner()) {
        $operator = $user->ownedOperators()->first();
        if ($operator) {
            $query->where(function ($q) use ($user, $operator) {
                // الرسائل المرسلة منه
                $q->where('sender_id', $user->id)
                  // الرسائل المستقبلة منه
                  ->orWhere('receiver_id', $user->id)
                  // الرسائل الموجهة لمشغله من Admin
                  ->orWhere(function ($subQ) use ($operator) {
                      $subQ->where('type', 'admin_to_operator')
                           ->where('operator_id', $operator->id);
                  })
                  // الرسائل الموجهة لجميع المشغلين
                  ->orWhere(function ($subQ) {
                      $subQ->where('type', 'admin_to_all')
                           ->whereNull('operator_id');
                  })
                  // الرسائل الموجهة لموظفيه
                  ->orWhere(function ($subQ) use ($operator) {
                      $subQ->where('type', 'operator_to_staff')
                           ->where('operator_id', $operator->id);
                  });
            });
        }
    }
    
    // Employee/Technician
    elseif ($user->hasOperatorLinkedCustomRole()) {
        $operatorId = $user->roleModel->operator_id;
        $query->where(function ($q) use ($user, $operatorId) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id)
              ->orWhere(function ($subQ) use ($operatorId) {
                  // الرسائل الموجهة لجميع موظفي المشغل
                  $subQ->where('type', 'operator_to_staff')
                       ->where('operator_id', $operatorId);
              });
        });
    }
    
    // Regular users
    else {
        $query->where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        });
    }
    
    $messages = $query->orderBy('created_at', 'desc')->paginate(20);
    
    return view('admin.messages.index', compact('messages'));
}
```

---

## 🔔 الإشعارات والعدادات

### الحصول على عدد الرسائل غير المقروءة

```php
public function getUnreadCount(): JsonResponse
{
    $user = auth()->user();
    
    $count = Message::where(function ($q) use ($user) {
        // نفس منطق الفلترة في index()
        // ...
    })
    ->where('is_read', false)
    ->where('sender_id', '!=', $user->id)
    ->count();
    
    return response()->json(['count' => $count]);
}
```

**Route:**
```php
Route::get('messages/unread-count', [MessageController::class, 'getUnreadCount'])
    ->name('messages.unread-count');
```

**استخدام في JavaScript:**
```javascript
// تحديث عداد الرسائل غير المقروءة
function updateUnreadCount() {
    $.get('/admin/messages/unread-count')
        .done(function(data) {
            $('#unread-messages-count').text(data.count);
            if (data.count > 0) {
                $('#unread-messages-count').removeClass('d-none');
            } else {
                $('#unread-messages-count').addClass('d-none');
            }
        });
}

// تحديث كل 30 ثانية
setInterval(updateUnreadCount, 30000);
```

---

## 📎 المرفقات

### رفع مرفق

```php
if ($request->hasFile('attachment')) {
    $file = $request->file('attachment');
    
    // التحقق من نوع الملف
    $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $extension = $file->getClientOriginalExtension();
    
    if (!in_array(strtolower($extension), $allowedTypes)) {
        return back()->withErrors(['attachment' => 'نوع الملف غير مدعوم']);
    }
    
    // التحقق من حجم الملف (مثلاً 5MB)
    if ($file->getSize() > 5 * 1024 * 1024) {
        return back()->withErrors(['attachment' => 'حجم الملف كبير جداً']);
    }
    
    // رفع الملف
    $attachmentPath = $file->store('messages/attachments', 'public');
    
    // حفظ المسار في قاعدة البيانات
    $message->attachment = $attachmentPath;
    $message->save();
}
```

### عرض المرفق

```php
// في Model
public function getAttachmentUrlAttribute(): ?string
{
    return $this->attachment ? asset('storage/' . $this->attachment) : null;
}

// في View
@if($message->hasAttachment())
    <a href="{{ $message->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-paperclip"></i>
        تحميل المرفق
    </a>
@endif
```

---

## 🗑️ حذف الرسائل

### آلية الحذف

النظام يستخدم **Soft Delete** مع آلية خاصة:

1. **إذا كان المستخدم هو المرسل:**
   - يتم وضع `deleted_by_sender = true`
   - الرسالة تبقى في قاعدة البيانات

2. **إذا كان المستخدم هو المستقبل:**
   - يتم وضع `deleted_by_receiver = true`
   - الرسالة تبقى في قاعدة البيانات

3. **إذا حذف كلا الطرفين الرسالة:**
   - يتم حذف الرسالة نهائياً (soft delete)
   - يتم حذف المرفق من التخزين

```php
public function destroy(Message $message)
{
    $user = auth()->user();
    $isSender = $message->sender_id === $user->id;
    $isReceiver = $message->receiver_id === $user->id;
    
    if ($isSender) {
        $message->update(['deleted_by_sender' => true]);
    } elseif ($isReceiver) {
        $message->update(['deleted_by_receiver' => true]);
    }
    
    // إذا حذف كلا الطرفين، احذف نهائياً
    if ($message->deleted_by_sender && $message->deleted_by_receiver) {
        // حذف المرفق
        if ($message->attachment) {
            Storage::disk('public')->delete($message->attachment);
        }
        $message->delete(); // Soft delete
    }
    
    return redirect()->route('admin.messages.index')
        ->with('success', 'تم حذف الرسالة بنجاح');
}
```

---

## ✅ تحديد الرسالة كمقروءة

```php
public function show(Message $message)
{
    // تحديد الرسالة كمقروءة عند فتحها
    if (!$message->is_read && $message->receiver_id === auth()->id()) {
        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
    
    return view('admin.messages.show', compact('message'));
}

// أو عبر AJAX
public function markAsRead(Message $message): JsonResponse
{
    if (!$message->is_read) {
        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
    
    return response()->json(['success' => true]);
}
```

---

## 🔄 الرد على الرسائل

```php
// في Model
public function canBeRepliedBy(User $user): bool
{
    if (!$this->canBeViewedBy($user)) {
        return false;
    }
    
    // لا يمكن الرد على الرسائل الموجهة للجميع
    if ($this->isBroadcastToStaff() || $this->isBroadcastToOperators()) {
        return false;
    }
    
    // المرسل أو المستقبل يمكنهما الرد
    return $this->sender_id === $user->id || $this->receiver_id === $user->id;
}
```

---

## 📊 Routes المتاحة

```php
// عرض قائمة الرسائل
Route::get('messages', [MessageController::class, 'index'])
    ->name('messages.index');

// إنشاء رسالة جديدة
Route::get('messages/create', [MessageController::class, 'create'])
    ->name('messages.create');
Route::post('messages', [MessageController::class, 'store'])
    ->name('messages.store');

// عرض رسالة
Route::get('messages/{message}', [MessageController::class, 'show'])
    ->name('messages.show');

// تحديد كمقروءة
Route::post('messages/{message}/mark-read', [MessageController::class, 'markAsRead'])
    ->name('messages.mark-read');

// حذف رسالة
Route::delete('messages/{message}', [MessageController::class, 'destroy'])
    ->name('messages.destroy');

// API endpoints
Route::get('messages/unread-count', [MessageController::class, 'getUnreadCount'])
    ->name('messages.unread-count');
Route::get('messages/recent', [MessageController::class, 'getRecentMessages'])
    ->name('messages.recent');
```

---

## 🎨 واجهة المستخدم

### عرض الرسائل في القائمة

```blade
@foreach($messages as $message)
    <div class="message-item {{ !$message->is_read ? 'unread' : '' }}">
        <div class="message-sender">
            {{ $message->senderDisplayName }}
        </div>
        <div class="message-subject">
            <a href="{{ route('admin.messages.show', $message) }}">
                {{ $message->subject }}
            </a>
        </div>
        <div class="message-time">
            {{ $message->created_at->diffForHumans() }}
        </div>
        @if($message->hasAttachment())
            <i class="bi bi-paperclip"></i>
        @endif
    </div>
@endforeach
```

---

## 🔍 البحث والفلترة

```php
// البحث في الموضوع والمحتوى
if ($request->filled('search')) {
    $search = $request->input('search');
    $query->where(function ($q) use ($search) {
        $q->where('subject', 'like', "%{$search}%")
          ->orWhere('body', 'like', "%{$search}%")
          ->orWhereHas('sender', function ($userQuery) use ($search) {
              $userQuery->where('name', 'like', "%{$search}%");
          });
    });
}

// فلترة بنوع الرسالة
if ($request->filled('type')) {
    $query->where('type', $request->input('type'));
}

// فلترة بالحالة (مقروء/غير مقروء)
if ($request->filled('is_read')) {
    $query->where('is_read', $request->boolean('is_read'));
}
```

---

## 📝 ملاحظات مهمة

1. **الأمان:** كل مستخدم يرى فقط الرسائل المرسلة منه أو المستقبلة له
2. **Soft Delete:** الرسائل المحذوفة تبقى في قاعدة البيانات حتى يحذفها كلا الطرفين
3. **المرفقات:** يتم تخزينها في `storage/app/public/messages/attachments`
4. **الصلاحيات:** يتم التحقق من الصلاحيات عبر Policies
5. **الأنواع:** نوع الرسالة يتم تحديده تلقائياً حسب المرسل والمستقبل

---

## 🚀 أمثلة استخدام

### إرسال رسالة من Admin لجميع المشغلين

```php
$admin = auth()->user(); // SuperAdmin أو EnergyAuthority

Message::create([
    'sender_id' => $admin->id,
    'receiver_id' => null,
    'operator_id' => null,
    'subject' => 'إعلان هام',
    'body' => 'نود إعلامكم بأن...',
    'type' => 'admin_to_all',
]);
```

### إرسال رسالة من مشغل لموظفيه

```php
$operator = auth()->user()->ownedOperators()->first();

Message::create([
    'sender_id' => auth()->id(),
    'receiver_id' => null,
    'operator_id' => $operator->id,
    'subject' => 'اجتماع مهم',
    'body' => 'يرجى الحضور في...',
    'type' => 'operator_to_staff',
]);
```

### إرسال رسالة مباشرة بين مستخدمين

```php
Message::create([
    'sender_id' => auth()->id(),
    'receiver_id' => $receiver->id,
    'operator_id' => null,
    'subject' => 'سؤال',
    'body' => 'أريد الاستفسار عن...',
    'type' => 'user_to_user',
]);
```

---

## 📚 الملفات ذات الصلة

- `app/Models/Message.php` - Model الرسائل
- `app/Http/Controllers/Admin/MessageController.php` - Controller الرسائل
- `app/Http/Requests/Admin/StoreMessageRequest.php` - Validation
- `app/Policies/MessagePolicy.php` - الصلاحيات
- `resources/views/admin/messages/` - Views الرسائل

---

**آخر تحديث:** 2024
