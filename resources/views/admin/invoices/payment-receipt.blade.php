<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال دفعة - {{ $payment->receipt_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tajawal', sans-serif; direction: rtl; background: #f5f5f5; color: #1a1a2e; }
        .receipt { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
        .receipt-header { background: linear-gradient(135deg, #19228f, #2d3a9f); color: #fff; padding: 24px; text-align: center; }
        .receipt-header img { height: 50px; margin-bottom: 8px; }
        .receipt-header h1 { font-size: 1.3rem; font-weight: 700; margin: 4px 0; }
        .receipt-header p { font-size: 0.85rem; opacity: 0.85; }
        .receipt-body { padding: 24px; }
        .receipt-number { text-align: center; margin-bottom: 20px; }
        .receipt-number span { background: #f0f4ff; color: #19228f; font-size: 1.1rem; font-weight: 700; padding: 8px 20px; border-radius: 8px; display: inline-block; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .info-item { padding: 12px; background: #f8f9fa; border-radius: 8px; }
        .info-item .label { font-size: 0.78rem; color: #6c757d; margin-bottom: 4px; }
        .info-item .value { font-size: 0.95rem; font-weight: 600; }
        .amount-box { text-align: center; padding: 20px; margin: 16px 0; background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-radius: 12px; border: 2px solid #a7f3d0; }
        .amount-box .label { font-size: 0.85rem; color: #047857; margin-bottom: 4px; }
        .amount-box .amount { font-size: 2rem; font-weight: 800; color: #065f46; }
        .amount-box .currency { font-size: 1rem; font-weight: 500; }
        .receipt-footer { padding: 16px 24px; background: #f8f9fa; text-align: center; font-size: 0.8rem; color: #6c757d; border-top: 1px solid #e9ecef; }
        .divider { border: 0; border-top: 1px dashed #dee2e6; margin: 16px 0; }
        @media print {
            body { background: #fff; }
            .receipt { box-shadow: none; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
        .print-btn { display: block; max-width: 600px; margin: 12px auto; text-align: center; }
        .print-btn button { background: #19228f; color: #fff; border: none; padding: 10px 32px; border-radius: 8px; font-family: 'Tajawal'; font-size: 1rem; cursor: pointer; }
        .print-btn button:hover { background: #2d3a9f; }
    </style>
</head>
<body>
    <div class="print-btn no-print">
        <button onclick="window.print()"><i class="bi bi-printer"></i> طباعة الإيصال</button>
    </div>

    <div class="receipt">
        <div class="receipt-header">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" onerror="this.style.display='none'">
            <h1>{{ $siteName }}</h1>
            <p>إيصال دفعة</p>
        </div>

        <div class="receipt-body">
            <div class="receipt-number">
                <span>إيصال رقم: {{ $payment->receipt_number ?? '—' }}</span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="label">تاريخ الدفعة</div>
                    <div class="value">{{ $payment->payment_date?->format('Y-m-d') }}</div>
                </div>
                <div class="info-item">
                    <div class="label">طريقة الدفع</div>
                    <div class="value">
                        @php
                            $methods = ['cash' => 'نقداً', 'bank_transfer' => 'تحويل بنكي', 'cheque' => 'شيك', 'online' => 'إلكتروني'];
                        @endphp
                        {{ $methods[$payment->payment_method] ?? $payment->payment_method }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">المشترك</div>
                    <div class="value">{{ $payment->invoice?->subscriber?->subscriber_name }}</div>
                </div>
                <div class="info-item">
                    <div class="label">رقم الاشتراك</div>
                    <div class="value">{{ $payment->invoice?->subscriber?->subscription_number }}</div>
                </div>
                <div class="info-item">
                    <div class="label">رقم الفاتورة</div>
                    <div class="value">{{ $invoice->invoice_number ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">تاريخ الفاتورة</div>
                    <div class="value">{{ $invoice->invoice_date?->format('Y-m-d') }}</div>
                </div>
            </div>

            <div class="amount-box">
                <div class="label">المبلغ المدفوع</div>
                <div class="amount">{{ number_format($payment->amount_paid, 2) }} <span class="currency">₪</span></div>
            </div>

            @if($payment->notes)
            <hr class="divider">
            <div style="font-size:0.85rem;color:#6c757d;">
                <strong>ملاحظات:</strong> {{ $payment->notes }}
            </div>
            @endif
        </div>

        <div class="receipt-footer">
            <div>تم الإنشاء بواسطة: {{ $payment->creator?->name ?? '—' }} | {{ $payment->created_at?->format('Y-m-d H:i') }}</div>
            <div style="margin-top:4px;">{{ $siteName }} — جميع الحقوق محفوظة © {{ date('Y') }}</div>
        </div>
    </div>
</body>
</html>
