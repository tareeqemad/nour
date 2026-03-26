<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $generator->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #F8FAFC; padding: 20px; color: #1F2937; }

        .qr-container { max-width: 700px; margin: 0 auto; background: #fff; border-radius: 14px; border: 1px solid #E5E7EB; padding: 2.5rem; }

        .qr-header { text-align: center; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 2.5px solid #24308F; }
        .qr-header h1 { color: #24308F; font-size: 1.5rem; margin-bottom: 0.35rem; }
        .qr-header p { color: #5B6780; font-size: 0.88rem; }

        .qr-content { display: flex; flex-direction: column; align-items: center; gap: 1.5rem; }

        .qr-code-wrapper { padding: 1.25rem; border-radius: 10px; border: 1.5px solid #E5E7EB; background: #FAFCFF; display: inline-block; }
        .qr-code-wrapper svg { display: block; max-width: 350px; height: auto; }

        .qr-info { width: 100%; background: #FAFCFF; border: 1px solid #EDF1F5; border-radius: 10px; padding: 1.25rem; }
        .qr-info h3 { color: #24308F; margin-bottom: 0.85rem; font-size: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #EDF1F5; }

        .info-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #EDF1F5; font-size: 0.88rem; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-weight: 600; color: #3B4863; }
        .info-value { color: #1F2937; text-align: left; }
        .info-note { font-size: 0.78rem; color: #5B6780; }

        .print-btn-wrap { margin-top: 1.5rem; text-align: center; }
        .btn-print { background: #24308F; color: #fff; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
        .btn-print:hover { background: #2330B3; }

        @media print {
            body { background: #fff; padding: 0; }
            .qr-container { border: none; padding: 1rem; }
            .print-btn-wrap { display: none; }
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <div class="qr-header">
            <h1>{{ $generator->name ?? 'المولد' }}</h1>
            <p>{{ $generator->generator_number ?? '' }}</p>
        </div>

        <div class="qr-content">
            <div class="qr-code-wrapper">
                {!! $qrCodeSvg !!}
            </div>

            <div class="qr-info">
                <h3>بيانات المولد</h3>
                @foreach([
                    ['label' => 'رقم المولد', 'value' => $generator->generator_number ?? 'GEN-'.$generator->id, 'bold' => true],
                    ['label' => 'اسم المولد', 'value' => $generator->name],
                    ['label' => 'المشغل', 'value' => $generator->operator->name ?? null],
                    ['label' => 'وحدة التوليد', 'value' => $generator->generationUnit ? $generator->generationUnit->name.' ('.$generator->generationUnit->unit_code.')' : null],
                    ['label' => 'القدرة', 'value' => $generator->capacity_kva ? number_format($generator->capacity_kva, 0).' KVA' : null],
                ] as $row)
                    @if($row['value'])
                    <div class="info-row">
                        <span class="info-label">{{ $row['label'] }}</span>
                        <span class="info-value">{!! ($row['bold'] ?? false) ? '<strong>'.$row['value'].'</strong>' : $row['value'] !!}</span>
                    </div>
                    @endif
                @endforeach
                <div class="info-row">
                    <span class="info-note">عند المسح تُعرض البيانات أوفلاين بصيغة JSON</span>
                </div>
            </div>
        </div>

        <div class="print-btn-wrap">
            <button class="btn-print" onclick="window.print()">طباعة QR Code</button>
        </div>
    </div>
</body>
</html>
