<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Operator;
use App\Models\Generator;
use App\Models\GenerationUnit;
use App\Enums\Role;
use App\Traits\SanitizesInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    use SanitizesInput;

    /**
     * عرض قائمة المهام
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // تضمين المولدات المحذوفة soft delete في المهام
        $query = Task::with([
            'assignedTo',
            'assignedBy',
            'operator',
            'generationUnit',
            'generator' // العلاقة generator تستخدم withTrashed() بشكل افتراضي
        ]);

        // تصفية حسب نوع المستخدم
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            // يمكنهم رؤية جميع المهام
            if ($request->filled('assigned_to')) {
                $query->where('assigned_to', $request->assigned_to);
            }
        } elseif ($user->isTechnician() || $user->isCivilDefense()) {
            // الفني والدفاع المدني يشوفوا فقط المهام المكلفين بها
            $query->where('assigned_to', $user->id);
        } else {
            // غير مصرح له
            abort(403);
        }

        // تصفية حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // البحث - تنظيف المدخلات لمنع SQL Injection
        if ($request->filled('search')) {
            $search = $this->sanitizeSearchInput($request->input('search'));
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhereHas('operator', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('generator', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(20);

        // إحصائيات
        $stats = [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed' => Task::where('status', 'completed')->count(),
        ];

        // جلب الفنيين والدفاع المدني للفلترة
        $technicians = User::where('role', Role::Technician)->get();
        $civilDefense = User::where('role', Role::CivilDefense)->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.tasks.partials.tbody-rows', compact('tasks'))->render(),
                'pagination' => view('admin.tasks.partials.pagination', compact('tasks'))->render(),
                'count' => $tasks->total(),
                'stats' => $stats,
            ]);
        }

        return view('admin.tasks.index', compact('tasks', 'stats', 'technicians', 'civilDefense'));
    }

    /**
     * عرض نموذج إنشاء مهمة جديدة
     */
    public function create()
    {
        $user = Auth::user();
        
        // فقط SuperAdmin, Admin, EnergyAuthority يمكنهم إنشاء مهام
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isEnergyAuthority()) {
            abort(403);
        }

        // جلب الفنيين والدفاع المدني
        $technicians = User::where('role', Role::Technician)->orderBy('name')->get();
        $civilDefense = User::where('role', Role::CivilDefense)->orderBy('name')->get();

        // جلب المشغلين
        $operators = Operator::orderBy('name')->get();

        return view('admin.tasks.create', compact('technicians', 'civilDefense', 'operators'));
    }

    /**
     * الحصول على وحدات التوليد حسب المشغل (AJAX)
     */
    public function getGenerationUnits(Request $request, Operator $operator): JsonResponse
    {
        $generationUnits = $operator->generationUnits()->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $generationUnits->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'unit_code' => $unit->unit_code,
                ];
            }),
        ]);
    }

    /**
     * الحصول على المولدات حسب وحدة التوليد (AJAX)
     */
    public function getGeneratorsByGenerationUnit(Request $request, GenerationUnit $generationUnit): JsonResponse
    {
        $generators = $generationUnit->generators()->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $generators->map(function ($generator) {
                return [
                    'id' => $generator->id,
                    'name' => $generator->name,
                    'generator_number' => $generator->generator_number,
                ];
            }),
        ]);
    }

    /**
     * حفظ مهمة جديدة
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // فقط SuperAdmin, Admin, EnergyAuthority يمكنهم إنشاء مهام
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isEnergyAuthority()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:maintenance,safety_inspection',
            'assigned_to' => 'required|exists:users,id',
            'operator_id' => 'required|exists:operators,id',
            'generation_unit_id' => 'nullable|exists:generation_units,id',
            'generator_id' => 'nullable|exists:generators,id',
            'description' => 'required|string|max:1000',
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ], [
            'type.required' => 'نوع المهمة مطلوب',
            'assigned_to.required' => 'يجب اختيار المستخدم المكلف',
            'assigned_to.exists' => 'المستخدم المكلف غير موجود',
            'operator_id.required' => 'يجب اختيار المشغل',
            'operator_id.exists' => 'المشغل غير موجود',
            'description.required' => 'وصف المهمة مطلوب',
            'due_date.after' => 'تاريخ الاستحقاق يجب أن يكون في المستقبل',
        ]);

        // التحقق من أن المستخدم المكلف هو فني أو دفاع مدني
        $assignedUser = User::findOrFail($validated['assigned_to']);
        if (!$assignedUser->isTechnician() && !$assignedUser->isCivilDefense()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['assigned_to' => 'يجب اختيار فني أو دفاع مدني']);
        }

        // التحقق من أن نوع المهمة يطابق دور المستخدم
        if ($validated['type'] === 'maintenance' && !$assignedUser->isTechnician()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['type' => 'مهمة الصيانة يجب أن تُكلف لفني']);
        }

        if ($validated['type'] === 'safety_inspection' && !$assignedUser->isCivilDefense()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['type' => 'مهمة فحص السلامة يجب أن تُكلف لدفاع مدني']);
        }

        // إنشاء المهمة
        $task = Task::create([
            'type' => $validated['type'],
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => $user->id,
            'operator_id' => $validated['operator_id'],
            'generation_unit_id' => $validated['generation_unit_id'] ?? null,
            'generator_id' => $validated['generator_id'] ?? null,
            'status' => 'pending',
            'description' => $validated['description'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        // إرسال SMS
        $this->sendTaskSMS($task, $assignedUser);

        // إنشاء إشعار للمكلف
        $this->createTaskNotification($task, $assignedUser);

        // إشعار لسلطة الطاقة والسوبر ادمن والادمن
        $this->notifyTaskManagers($task, 'created');

        return redirect()->route('admin.tasks.index')
            ->with('success', 'تم إنشاء المهمة وإرسال التكليف بنجاح');
    }

    /**
     * عرض تفاصيل المهمة
     */
    public function show(Task $task)
    {
        $user = Auth::user();

        // التحقق من الصلاحيات
        if (!$this->canAccess($user, $task)) {
            abort(403);
        }

        $task->load([
            'assignedTo',
            'assignedBy',
            'operator',
            'generationUnit',
            'generator',
            'creator',
            'updater'
        ]);

        return view('admin.tasks.show', compact('task'));
    }

    /**
     * تحديث حالة المهمة
     */
    public function update(Request $request, Task $task)
    {
        $user = Auth::user();

        // التحقق من الصلاحيات
        if (!$this->canAccess($user, $task)) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $task->status;
        $task->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ? ($task->notes ? $task->notes . "\n\n" . $validated['notes'] : $validated['notes']) : $task->notes,
            'updated_by' => $user->id,
        ]);

        // إذا تم إكمال المهمة، تحديث تاريخ الإنجاز
        if ($validated['status'] === 'completed' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        // إنشاء إشعار للمكلف (إذا تغيرت الحالة)
        if ($oldStatus !== $validated['status']) {
            $this->createStatusChangeNotification($task, $oldStatus, $validated['status']);
            
            // إشعار لسلطة الطاقة والسوبر ادمن والادمن عند تغيير الحالة
            $this->notifyTaskManagers($task, 'status_changed', $oldStatus, $validated['status']);
        }

        return redirect()->route('admin.tasks.show', $task)
            ->with('success', 'تم تحديث حالة المهمة بنجاح');
    }

    /**
     * حذف مهمة
     */
    public function destroy(Task $task)
    {
        $user = Auth::user();

        // فقط SuperAdmin, Admin, EnergyAuthority يمكنهم حذف المهام
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isEnergyAuthority()) {
            abort(403);
        }

        $task->delete();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'تم حذف المهمة بنجاح');
    }

    /**
     * إرسال SMS للمهمة
     */
    private function sendTaskSMS(Task $task, User $assignedUser): void
    {
        if (!$assignedUser->phone) {
            Log::warning('Cannot send SMS: User has no phone number', [
                'user_id' => $assignedUser->id,
                'task_id' => $task->id,
            ]);
            return;
        }

        $operatorName = $task->operator->name;
        $generatorName = $task->generator ? $task->generator->name : 'غير محدد';
        $generationUnitName = $task->generationUnit ? $task->generationUnit->name : 'غير محدد';
        $taskType = $task->type_label;
        $loginUrl = url('/login');
        $taskUrl = url('/admin/tasks/' . $task->id);

        $message = "تم تكليفك بمهمة {$taskType}\n";
        $message .= "المشغل: {$operatorName}\n";
        if ($task->generationUnit) {
            $message .= "وحدة التوليد: {$generationUnitName}\n";
        }
        if ($task->generator) {
            $message .= "المولد: {$generatorName}\n";
        }
        $message .= "رابط المهمة: {$taskUrl}\n";
        $message .= "رابط الدخول: {$loginUrl}";

        // تقصير الرسالة إذا كانت طويلة
        if (mb_strlen($message) > 160) {
            $message = mb_substr($message, 0, 157) . '...';
        }

        try {
            $smsService = new \App\Services\HotSMSService();
            $result = $smsService->sendSMS($assignedUser->phone, $message, 2);

            if ($result['success']) {
                Log::info('Task SMS sent successfully', [
                    'task_id' => $task->id,
                    'user_id' => $assignedUser->id,
                    'phone' => $assignedUser->phone,
                ]);
            } else {
                Log::error('Failed to send task SMS', [
                    'task_id' => $task->id,
                    'user_id' => $assignedUser->id,
                    'phone' => $assignedUser->phone,
                    'error' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Task SMS service exception', [
                'task_id' => $task->id,
                'user_id' => $assignedUser->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * إنشاء إشعار للمهمة
     */
    private function createTaskNotification(Task $task, User $assignedUser): void
    {
        $operatorName = $task->operator->name;
        $generatorName = $task->generator ? $task->generator->name : 'غير محدد';
        $taskType = $task->type_label;
        $taskUrl = route('admin.tasks.show', $task);

        $title = "مهمة جديدة: {$taskType}";
        $message = "تم تكليفك بمهمة {$taskType} للمشغل {$operatorName}";
        if ($task->generator) {
            $message .= " - المولد: {$generatorName}";
        }

        \App\Models\Notification::createNotification(
            $assignedUser->id,
            'task_assigned',
            $title,
            $message,
            $taskUrl
        );
    }

    /**
     * إنشاء إشعار عند تغيير حالة المهمة
     */
    private function createStatusChangeNotification(Task $task, string $oldStatus, string $newStatus): void
    {
        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
        ];

        $oldStatusLabel = $statusLabels[$oldStatus] ?? $oldStatus;
        $newStatusLabel = $statusLabels[$newStatus] ?? $newStatus;

        // إشعار للمكلف
        $title = "تحديث حالة المهمة";
        $message = "تم تحديث حالة المهمة من {$oldStatusLabel} إلى {$newStatusLabel}";
        $taskUrl = route('admin.tasks.show', $task);

        \App\Models\Notification::createNotification(
            $task->assigned_to,
            'task_status_changed',
            $title,
            $message,
            $taskUrl
        );

        // إشعار للمكلف (SuperAdmin, Admin, EnergyAuthority)
        \App\Models\Notification::createNotification(
            $task->assigned_by,
            'task_status_changed',
            $title,
            "تم تحديث حالة المهمة المكلفة لـ {$task->assignedTo->name} من {$oldStatusLabel} إلى {$newStatusLabel}",
            $taskUrl
        );
    }

    /**
     * إشعار المديرين (سلطة الطاقة، السوبر ادمن، الادمن) عند حدوث شيء في المهام
     */
    private function notifyTaskManagers(Task $task, string $event, ?string $oldStatus = null, ?string $newStatus = null): void
    {
        $taskType = $task->type_label;
        $operatorName = $task->operator->name;
        $assignedUserName = $task->assignedTo->name;
        $taskUrl = route('admin.tasks.show', $task);

        if ($event === 'created') {
            $title = "مهمة جديدة: {$taskType}";
            $message = "تم تكليف {$assignedUserName} بمهمة {$taskType} للمشغل {$operatorName}";
        } elseif ($event === 'status_changed' && $oldStatus && $newStatus) {
            $statusLabels = [
                'pending' => 'قيد الانتظار',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'مكتملة',
                'cancelled' => 'ملغاة',
            ];
            $oldStatusLabel = $statusLabels[$oldStatus] ?? $oldStatus;
            $newStatusLabel = $statusLabels[$newStatus] ?? $newStatus;
            
            $title = "تحديث حالة المهمة";
            $message = "تم تحديث حالة مهمة {$taskType} للمشغل {$operatorName} (المكلف: {$assignedUserName}) من {$oldStatusLabel} إلى {$newStatusLabel}";
        } else {
            return;
        }

        \App\Models\Notification::notifyOperatorApprovers(
            'task_' . $event,
            $title,
            $message,
            $taskUrl
        );
    }

    /**
     * التحقق من إمكانية الوصول للمهمة
     */
    private function canAccess($user, Task $task): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return true;
        }

        if ($user->isTechnician() || $user->isCivilDefense()) {
            return $task->assigned_to === $user->id;
        }

        return false;
    }
}
