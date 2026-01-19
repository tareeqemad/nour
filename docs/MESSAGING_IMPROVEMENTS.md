# تحسينات مقترحة لنظام الرسائل الداخلية

## 📋 التحسينات المقترحة

### 1. ✅ إشعارات فورية عند استقبال رسالة جديدة

**المشكلة الحالية:** لا يتم إرسال إشعارات عند استقبال رسالة جديدة.

**الحل المقترح:**

```php
// في MessageController@store
public function store(StoreMessageRequest $request)
{
    // ... الكود الحالي ...
    
    $message = Message::create([...]);
    
    // إرسال إشعارات للمستقبلين
    $this->notifyRecipients($message);
    
    return redirect()->route('admin.messages.index')
        ->with('success', 'تم إرسال الرسالة بنجاح');
}

private function notifyRecipients(Message $message)
{
    $recipients = $this->getRecipients($message);
    
    foreach ($recipients as $recipient) {
        Notification::createNotification(
            $recipient->id,
            'message_received',
            'رسالة جديدة',
            "لديك رسالة جديدة من: {$message->sender->name}",
            route('admin.messages.show', $message)
        );
    }
}

private function getRecipients(Message $message): Collection
{
    $recipients = collect();
    
    switch ($message->type) {
        case 'admin_to_all':
            // جميع المشغلين
            $recipients = User::where('role', Role::CompanyOwner)->get();
            break;
            
        case 'admin_to_operator':
            // المشغل المحدد
            if ($message->operator_id) {
                $operator = Operator::find($message->operator_id);
                if ($operator && $operator->owner_id) {
                    $recipients->push(User::find($operator->owner_id));
                }
            }
            break;
            
        case 'operator_to_staff':
            // جميع موظفي المشغل
            if ($message->operator_id) {
                $operator = Operator::find($message->operator_id);
                if ($operator) {
                    $recipients = $operator->users()
                        ->whereIn('role', [Role::Employee, Role::Technician])
                        ->get();
                }
            }
            break;
            
        case 'user_to_user':
        case 'operator_to_operator':
            // المستخدم المحدد
            if ($message->receiver_id) {
                $recipients->push(User::find($message->receiver_id));
            }
            break;
    }
    
    return $recipients->filter();
}
```

---

### 2. 📧 إشعارات بريد إلكتروني (اختياري)

**الميزة:** إرسال إيميل للمستخدم عند استقبال رسالة مهمة.

```php
// في MessageController
private function notifyRecipients(Message $message)
{
    $recipients = $this->getRecipients($message);
    
    foreach ($recipients as $recipient) {
        // إشعار داخلي
        Notification::createNotification(...);
        
        // إشعار بريد إلكتروني (إذا كان مفعلاً)
        if ($recipient->email_notifications_enabled ?? false) {
            Mail::to($recipient->email)->send(new NewMessageMail($message, $recipient));
        }
    }
}
```

**إنشاء Mail Class:**

```php
// app/Mail/NewMessageMail.php
class NewMessageMail extends Mailable
{
    public function __construct(
        public Message $message,
        public User $recipient
    ) {}
    
    public function build()
    {
        return $this->subject('رسالة جديدة - ' . $this->message->subject)
                    ->view('emails.new-message')
                    ->with([
                        'message' => $this->message,
                        'recipient' => $this->recipient,
                    ]);
    }
}
```

---

### 3. ⚡ تحسين الأداء (Caching & Eager Loading)

**المشكلة:** الاستعلامات المتكررة لعدد الرسائل غير المقروءة.

**الحل:**

```php
// في MessageController
public function getUnreadCount(): JsonResponse
{
    $user = auth()->user();
    
    // استخدام Cache
    $cacheKey = "user_{$user->id}_unread_messages_count";
    
    $count = Cache::remember($cacheKey, now()->addMinutes(1), function () use ($user) {
        return Message::where(function ($q) use ($user) {
            // ... منطق الفلترة ...
        })
        ->where('is_read', false)
        ->where('sender_id', '!=', $user->id)
        ->count();
    });
    
    return response()->json(['count' => $count]);
}

// مسح الـ Cache عند استقبال رسالة جديدة
private function clearUnreadCountCache(User $user)
{
    Cache::forget("user_{$user->id}_unread_messages_count");
}
```

---

### 4. 🔄 إعادة توجيه الرسائل (Forward)

**الميزة:** إمكانية إعادة توجيه رسالة لمستخدم آخر.

```php
// في MessageController
public function forward(Request $request, Message $message)
{
    $this->authorize('view', $message);
    
    $validated = $request->validate([
        'receiver_id' => 'required|exists:users,id',
        'subject' => 'nullable|string|max:255',
        'body' => 'nullable|string',
    ]);
    
    // إنشاء رسالة جديدة
    $forwardedMessage = Message::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $validated['receiver_id'],
        'subject' => $validated['subject'] ?? 'Fwd: ' . $message->subject,
        'body' => ($validated['body'] ?? '') . "\n\n--- الرسالة الأصلية ---\n" . $message->body,
        'attachment' => $message->attachment, // نسخ المرفق
        'type' => 'user_to_user',
        'forwarded_from_id' => $message->id, // إضافة حقل جديد
    ]);
    
    // إشعار المستقبل
    $this->notifyRecipients($forwardedMessage);
    
    return redirect()->route('admin.messages.index')
        ->with('success', 'تم إعادة توجيه الرسالة بنجاح');
}
```

**Migration:**

```php
Schema::table('messages', function (Blueprint $table) {
    $table->unsignedBigInteger('forwarded_from_id')->nullable()->after('type');
    $table->foreign('forwarded_from_id')->references('id')->on('messages')->onDelete('set null');
});
```

---

### 5. 📋 إرسال نسخة (CC/BCC)

**الميزة:** إرسال نسخة من الرسالة لمستخدمين إضافيين.

```php
// في StoreMessageRequest
public function rules()
{
    return [
        // ... القواعد الحالية ...
        'cc' => 'nullable|array',
        'cc.*' => 'exists:users,id',
        'bcc' => 'nullable|array',
        'bcc.*' => 'exists:users,id',
    ];
}

// في MessageController@store
public function store(StoreMessageRequest $request)
{
    // ... إنشاء الرسالة الرئيسية ...
    
    $message = Message::create([...]);
    
    // إرسال نسخة (CC)
    if ($request->filled('cc')) {
        foreach ($request->cc as $userId) {
            Message::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $userId,
                'subject' => $message->subject,
                'body' => $message->body,
                'attachment' => $message->attachment,
                'type' => 'user_to_user',
                'is_cc' => true,
                'original_message_id' => $message->id,
            ]);
        }
    }
    
    // إرسال نسخة مخفية (BCC)
    if ($request->filled('bcc')) {
        foreach ($request->bcc as $userId) {
            Message::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $userId,
                'subject' => $message->subject,
                'body' => $message->body,
                'attachment' => $message->attachment,
                'type' => 'user_to_user',
                'is_bcc' => true,
                'original_message_id' => $message->id,
            ]);
        }
    }
    
    $this->notifyRecipients($message);
    
    return redirect()->route('admin.messages.index')
        ->with('success', 'تم إرسال الرسالة بنجاح');
}
```

---

### 6. ⭐ تحديد الرسائل المهمة (Starred/Important)

**الميزة:** إمكانية تحديد رسالة كمهمة.

```php
// Migration
Schema::table('messages', function (Blueprint $table) {
    $table->boolean('is_important')->default(false)->after('is_read');
    $table->boolean('is_starred')->default(false)->after('is_important');
});

// في MessageController
public function toggleStar(Message $message)
{
    $this->authorize('view', $message);
    
    $message->update([
        'is_starred' => !$message->is_starred,
    ]);
    
    return response()->json([
        'success' => true,
        'is_starred' => $message->is_starred,
    ]);
}

public function toggleImportant(Message $message)
{
    $this->authorize('view', $message);
    
    $message->update([
        'is_important' => !$message->is_important,
    ]);
    
    return response()->json([
        'success' => true,
        'is_important' => $message->is_important,
    ]);
}
```

---

### 7. 📁 أرشفة الرسائل

**الميزة:** نقل الرسائل القديمة إلى الأرشيف.

```php
// Migration
Schema::table('messages', function (Blueprint $table) {
    $table->boolean('is_archived')->default(false)->after('is_starred');
    $table->timestamp('archived_at')->nullable()->after('is_archived');
});

// في MessageController
public function archive(Message $message)
{
    $this->authorize('view', $message);
    
    $message->update([
        'is_archived' => true,
        'archived_at' => now(),
    ]);
    
    return redirect()->route('admin.messages.index')
        ->with('success', 'تم أرشفة الرسالة بنجاح');
}

public function unarchive(Message $message)
{
    $this->authorize('view', $message);
    
    $message->update([
        'is_archived' => false,
        'archived_at' => null,
    ]);
    
    return redirect()->route('admin.messages.index')
        ->with('success', 'تم إلغاء أرشفة الرسالة بنجاح');
}

// فلترة الرسائل المؤرشفة
public function index(Request $request)
{
    // ... الكود الحالي ...
    
    // فلترة الأرشيف
    if ($request->filled('archived')) {
        $query->where('is_archived', $request->boolean('archived'));
    } else {
        // افتراضياً: لا نعرض المؤرشفة
        $query->where('is_archived', false);
    }
    
    // ...
}
```

---

### 8. 🔍 بحث متقدم

**الميزة:** بحث متقدم في الرسائل.

```php
// في MessageController@index
if ($request->filled('search')) {
    $search = $request->input('search');
    $query->where(function ($q) use ($search) {
        $q->where('subject', 'like', "%{$search}%")
          ->orWhere('body', 'like', "%{$search}%")
          ->orWhereHas('sender', function ($userQuery) use ($search) {
              $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
          })
          ->orWhereHas('receiver', function ($userQuery) use ($search) {
              $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
          });
    });
}

// فلترة بالتاريخ
if ($request->filled('date_from')) {
    $query->whereDate('created_at', '>=', $request->date_from);
}

if ($request->filled('date_to')) {
    $query->whereDate('created_at', '<=', $request->date_to);
}

// فلترة بالمرسل
if ($request->filled('sender_id')) {
    $query->where('sender_id', $request->sender_id);
}

// فلترة بالمستقبل
if ($request->filled('receiver_id')) {
    $query->where('receiver_id', $request->receiver_id);
}

// فلترة بالرسائل المهمة
if ($request->filled('important')) {
    $query->where('is_important', true);
}

// فلترة بالرسائل المميزة
if ($request->filled('starred')) {
    $query->where('is_starred', true);
}
```

---

### 9. 📊 إحصائيات الرسائل

**الميزة:** عرض إحصائيات عن الرسائل.

```php
// في MessageController
public function statistics()
{
    $user = auth()->user();
    
    $stats = [
        'total' => Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->count(),
        'unread' => Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count(),
        'sent' => Message::where('sender_id', $user->id)->count(),
        'received' => Message::where('receiver_id', $user->id)->count(),
        'important' => Message::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        })->where('is_important', true)->count(),
        'starred' => Message::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        })->where('is_starred', true)->count(),
        'archived' => Message::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('receiver_id', $user->id);
        })->where('is_archived', true)->count(),
    ];
    
    return view('admin.messages.statistics', compact('stats'));
}
```

---

### 10. 🔔 Real-time Notifications (WebSockets/Pusher)

**الميزة:** إشعارات فورية بدون تحديث الصفحة.

**استخدام Laravel Broadcasting:**

```php
// في MessageController@store
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

public function store(StoreMessageRequest $request)
{
    // ... إنشاء الرسالة ...
    
    $message = Message::create([...]);
    
    // إرسال إشعار فوري
    $recipients = $this->getRecipients($message);
    
    foreach ($recipients as $recipient) {
        broadcast(new MessageReceived($message, $recipient))->toOthers();
    }
    
    return redirect()->route('admin.messages.index')
        ->with('success', 'تم إرسال الرسالة بنجاح');
}

// Event Class
class MessageReceived implements ShouldBroadcast
{
    public function __construct(
        public Message $message,
        public User $recipient
    ) {}
    
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->recipient->id);
    }
    
    public function broadcastWith()
    {
        return [
            'message_id' => $this->message->id,
            'sender_name' => $this->message->sender->name,
            'subject' => $this->message->subject,
            'url' => route('admin.messages.show', $this->message),
        ];
    }
}
```

**JavaScript (Frontend):**

```javascript
// استخدام Laravel Echo
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// الاستماع للإشعارات
Echo.private(`user.${userId}`)
    .listen('.MessageReceived', (e) => {
        // عرض إشعار
        showNotification(e.message.subject, e.message.sender_name);
        
        // تحديث العداد
        updateUnreadCount();
        
        // إضافة صوت
        playNotificationSound();
    });
```

---

### 11. 📎 تحسين المرفقات

**الميزة:** دعم أنواع ملفات أكثر وتحسين الأمان.

```php
// في StoreMessageRequest
public function rules()
{
    return [
        // ... القواعد الحالية ...
        'attachment' => [
            'nullable',
            'file',
            'max:10240', // 10MB
            'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar',
        ],
    ];
}

// في MessageController@store
if ($request->hasFile('attachment')) {
    $file = $request->file('attachment');
    
    // التحقق من الحجم
    if ($file->getSize() > 10 * 1024 * 1024) {
        return back()->withErrors(['attachment' => 'حجم الملف يجب أن يكون أقل من 10MB']);
    }
    
    // التحقق من نوع الملف
    $allowedMimes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
    if (!in_array($file->getClientOriginalExtension(), $allowedMimes)) {
        return back()->withErrors(['attachment' => 'نوع الملف غير مدعوم']);
    }
    
    // تسمية الملف بشكل آمن
    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
    $attachmentPath = $file->storeAs('messages/attachments', $fileName, 'public');
}
```

---

### 12. 🔒 تحسين الأمان

**الميزة:** حماية إضافية للرسائل.

```php
// في MessagePolicy
public function view(User $user, Message $message): bool
{
    // التحقق من أن المستخدم يمكنه رؤية الرسالة
    return $message->canBeViewedBy($user);
}

// في Message Model
public function canBeViewedBy(User $user): bool
{
    // ... الكود الحالي ...
    
    // إضافة: التحقق من أن الرسالة لم يتم حذفها
    if ($user->id === $this->sender_id && $this->deleted_by_sender) {
        return false;
    }
    
    if ($user->id === $this->receiver_id && $this->deleted_by_receiver) {
        return false;
    }
    
    return true;
}
```

---

## 📝 ملخص التحسينات المقترحة

| # | التحسين | الأولوية | الصعوبة |
|---|---------|----------|----------|
| 1 | إشعارات فورية | عالية | منخفضة |
| 2 | إشعارات بريد إلكتروني | متوسطة | منخفضة |
| 3 | تحسين الأداء (Caching) | عالية | منخفضة |
| 4 | إعادة توجيه الرسائل | متوسطة | متوسطة |
| 5 | CC/BCC | متوسطة | متوسطة |
| 6 | تحديد الرسائل المهمة | منخفضة | منخفضة |
| 7 | أرشفة الرسائل | متوسطة | منخفضة |
| 8 | بحث متقدم | عالية | منخفضة |
| 9 | إحصائيات الرسائل | منخفضة | منخفضة |
| 10 | Real-time Notifications | عالية | عالية |
| 11 | تحسين المرفقات | متوسطة | منخفضة |
| 12 | تحسين الأمان | عالية | منخفضة |

---

## 🚀 خطة التنفيذ المقترحة

### المرحلة 1 (أولوية عالية):
1. ✅ إشعارات فورية عند استقبال رسالة
2. ✅ تحسين الأداء (Caching)
3. ✅ تحسين الأمان
4. ✅ بحث متقدم

### المرحلة 2 (أولوية متوسطة):
5. ✅ إعادة توجيه الرسائل
6. ✅ CC/BCC
7. ✅ أرشفة الرسائل
8. ✅ تحسين المرفقات

### المرحلة 3 (أولوية منخفضة):
9. ✅ تحديد الرسائل المهمة
10. ✅ إحصائيات الرسائل
11. ✅ إشعارات بريد إلكتروني
12. ✅ Real-time Notifications

---

**آخر تحديث:** 2024
