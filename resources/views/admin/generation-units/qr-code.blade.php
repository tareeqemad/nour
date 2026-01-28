<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $generationUnit->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .qr-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        .qr-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
        }
        .qr-header h1 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .qr-header p {
            color: #666;
            font-size: 16px;
        }
        .qr-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }
        .qr-code-wrapper {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            display: inline-block;
        }
        .qr-code-wrapper svg {
            display: block;
            max-width: 400px;
            height: auto;
        }
        .qr-info {
            width: 100%;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        .qr-info h3 {
            color: #2563eb;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #212529;
            text-align: left;
        }
        .print-button {
            margin-top: 30px;
            text-align: center;
        }
        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .qr-container {
                box-shadow: none;
                padding: 20px;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <div class="qr-header">
            <h1>QR Code - وحدة التوليد</h1>
            <p>كود الاستجابة السريعة للتعريف بوحدة التوليد</p>
        </div>

        <div class="qr-content">
            <div class="qr-code-wrapper">
                {!! $qrCodeSvg !!}
            </div>

            <div class="qr-info">
                <h3>معلومات وحدة التوليد</h3>
                <div class="info-row">
                    <span class="info-label">كود الوحدة (QR Code):</span>
                    <span class="info-value"><strong>{{ $generationUnit->unit_code ?? 'GU-' . $generationUnit->id }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">اسم الوحدة:</span>
                    <span class="info-value">{{ $generationUnit->name }}</span>
                </div>
                @if($generationUnit->operator)
                <div class="info-row">
                    <span class="info-label">المشغل:</span>
                    <span class="info-value">{{ $generationUnit->operator->name }}</span>
                </div>
                @endif
                @if($generationUnit->unit_number)
                <div class="info-row">
                    <span class="info-label">رقم الوحدة:</span>
                    <span class="info-value">{{ $generationUnit->unit_number }}</span>
                </div>
                @endif
                @if($generationUnit->total_capacity)
                <div class="info-row">
                    <span class="info-label">القدرة الإجمالية:</span>
                    <span class="info-value">{{ number_format($generationUnit->total_capacity, 0) }} KVA</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">ملاحظة:</span>
                    <span class="info-value" style="font-size: 12px; color: #666;">QR Code يحتوي على كود الوحدة فقط. استخدمه للبحث في النظام.</span>
                </div>
            </div>
        </div>

        <div class="print-button">
            <button class="btn-print" onclick="window.print()">
                <i class="bi bi-printer"></i> طباعة QR Code
            </button>
        </div>
    </div>

    <script>
        // طباعة تلقائية عند تحميل الصفحة (اختياري)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>

