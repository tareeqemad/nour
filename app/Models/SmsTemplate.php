<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'name',
        'template',
        'max_length',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_length' => 'integer',
        ];
    }

    /**
     * الحصول على قالب حسب المفتاح
     */
    public static function getByKey(string $key): ?self
    {
        return static::where('key', $key)
            ->where('is_active', true)
            ->first();
    }

    /**
     * استبدال placeholders في القالب
     */
    public function render(array $data): string
    {
        $message = $this->template;
        
        // استبدال placeholders
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value ?? '', $message);
        }
        
        // إزالة أي placeholders غير مستبدلة
        $message = preg_replace('/\{[^}]+\}/', '', $message);
        
        // التأكد من أن الرسالة لا تتجاوز الحد الأقصى (160 حرف لرسائل SMS)
        if (mb_strlen($message) > $this->max_length) {
            $message = mb_substr($message, 0, $this->max_length - 3) . '...';
        }
        
        return trim($message);
    }
}
