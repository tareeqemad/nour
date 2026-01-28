<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معلومات وحدة التوليد - {{ $generationUnit->name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tajawal-font.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .qr-info-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .qr-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .qr-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .qr-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        .qr-content {
            padding: 30px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h3 {
            color: #2563eb;
            font-size: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
            flex: 0 0 40%;
        }
        .info-value {
            color: #111827;
            text-align: right;
            flex: 0 0 60%;
        }
        .badge-custom {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-secondary {
            background: #e5e7eb;
            color: #374151;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="qr-info-card">
        <div class="qr-header">
            <h1><i class="bi bi-lightning-charge me-2"></i>معلومات وحدة التوليد</h1>
            <p>تم الوصول عبر QR Code</p>
        </div>

        <div class="qr-content">
            <!-- المعلومات الأساسية -->
            <div class="info-section">
                <h3><i class="bi bi-info-circle me-2"></i>المعلومات الأساسية</h3>
                <div class="info-row">
                    <span class="info-label">اسم وحدة التوليد:</span>
                    <span class="info-value"><strong>{{ $generationUnit->name }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">كود الوحدة:</span>
                    <span class="info-value">
                        <span class="badge-custom badge-secondary">{{ $generationUnit->unit_code }}</span>
                    </span>
                </div>
                @if($generationUnit->unit_number)
                <div class="info-row">
                    <span class="info-label">رقم الوحدة:</span>
                    <span class="info-value">{{ $generationUnit->unit_number }}</span>
                </div>
                @endif
                @if($generationUnit->statusDetail)
                <div class="info-row">
                    <span class="info-label">الحالة:</span>
                    <span class="info-value">
                        <span class="badge-custom {{ $generationUnit->statusDetail->code === 'ACTIVE' ? 'badge-success' : 'badge-secondary' }}">
                            {{ $generationUnit->statusDetail->label }}
                        </span>
                    </span>
                </div>
                @endif
            </div>

            <!-- معلومات المشغل -->
            @if($generationUnit->operator)
            <div class="info-section">
                <h3><i class="bi bi-building me-2"></i>معلومات المشغل</h3>
                <div class="info-row">
                    <span class="info-label">اسم المشغل:</span>
                    <span class="info-value">{{ $generationUnit->operator->name }}</span>
                </div>
                @if($generationUnit->operator->phone)
                <div class="info-row">
                    <span class="info-label">رقم الهاتف:</span>
                    <span class="info-value">
                        <a href="tel:{{ $generationUnit->operator->phone }}">{{ $generationUnit->operator->phone }}</a>
                    </span>
                </div>
                @endif
                @if($generationUnit->operator->email)
                <div class="info-row">
                    <span class="info-label">البريد الإلكتروني:</span>
                    <span class="info-value">
                        <a href="mailto:{{ $generationUnit->operator->email }}">{{ $generationUnit->operator->email }}</a>
                    </span>
                </div>
                @endif
            </div>
            @endif

            <!-- الموقع -->
            @if($generationUnit->city || $generationUnit->detailed_address)
            <div class="info-section">
                <h3><i class="bi bi-geo-alt me-2"></i>الموقع</h3>
                @if($generationUnit->city)
                <div class="info-row">
                    <span class="info-label">المدينة:</span>
                    <span class="info-value">{{ $generationUnit->city->label }}</span>
                </div>
                @endif
                @if($generationUnit->detailed_address)
                <div class="info-row">
                    <span class="info-label">العنوان التفصيلي:</span>
                    <span class="info-value">{{ $generationUnit->detailed_address }}</span>
                </div>
                @endif
            </div>
            @endif

            <!-- القدرات -->
            @if($generationUnit->total_capacity)
            <div class="info-section">
                <h3><i class="bi bi-speedometer2 me-2"></i>القدرات</h3>
                <div class="info-row">
                    <span class="info-label">القدرة الإجمالية:</span>
                    <span class="info-value"><strong>{{ number_format($generationUnit->total_capacity, 0) }} KVA</strong></span>
                </div>
            </div>
            @endif

            <div class="back-link">
                <a href="{{ route('front.home') }}">
                    <i class="bi bi-arrow-right me-1"></i>العودة للصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>
</body>
</html>



