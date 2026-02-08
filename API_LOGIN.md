# دليل تسجيل الدخول عبر API للتطبيق المحمول

## إعداد Sanctum (مرة واحدة)

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

> **ملاحظة:** الـ User model يحتوي بالفعل على `HasApiTokens` - لا حاجة لتعديله.

---

## 1. تسجيل الدخول

**الرابط:** `POST /api/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "username": "اسم_المستخدم_أو_الإيميل",
  "password": "كلمة_المرور"
}
```

**ملاحظة:** يمكن استخدام `username` أو `email` في حقل `username` - النظام يكتشف تلقائياً.

**نجاح (200):**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح.",
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "username": "ahmed",
      "email": "ahmed@example.com",
      "role": "technician",
      "role_id": 5,
      "role_label": "فني",
      "role_type": "system",
      "is_technician": true,
      "is_civil_defense": false
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**فشل (401):**
```json
{
  "success": false,
  "message": "بيانات الدخول غير صحيحة."
}
```

**حساب معطل (403):**
```json
{
  "success": false,
  "message": "حسابك محظور/معطل. يرجى التواصل مع الإدارة."
}
```

---

## 2. استخدام الـ Token في الطلبات

كل طلب للـ API المحمي يحتاج إرسال الـ token:

**Header:**
```
Authorization: Bearer {token}
```

**مثال:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## 3. نقاط النهاية المحمية (تحتاج تسجيل دخول)

| الطريقة | الرابط | الوصف |
|---------|--------|-------|
| GET | `/api/user` | معلومات المستخدم الحالي |
| POST | `/api/logout` | تسجيل الخروج |
| POST | `/api/qr/scan` | مسح QR وفحص المولد |
| GET | `/api/maintenance/form-data/{generator}` | بيانات نموذج الصيانة (فني) |
| POST | `/api/maintenance/store` | حفظ سجل صيانة |
| GET | `/api/maintenance/records` | قائمة سجلات الصيانة |
| GET | `/api/compliance-safety/form-data/{generator}` | بيانات نموذج السلامة (دفاع مدني) |
| POST | `/api/compliance-safety/store` | حفظ سجل سلامة |
| GET | `/api/compliance-safety/records` | قائمة سجلات السلامة |

---

## 4. تسجيل الخروج

**الرابط:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح."
}
```

> يُفضّل استدعاء Logout من التطبيق عند الخروج لحذف الـ token من الخادم.

---

## 5. التخزين في التطبيق

1. عند تسجيل الدخول: احفظ `token` و`user` (مثلاً في Secure Storage / Keychain)
2. عند كل طلب: أضف `Authorization: Bearer {token}`
3. عند استلام 401: امسح البيانات وانتقل لشاشة تسجيل الدخول
4. عند تسجيل الخروج: استدعِ `/api/logout` ثم امسح الـ token محلياً

---

## 6. Base URL

استخدم رابط السيرفر حسب البيئة:

- **تطوير:** `http://localhost/api` أو `https://your-domain.test/api`
- **إنتاج:** `https://your-domain.com/api`
