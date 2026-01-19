<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $siteName = \App\Models\Setting::get('site_name', 'نور');
    @endphp
    <title>بيانات تسجيل الدخول - نظام {{ $siteName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .credentials {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
        .credentials-item {
            margin: 10px 0;
        }
        .credentials-label {
            font-weight: bold;
            color: #555;
        }
        .credentials-value {
            font-family: monospace;
            background-color: #f5f5f5;
            padding: 8px;
            border-radius: 3px;
            margin-top: 5px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 12px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>مرحباً بك في نظام {{ $siteName }}</h1>
    </div>
    
    <div class="content">
        <p>عزيزي المشغل،</p>
        
        <p>تم إنشاء حسابك في نظام {{ $siteName }} بنجاح. يمكنك الآن تسجيل الدخول باستخدام البيانات التالية:</p>
        
        <div class="credentials">
            <div class="credentials-item">
                <div class="credentials-label">اسم المستخدم:</div>
                <div class="credentials-value">{{ $username }}</div>
            </div>
            <div class="credentials-item">
                <div class="credentials-label">كلمة المرور:</div>
                <div class="credentials-value">{{ $password }}</div>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">تسجيل الدخول الآن</a>
        </div>
        
        <div class="warning">
            <strong>ملاحظة مهمة:</strong>
            <ul style="margin: 10px 0; padding-right: 20px;">
                <li>يرجى تغيير كلمة المرور بعد تسجيل الدخول لأول مرة</li>
                <li>بعد تسجيل الدخول، ستحتاج إلى إكمال بيانات المشغل المطلوبة</li>
                <li>بعد إكمال البيانات، يمكنك إضافة بيانات المولدات</li>
            </ul>
        </div>
        
        <p>إذا كان لديك أي استفسار، يرجى التواصل معنا.</p>
        
        <p>مع تحياتنا،<br>فريق نظام {{ $siteName }}</p>
    </div>
    
    <div class="footer">
        <p>هذا الإيميل تم إرساله تلقائياً من نظام {{ $siteName }}. يرجى عدم الرد على هذا الإيميل.</p>
    </div>
</body>
</html>












