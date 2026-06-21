@php
    $siteName  = \App\Models\Setting::get('site_name', 'نور');
    $siteLogo  = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png');
    if (!empty($isPdf)) {
        $logoUrl = str_starts_with($siteLogo, 'http') ? $siteLogo : public_path($siteLogo);
    } else {
        $logoUrl = str_starts_with($siteLogo, 'http') ? $siteLogo : asset($siteLogo);
    }
    $operator  = $invoice->subscriber?->generationUnits->first()?->operator;
    $paidAmt   = $invoice->paidAmount();
    $remaining = $invoice->remainingAmount();
    $isEmployee  = $invoice->subscriber ? $invoice->subscriber->isEligibleForEmployeeDiscount() : false;
    $subAmpere   = intval($invoice->subscriber?->ampere ?? 0);
    $subPhase    = $invoice->subscriber?->phase_type ?? 1;
    $phaseLabel  = $subPhase == 2 ? '3' : '1';
    $operatorId = $operator?->id ?? 0;
    $autoMinCharge = \App\Models\MinimumChargeRule::getMinCharge($subAmpere, $subPhase, $operatorId);
    // القيم الفعلية - للمسودات: تطبيق القواعد تلقائياً عبر الدالة المركزية
    $isDraft = $invoice->invoice_status === \App\Models\Invoice::STATUS_DRAFT;
    if ($isDraft && $invoice->subscriber) {
        $printInvDate = $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date) : null;
        [$eDiscount, $eMinCharge] = \App\Models\Invoice::applySubscriberRules(
            $invoice->subscriber, (float)$invoice->discount_rate, (float)$invoice->minimum_charge, $printInvDate
        );
        $eCalc = \App\Models\Invoice::calculateAmounts(
            (float)$invoice->consumption_kwh, (float)$invoice->price_per_kwh,
            $eDiscount, $eMinCharge, (float)$invoice->previous_balance
        );
        $eCost   = $eCalc['consumption_cost'];
        $eInvAmt = $eCalc['invoice_amount'];
        $eTotal  = $eCalc['total_amount'];
    } else {
        $eDiscount  = (float)$invoice->discount_rate;
        $eMinCharge = (float)$invoice->minimum_charge;
        $eCost      = $invoice->consumption_cost;
        $eInvAmt    = (float)$invoice->invoice_amount;
        $eTotal     = (float)$invoice->total_amount;
    }
    $ePrevBal = (float)$invoice->previous_balance;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $invoice->invoice_number ?? 'مسودة' }} - {{ $siteName }}</title>
    @if(empty($isPdf))
        <link rel="stylesheet" href="{{ asset('assets/admin/css/tajawal-font.css') }}">
    @endif
    <style>
        @if(!empty($isPdf))
        * { font-family: 'DejaVu Sans', sans-serif !important; }
        @endif
        /* ===== Reset & Base ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: A4;
            margin: 12mm 10mm 12mm 10mm;
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #1e293b;
            background: #f8fafc;
            direction: rtl;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ===== Invoice Container ===== */
        .invoice-container {
            max-width: 210mm;
            margin: 20px auto;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 30px 35px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 24px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        /* ===== Decorative Top Border ===== */
        .invoice-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, transparent 0%, #3b82f6 20%, #10b981 50%, #3b82f6 80%, transparent 100%);
        }

        /* ===== Watermark for Draft ===== */
        @if($invoice->invoice_status === \App\Models\Invoice::STATUS_DRAFT)
        .invoice-container::after {
            content: 'مسودة';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 90px;
            font-weight: bold;
            color: rgba(220, 53, 69, 0.07);
            pointer-events: none;
            z-index: 0;
            letter-spacing: 15px;
        }
        @endif

        @if($invoice->invoice_status === \App\Models\Invoice::STATUS_CANCELLED)
        .invoice-container::after {
            content: 'ملغاة';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 90px;
            font-weight: bold;
            color: rgba(220, 53, 69, 0.07);
            pointer-events: none;
            z-index: 0;
            letter-spacing: 15px;
        }
        @endif

        /* ===== Header ===== */
        .invoice-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding-bottom: 18px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            gap: 8px;
        }

        .company-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 12px;
        }

        .header-system-name {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }

        .invoice-number-badge {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            padding: 3px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }

        .invoice-status-badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
        }

        .badge-draft     { background: #6c757d; color: #fff; }
        .badge-issued    { background: #3b82f6; color: #fff; }
        .badge-partial   { background: #f59e0b; color: #fff; }
        .badge-paid      { background: #16a34a; color: #fff; }
        .badge-cancelled { background: #dc2626; color: #fff; }
        .badge-overdue   { background: #dc2626; color: #fff; }

        /* ===== Info Grid (subscriber + invoice) ===== */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .info-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .info-box-title {
            font-size: 13px;
            font-weight: 700;
            color: #3b82f6;
            padding-bottom: 8px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-box-title svg {
            width: 16px;
            height: 16px;
            fill: #3b82f6;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dashed #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #64748b;
            font-size: 12px;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
            font-size: 12px;
        }

        /* ===== Main Details Section ===== */
        .details-section {
            margin-bottom: 22px;
            position: relative;
            z-index: 1;
        }

        .section-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
            padding-bottom: 8px;
            margin-bottom: 14px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* --- Metric Cards --- */
        .metric-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .metric-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 10px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: #3b82f6;
            border-radius: 8px 8px 0 0;
        }

        .metric-card.accent::before { background: #3b82f6; }
        .metric-card.success::before { background: #16a34a; }
        .metric-card.warning::before { background: #f59e0b; }

        .metric-label {
            font-size: 10.5px;
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .metric-value {
            font-size: 15px;
            font-weight: 700;
            color: #3b82f6;
            line-height: 1.2;
        }

        .metric-value.accent { color: #3b82f6; }
        .metric-value.success { color: #16a34a; }
        .metric-value.muted { color: #6c757d; }

        .metric-unit {
            font-size: 10px;
            color: #adb5bd;
            margin-top: 2px;
        }

        /* --- Calculation Steps Table --- */
        .calc-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12.5px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .calc-table thead tr th {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 11.5px;
            letter-spacing: 0.3px;
            border: none;
        }

        .calc-table thead tr th:first-child { text-align: right; }
        .calc-table thead tr th:last-child  { text-align: left; width: 30%; }

        .calc-table tbody tr {
            border-bottom: 1px solid #f0f9ff;
            transition: background 0.15s;
        }

        .calc-table tbody tr:last-child { border-bottom: none; }

        .calc-table tbody tr:nth-child(even) td { background: #f8fafc; }

        .calc-table tbody td {
            padding: 9px 16px;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f0f9ff;
        }

        .calc-table tbody td:first-child {
            font-weight: 600;
            color: #1e293b;
            border-left: 3px solid transparent;
        }

        .calc-table tbody td:last-child {
            text-align: left;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
        }

        .calc-table .step-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background: #e2e8f0;
            color: #3b82f6;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 700;
            margin-left: 8px;
            flex-shrink: 0;
        }

        .calc-table .row-highlight td {
            background: #eff6ff !important;
            border-left: 3px solid #3b82f6 !important;
        }

        .calc-table .row-total td {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: #fff !important;
            font-size: 14px;
            font-weight: 700;
            border-left: none !important;
        }

        .calc-table .row-total td:first-child { color: #fff !important; }

        .calc-table .row-danger td:first-child { border-left-color: #dc3545 !important; }
        .calc-table .row-danger td:last-child  { color: #dc3545 !important; }
        .calc-table .row-success td:first-child { border-left-color: #16a34a !important; }
        .calc-table .row-success td:last-child  { color: #16a34a !important; }

        .calc-table .cell-desc {
            font-size: 10px;
            color: #9ca3af;
            font-weight: 400;
            margin-top: 1px;
        }

        /* ===== Summary / Totals ===== */
        .summary-section {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .summary-table {
            width: 45%;
            margin-right: auto;
            border-collapse: collapse;
            font-size: 13px;
        }

        .summary-table tr td {
            padding: 8px 14px;
            border: 1px solid #dee2e6;
        }

        .summary-table tr td:first-child {
            font-weight: 600;
            color: #64748b;
            background: #f8f9fa;
            width: 55%;
        }

        .summary-table tr td:last-child {
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }

        .summary-table tr.total-row td {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: #fff !important;
            font-size: 14px;
            font-weight: 700;
        }

        .summary-table tr.paid-row td:last-child {
            color: #198754;
        }

        .summary-table tr.balance-row td:last-child {
            color: #dc3545;
        }

        .summary-table tr.remaining-row td {
            background: #fff3cd !important;
            font-weight: 700;
        }

        /* ===== Payment Status ===== */
        .payment-status {
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }

        .payment-bar-container {
            background: #f8fafc;
            border-radius: 8px;
            height: 10px;
            overflow: hidden;
            margin-top: 6px;
        }

        .payment-bar {
            background: linear-gradient(90deg, #16a34a, #22c55e);
            height: 100%;
            border-radius: 8px;
        }

        .payment-info {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #6c757d;
            margin-top: 4px;
        }

        /* ===== Footer ===== */
        .invoice-footer {
            border-top: 2px solid #e2e8f0;
            padding-top: 14px;
            margin-top: 10px;
            position: relative;
            z-index: 1;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        .footer-box {
            text-align: center;
            padding: 10px;
            border: 1px dashed #e2e8f0;
            border-radius: 10px;
            min-height: 60px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .footer-box-label {
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .footer-box-value {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
        }

        .footer-note {
            text-align: center;
            font-size: 10.5px;
            color: #888;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .footer-note strong {
            color: #3b82f6;
        }

        /* ===== Notes ===== */
        .notes-section {
            background: #fffbea;
            border: 1px solid #ffeab0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .notes-section .notes-title {
            font-weight: 700;
            color: #856404;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .notes-section p {
            font-size: 12px;
            color: #664d03;
            margin: 0;
        }

        /* ===== Due Date Highlight ===== */
        .due-date-highlight {
            display: inline-block;
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            color: #856404;
        }

        .due-date-overdue {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        /* ===== Currency Styling ===== */
        .currency {
            font-size: 11px;
            color: #6c757d;
        }

        /* ===== Print Controls (hidden on print) ===== */
        .print-controls {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            max-width: 210mm;
            margin: 20px auto 0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .print-controls button,
        .print-controls a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 24px;
            margin: 0 6px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-print {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
        }
        .btn-print:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(59, 130, 246, 0.35);
        }

        .btn-back {
            background: #6c757d;
            color: #fff;
        }
        .btn-back:hover { background: #565e64; }

        /* ===== Print Media ===== */
        @media print {
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                margin: 0;
                padding: 0 10px;
                max-width: 100%;
                border-radius: 0;
            }

            .print-controls {
                display: none !important;
            }

            .no-print {
                display: none !important;
            }
        }

        /* ===== Responsive for screen preview ===== */
        @media screen and (max-width: 768px) {
            .invoice-container { padding: 18px; }
            .info-grid { grid-template-columns: 1fr; }
            .summary-table { width: 100%; }
            .footer-grid { grid-template-columns: 1fr; }
            .invoice-header { grid-template-columns: 1fr; gap: 12px; text-align: center; }
            .invoice-title-block { text-align: center; }
            .company-info { align-items: center; }
        }
    </style>
</head>
<body>

    {{-- Print Controls --}}
    <div class="print-controls no-print" @if(!empty($isPdf)) style="display:none" @endif>
        <button class="btn-print" onclick="window.print()">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 10a1 1 0 0 1-1-1v-2h8v2a1 1 0 0 1-1 1H5z"/>
            </svg>
            طباعة الفاتورة
        </button>
        @if(empty($publicPrint))
        <a class="btn-back" href="{{ route('admin.invoices.show', $invoice) }}">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
            </svg>
            العودة للفاتورة
        </a>
        @endif
    </div>

    {{-- Invoice --}}
    <div class="invoice-container">

        {{-- ===== Header ===== --}}
        @php
            $badgeClass = 'badge-issued';
            if ($invoice->isOverdue()) $badgeClass = 'badge-overdue';
            elseif ($invoice->invoice_status === \App\Models\Invoice::STATUS_DRAFT) $badgeClass = 'badge-draft';
            elseif ($invoice->invoice_status === \App\Models\Invoice::STATUS_PARTIAL) $badgeClass = 'badge-partial';
            elseif ($invoice->invoice_status === \App\Models\Invoice::STATUS_PAID) $badgeClass = 'badge-paid';
            elseif ($invoice->invoice_status === \App\Models\Invoice::STATUS_CANCELLED) $badgeClass = 'badge-cancelled';
        @endphp
        <div class="invoice-header">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="company-logo"
                 onerror="this.style.display='none'">
            <div class="header-system-name">نظام إدارة الطاقة</div>
        </div>

        {{-- ===== Subscriber & Invoice Info ===== --}}
        <div class="info-grid">
            {{-- بيانات المشترك --}}
            <div class="info-box">
                <div class="info-box-title">
                    <svg viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                    بيانات المشترك
                </div>
                <div class="info-row">
                    <span class="info-label">رقم المشترك</span>
                    <span class="info-value">{{ $invoice->subscriber?->subscription_number ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">اسم المشترك</span>
                    <span class="info-value">{{ $invoice->subscriber?->subscriber_name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">اسم وحدة التوليد</span>
                    <span class="info-value">{{ $invoice->subscriber?->generationUnits->first()?->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">نوع المشترك</span>
                    <span class="info-value">
                        @if($invoice->subscriber?->is_employee_subscription)
                            موظف شركة ✔
                            @if(!$isEmployee)
                                <span style="font-size:9px;color:#dc3545;">(غير مستحق للخصم)</span>
                            @endif
                        @else
                            مشترك عادي
                        @endif
                    </span>
                </div>
                @if($operator)
                <div class="info-row">
                    <span class="info-label">المشغل</span>
                    <span class="info-value">{{ $operator->name }}</span>
                </div>
                @endif
            </div>

            {{-- بيانات الفاتورة --}}
            <div class="info-box">
                <div class="info-box-title">
                    <svg viewBox="0 0 16 16"><path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27zM2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.51.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0l-.51-.51L2 2.118z"/><path d="M12.5 4a.5.5 0 0 1 0 1h-9a.5.5 0 0 1 0-1h9zm0 3a.5.5 0 0 1 0 1h-9a.5.5 0 0 1 0-1h9zm0 3a.5.5 0 0 1 0 1h-9a.5.5 0 0 1 0-1h9z"/></svg>
                    بيانات الفاتورة
                </div>
                <div class="info-row">
                    <span class="info-label">رقم الفاتورة</span>
                    <span class="info-value" style="font-weight:700">{{ $invoice->invoice_number ?? 'مسودة' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الفاتورة</span>
                    <span class="info-value">{{ $invoice->invoice_date?->format('Y-m-d') ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الاستحقاق</span>
                    <span class="info-value">
                        @if($invoice->due_date)
                            <span class="{{ $invoice->due_date->isPast() && $invoice->invoice_status < 3 ? 'due-date-highlight due-date-overdue' : 'due-date-highlight' }}">
                                {{ $invoice->due_date->format('Y-m-d') }}
                            </span>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">حالة الفاتورة</span>
                    <span class="info-value">
                        <span class="invoice-status-badge {{ $badgeClass }}" style="font-size:11px; padding:1px 10px;">
                            {{ $invoice->status_name }}
                        </span>
                    </span>
                </div>
            </div>
        </div>

        {{-- ===== Consumption Details ===== --}}
        <div class="details-section">
            <div class="section-title">
                <svg width="15" height="15" fill="#3b82f6" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h13A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 12.5v-9zM1.5 3a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-13z"/><path d="M3 5.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/></svg>
                تفاصيل الاستهلاك والاحتساب
            </div>

            {{-- Metric Cards --}}
            <div class="metric-cards">
                <div class="metric-card">
                    <div class="metric-label">فترة الاستهلاك</div>
                    <div class="metric-value">{{ $invoice->consumption_period_days ?? '—' }}</div>
                    <div class="metric-unit">يوم</div>
                </div>
                <div class="metric-card accent">
                    <div class="metric-label">الاستهلاك</div>
                    <div class="metric-value accent">{{ number_format($invoice->consumption_kwh, 2) }}</div>
                    <div class="metric-unit">kWh</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">سعر الكيلوواط</div>
                    <div class="metric-value">{{ number_format($invoice->price_per_kwh, 2) }}</div>
                    <div class="metric-unit">₪ / kWh</div>
                </div>
                <div class="metric-card {{ $eDiscount > 0 ? 'success' : '' }}">
                    <div class="metric-label">
                        نسبة الخصم
                        @if($isEmployee)<span style="font-size:9px;color:#16a34a;"> موظف شركة</span>@endif
                    </div>
                    <div class="metric-value {{ $eDiscount > 0 ? 'success' : 'muted' }}">{{ number_format($eDiscount, 0) }}</div>
                    <div class="metric-unit">@if($isEmployee)% تلقائي@else%@endif</div>
                </div>
                <div class="metric-card {{ $eMinCharge > 0 ? 'warning' : '' }}">
                    <div class="metric-label">
                        الحد الأدنى
                        @if($subAmpere && $autoMinCharge !== null)
                            <span style="font-size:9px;color:#856404;">{{ $subAmpere }}أ / {{ $phaseLabel }}ف</span>
                        @endif
                    </div>
                    <div class="metric-value" style="color:{{ $eMinCharge > 0 ? '#856404' : '#6c757d' }}">{{ number_format($eMinCharge, 2) }}</div>
                    <div class="metric-unit">₪</div>
                </div>
            </div>

            {{-- Calculation Steps Table --}}
            <table class="calc-table">
                <thead>
                    <tr>
                        <th>الاحتساب</th>
                        <th>المبلغ (₪)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span class="step-label">1</span>
                            ثمن الاستهلاك
                            <div class="cell-desc">
                                الاستهلاك × سعر الكيلوواط{{ $eDiscount > 0 ? ' × (1 − ' . number_format($eDiscount, 0) . '%)' : '' }}
                                @if($isEmployee && $eDiscount > 0)
                                    &mdash; <span style="color:#16a34a;font-weight:600">خصم موظف شركة</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ number_format($eCost, 2) }} ₪</td>
                    </tr>
                    @if($eMinCharge > 0)
                    <tr>
                        <td>
                            <span class="step-label">2</span>
                            الحد الأدنى للفاتورة
                            @if($subAmpere && $autoMinCharge !== null)
                                <div class="cell-desc">حسب اشتراك {{ $subAmpere }} أمبير / {{ $phaseLabel }} فاز</div>
                            @endif
                        </td>
                        <td>{{ number_format($eMinCharge, 2) }} ₪</td>
                    </tr>
                    @endif
                    <tr class="row-highlight">
                        <td>
                            <span class="step-label" style="background:#bfdbfe;color:#3b82f6">3</span>
                            قيمة الفاتورة
                            @if($eMinCharge > 0)
                                <div class="cell-desc">القيمة الأعلى بين ثمن الاستهلاك والحد الأدنى</div>
                            @endif
                        </td>
                        <td style="color:#3b82f6; font-size:13px">{{ number_format($eInvAmt, 2) }} ₪</td>
                    </tr>
                    @if($eDiscount > 0)
                    @php
                        $discountValue = (float)$invoice->consumption_kwh * (float)$invoice->price_per_kwh * ($eDiscount / 100);
                    @endphp
                    <tr class="row-success">
                        <td>
                            <span class="step-label" style="background:#bbf7d0;color:#16a34a">%</span>
                            قيمة الخصم ({{ number_format($eDiscount, 0) }}%)
                            @if($isEmployee)
                                <div class="cell-desc">خصم موظف شركة</div>
                            @endif
                        </td>
                        <td style="color:#16a34a">-{{ number_format($discountValue, 2) }} ₪</td>
                    </tr>
                    @endif
                    <tr class="@if($ePrevBal > 0) row-danger @elseif($ePrevBal < 0) row-success @endif">
                        <td>
                            <span class="step-label">4</span>
                            الرصيد السابق
                            @if($ePrevBal > 0)
                                <div class="cell-desc">مديون على المشترك — يُضاف للفاتورة</div>
                            @elseif($ePrevBal < 0)
                                <div class="cell-desc">دائن لصالح المشترك — يُخصم من الفاتورة</div>
                            @else
                                <div class="cell-desc">لا يوجد رصيد سابق</div>
                            @endif
                        </td>
                        <td>{{ $ePrevBal > 0 ? '+' : '' }}{{ number_format($ePrevBal, 2) }} ₪</td>
                    </tr>
                    <tr class="row-total">
                        <td>
                            <span class="step-label" style="background:rgba(255,255,255,0.25);color:#fff">5</span>
                            المبلغ المطلوب
                            <div style="font-size:10px;opacity:.8;font-weight:400;margin-top:1px">قيمة الفاتورة + الرصيد السابق</div>
                        </td>
                        <td style="font-size:16px">{{ number_format($eTotal, 2) }} ₪</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ===== Summary (only shown if there are payments) ===== --}}
        @if($paidAmt > 0)
        <div class="summary-section">
            <table class="summary-table">
                <tr class="total-row">
                    <td>المبلغ المطلوب</td>
                    <td>{{ number_format($invoice->total_amount, 2) }} ₪</td>
                </tr>
                <tr class="paid-row">
                    <td>إجمالي المسدد</td>
                    <td>{{ number_format($paidAmt, 2) }} ₪</td>
                </tr>
                <tr class="remaining-row">
                    <td>المتبقي للسداد</td>
                    <td>{{ number_format($remaining, 2) }} ₪</td>
                </tr>
            </table>
        </div>
        @endif

        {{-- ===== Payment Progress (if any payments) ===== --}}
        @if($paidAmt > 0)
        <div class="payment-status">
            @php
                $totalAmt = (float)$invoice->total_amount;
                $paidPct  = $totalAmt > 0 ? min(round(($paidAmt / $totalAmt) * 100), 100) : 0;
            @endphp
            <div class="payment-bar-container">
                <div class="payment-bar" style="width: {{ $paidPct }}%"></div>
            </div>
            <div class="payment-info">
                <span>مسدد: {{ number_format($paidAmt, 2) }} ₪ ({{ $paidPct }}%)</span>
                <span>متبقي: {{ number_format($remaining, 2) }} ₪</span>
            </div>
        </div>
        @endif

        {{-- ===== Notes ===== --}}
        @if($invoice->notes)
        <div class="notes-section">
            <div class="notes-title">ملاحظات:</div>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        {{-- ===== Footer ===== --}}
        <div class="invoice-footer">
            <div class="footer-grid">
                <div class="footer-box">
                    <div class="footer-box-label">أُصدرت بواسطة</div>
                    <div class="footer-box-value">{{ $invoice->issuer?->name ?? $invoice->creator?->name ?? '—' }}</div>
                    <div class="footer-box-label" style="margin-top:2px">{{ $invoice->issued_at?->format('Y-m-d H:i') ?? $invoice->created_at?->format('Y-m-d H:i') }}</div>
                </div>
                <div class="footer-box">
                    <div class="footer-box-label">توقيع المحصّل</div>
                    <div style="min-height: 30px;"></div>
                </div>
                <div class="footer-box">
                    <div class="footer-box-label">توقيع المشترك</div>
                    <div style="min-height: 30px;"></div>
                </div>
            </div>

            <div class="footer-note">
                <strong>{{ $siteName }}</strong> — نظام إدارة الطاقة والفوترة الإلكتروني
                <br>
                تم إنشاء هذه الفاتورة إلكترونياً وهي صالحة بدون توقيع أو ختم
                <br>
                تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}
            </div>
        </div>

    </div>

    <script>
        // Auto-print on load (optional, user can click print button)
        // window.onload = function() { window.print(); };
        @if(!empty($publicPrint))
        // فتح حوار الطباعة تلقائياً للعرض العام بعد تحميل الخط/الشعار
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 700);
        });
        @endif
    </script>
</body>
</html>
