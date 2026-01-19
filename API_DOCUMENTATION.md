# API Documentation - تطبيق الأندرويد

## نظرة عامة

هذا API مصمم لتطبيق الأندرويد الذي يسمح للمستخدمين (فنيين ودفاع مدني) بمسح QR codes للمولدات وإدخال البيانات.

## Base URL

```
http://127.0.0.1:8000/api
```

## Authentication

### تسجيل الدخول

**POST** `/api/login`

**Request Body:**
```json
{
    "username": "technician1",
    "password": "password123"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "تم تسجيل الدخول بنجاح.",
    "data": {
        "user": {
            "id": 1,
            "name": "أحمد محمد",
            "username": "technician1",
            "email": "technician@example.com",
            "role": "technician",
            "role_label": "فني",
            "is_technician": true,
            "is_civil_defense": false
        },
        "token": "1|xxxxxxxxxxxxx" // إذا كان Sanctum مثبت
    }
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "بيانات الدخول غير صحيحة."
}
```

### تسجيل الخروج

**POST** `/api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "تم تسجيل الخروج بنجاح."
}
```

### معلومات المستخدم الحالي

**GET** `/api/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "أحمد محمد",
            "username": "technician1",
            "email": "technician@example.com",
            "role": "technician",
            "role_label": "فني",
            "is_technician": true,
            "is_civil_defense": false
        }
    }
}
```

## QR Code Scanning

### مسح QR Code

**POST** `/api/qr/scan`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "qr_code": "GEN-12345"
}
```

**Response (Success - فني):**
```json
{
    "success": true,
    "data": {
        "generator": {
            "id": 1,
            "name": "مولد 1",
            "generator_number": "GEN-12345",
            "operator": {
                "id": 1,
                "name": "مشغل 1"
            },
            "status": "نشط"
        },
        "form_type": "maintenance",
        "can_access": true
    }
}
```

**Response (Success - دفاع مدني):**
```json
{
    "success": true,
    "data": {
        "generator": {
            "id": 1,
            "name": "مولد 1",
            "generator_number": "GEN-12345",
            "operator": {
                "id": 1,
                "name": "مشغل 1"
            },
            "status": "نشط"
        },
        "form_type": "compliance_safety",
        "can_access": true
    }
}
```

## Maintenance Records (للفني)

### الحصول على بيانات النموذج

**GET** `/api/maintenance/form-data/{generator_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "generator": {
            "id": 1,
            "name": "مولد 1",
            "generator_number": "GEN-12345",
            "operator": {
                "id": 1,
                "name": "مشغل 1"
            }
        },
        "maintenance_types": [
            {
                "id": 1,
                "label": "صيانة دورية",
                "code": "PERIODIC"
            },
            {
                "id": 2,
                "label": "صيانة طارئة",
                "code": "EMERGENCY"
            }
        ]
    }
}
```

### حفظ سجل صيانة

**POST** `/api/maintenance/store`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "generator_id": 1,
    "maintenance_type_id": 1,
    "next_maintenance_type_id": 1,
    "maintenance_date": "2024-01-15",
    "start_time": "08:00",
    "end_time": "12:00",
    "technician_name": "أحمد محمد",
    "work_performed": "تنظيف الفلاتر وتغيير الزيت",
    "parts_cost": 500.00,
    "labor_hours": 4.0,
    "labor_rate_per_hour": 50.00
}
```

**ملاحظات:**
- `downtime_hours` و `maintenance_cost` يتم حسابهما تلقائياً
- `downtime_hours` = الفرق بين `start_time` و `end_time`
- `maintenance_cost` = `parts_cost` + (`labor_hours` × `labor_rate_per_hour`)

**Response:**
```json
{
    "success": true,
    "message": "تم حفظ سجل الصيانة بنجاح.",
    "data": {
        "maintenance_record": {
            "id": 1,
            "generator_id": 1,
            "maintenance_date": "2024-01-15"
        }
    }
}
```

### قائمة سجلات الصيانة

**GET** `/api/maintenance/records`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `generator_id` (optional): تصفية حسب المولد
- `date_from` (optional): تاريخ البداية (Y-m-d)
- `date_to` (optional): تاريخ النهاية (Y-m-d)
- `per_page` (optional): عدد النتائج في الصفحة (default: 20)

**Response:**
```json
{
    "success": true,
    "data": {
        "records": [...],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 20,
            "total": 100
        }
    }
}
```

### عرض سجل صيانة محدد

**GET** `/api/maintenance/records/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

## Compliance Safety (لدفاع مدني)

### الحصول على بيانات النموذج

**GET** `/api/compliance-safety/form-data/{generator_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "generator": {
            "id": 1,
            "name": "مولد 1",
            "generator_number": "GEN-12345"
        },
        "operator": {
            "id": 1,
            "name": "مشغل 1"
        },
        "safety_certificate_statuses": [
            {
                "id": 1,
                "label": "صالح",
                "code": "VALID"
            },
            {
                "id": 2,
                "label": "منتهي",
                "code": "EXPIRED"
            }
        ]
    }
}
```

### حفظ سجل وقاية وسلامة

**POST** `/api/compliance-safety/store`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "generator_id": 1,
    "safety_certificate_status_id": 1,
    "last_inspection_date": "2024-01-15",
    "inspection_authority": "الدفاع المدني",
    "inspection_result": "جميع المتطلبات متوفرة",
    "violations": "لا توجد مخالفات"
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم حفظ سجل الوقاية والسلامة بنجاح.",
    "data": {
        "compliance_safety": {
            "id": 1,
            "operator_id": 1,
            "last_inspection_date": "2024-01-15"
        }
    }
}
```

### قائمة سجلات الوقاية والسلامة

**GET** `/api/compliance-safety/records`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `operator_id` (optional): تصفية حسب المشغل
- `date_from` (optional): تاريخ البداية (Y-m-d)
- `date_to` (optional): تاريخ النهاية (Y-m-d)
- `per_page` (optional): عدد النتائج في الصفحة (default: 20)

## Error Responses

جميع الأخطاء تعيد نفس التنسيق:

```json
{
    "success": false,
    "message": "رسالة الخطأ"
}
```

**Status Codes:**
- `200`: نجاح
- `201`: تم الإنشاء بنجاح
- `400`: خطأ في البيانات
- `401`: غير مصرح (لم يتم تسجيل الدخول)
- `403`: غير مصرح (لا توجد صلاحيات)
- `404`: غير موجود
- `500`: خطأ في الخادم

## ملاحظات مهمة

1. **Authentication**: إذا لم يكن Laravel Sanctum مثبت، يجب تثبيته:
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

2. **Roles**: 
   - **فني (Technician)**: يمكنه الوصول إلى Maintenance Records
   - **دفاع مدني (Civil Defense)**: يمكنه الوصول إلى Compliance Safety
   - يمكن إنشاء role مخصص باسم `civil_defense` أو استخدام role موجود مع permission `compliance_safety.create`

3. **QR Code Format**: 
   - QR Code قد يكون `generator_number` مباشرة
   - أو URL مثل: `http://example.com/qr/generator/GEN-12345`
   - API يستخرج `generator_number` تلقائياً

4. **Permissions**: 
   - الفني يجب أن يكون مرتبط بمشغل نشط
   - دفاع مدني يمكن أن يكون Energy Authority أو لديه permission `compliance_safety.create`
