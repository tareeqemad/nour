<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * نسخة محلية من طلب بوابة (e.services.gov.ps).
 * تُملأ عبر الأمر portal:sync-requests وتُستخدم للاستعلام دون استدعاء الـ API.
 */
class PortalRequest extends Model
{
    protected $fillable = [
        'app_no',
        'ser_no',
        'applicant_id',
        'applicant_name',
        'app_status',
        'desc_app_status',
        'status_note',
        'inserted_at',
        'changed_at',
        'pure_data',
        'synced_at',
    ];

    protected $casts = [
        'inserted_at' => 'datetime',
        'changed_at'  => 'datetime',
        'synced_at'   => 'datetime',
        'pure_data'   => 'array',
    ];

    /**
     * سجل المعالجة المرتبط (إن وُجد) — هل أُنشئ حساب لهذا الطلب.
     */
    public function processed(): HasOne
    {
        return $this->hasOne(ProcessedPortalRequest::class, 'app_no', 'app_no');
    }
}
