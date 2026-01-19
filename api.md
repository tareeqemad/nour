# API Documentation - تطبيق الجوال

## نظرة عامة

هذا API مصمم لتطبيق الجوال (Android/iOS) الذي يسمح للمستخدمين (فنيين ودفاع مدني) بمسح QR codes للمولدات وإدخال البيانات.

## Base URL

```
https://gazarased.com/api
```

## Authentication (المصادقة)

جميع الطلبات المحمية تتطلب إرسال token في header:

```
Authorization: Bearer {token}
```

---

## 1. Authentication Endpoints

### 1.1 تسجيل الدخول

**POST** `/api/login`

**Request Body:**
```json
{
    "username": "technician1",
    "password": "password123"
}
```

**ملاحظات:**
- يمكن استخدام `username` أو `email` في حقل `username`
- يجب أن يكون المستخدم غير محظور وغير معطل
- يجب أن يكون المشغل المرتبط نشط (active) إذا كان المستخدم CompanyOwner أو Employee أو Technician

**Response (Success - 200):**
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
            "role_id": null,
            "role_label": "فني",
            "role_type": "system",
            "is_technician": true,
            "is_civil_defense": false
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    }
}
```

**Response (Error - 401):**
```json
{
    "success": false,
    "message": "بيانات الدخول غير صحيحة."
}
```

**Response (Error - 403):**
```json
{
    "success": false,
    "message": "حسابك محظور/معطل. يرجى التواصل مع الإدارة."
}
```

**مثال JavaScript/React Native:**
```javascript
const login = async (username, password) => {
  try {
    const response = await fetch('https://gazarased.com/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        username: username,
        password: password,
      }),
    });

    const data = await response.json();

    if (data.success) {
      // حفظ token
      await AsyncStorage.setItem('token', data.data.token);
      
      // حفظ معلومات المستخدم
      await AsyncStorage.setItem('user', JSON.stringify(data.data.user));
      
      return data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Login error:', error);
    throw error;
  }
};
```

---

### 1.2 تسجيل الخروج

**POST** `/api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (Success - 200):**
```json
{
    "success": true,
    "message": "تم تسجيل الخروج بنجاح."
}
```

**مثال JavaScript/React Native:**
```javascript
const logout = async () => {
  const token = await AsyncStorage.getItem('token');
  
  try {
    const response = await fetch('https://gazarased.com/api/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });

    const data = await response.json();
    
    // حذف token والمعلومات المحفوظة
    await AsyncStorage.removeItem('token');
    await AsyncStorage.removeItem('user');
    
    return data;
  } catch (error) {
    console.error('Logout error:', error);
    throw error;
  }
};
```

---

### 1.3 معلومات المستخدم الحالي

**GET** `/api/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (Success - 200):**
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
            "role_id": null,
            "role_label": "فني",
            "role_type": "system",
            "is_technician": true,
            "is_civil_defense": false
        }
    }
}
```

**مثال JavaScript/React Native:**
```javascript
const getCurrentUser = async () => {
  const token = await AsyncStorage.getItem('token');
  
  try {
    const response = await fetch('https://gazarased.com/api/user', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });

    const data = await response.json();
    
    if (data.success) {
      return data.data.user;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Get user error:', error);
    throw error;
  }
};
```

---

## 2. QR Code Scanning

### 2.1 مسح QR Code

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

**ملاحظات:**
- QR Code قد يكون `generator_number` مباشرة (مثل: `GEN-12345`)
- أو URL يحتوي على `generator_number` (مثل: `https://gazarased.com/qr/generator/GEN-12345`)
- API يستخرج `generator_number` تلقائياً من أي تنسيق

**Response (Success - 200):**

*للفني (Technician):*
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

*لدفاع مدني (Civil Defense):*
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

**Response (Error - 400):**
```json
{
    "success": false,
    "message": "QR Code غير صحيح."
}
```

**Response (Error - 404):**
```json
{
    "success": false,
    "message": "المولد غير موجود."
}
```

**مثال JavaScript/React Native:**
```javascript
const scanQRCode = async (qrCode) => {
  const token = await AsyncStorage.getItem('token');
  
  try {
    const response = await fetch('https://gazarased.com/api/qr/scan', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        qr_code: qrCode,
      }),
    });

    const data = await response.json();
    
    if (data.success) {
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('QR scan error:', error);
    throw error;
  }
};
```

---

## 3. Maintenance Records (سجلات الصيانة - للفني)

### 3.1 الحصول على بيانات النموذج

**GET** `/api/maintenance/form-data/{generator}`

**ملاحظة:** يمكن إرسال `generator_id` (رقم المولد) في URL، Laravel سيقوم بتحويله تلقائياً.

**Headers:**
```
Authorization: Bearer {token}
```

**ملاحظات:**
- يجب أن يكون المستخدم فني (Technician)
- يجب أن يكون المولد مرتبط بمشغل المستخدم

**Response (Success - 200):**
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

**Response (Error - 403):**
```json
{
    "success": false,
    "message": "غير مصرح لك بالوصول."
}
```

---

### 3.2 حفظ سجل صيانة جديد

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

**ملاحظات مهمة:**
- `downtime_hours`: يتم حسابه تلقائياً من `start_time` و `end_time` (الفارق بالساعات)
- `maintenance_cost`: يتم حسابه تلقائياً = `parts_cost` + (`labor_hours` × `labor_rate_per_hour`)
- إذا كانت الصيانة دورية (PERIODIC)، يتم تحديث `last_major_maintenance_date` للمولد تلقائياً

**الحقول المطلوبة:**
- `generator_id` (required)
- `maintenance_type_id` (required)
- `maintenance_date` (required, format: Y-m-d)

**الحقول الاختيارية:**
- `next_maintenance_type_id`
- `start_time` (format: H:i)
- `end_time` (format: H:i)
- `technician_name`
- `work_performed`
- `downtime_hours` (يُحسب تلقائياً)
- `parts_cost`
- `labor_hours`
- `labor_rate_per_hour`
- `maintenance_cost` (يُحسب تلقائياً)

**Response (Success - 201):**
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

**مثال JavaScript/React Native:**
```javascript
const saveMaintenanceRecord = async (formData) => {
  const token = await AsyncStorage.getItem('token');
  
  try {
    const response = await fetch('https://gazarased.com/api/maintenance/store', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(formData),
    });

    const data = await response.json();
    
    if (data.success) {
      return data.data.maintenance_record;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Save maintenance error:', error);
    throw error;
  }
};
```

---

### 3.3 قائمة سجلات الصيانة

**GET** `/api/maintenance/records`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `generator_id` (optional): تصفية حسب المولد
- `date_from` (optional): تاريخ البداية (format: Y-m-d)
- `date_to` (optional): تاريخ النهاية (format: Y-m-d)
- `per_page` (optional): عدد النتائج في الصفحة (default: 20)
- `page` (optional): رقم الصفحة (default: 1)

**Response (Success - 200):**
```json
{
    "success": true,
    "data": {
        "records": [
            {
                "id": 1,
                "generator_id": 1,
                "maintenance_type_id": 1,
                "maintenance_date": "2024-01-15",
                "start_time": "08:00",
                "end_time": "12:00",
                "technician_name": "أحمد محمد",
                "work_performed": "تنظيف الفلاتر وتغيير الزيت",
                "downtime_hours": 4.0,
                "parts_cost": 500.00,
                "labor_hours": 4.0,
                "labor_rate_per_hour": 50.00,
                "maintenance_cost": 700.00,
                "generator": {
                    "id": 1,
                    "name": "مولد 1",
                    "generator_number": "GEN-12345",
                    "operator": {
                        "id": 1,
                        "name": "مشغل 1"
                    }
                },
                "maintenance_type_detail": {
                    "id": 1,
                    "label": "صيانة دورية",
                    "code": "PERIODIC"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 20,
            "total": 100
        }
    }
}
```

**مثال JavaScript/React Native:**
```javascript
const getMaintenanceRecords = async (filters = {}) => {
  const token = await AsyncStorage.getItem('token');
  
  // بناء query string
  const queryParams = new URLSearchParams();
  if (filters.generator_id) queryParams.append('generator_id', filters.generator_id);
  if (filters.date_from) queryParams.append('date_from', filters.date_from);
  if (filters.date_to) queryParams.append('date_to', filters.date_to);
  if (filters.per_page) queryParams.append('per_page', filters.per_page);
  if (filters.page) queryParams.append('page', filters.page);
  
  const url = `https://gazarased.com/api/maintenance/records${queryParams.toString() ? '?' + queryParams.toString() : ''}`;
  
  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });

    const data = await response.json();
    
    if (data.success) {
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Get maintenance records error:', error);
    throw error;
  }
};
```

---

### 3.4 عرض سجل صيانة محدد

**GET** `/api/maintenance/records/{maintenanceRecord}`

**ملاحظة:** يمكن إرسال `maintenance_record_id` (رقم السجل) في URL، Laravel سيقوم بتحويله تلقائياً.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (Success - 200):**
```json
{
    "success": true,
    "data": {
        "maintenance_record": {
            "id": 1,
            "generator_id": 1,
            "maintenance_type_id": 1,
            "next_maintenance_type_id": 1,
            "maintenance_date": "2024-01-15",
            "start_time": "08:00",
            "end_time": "12:00",
            "technician_name": "أحمد محمد",
            "work_performed": "تنظيف الفلاتر وتغيير الزيت",
            "downtime_hours": 4.0,
            "parts_cost": 500.00,
            "labor_hours": 4.0,
            "labor_rate_per_hour": 50.00,
            "maintenance_cost": 700.00,
            "generator": {
                "id": 1,
                "name": "مولد 1",
                "generator_number": "GEN-12345",
                "operator": {
                    "id": 1,
                    "name": "مشغل 1"
                }
            },
            "maintenance_type_detail": {
                "id": 1,
                "label": "صيانة دورية",
                "code": "PERIODIC"
            },
            "next_maintenance_type_detail": {
                "id": 1,
                "label": "صيانة دورية",
                "code": "PERIODIC"
            }
        }
    }
}
```

---

## 4. Compliance Safety (الوقاية والسلامة - لدفاع مدني)

### 4.1 الحصول على بيانات النموذج

**GET** `/api/compliance-safety/form-data/{generator}`

**ملاحظة:** يمكن إرسال `generator_id` (رقم المولد) في URL، Laravel سيقوم بتحويله تلقائياً.

**Headers:**
```
Authorization: Bearer {token}
```

**ملاحظات:**
- يجب أن يكون المستخدم دفاع مدني (Civil Defense) أو Energy Authority أو لديه permission `compliance_safety.create`
- يجب أن يكون المولد مرتبط بمشغل

**Response (Success - 200):**
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

---

### 4.2 حفظ سجل وقاية وسلامة جديد

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

**ملاحظات:**
- `operator_id` يتم أخذه تلقائياً من المولد المرتبط
- السجل يُحفظ على مستوى المشغل (operator)، وليس المولد

**الحقول المطلوبة:**
- `generator_id` (required)
- `safety_certificate_status_id` (required)

**الحقول الاختيارية:**
- `last_inspection_date` (format: Y-m-d)
- `inspection_authority`
- `inspection_result`
- `violations`

**Response (Success - 201):**
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

---

### 4.3 قائمة سجلات الوقاية والسلامة

**GET** `/api/compliance-safety/records`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `operator_id` (optional): تصفية حسب المشغل
- `date_from` (optional): تاريخ البداية (format: Y-m-d)
- `date_to` (optional): تاريخ النهاية (format: Y-m-d)
- `per_page` (optional): عدد النتائج في الصفحة (default: 20)
- `page` (optional): رقم الصفحة (default: 1)

**Response (Success - 200):**
```json
{
    "success": true,
    "data": {
        "records": [
            {
                "id": 1,
                "operator_id": 1,
                "safety_certificate_status_id": 1,
                "last_inspection_date": "2024-01-15",
                "inspection_authority": "الدفاع المدني",
                "inspection_result": "جميع المتطلبات متوفرة",
                "violations": "لا توجد مخالفات",
                "operator": {
                    "id": 1,
                    "name": "مشغل 1"
                },
                "safety_certificate_status_detail": {
                    "id": 1,
                    "label": "صالح",
                    "code": "VALID"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 20,
            "total": 100
        }
    }
}
```

---

### 4.4 عرض سجل وقاية وسلامة محدد

**GET** `/api/compliance-safety/records/{complianceSafety}`

**ملاحظة:** يمكن إرسال `compliance_safety_id` (رقم السجل) في URL، Laravel سيقوم بتحويله تلقائياً.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (Success - 200):**
```json
{
    "success": true,
    "data": {
        "compliance_safety": {
            "id": 1,
            "operator_id": 1,
            "safety_certificate_status_id": 1,
            "last_inspection_date": "2024-01-15",
            "inspection_authority": "الدفاع المدني",
            "inspection_result": "جميع المتطلبات متوفرة",
            "violations": "لا توجد مخالفات",
            "operator": {
                "id": 1,
                "name": "مشغل 1"
            },
            "safety_certificate_status_detail": {
                "id": 1,
                "label": "صالح",
                "code": "VALID"
            }
        }
    }
}
```

---

## 5. Error Handling (معالجة الأخطاء)

### هيكل الاستجابة عند الخطأ

جميع الأخطاء تعيد نفس التنسيق:

```json
{
    "success": false,
    "message": "رسالة الخطأ بالعربية"
}
```

### Status Codes (رموز الحالة)

- **200 OK**: نجاح العملية
- **201 Created**: تم الإنشاء بنجاح
- **400 Bad Request**: خطأ في البيانات المرسلة
- **401 Unauthorized**: غير مصرح (لم يتم تسجيل الدخول أو token غير صالح)
- **403 Forbidden**: غير مصرح (لا توجد صلاحيات)
- **404 Not Found**: المورد غير موجود
- **422 Unprocessable Entity**: خطأ في التحقق من البيانات (validation errors)
- **500 Internal Server Error**: خطأ في الخادم

### أمثلة على الأخطاء

**401 Unauthorized:**
```json
{
    "success": false,
    "message": "غير مصرح."
}
```

**403 Forbidden:**
```json
{
    "success": false,
    "message": "غير مصرح لك بالوصول."
}
```

**422 Validation Error:**
```json
{
    "success": false,
    "message": "التحقق من البيانات فشل.",
    "errors": {
        "generator_id": ["حقل generator_id مطلوب."],
        "maintenance_date": ["حقل maintenance_date يجب أن يكون تاريخ صحيح."]
    }
}
```

---

## 6. User Roles & Permissions (الأدوار والصلاحيات)

### معلومات الدور في الاستجابة

كل استجابة تحتوي على معلومات الدور:

```json
{
    "role": "technician",           // قيمة الدور (من enum أو roleModel)
    "role_id": null,                // معرف الدور (إذا كان custom role)
    "role_label": "فني",            // تسمية الدور بالعربية
    "role_type": "system",          // نوع الدور: "system" أو "custom"
    "is_technician": true,          // هل المستخدم فني؟
    "is_civil_defense": false       // هل المستخدم دفاع مدني؟
}
```

### الأدوار المتاحة

1. **Technician (فني)**
   - يمكنه الوصول إلى Maintenance Records
   - يجب أن يكون مرتبط بمشغل نشط

2. **Civil Defense (دفاع مدني)**
   - يمكنه الوصول إلى Compliance Safety
   - يمكن أن يكون:
     - role مخصص باسم `civil_defense`
     - `energy_authority`
     - لديه permission `compliance_safety.create`

3. **CompanyOwner (مشغل)**
   - يمكنه إدارة مشغله ومولداته
   - يجب أن يكون مشغله نشط

4. **Employee (موظف)**
   - يمكنه عرض البيانات حسب الصلاحيات الممنوحة
   - يجب أن يكون مرتبط بمشغل نشط

---

## 7. Helper Functions (دوال مساعدة)

### مثال كامل لتطبيق React Native

```javascript
// api.js
const BASE_URL = 'https://gazarased.com/api';

class ApiService {
  constructor() {
    this.token = null;
  }

  async getToken() {
    if (!this.token) {
      this.token = await AsyncStorage.getItem('token');
    }
    return this.token;
  }

  async request(endpoint, options = {}) {
    const token = await this.getToken();
    
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers,
    };

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const url = `${BASE_URL}${endpoint}`;
    
    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'حدث خطأ');
      }

      return data;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }

  // Authentication
  async login(username, password) {
    return this.request('/login', {
      method: 'POST',
      body: JSON.stringify({ username, password }),
    });
  }

  async logout() {
    const result = await this.request('/logout', {
      method: 'POST',
    });
    await AsyncStorage.removeItem('token');
    await AsyncStorage.removeItem('user');
    this.token = null;
    return result;
  }

  async getCurrentUser() {
    const data = await this.request('/user');
    return data.data.user;
  }

  // QR Code
  async scanQRCode(qrCode) {
    const data = await this.request('/qr/scan', {
      method: 'POST',
      body: JSON.stringify({ qr_code: qrCode }),
    });
    return data.data;
  }

  // Maintenance
  async getMaintenanceFormData(generatorId) {
    const data = await this.request(`/maintenance/form-data/${generatorId}`);
    return data.data;
  }

  async saveMaintenanceRecord(formData) {
    const data = await this.request('/maintenance/store', {
      method: 'POST',
      body: JSON.stringify(formData),
    });
    return data.data.maintenance_record;
  }

  async getMaintenanceRecords(filters = {}) {
    const queryParams = new URLSearchParams(filters);
    const data = await this.request(`/maintenance/records?${queryParams}`);
    return data.data;
  }

  async getMaintenanceRecord(id) {
    const data = await this.request(`/maintenance/records/${id}`);
    return data.data.maintenance_record;
  }

  // Compliance Safety
  async getComplianceFormData(generatorId) {
    const data = await this.request(`/compliance-safety/form-data/${generatorId}`);
    return data.data;
  }

  async saveComplianceRecord(formData) {
    const data = await this.request('/compliance-safety/store', {
      method: 'POST',
      body: JSON.stringify(formData),
    });
    return data.data.compliance_safety;
  }

  async getComplianceRecords(filters = {}) {
    const queryParams = new URLSearchParams(filters);
    const data = await this.request(`/compliance-safety/records?${queryParams}`);
    return data.data;
  }

  async getComplianceRecord(id) {
    const data = await this.request(`/compliance-safety/records/${id}`);
    return data.data.compliance_safety;
  }
}

export default new ApiService();
```

### استخدام في Component

```javascript
// LoginScreen.js
import React, { useState } from 'react';
import { View, TextInput, Button, Alert } from 'react-native';
import ApiService from './api';
import AsyncStorage from '@react-native-async-storage/async-storage';

const LoginScreen = ({ navigation }) => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');

  const handleLogin = async () => {
    try {
      const response = await ApiService.login(username, password);
      
      if (response.success) {
        // حفظ token
        await AsyncStorage.setItem('token', response.data.token);
        await AsyncStorage.setItem('user', JSON.stringify(response.data.user));
        
        // الانتقال للصفحة الرئيسية
        navigation.navigate('Home');
      }
    } catch (error) {
      Alert.alert('خطأ', error.message);
    }
  };

  return (
    <View>
      <TextInput
        value={username}
        onChangeText={setUsername}
        placeholder="اسم المستخدم"
      />
      <TextInput
        value={password}
        onChangeText={setPassword}
        placeholder="كلمة المرور"
        secureTextEntry
      />
      <Button title="تسجيل الدخول" onPress={handleLogin} />
    </View>
  );
};
```

---

## 8. ملاحظات مهمة

### 1. Authentication Token
- احفظ token في `AsyncStorage` (React Native) أو `SecureStore` (Expo)
- أرسل token في header `Authorization: Bearer {token}` لكل طلب محمي
- إذا حصلت على 401، قم بتسجيل الخروج وإعادة تسجيل الدخول

### 2. Date Formats
- جميع التواريخ بصيغة `Y-m-d` (مثل: `2024-01-15`)
- جميع الأوقات بصيغة `H:i` (مثل: `08:00`, `14:30`)

### 3. Pagination
- استخدم `per_page` لتحديد عدد النتائج (default: 20)
- استخدم `page` للتنقل بين الصفحات
- الاستجابة تحتوي على `pagination` object مع معلومات الصفحات

### 4. Error Handling
- دائماً تحقق من `success` في الاستجابة
- اعرض `message` للمستخدم عند الخطأ
- في حالة 422، اعرض `errors` object للمستخدم

### 5. Network Timeout
- أضف timeout للطلبات (مثل: 30 ثانية)
- اعرض رسالة للمستخدم عند انقطاع الاتصال

### 6. CORS
- API يدعم CORS للطلبات من تطبيقات الجوال
- تأكد من إرسال header `Accept: application/json`

### 7. Rate Limiting
- API محمي بـ rate limiting
- إذا حصلت على 429 (Too Many Requests)، انتظر قليلاً قبل إعادة المحاولة

---

## 9. Testing (الاختبار)

### استخدام Postman

1. **تسجيل الدخول:**
   - Method: POST
   - URL: `https://gazarased.com/api/login`
   - Body (JSON):
     ```json
     {
         "username": "technician1",
         "password": "password123"
     }
     ```

2. **استخدام Token:**
   - في Authorization tab، اختر "Bearer Token"
   - الصق token من استجابة تسجيل الدخول

3. **اختبار QR Scan:**
   - Method: POST
   - URL: `https://gazarased.com/api/qr/scan`
   - Body (JSON):
     ```json
     {
         "qr_code": "GEN-12345"
     }
     ```

### استخدام cURL

```bash
# تسجيل الدخول
curl -X POST https://gazarased.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"technician1","password":"password123"}'

# استخدام token
curl -X GET https://gazarased.com/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 10. Support (الدعم)

إذا واجهت أي مشاكل أو لديك أسئلة:

1. تحقق من Status Codes في الاستجابة
2. تحقق من `message` في الاستجابة
3. تأكد من إرسال جميع الحقول المطلوبة
4. تأكد من صحة token
5. تأكد من صلاحيات المستخدم

---

**آخر تحديث:** 2024

**Base URL:** `https://gazarased.com/api`
