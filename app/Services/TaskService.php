<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Services\HotSMSService;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    public function __construct(
        private HotSMSService $smsService
    ) {}

    /**
     * إنشاء مهمة جديدة
     *
     * @param array<string, mixed> $data
     * @param User $creator
     * @return Task
     */
    public function createTask(array $data, User $creator): Task
    {
        DB::beginTransaction();
        try {
            $assignedUser = User::findOrFail($data['assigned_to']);

            // إنشاء المهمة
            $task = Task::create([
                'type' => $data['type'],
                'assigned_to' => $data['assigned_to'],
                'assigned_by' => $creator->id,
                'operator_id' => $data['operator_id'],
                'generation_unit_id' => $data['generation_unit_id'] ?? null,
                'generator_id' => $data['generator_id'] ?? null,
                'status' => 'pending',
                'description' => $data['description'],
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator->id,
            ]);

            // إرسال SMS
            $this->sendTaskSMS($task, $assignedUser);

            // إنشاء إشعار للمكلف
            $this->createTaskNotification($task, $assignedUser);

            // إشعار لسلطة الطاقة والسوبر ادمن والادمن
            $this->notifyTaskManagers($task, 'created');

            DB::commit();

            return $task;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating task', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * تحديث حالة المهمة
     *
     * @param Task $task
     * @param array<string, mixed> $data
     * @param User $updater
     * @return Task
     */
    public function updateTaskStatus(Task $task, array $data, User $updater): Task
    {
        DB::beginTransaction();
        try {
            $oldStatus = $task->status;

            $task->update([
                'status' => $data['status'],
                'notes' => $data['notes'] 
                    ? ($task->notes ? $task->notes . "\n\n" . $data['notes'] : $data['notes']) 
                    : $task->notes,
                'updated_by' => $updater->id,
            ]);

            // إذا تم إكمال المهمة، تحديث تاريخ الإنجاز
            if ($data['status'] === 'completed' && !$task->completed_at) {
                $task->update(['completed_at' => now()]);
            }

            // إنشاء إشعار للمكلف (إذا تغيرت الحالة)
            if ($oldStatus !== $data['status']) {
                $this->createStatusChangeNotification($task, $oldStatus, $data['status']);
                $this->notifyTaskManagers($task, 'status_changed', $oldStatus, $data['status']);
            }

            DB::commit();

            return $task->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating task status', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
            ]);
            throw $e;
        }
    }

    /**
     * إرسال SMS للمهمة
     *
     * @param Task $task
     * @param User $assignedUser
     * @return void
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

        $task->load(['operator', 'generator', 'generationUnit']);

        $operatorName = $task->operator->name ?? 'غير محدد';
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
            $result = $this->smsService->sendSMS($assignedUser->phone, $message, 2);

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
                    'error' => $result['message'] ?? 'Unknown error',
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
     *
     * @param Task $task
     * @param User $assignedUser
     * @return void
     */
    private function createTaskNotification(Task $task, User $assignedUser): void
    {
        $task->load(['operator', 'generator']);

        $operatorName = $task->operator->name ?? 'غير محدد';
        $generatorName = $task->generator ? $task->generator->name : 'غير محدد';
        $taskType = $task->type_label;
        $taskUrl = route('admin.tasks.show', $task);

        $title = "مهمة جديدة: {$taskType}";
        $message = "تم تكليفك بمهمة {$taskType} للمشغل {$operatorName}";
        if ($task->generator) {
            $message .= " - المولد: {$generatorName}";
        }

        Notification::createNotification(
            $assignedUser->id,
            'task_assigned',
            $title,
            $message,
            $taskUrl
        );
    }

    /**
     * إنشاء إشعار لتغيير حالة المهمة
     *
     * @param Task $task
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    private function createStatusChangeNotification(Task $task, string $oldStatus, string $newStatus): void
    {
        $task->load('assignedTo');

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

        Notification::createNotification(
            $task->assigned_to,
            'task_status_changed',
            $title,
            $message,
            $taskUrl
        );

        // إشعار للمكلف (SuperAdmin, Admin, EnergyAuthority)
        Notification::createNotification(
            $task->assigned_by,
            'task_status_changed',
            $title,
            "تم تحديث حالة المهمة المكلفة لـ {$task->assignedTo->name} من {$oldStatusLabel} إلى {$newStatusLabel}",
            $taskUrl
        );
    }

    /**
     * إشعار لسلطة الطاقة والسوبر ادمن والادمن
     *
     * @param Task $task
     * @param string $event
     * @param string|null $oldStatus
     * @param string|null $newStatus
     * @return void
     */
    private function notifyTaskManagers(Task $task, string $event, ?string $oldStatus = null, ?string $newStatus = null): void
    {
        $task->load(['operator', 'assignedTo']);

        $taskType = $task->type_label;
        $operatorName = $task->operator->name ?? 'غير محدد';
        $assignedUserName = $task->assignedTo->name ?? 'غير محدد';
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

        Notification::notifyOperatorApprovers(
            'task_' . $event,
            $title,
            $message,
            $taskUrl
        );
    }
}
