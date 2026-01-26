<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\Operator;
use App\Models\Generator;
use App\Models\GenerationUnit;
use App\Enums\Role;
use App\Services\TaskService;
use App\Traits\SanitizesInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    use SanitizesInput;

    public function __construct(
        private TaskService $taskService
    ) {}

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
    public function store(StoreTaskRequest $request)
    {
        $user = Auth::user();
        
        try {
            $task = $this->taskService->createTask(
                $request->validated(),
                $user
            );

            return redirect()->route('admin.tasks.index')
                ->with('success', 'تم إنشاء المهمة وإرسال التكليف بنجاح');

        } catch (\Exception $e) {
            Log::error('Error creating task', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء المهمة. يرجى المحاولة مرة أخرى.');
        }
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

        try {
            $this->taskService->updateTaskStatus($task, $validated, $user);

            return redirect()->route('admin.tasks.show', $task)
                ->with('success', 'تم تحديث حالة المهمة بنجاح');

        } catch (\Exception $e) {
            Log::error('Error updating task status', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث حالة المهمة. يرجى المحاولة مرة أخرى.');
        }
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
