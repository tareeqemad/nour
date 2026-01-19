<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMessageRequest;
use App\Models\Message;
use App\Models\Operator;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * ============================================
 * MessageController - كنترولر إدارة الرسائل
 * ============================================
 * 
 * هذا الكنترولر مسؤول عن إدارة جميع الرسائل في النظام
 * 
 * الأدوار الرئيسية:
 * ------------------
 * 1. السوبر أدمن (SuperAdmin):
 *    - يرى جميع الرسائل في النظام
 *    - يمكنه إرسال رسائل لجميع المشغلين أو لمشغل معين
 *    - لديه كنترول كامل على الرسائل
 * 
 * 2. سلطة الطاقة (Admin) - دور رئيسي في النظام:
 *    - يرى جميع الرسائل في النظام
 *    - يمكنه إرسال رسائل لجميع المشغلين أو لمشغل معين
 *    - لديه كنترول كامل على الرسائل
 *    - يمكنه التواصل مع جميع المشغلين والموظفين
 * 
 * 3. المشغل (CompanyOwner):
 *    - يرى الرسائل المرسلة منه
 *    - يرى الرسائل الموجهة له (من أدمن أو مشغلين آخرين)
 *    - يرى الرسائل الموجهة لموظفيه
 *    - يمكنه إرسال رسائل لموظفيه أو لمشغلين آخرين
 * 
 * 4. الموظف/الفني (Employee/Technician):
 *    - يرى الرسائل الموجهة له
 *    - يرى الرسائل الموجهة لجميع موظفي المشغل
 *    - يمكنه إرسال رسائل للمشغل
 * 
 * ============================================
 */
class MessageController extends Controller
{
    /**
     * عرض قائمة الرسائل
     * 
     * ============================================
     * سياسة عرض الرسائل حسب الدور:
     * ============================================
     * 
     * 1. السوبر أدمن (SuperAdmin):
     *    - يرى جميع الرسائل في النظام
     * 
     * 2. سلطة الطاقة (EnergyAuthority):
     *    - يرى جميع الرسائل في النظام
     *    - يمكنه إرسال رسائل لجميع المشغلين أو لمشغل معين
     *    - لديه كنترول كامل على الرسائل
     * 
     * 3. المشغل (CompanyOwner):
     *    - يرى الرسائل المرسلة منه
     *    - يرى الرسائل الموجهة له (من أدمن أو مشغلين آخرين)
     *    - يرى الرسائل الموجهة لموظفيه
     *    - يمكنه إرسال رسائل لموظفيه أو لمشغلين آخرين
     * 
     * 4. الموظف/الفني (Employee/Technician):
     *    - يرى الرسائل الموجهة له
     *    - يرى الرسائل الموجهة لجميع موظفي المشغل
     *    - يمكنه إرسال رسائل للمشغل
     * 
     * ============================================
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Message::class);

        $user = auth()->user();
        $query = Message::with(['sender', 'receiver', 'operator']);

        // Filter messages: Each user can only see messages they sent or received
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where(function ($q) use ($user, $operator) {
                    // Messages sent by this user
                    $q->where('sender_id', $user->id)
                      // Messages received by this user (including welcome messages)
                      ->orWhere('receiver_id', $user->id)
                      // Messages sent to this operator (from admin/energy authority) - even if receiver_id is set, this ensures operator messages are visible
                      ->orWhere(function ($subQ) use ($operator) {
                          $subQ->where('type', 'admin_to_operator')
                               ->where('operator_id', $operator->id);
                      })
                      // Messages broadcast to all operators
                      ->orWhere(function ($subQ) {
                          $subQ->where('type', 'admin_to_all')
                               ->whereNull('operator_id');
                      })
                      // Messages broadcast to all staff of this operator
                      ->orWhere(function ($subQ) use ($operator) {
                          $subQ->where('type', 'operator_to_staff')
                               ->where('operator_id', $operator->id);
                      });
                });
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
                });
            }
        } elseif ($user->hasOperatorLinkedCustomRole()) {
            // Users with custom roles linked to operator
            $operatorId = $user->roleModel->operator_id;
            $query->where(function ($q) use ($user, $operatorId) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id)
                  ->orWhere(function ($subQ) use ($operatorId) {
                      // Messages broadcast to all staff of this operator
                      $subQ->where('type', 'operator_to_staff')
                           ->where('operator_id', $operatorId);
                  });
            });
        } else {
            // Regular users (or users with general custom roles): only sent/received messages
            $query->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });
        }
        // Note: SuperAdmin and EnergyAuthority can only see their own messages (sent/received)

        // فلترة بالبحث المتقدم
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

        // فلترة بالتاريخ من
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // فلترة بالتاريخ إلى
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

        // فلترة بنوع الرسالة
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // فلترة بالحالة (مقروء/غير مقروء)
        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // فلترة بالرسائل المهمة
        if ($request->filled('important')) {
            $query->where('is_important', true);
        }

        // فلترة بالرسائل المميزة
        if ($request->filled('starred')) {
            $query->where('is_starred', true);
        }

        // فلترة بالأرشيف
        if ($request->filled('archived')) {
            $query->where('is_archived', $request->boolean('archived'));
        } else {
            // افتراضياً: لا نعرض المؤرشفة
            $query->where('is_archived', false);
        }

        // تصفية الرسائل المحذوفة: لا نعرض الرسائل التي حذفها المستخدم
        $query->where(function ($q) use ($user) {
            // الرسائل المرسلة من هذا المستخدم: لا نعرضها إذا حذفها المرسل
            $q->where(function ($subQ) use ($user) {
                $subQ->where('sender_id', '!=', $user->id)
                     ->orWhere('deleted_by_sender', false);
            })
            // الرسائل المستقبلة من قبل هذا المستخدم: لا نعرضها إذا حذفها المستقبل
            ->where(function ($subQ) use ($user) {
                $subQ->whereNull('receiver_id')
                     ->orWhere('receiver_id', '!=', $user->id)
                     ->orWhere('deleted_by_receiver', false);
            });
        });

        // AJAX request
        if ($request->ajax() || $request->has('ajax')) {
            $messages = $query->orderBy('created_at', 'desc')->paginate(20);
            
            $html = view('admin.messages.partials.tbody-rows', ['messages' => $messages])->render();
            $pagination = '';
            if ($messages->hasPages()) {
                $pagination = view('admin.messages.partials.pagination', ['messages' => $messages])->render();
            }
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $messages->total(),
            ]);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get users and operators for creating messages
        $users = collect();
        $operators = collect();

        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            // Get all users with custom roles (excluding system roles)
            $users = User::whereHas('roleModel', function ($q) {
                $q->where('is_system', false);
            })->orWhere('role', Role::CompanyOwner)
              ->orderBy('name')
              ->get(['id', 'name', 'username', 'role', 'role_id']);
            $operators = Operator::orderBy('name')->get(['id', 'name']);
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // Get staff users (custom roles linked to this operator)
                $users = User::whereHas('roleModel', function ($q) use ($operator) {
                    $q->where('is_system', false)
                      ->where('operator_id', $operator->id);
                })->orderBy('name')
                  ->get(['id', 'name', 'username', 'role', 'role_id']);
                
                // Other operators
                $operators = Operator::where('id', '!=', $operator->id)
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
        } else {
            // Regular users with custom roles can only message their operator owner
            if ($user->hasOperatorLinkedCustomRole()) {
                $operator = $user->roleModel->operator;
                if ($operator && $operator->owner_id) {
                    $users = User::where('id', $operator->owner_id)->get(['id', 'name', 'username', 'role', 'role_id']);
                }
            }
        }

        return view('admin.messages.index', compact('messages', 'users', 'operators'));
    }

    /**
     * Show the form for creating a new message.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Message::class);

        $user = auth()->user();
        $users = collect();
        $operators = collect();

        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            // Get all users with custom roles (excluding system roles)
            $users = User::whereHas('roleModel', function ($q) {
                $q->where('is_system', false);
            })->orWhere('role', Role::CompanyOwner)
              ->orderBy('name')
              ->get(['id', 'name', 'username', 'role', 'role_id']);
            $operators = Operator::orderBy('name')->get(['id', 'name']);
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // Get staff users (custom roles linked to this operator)
                $users = User::whereHas('roleModel', function ($q) use ($operator) {
                    $q->where('is_system', false)
                      ->where('operator_id', $operator->id);
                })->orderBy('name')
                  ->get(['id', 'name', 'username', 'role', 'role_id']);
                
                $operators = Operator::where('id', '!=', $operator->id)
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
        } else {
            // Regular users with custom roles can only message their operator owner
            if ($user->hasOperatorLinkedCustomRole()) {
                $operator = $user->roleModel->operator;
                if ($operator && $operator->owner_id) {
                    $users = User::where('id', $operator->owner_id)->get(['id', 'name', 'username', 'role', 'role_id']);
                }
            }
        }

        return view('admin.messages.create', compact('users', 'operators'));
    }

    /**
     * إنشاء رسالة جديدة
     * 
     * ============================================
     * أنواع الرسائل:
     * ============================================
     * 
     * 1. admin_to_all: رسالة من أدمن/سوبر أدمن لجميع المشغلين
     * 2. admin_to_operator: رسالة من أدمن/سوبر أدمن لمشغل معين
     * 3. operator_to_operator: رسالة من مشغل لمشغل آخر
     * 4. operator_to_staff: رسالة من مشغل لموظفيه
     * 5. user_to_user: رسالة من مستخدم لمستخدم آخر
     * 
     * ============================================
     */
    public function store(StoreMessageRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Message::class);

        $user = auth()->user();
        $data = $request->validated();

        // تحديد نوع الرسالة حسب المرسل والمستقبل
        $type = 'operator_to_operator';
        
        // السوبر أدمن وسلطة الطاقة (EnergyAuthority): يمكنهما إرسال رسائل لجميع المشغلين أو لمشغل معين
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            if ($data['send_to'] === 'all_operators') {
                $type = 'admin_to_all';
                $data['operator_id'] = null;
                $data['receiver_id'] = null;
            } elseif (isset($data['operator_id'])) {
                $type = 'admin_to_operator';
                $data['receiver_id'] = null;
            } elseif (isset($data['receiver_id'])) {
                $type = 'operator_to_operator';
                $data['operator_id'] = null;
            }
        } elseif ($user->isCompanyOwner()) {
            if ($data['send_to'] === 'my_staff') {
                $type = 'operator_to_staff';
                $operator = $user->ownedOperators()->first();
                $data['operator_id'] = $operator?->id;
                $data['receiver_id'] = null;
            } elseif (isset($data['operator_id'])) {
                $type = 'operator_to_operator';
                $data['receiver_id'] = null;
            } elseif (isset($data['receiver_id'])) {
                $type = 'operator_to_operator';
                $data['operator_id'] = null;
            }
        }

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('messages/attachments', 'public');
        }

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $data['receiver_id'] ?? null,
            'operator_id' => $data['operator_id'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'attachment' => $attachmentPath,
            'type' => $type,
        ]);

        // إرسال نسخة (CC)
        if ($request->filled('cc')) {
            foreach ($request->cc as $userId) {
                if ($userId != $message->receiver_id) {
                    Message::create([
                        'sender_id' => $user->id,
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
        }

        // إرسال نسخة مخفية (BCC)
        if ($request->filled('bcc')) {
            foreach ($request->bcc as $userId) {
                if ($userId != $message->receiver_id && !in_array($userId, $request->cc ?? [])) {
                    Message::create([
                        'sender_id' => $user->id,
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
        }

        // إرسال إشعارات للمستقبلين
        $this->notifyRecipients($message);
        
        // إرسال إشعارات لـ CC/BCC
        if ($request->filled('cc')) {
            foreach ($request->cc as $userId) {
                $ccMessage = Message::where('original_message_id', $message->id)
                    ->where('receiver_id', $userId)
                    ->where('is_cc', true)
                    ->first();
                if ($ccMessage) {
                    $this->notifyRecipients($ccMessage);
                }
            }
        }
        if ($request->filled('bcc')) {
            foreach ($request->bcc as $userId) {
                $bccMessage = Message::where('original_message_id', $message->id)
                    ->where('receiver_id', $userId)
                    ->where('is_bcc', true)
                    ->first();
                if ($bccMessage) {
                    $this->notifyRecipients($bccMessage);
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الرسالة بنجاح',
            ]);
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'تم إرسال الرسالة بنجاح')
            ->with('message_sent', true); // Flag for JavaScript event
    }

    /**
     * إرسال إشعارات للمستقبلين عند استقبال رسالة جديدة
     */
    private function notifyRecipients(Message $message): void
    {
        $recipients = $this->getRecipients($message);
        
        foreach ($recipients as $recipient) {
            if ($recipient && $recipient->id !== $message->sender_id) {
                \App\Models\Notification::createNotification(
                    $recipient->id,
                    'message_received',
                    'رسالة جديدة',
                    "لديك رسالة جديدة من: {$message->sender->name}",
                    route('admin.messages.show', $message)
                );
                
                // مسح الـ Cache لعداد الرسائل غير المقروءة
                \Illuminate\Support\Facades\Cache::forget("user_{$recipient->id}_unread_messages_count");
            }
        }
    }

    /**
     * الحصول على قائمة المستقبلين حسب نوع الرسالة
     */
    private function getRecipients(Message $message): \Illuminate\Support\Collection
    {
        $recipients = collect();
        
        switch ($message->type) {
            case 'admin_to_all':
                // جميع المشغلين
                $recipients = \App\Models\User::where('role', \App\Enums\Role::CompanyOwner)->get();
                break;
                
            case 'admin_to_operator':
                // المشغل المحدد
                if ($message->operator_id) {
                    $operator = \App\Models\Operator::find($message->operator_id);
                    if ($operator && $operator->owner_id) {
                        $owner = \App\Models\User::find($operator->owner_id);
                        if ($owner) {
                            $recipients->push($owner);
                        }
                    }
                }
                break;
                
            case 'operator_to_staff':
                // جميع موظفي المشغل
                if ($message->operator_id) {
                    $operator = \App\Models\Operator::find($message->operator_id);
                    if ($operator) {
                        $recipients = $operator->users()
                            ->whereIn('role', [\App\Enums\Role::Employee, \App\Enums\Role::Technician])
                            ->get();
                    }
                }
                break;
                
            case 'user_to_user':
            case 'operator_to_operator':
                // المستخدم المحدد
                if ($message->receiver_id) {
                    $receiver = \App\Models\User::find($message->receiver_id);
                    if ($receiver) {
                        $recipients->push($receiver);
                    }
                }
                break;
        }
        
        return $recipients->filter();
    }

    /**
     * Display the specified message.
     */
    public function show(Message $message): View
    {
        $this->authorize('view', $message);

        // تحديد الرسالة كمقروءة
        $user = auth()->user();
        if (!$message->is_read && $message->receiver_id === $user->id) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            
            // مسح الـ Cache لعداد الرسائل غير المقروءة
            \Illuminate\Support\Facades\Cache::forget("user_{$user->id}_unread_messages_count");
        }

        $message->load(['sender', 'receiver', 'operator']);

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message): JsonResponse
    {
        $this->authorize('view', $message);

        $user = auth()->user();
        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            
            // مسح الـ Cache لعداد الرسائل غير المقروءة
            \Illuminate\Support\Facades\Cache::forget("user_{$user->id}_unread_messages_count");
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete the specified message.
     * 
     * السلوك:
     * - إذا كان المستخدم هو المرسل: نضع deleted_by_sender = true
     * - إذا كان المستخدم هو المستقبل: نضع deleted_by_receiver = true
     * - إذا كان كلا الطرفين قد حذفا الرسالة: نحذفها نهائياً (soft delete)
     */
    public function destroy(Message $message): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $message);

        $user = auth()->user();
        $isSender = $message->sender_id === $user->id;
        $isReceiver = $message->receiver_id === $user->id;

        // تحديد من يحذف الرسالة
        if ($isSender) {
            // المرسل يحذف الرسالة
            $message->update(['deleted_by_sender' => true]);
        } elseif ($isReceiver) {
            // المستقبل يحذف الرسالة
            $message->update(['deleted_by_receiver' => true]);
        } else {
            // إذا لم يكن المستخدم هو المرسل أو المستقبل (مثل admin يرى جميع الرسائل)
            // نستخدم soft delete العادي
            $message->delete();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف الرسالة بنجاح',
                ]);
            }

            return redirect()->route('admin.messages.index')
                ->with('success', 'تم حذف الرسالة بنجاح');
        }

        // التحقق: إذا كان كلا الطرفين قد حذفا الرسالة، نحذفها نهائياً
        if ($message->deleted_by_sender && $message->deleted_by_receiver) {
            // Delete attachment file if exists
            if ($message->attachment && Storage::disk('public')->exists($message->attachment)) {
                Storage::disk('public')->delete($message->attachment);
            }
            $message->delete();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الرسالة بنجاح',
            ]);
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'تم حذف الرسالة بنجاح');
    }

    /**
     * Get unread messages count (AJAX).
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = auth()->user();
        
        // استخدام Cache لتحسين الأداء
        $cacheKey = "user_{$user->id}_unread_messages_count";
        
        $count = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(1), function () use ($user) {
            return Message::where(function ($q) use ($user) {
            if ($user->isCompanyOwner()) {
                $operator = $user->ownedOperators()->first();
                if ($operator) {
                    $q->where(function ($subQ) use ($user, $operator) {
                        $subQ->where('receiver_id', $user->id)
                             ->orWhere(function ($q2) use ($operator) {
                                 $q2->where('type', 'admin_to_operator')
                                    ->where('operator_id', $operator->id)
                                    ->where('is_read', false);
                             })
                             ->orWhere(function ($q2) use ($operator) {
                                 $q2->where('type', 'admin_to_all')
                                    ->whereNull('operator_id')
                                    ->where('is_read', false);
                             })
                             ->orWhere(function ($q2) use ($operator) {
                                 $q2->where('type', 'operator_to_staff')
                                    ->where('operator_id', $operator->id)
                                    ->where('is_read', false);
                             });
                    });
                } else {
                    $q->where('receiver_id', $user->id);
                }
            } elseif ($user->hasOperatorLinkedCustomRole()) {
                $operatorId = $user->roleModel->operator_id;
                $q->where(function ($subQ) use ($user, $operatorId) {
                    $subQ->where('receiver_id', $user->id)
                         ->orWhere(function ($q2) use ($operatorId) {
                             $q2->where('type', 'operator_to_staff')
                                ->where('operator_id', $operatorId)
                                ->where('is_read', false);
                         });
                });
            } else {
                $q->where('receiver_id', $user->id);
            }
        })->where('is_read', false)
          ->where('sender_id', '!=', $user->id)
          ->where(function ($q) use ($user) {
              // الرسائل المستقبلة: لا نعرضها إذا حذفها المستقبل
              $q->whereNull('receiver_id')
                ->orWhere('receiver_id', '!=', $user->id)
                ->orWhere('deleted_by_receiver', false);
          })
          ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent messages (AJAX) for dropdown.
     */
    public function getRecentMessages(): JsonResponse
    {
        $user = auth()->user();
        
        $messages = Message::with(['sender', 'receiver', 'operator'])
            ->where(function ($q) use ($user) {
                if ($user->isCompanyOwner()) {
                    $operator = $user->ownedOperators()->first();
                    if ($operator) {
                        $q->where(function ($subQ) use ($user, $operator) {
                            $subQ->where('receiver_id', $user->id)
                                 ->orWhere(function ($q2) use ($operator) {
                                     $q2->where('type', 'admin_to_operator')
                                        ->where('operator_id', $operator->id);
                                 })
                                 ->orWhere(function ($q2) use ($operator) {
                                     $q2->where('type', 'admin_to_all')
                                        ->whereNull('operator_id');
                                 })
                                 ->orWhere(function ($q2) use ($operator) {
                                     $q2->where('type', 'operator_to_staff')
                                        ->where('operator_id', $operator->id);
                                 });
                        });
                    } else {
                        $q->where('receiver_id', $user->id);
                    }
                } elseif ($user->hasOperatorLinkedCustomRole()) {
                    $operatorId = $user->roleModel->operator_id;
                    $q->where(function ($subQ) use ($user, $operatorId) {
                        $subQ->where('receiver_id', $user->id)
                             ->orWhere(function ($q2) use ($operatorId) {
                                 $q2->where('type', 'operator_to_staff')
                                    ->where('operator_id', $operatorId);
                             });
                    });
                } else {
                    $q->where('receiver_id', $user->id);
                }
            })
            ->where('sender_id', '!=', $user->id)
            ->where(function ($q) use ($user) {
                // الرسائل المستقبلة: لا نعرضها إذا حذفها المستقبل
                $q->whereNull('receiver_id')
                  ->orWhere('receiver_id', '!=', $user->id)
                  ->orWhere('deleted_by_receiver', false);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['messages' => $messages]);
    }

    /**
     * إعادة توجيه رسالة
     */
    public function forward(Request $request, Message $message): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $message);

        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string|max:5000',
        ]);

        // نسخ المرفق إذا كان موجوداً
        $attachmentPath = null;
        if ($message->attachment && \Illuminate\Support\Facades\Storage::disk('public')->exists($message->attachment)) {
            $extension = pathinfo($message->attachment, PATHINFO_EXTENSION);
            $newFileName = 'forwarded_' . time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $extension;
            $attachmentPath = 'messages/attachments/' . $newFileName;
            \Illuminate\Support\Facades\Storage::disk('public')->copy($message->attachment, $attachmentPath);
        }

        // إنشاء رسالة جديدة
        $forwardedMessage = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $validated['receiver_id'],
            'subject' => $validated['subject'] ?? 'Fwd: ' . $message->subject,
            'body' => ($validated['body'] ?? '') . "\n\n--- الرسالة الأصلية ---\n" . $message->body,
            'attachment' => $attachmentPath,
            'type' => 'user_to_user',
            'forwarded_from_id' => $message->id,
        ]);

        // إشعار المستقبل
        $this->notifyRecipients($forwardedMessage);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إعادة توجيه الرسالة بنجاح',
            ]);
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'تم إعادة توجيه الرسالة بنجاح');
    }

    /**
     * تحديد/إلغاء تحديد الرسالة كمميزة
     */
    public function toggleStar(Message $message): JsonResponse
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

    /**
     * تحديد/إلغاء تحديد الرسالة كمهمة
     */
    public function toggleImportant(Message $message): JsonResponse
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

    /**
     * أرشفة الرسالة
     */
    public function archive(Message $message): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $message);

        $message->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم أرشفة الرسالة بنجاح',
            ]);
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'تم أرشفة الرسالة بنجاح');
    }

    /**
     * إلغاء أرشفة الرسالة
     */
    public function unarchive(Message $message): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $message);

        $message->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء أرشفة الرسالة بنجاح',
            ]);
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'تم إلغاء أرشفة الرسالة بنجاح');
    }
}
