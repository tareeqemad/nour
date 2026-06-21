<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * استعلام عام يتيح للمشترك معرفة فواتيره المستحقة دون تسجيل دخول.
 *
 * المصادقة تتم بمطابقة ثلاثة حقول معاً: رقم الاشتراك + رقم الهوية + رقم الجوال.
 * الأرقام المالية تُحتسب بنفس منطق التسوية الداخلي (FIFO على invoice_amount مطروحاً
 * منه مجموع الدفعات) لتفادي ازدواجية previous_balance المضمّنة في total_amount.
 */
class SubscriberInvoiceController extends Controller
{
    /**
     * صفحة نموذج الاستعلام.
     */
    public function index(): View
    {
        return view('subscriber-invoices.index');
    }

    /**
     * البحث عن فواتير المشترك (JSON — يُستدعى عبر fetch من الواجهة).
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $this->validateInput($request);
        $data = $this->resolveInvoiceData($validated);

        if ($data === null) {
            // رسالة عامة لا تكشف أي الحقول كان خاطئاً (منعاً للتخمين)
            return response()->json([
                'found'   => false,
                'message' => 'لم نتمكن من العثور على اشتراك بهذه البيانات. يرجى التأكد من رقم الاشتراك ورقم الهوية ورقم الجوال ثم المحاولة مرة أخرى.',
            ]);
        }

        return response()->json([
            'found'      => true,
            'subscriber' => $data['subscriber'],
            'summary'    => $data['summary'],
            'invoices'   => $data['invoices'],
        ]);
    }

    /**
     * تصدير فواتير المشترك كملف Excel (.xlsx).
     */
    public function export(Request $request): StreamedResponse|JsonResponse
    {
        $validated = $this->validateInput($request);
        $data = $this->resolveInvoiceData($validated);

        if ($data === null) {
            return response()->json([
                'found'   => false,
                'message' => 'لم نتمكن من العثور على اشتراك بهذه البيانات.',
            ], 422);
        }

        return $this->buildExcel($data);
    }

    // ===================== Internals =====================

    /**
     * قواعد التحقق المشتركة بين البحث والتصدير.
     */
    private function validateInput(Request $request): array
    {
        return $request->validate([
            'subscription_number' => ['required', 'string', 'max:255'],
            'id_number'           => ['required', 'string', 'max:255'],
            'phone'               => ['required', 'string', 'max:30'],
            'from_date'           => ['nullable', 'date'],
            'to_date'             => ['nullable', 'date', 'after_or_equal:from_date'],
        ], [
            'subscription_number.required' => 'يرجى إدخال رقم الاشتراك',
            'id_number.required'           => 'يرجى إدخال رقم الهوية',
            'phone.required'               => 'يرجى إدخال رقم الجوال',
            'to_date.after_or_equal'       => 'تاريخ نهاية الفترة يجب أن يكون بعد تاريخ البداية',
        ]);
    }

    /**
     * يطابق المشترك ويحتسب الأرصدة والفواتير المعروضة ضمن الفترة.
     * يُرجع مصفوفة جاهزة للعرض/التصدير، أو null إذا لم يُعثر على تطابق.
     */
    private function resolveInvoiceData(array $validated): ?array
    {
        $subscriptionNumber = trim($validated['subscription_number']);
        $idNumber           = trim($validated['id_number']);
        $phone              = preg_replace('/\s+/', '', trim($validated['phone']));

        // المصادقة: لا بد من تطابق الحقول الثلاثة معاً (الجوال أو الجوال البديل)
        $subscriber = Subscriber::query()
            ->where('subscription_number', $subscriptionNumber)
            ->where('subscriber_id_number', $idNumber)
            ->where(function ($q) use ($phone) {
                $q->where('phone', $phone)->orWhere('alt_phone', $phone);
            })
            ->first();

        if (! $subscriber) {
            return null;
        }

        // كل فواتير المشترك (عدا المسودات والملغاة) مرتّبة الأقدم أولاً لتوزيع FIFO
        $allInvoices = $subscriber->invoices()
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get(['id', 'invoice_number', 'invoice_date', 'due_date', 'consumption_kwh', 'invoice_amount']);

        // المجمّع: إجمالي ما دفعه المشترك على هذه الفواتير
        $pool = (float) Payment::whereIn('invoice_id', $allInvoices->pluck('id'))->sum('amount_paid');

        // توزيع المدفوعات FIFO على رسوم كل فاتورة (نفس منطق Invoice::reconcileSubscriber)
        $remainingMap = [];
        $running = $pool;
        foreach ($allInvoices as $inv) {
            $charge = (float) $inv->invoice_amount;
            if ($charge <= 0) {
                $remainingMap[$inv->id] = 0.0;
            } elseif ($running >= $charge) {
                $remainingMap[$inv->id] = 0.0;
                $running -= $charge;
            } elseif ($running > 0) {
                $remainingMap[$inv->id] = round($charge - $running, 2);
                $running = 0;
            } else {
                $remainingMap[$inv->id] = round($charge, 2);
            }
        }

        $creditBalance = round($running, 2);                  // رصيد دائن (دفع زائد) إن وُجد
        $netBalance    = round(array_sum($remainingMap), 2);  // إجمالي المستحق الحالي
        $unpaidCount   = count(array_filter($remainingMap, fn ($r) => $r > 0));

        // تصفية القائمة المعروضة حسب الفترة المختارة (على تاريخ الفاتورة)
        $from = $validated['from_date'] ?? null;
        $to   = $validated['to_date'] ?? null;

        $today = now()->startOfDay();

        $invoices = $allInvoices
            ->filter(function ($inv) use ($from, $to) {
                $d = optional($inv->invoice_date)->format('Y-m-d');
                if (! $d) {
                    return false;
                }
                if ($from && $d < $from) {
                    return false;
                }
                if ($to && $d > $to) {
                    return false;
                }
                return true;
            })
            ->sortByDesc('invoice_date')
            ->values()
            ->map(function ($inv) use ($remainingMap, $today) {
                $remaining = $remainingMap[$inv->id] ?? 0.0;
                $charge    = (float) $inv->invoice_amount;
                $isOverdue = $remaining > 0 && $inv->due_date && $inv->due_date->lt($today);

                if ($remaining <= 0) {
                    [$key, $label] = ['paid', 'مسددة'];
                } elseif ($remaining < $charge) {
                    [$key, $label] = ['partial', 'مسددة جزئياً'];
                } elseif ($isOverdue) {
                    [$key, $label] = ['overdue', 'متأخرة'];
                } else {
                    [$key, $label] = ['due', 'مستحقة'];
                }

                return [
                    'invoice_number'  => $inv->invoice_number ?: '—',
                    'invoice_date'    => optional($inv->invoice_date)->format('Y-m-d'),
                    'due_date'        => optional($inv->due_date)->format('Y-m-d'),
                    'consumption_kwh' => (float) $inv->consumption_kwh,
                    'amount'          => round($charge, 2),
                    'remaining'       => $remaining,
                    'status_key'      => $key,
                    'status_label'    => $label,
                    'is_overdue'      => $isOverdue,
                ];
            });

        return [
            'subscriber' => [
                'name'                => $subscriber->subscriber_name,
                'subscription_number' => $subscriber->subscription_number,
                'meter_number'        => $subscriber->meter_number,
            ],
            'summary' => [
                'net_balance'    => $netBalance,
                'credit_balance' => $creditBalance,
                'unpaid_count'   => $unpaidCount,
                'total_invoices' => $allInvoices->count(),
                'period_due'     => round($invoices->sum('remaining'), 2),
                'period_count'   => $invoices->count(),
            ],
            'invoices' => $invoices,
        ];
    }

    /**
     * يبني ملف Excel من البيانات المحلولة ويُرجعه كتنزيل.
     */
    private function buildExcel(array $data): StreamedResponse
    {
        $sub     = $data['subscriber'];
        $summary = $data['summary'];
        $invoices = $data['invoices'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('فواتيري');
        $sheet->setRightToLeft(true);

        $navy   = '24308F';
        $light  = 'EEF2FF';
        $red    = 'DC2626';
        $headers = ['رقم الفاتورة', 'تاريخ الفاتورة', 'تاريخ الاستحقاق', 'الاستهلاك (ك.و.س)', 'قيمة الفاتورة (₪)', 'المتبقي (₪)', 'الحالة'];
        $lastCol = 'G';

        // ===== ترويسة الكشف =====
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'كشف حساب المشترك — فواتيري');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB($navy);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $meta = [
            ['المشترك', $sub['name']],
            ['رقم الاشتراك', $sub['subscription_number']],
            ['رقم العداد', $sub['meter_number'] ?: '—'],
            ['إجمالي المبلغ المستحق', number_format($summary['net_balance'], 2) . ' ₪'],
            ['عدد الفواتير غير المسددة', (string) $summary['unpaid_count']],
            ['تاريخ الإصدار', now()->format('Y-m-d H:i')],
        ];

        $row = 2;
        foreach ($meta as $line) {
            $sheet->setCellValue("A{$row}", $line[0] . ':');
            $sheet->mergeCells("B{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("B{$row}", $line[1]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->getColor()->setRGB('5B6780');
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);
            $row++;
        }
        // إبراز المبلغ المستحق بالأحمر
        $sheet->getStyle('B5')->getFont()->getColor()->setRGB($red);
        $sheet->getStyle('B5')->getFont()->setSize(12);

        // ===== رأس الجدول =====
        $row++; // سطر فارغ
        $headerRow = $row;
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue("{$col}{$headerRow}", $h);
            $col++;
        }
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navy]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        // ===== صفوف الفواتير =====
        $dataStart = $headerRow + 1;
        $row = $dataStart;
        foreach ($invoices as $inv) {
            $sheet->setCellValueExplicit("A{$row}", $inv['invoice_number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("B{$row}", $inv['invoice_date'] ?: '—');
            $sheet->setCellValue("C{$row}", $inv['due_date'] ?: '—');
            $sheet->setCellValue("D{$row}", $inv['consumption_kwh']);
            $sheet->setCellValue("E{$row}", $inv['amount']);
            $sheet->setCellValue("F{$row}", $inv['remaining']);
            $sheet->setCellValue("G{$row}", $inv['status_label']);

            // المتبقي بالأحمر إذا > 0
            if ($inv['remaining'] > 0) {
                $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setRGB($red);
            }
            $row++;
        }
        $dataEnd = $row - 1;

        // تنسيق الأرقام
        if ($dataEnd >= $dataStart) {
            $sheet->getStyle("D{$dataStart}:D{$dataEnd}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("E{$dataStart}:F{$dataEnd}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // صف الإجمالي
            $row++;
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", 'إجمالي المستحق ضمن الفواتير المعروضة');
            $sheet->setCellValue("F{$row}", $summary['period_due']);
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $light]],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB($red);
        }

        // عرض الأعمدة
        foreach (['A' => 22, 'B' => 16, 'C' => 16, 'D' => 18, 'E' => 18, 'F' => 16, 'G' => 16] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        $fileName = 'فواتير-' . $sub['subscription_number'] . '-' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-store',
        ]);
    }
}
