<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'assigned_to',
        'assigned_by',
        'operator_id',
        'generation_unit_id',
        'generator_id',
        'status',
        'description',
        'due_date',
        'completed_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * المستخدم المكلف (الفني أو الدفاع المدني)
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * المستخدم الذي كلف المهمة
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * المشغل
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * وحدة التوليد
     */
    public function generationUnit(): BelongsTo
    {
        return $this->belongsTo(GenerationUnit::class);
    }

    /**
     * المولد (بما في ذلك المحذوفة soft delete)
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class)->withTrashed();
    }

    /**
     * المستخدم الذي أنشأ المهمة
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * المستخدم الذي حدث المهمة
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * الحصول على نوع المهمة بالعربية
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'maintenance' => 'صيانة',
            'safety_inspection' => 'فحص سلامة',
            default => 'غير معروف',
        };
    }

    /**
     * الحصول على حالة المهمة بالعربية
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغاة',
            default => 'غير معروف',
        };
    }

    /**
     * التحقق من أن المهمة مكتملة
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * التحقق من أن المهمة قيد التنفيذ
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * التحقق من أن المهمة معلقة
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * إكمال المهمة
     */
    public function markAsCompleted(?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes ? ($this->notes ? $this->notes . "\n\n" . $notes : $notes) : $this->notes,
        ]);
    }

    /**
     * بدء المهمة
     */
    public function markAsInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
        ]);
    }

    /**
     * إلغاء المهمة
     */
    public function cancel(?string $notes = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $notes ? ($this->notes ? $this->notes . "\n\n" . $notes : $notes) : $this->notes,
        ]);
    }
}
