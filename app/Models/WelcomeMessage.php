<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WelcomeMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'title',
        'subject',
        'body',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * الحصول على الرسائل النشطة مرتبة
     */
    public static function getActiveMessages(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * الحصول على رسالة حسب المفتاح
     */
    public static function getByKey(string $key): ?self
    {
        return static::where('key', $key)
            ->where('is_active', true)
            ->first();
    }
}
