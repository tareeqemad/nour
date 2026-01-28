<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معلومات المولد - {{ $generator->name }}</title>
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
            color: #f59e0b;
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
            color: #f59e0b;
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
            <h1><i class="bi bi-lightning-charge me-2"></i>معلومات المولد</h1>
            <p>تم الوصول عبر QR Code</p>
        </div>

        <div class="qr-content">
            <!-- المعلومات الأساسية -->
            <div class="info-section">
                <h3><i class="bi bi-info-circle me-2"></i>المعلومات الأساسية</h3>
                <div class="info-row">
                    <span class="info-label">اسم المولد:</span>
                    <span class="info-value"><strong>{{ $generator->name }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">رقم المولد:</span>
                    <span class="info-value">
                        <span class="badge-custom badge-secondary">{{ $generator->generator_number }}</span>
                    </span>
                </div>
                @if($generator->statusDetail)
                <div class="info-row">
                    <span class="info-label">الحالة:</span>
                    <span class="info-value">
                        <span class="badge-custom {{ $generator->statusDetail->code === 'ACTIVE' ? 'badge-success' : 'badge-secondary' }}">
                            {{ $generator->statusDetail->label }}
                        </span>
                    </span>
                </div>
                @endif
            </div>

            <!-- معلومات المشغل -->
            @if($generator->operator)
            <div class="info-section">
                <h3><i class="bi bi-building me-2"></i>معلومات المشغل</h3>
                <div class="info-row">
                    <span class="info-label">اسم المشغل:</span>
                    <span class="info-value">{{ $generator->operator->name }}</span>
                </div>
            </div>
            @endif

            <!-- معلومات وحدة التوليد -->
            @if($generator->generationUnit)
            <div class="info-section">
                <h3><i class="bi bi-lightning-charge me-2"></i>وحدة التوليد</h3>
                <div class="info-row">
                    <span class="info-label">اسم الوحدة:</span>
                    <span class="info-value">{{ $generator->generationUnit->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">كود الوحدة:</span>
                    <span class="info-value">
                        <span class="badge-custom badge-secondary">{{ $generator->generationUnit->unit_code }}</span>
                    </span>
                </div>
            </div>
            @endif

            <!-- المواصفات الفنية -->
            @if($generator->capacity_kva || $generator->voltage || $generator->frequency)
            <div class="info-section">
                <h3><i class="bi bi-gear me-2"></i>المواصفات الفنية</h3>
                @if($generator->capacity_kva)
                <div class="info-row">
                    <span class="info-label">القدرة:</span>
                    <span class="info-value"><strong>{{ number_format($generator->capacity_kva, 0) }} KVA</strong></span>
                </div>
                @endif
                @if($generator->voltage)
                <div class="info-row">
                    <span class="info-label">الجهد:</span>
                    <span class="info-value">{{ $generator->voltage }}V</span>
                </div>
                @endif
                @if($generator->frequency)
                <div class="info-row">
                    <span class="info-label">التردد:</span>
                    <span class="info-value">{{ $generator->frequency }} Hz</span>
                </div>
                @endif
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



