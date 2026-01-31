<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;

class Subscriber extends Model
{
    use HasFactory, SoftDeletes, TracksUser;

    protected $table = 'subscribers';

    protected $fillable = [
        'subscription_number',
        'subscriber_id_number',
        'subscriber_name',
        'phone',
        'address',
        'subscription_date',
        'subscription_category',
        'phase_type',
        'subscription_status',
        'meter_number',
        'service_type',
        'created_by',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'subscription_date' => 'date',
            'subscription_category' => 'integer',
            'phase_type' => 'integer',
            'subscription_status' => 'integer',
            'service_type' => 'integer',
        ];
    }

    /**
     * العلاقة مع وحدات التوليد (Many-to-Many)
     */
    public function generationUnits(): BelongsToMany
    {
        return $this->belongsToMany(GenerationUnit::class, 'subscriber_generation_unit')
            ->withTimestamps();
    }

    /**
     * العلاقة مع قراءات العدادات
     */
    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ السجل
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المستخدم الذي حدث السجل
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /**
     * Accessor للحصول على اسم تصنيف الاشتراك
     */
    public function getSubscriptionCategoryNameAttribute(): string
    {
        return match($this->subscription_category) {
            1 => 'منزلي',
            2 => 'تجاري',
            3 => 'خدماتي',
            4 => 'صناعي',
            default => 'غير محدد',
        };
    }

    /**
     * Accessor للحصول على اسم نوع الفاز
     */
    public function getPhaseTypeNameAttribute(): string
    {
        return match($this->phase_type) {
            1 => '1 فاز',
            2 => '3 فاز',
            default => 'غير محدد',
        };
    }

    /**
     * Accessor للحصول على اسم حالة الاشتراك
     */
    public function getSubscriptionStatusNameAttribute(): string
    {
        return match($this->subscription_status) {
            1 => 'نشط',
            2 => 'موقوف',
            3 => 'مغلق',
            default => 'غير محدد',
        };
    }

    /**
     * Accessor للحصول على اسم نوع الخدمة
     */
    public function getServiceTypeNameAttribute(): string
    {
        return match($this->service_type) {
            1 => 'مولد',
            2 => 'شمسي',
            3 => 'هجين',
            default => 'غير محدد',
        };
    }
}
