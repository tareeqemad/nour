# إعداد API لتطبيق الأندرويد

## متطلبات

1. Laravel 12
2. PHP 8.2+
3. Laravel Sanctum (للمصادقة)

## خطوات التثبيت

### 1. تثبيت Laravel Sanctum

```bash
composer require laravel/sanctum
```

### 2. نشر ملفات التكوين

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 3. تشغيل Migrations

```bash
php artisan migrate
```

### 4. تحديث User Model

تأكد من أن `app/Models/User.php` يستخدم `HasApiTokens` trait:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, ...;
}
```

### 5. تحديث config/sanctum.php (اختياري)

في ملف `config/sanctum.php`، تأكد من أن `stateful` domains تحتوي على domain التطبيق:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### 6. تحديث .env

أضف إلى ملف `.env`:

```
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

## اختبار API

### استخدام Postman أو cURL

#### 1. تسجيل الدخول

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "technician1",
    "password": "password123"
  }'
```

#### 2. استخدام Token

احفظ الـ token من الاستجابة واستخدمه في الطلبات التالية:

```bash
curl -X GET http://127.0.0.1:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### 3. مسح QR Code

```bash
curl -X POST http://127.0.0.1:8000/api/qr/scan \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "qr_code": "GEN-12345"
  }'
```

## ملاحظات

1. **CORS**: إذا كنت تواجه مشاكل CORS، تأكد من إعداد `config/cors.php` بشكل صحيح.

2. **Rate Limiting**: API محمي بـ rate limiting افتراضي. يمكن تخصيصه في `app/Http/Kernel.php`.

3. **Security**: تأكد من استخدام HTTPS في الإنتاج.

4. **Testing**: يمكنك استخدام `php artisan serve` لتشغيل الخادم محلياً.

## الأدوار المطلوبة

### للفني (Technician):
- يجب أن يكون المستخدم لديه role `technician`
- يجب أن يكون مرتبط بمشغل نشط (active)

### لدفاع مدني (Civil Defense):
- يمكن أن يكون role `civil_defense` (مخصص)
- أو `energy_authority`
- أو لديه permission `compliance_safety.create`

## استكشاف الأخطاء

### خطأ 401 (Unauthorized)
- تأكد من إرسال token في header: `Authorization: Bearer {token}`
- تأكد من أن token صالح وغير منتهي

### خطأ 403 (Forbidden)
- تأكد من أن المستخدم لديه الصلاحيات المطلوبة
- تأكد من أن المستخدم مرتبط بمشغل نشط (لل فني)

### خطأ 404 (Not Found)
- تأكد من أن QR code صحيح
- تأكد من أن المولد موجود في قاعدة البيانات
