<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersionLog extends Model
{
    protected $fillable = [
        'version',
        'title',
        'description',
        'changes',
        'type',
        'release_date',
        'is_current',
        'released_by',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'release_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /**
     * المستخدم الذي أصدر هذا الإصدار
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * الحصول على الإصدار الحالي
     */
    public static function getCurrentVersion(): ?self
    {
        return static::where('is_current', true)->first();
    }

    /**
     * الحصول على رقم الإصدار الحالي
     */
    public static function getCurrentVersionNumber(): string
    {
        $current = static::getCurrentVersion();
        return $current ? $current->version : '1.0.0';
    }

    /**
     * تعيين هذا الإصدار كإصدار حالي
     */
    public function setAsCurrent(): void
    {
        // إلغاء تحديد الإصدار الحالي السابق
        static::where('is_current', true)->update(['is_current' => false]);
        
        // تعيين هذا الإصدار كحالي
        $this->update(['is_current' => true]);
    }

    /**
     * الحصول على لون نوع الإصدار
     */
    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            'major' => 'bg-danger',
            'minor' => 'bg-warning',
            'patch' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * الحصول على اسم نوع الإصدار بالعربي
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'major' => 'إصدار رئيسي',
            'minor' => 'إصدار فرعي',
            'patch' => 'تحديث',
            default => 'غير محدد',
        };
    }

    /**
     * الحصول على التغييرات المصنفة
     */
    public function getCategorizedChanges(): array
    {
        $changes = $this->changes ?? [];
        
        return [
            'features' => $changes['features'] ?? [],
            'fixes' => $changes['fixes'] ?? [],
            'improvements' => $changes['improvements'] ?? [],
            'security' => $changes['security'] ?? [],
        ];
    }

    /**
     * توليد رقم الإصدار التالي
     */
    public static function generateNextVersion(string $type = 'patch'): string
    {
        $current = static::getCurrentVersionNumber();
        $parts = explode('.', $current);
        
        $major = (int) ($parts[0] ?? 1);
        $minor = (int) ($parts[1] ?? 0);
        $patch = (int) ($parts[2] ?? 0);
        
        return match($type) {
            'major' => ($major + 1) . '.0.0',
            'minor' => $major . '.' . ($minor + 1) . '.0',
            'patch' => $major . '.' . $minor . '.' . ($patch + 1),
            default => $major . '.' . $minor . '.' . ($patch + 1),
        };
    }

    /**
     * Scope للإصدارات مرتبة حسب التاريخ
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('release_date', 'desc')->orderBy('id', 'desc');
    }
}
