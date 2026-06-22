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
     * المتغيرات المتاحة لكل قالب (مفتاح => قيمة مثال للمعاينة)
     *
     * @return array<string, array<string, string>>
     */
    public static function placeholders(): array
    {
        return [
            'user_credentials' => [
                'name' => 'أحمد محمد', 'role' => 'مشغل', 'username' => 'ahmad_op',
                'password' => 'A1b2C3d4', 'login_url' => 'https://gazarased.com/login',
            ],
            'password_reset' => [
                'name' => 'أحمد محمد', 'username' => 'ahmad_op',
                'password' => 'A1b2C3d4', 'login_url' => 'https://gazarased.com/login',
            ],
            'join_credentials' => [
                'site_name' => 'نور', 'username' => 'op_test',
                'password' => 'A1b2C3d4', 'login_url' => 'https://gazarased.com/login',
            ],
            'authorized_phone_welcome' => [
                'name' => 'أحمد', 'phone' => '0591234567',
                'site_name' => 'نور', 'join_url' => 'https://gazarased.com/join',
            ],
            'task_assigned' => [
                'task_type' => 'صيانة', 'operator_name' => 'مشغل غزة',
                'task_url' => 'https://gazarased.com/admin/tasks/5',
                'login_url' => 'https://gazarased.com/login',
            ],
        ];
    }

    /**
     * المتغيرات المتاحة لقالب محدد (حسب المفتاح)
     *
     * @return array<string, string>
     */
    public static function placeholdersFor(string $key): array
    {
        return static::placeholders()[$key] ?? [];
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
