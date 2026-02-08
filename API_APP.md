# API التطبيق المحمول - حسب الدور

## الصلاحيات حسب الدور

| الدور | الوحدة | العرض (GET) | إنشاء (POST) | تحديث (PUT) |
|-------|--------|-------------|--------------|-------------|
| **technician** (فني) | maintenance-records | ✅ | ✅ | ✅ |
| **civil_defense** (دفاع مدني) | compliance-safeties | ✅ | ✅ | ✅ |

---

## 1. المصدر: مسح QR Code

**جميع بيانات المولد والمشغل تأتي من مسح الـ QR:**

```http
GET /api/generators/qr-data/{code}
```

أو عند مسح QR الذي فيه رابط:
```http
GET /qr/generator/{code}?format=json
```

**الاستجابة تحتوي:**
- `data.generator` → id, name, generator_number, operator_id, generation_unit_id
- `data.operator` → id, name
- `data.generation_unit` → id, name, unit_code

---

## 2. فني (technician) – سجلات الصيانة

### بيانات النموذج (قبل إنشاء/تحديث)
```http
GET /api/maintenance/form-data/{generator}
Authorization: Bearer {token}
```

### إنشاء سجل صيانة
```http
POST /api/maintenance/store
Authorization: Bearer {token}
Content-Type: application/json

{
  "generator_id": 1,
  "maintenance_type_id": 5,
  "next_maintenance_type_id": 6,
  "maintenance_date": "2025-02-08",
  "start_time": "09:00",
  "end_time": "11:30",
  "technician_name": "أحمد",
  "work_performed": "صيانة دورية",
  "parts_cost": 500,
  "labor_hours": 2,
  "labor_rate_per_hour": 100
}
```

### قائمة السجلات
```http
GET /api/maintenance/records?generator_id=1&date_from=2025-01-01&date_to=2025-02-08
Authorization: Bearer {token}
```

### عرض سجل محدد
```http
GET /api/maintenance/records/{id}
Authorization: Bearer {token}
```

### تحديث سجل
```http
PUT /api/maintenance/records/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "generator_id": 1,
  "maintenance_type_id": 5,
  "maintenance_date": "2025-02-08",
  ...
}
```

---

## 3. دفاع مدني (civil_defense) – سجلات الوقاية والسلامة

### بيانات النموذج
```http
GET /api/compliance-safety/form-data/{generator}
Authorization: Bearer {token}
```

### إنشاء سجل وقاية وسلامة
```http
POST /api/compliance-safety/store
Authorization: Bearer {token}
Content-Type: application/json

{
  "generator_id": 1,
  "safety_certificate_status_id": 3,
  "last_inspection_date": "2025-02-08",
  "inspection_authority": "دفاع مدني غزة",
  "inspection_result": "مطابق",
  "violations": null
}
```
> `generator_id` يُستبدل بـ `operator_id` تلقائياً من بيانات المولد

### قائمة السجلات
```http
GET /api/compliance-safety/records?operator_id=1&date_from=2025-01-01
Authorization: Bearer {token}
```

### عرض سجل محدد
```http
GET /api/compliance-safety/records/{id}
Authorization: Bearer {token}
```

### تحديث سجل
```http
PUT /api/compliance-safety/records/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "safety_certificate_status_id": 3,
  "last_inspection_date": "2025-02-08",
  "inspection_authority": "دفاع مدني غزة",
  "inspection_result": "مطابق",
  "violations": null
}
```

---

## 4. تدفق التطبيق

1. تسجيل الدخول → حفظ `token` و `user`
2. التحقق من الدور:
   - `user.is_technician` → عرض وحدة الصيانة
   - `user.is_civil_defense` → عرض وحدة الوقاية والسلامة
3. مسح QR → استدعاء `/api/generators/qr-data/{code}` → حفظ `generator` و `operator`
4. إنشاء: استخدام `generator.id` و `operator.id` من نتيجة المسح
5. التحديث: استخدام `id` للسجل المراد تعديله
