<?php

namespace App\Imports;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscriber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * استيراد دفعات من ملف Excel.
 *
 * الأعمدة المتوقَّعة (العناوين تقبل العربي أو الإنجليزي):
 *  - رقم_الفاتورة      (invoice_number)       — اختياري
 *  - رقم_الاشتراك      (subscription_number)  — اختياري
 *  - رقم_الهوية         (id_number)            — اختياري
 *  - تاريخ_الدفع        (payment_date)         — اختياري (افتراضي: اليوم)
 *  - المبلغ             (amount_paid)          — مطلوب > 0
 *  - طريقة_الدفع        (payment_method)       — اختياري (افتراضي: نقداً)
 *
 * يجب تعبئة أحد أعمدة التعريف الثلاثة الأولى على الأقل.
 * الأولوية: رقم_الفاتورة ← رقم_الاشتراك ← رقم_الهوية.
 *
 * مطابقة الفاتورة:
 *   - رقم_الفاتورة       → الفاتورة مباشرة، ويجب أن تكون جديدة (ISSUED).
 *   - رقم_الاشتراك/الهوية → أقدم فاتورة جديدة (ISSUED) للمشترك.
 *   - الهوية المكرّرة بين مشتركين مختلفين → رفض الصف وطلب رقم الاشتراك.
 *
 * في وضع التنفيذ يتم:
 *   1) قفل الفاتورة (lockForUpdate) داخل transaction
 *   2) احتساب overpayment
 *   3) إنشاء Payment
 *   4) تحديث حالة الفاتورة (updateStatusFromPayments)
 *   5) إعادة احتساب الرصيد السابق لمسودات لاحقة لنفس المشترك
 *   6) تسجيل AuditLog
 */
class PaymentsImport
{
    protected array $errors = [];
    protected array $validRows = [];
    protected bool $previewMode;
    protected ?int $userId;
    protected array $allowedOperatorIds;
    protected bool $scopeByOperator;

    // map طرق الدفع من نصوص متعددة → قيمة Payment::METHOD_*
    protected array $methodMap = [
        'نقدا'          => Payment::METHOD_CASH,
        'نقداً'          => Payment::METHOD_CASH,
        'نقدي'          => Payment::METHOD_CASH,
        'cash'          => Payment::METHOD_CASH,
        'تحويل بنكي'    => Payment::METHOD_BANK_TRANSFER,
        'تحويل'          => Payment::METHOD_BANK_TRANSFER,
        'بنك'            => Payment::METHOD_BANK_TRANSFER,
        'bank_transfer' => Payment::METHOD_BANK_TRANSFER,
        'bank'          => Payment::METHOD_BANK_TRANSFER,
        'شيك'            => Payment::METHOD_CHEQUE,
        'cheque'        => Payment::METHOD_CHEQUE,
        'check'         => Payment::METHOD_CHEQUE,
        'دفع إلكتروني' => Payment::METHOD_ONLINE,
        'إلكتروني'     => Payment::METHOD_ONLINE,
        'online'        => Payment::METHOD_ONLINE,
    ];

    public function __construct(?int $userId = null, bool $previewMode = false, array $allowedOperatorIds = [], bool $scopeByOperator = false)
    {
        $this->userId = $userId;
        $this->previewMode = $previewMode;
        $this->allowedOperatorIds = $allowedOperatorIds;
        $this->scopeByOperator = $scopeByOperator;
    }

    public function import(string $filePath): self
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return $this;
        }

        $headers = array_shift($rows);
        $headers = $this->normalizeHeaders($headers);

        // المشتركون الذين تأثروا (لإعادة احتساب المسودات في النهاية)
        $touchedSubscribers = [];

        $rowNumber = 1;
        foreach ($rows as $rowData) {
            $rowNumber++;

            if ($this->isEmptyRow($rowData)) {
                continue;
            }

            $row = array_combine($headers, array_values($rowData));
            $data = $this->mapRowData($row);
            $validation = $this->validateRow($data, $rowNumber);

            if (!$validation['valid']) {
                $this->errors[] = [
                    'row_number' => $rowNumber,
                    'data' => $data,
                    'errors' => $validation['errors'],
                ];
                continue;
            }

            /** @var Invoice $invoice */
            $invoice = $validation['invoice'];
            $remaining = $invoice->remainingAmount();
            $overpayment = max($data['amount_paid'] - $remaining, 0);

            $this->validRows[] = [
                'row_number'          => $rowNumber,
                'invoice_number'      => $invoice->invoice_number,
                'subscriber_name'     => $invoice->subscriber->subscriber_name ?? '-',
                'subscription_number' => $invoice->subscriber->subscription_number ?? '-',
                'total_amount'        => (float) $invoice->total_amount,
                'already_paid'        => $invoice->paidAmount(),
                'remaining'           => $remaining,
                'amount_paid'         => $data['amount_paid'],
                'overpayment'         => round($overpayment, 2),
                'payment_date'        => $data['payment_date'],
                'method_label'        => Payment::methodLabels()[$data['payment_method']] ?? $data['payment_method'],
                'payment_method'      => $data['payment_method'],
            ];

            if ($this->previewMode) {
                continue;
            }

            try {
                DB::transaction(function () use ($invoice, $data) {
                    /** @var Invoice $locked */
                    $locked = Invoice::lockForUpdate()->find($invoice->id);
                    $remaining = $locked->remainingAmount();
                    $overpayment = max($data['amount_paid'] - $remaining, 0);

                    $payment = Payment::create([
                        'invoice_id'     => $locked->id,
                        'subscriber_id'  => $locked->subscriber_id,
                        'payment_date'   => $data['payment_date'],
                        'amount_paid'    => $data['amount_paid'],
                        'credit_applied' => 0,
                        'overpayment'    => round($overpayment, 2),
                        'payment_method' => $data['payment_method'],
                        'created_by'     => $this->userId,
                    ]);

                    $locked->refresh();
                    $locked->updateStatusFromPayments();

                    AuditLog::log(
                        'create',
                        $payment,
                        auth()->user(),
                        [],
                        $payment->toArray(),
                        'تسجيل دفعة عبر استيراد Excel للفاتورة: ' . ($locked->invoice_number ?? '#' . $locked->id)
                            . ' - المبلغ: ' . number_format($data['amount_paid'], 2)
                    );
                });

                $touchedSubscribers[$invoice->subscriber_id] = true;
            } catch (\Throwable $e) {
                // في حالة الفشل أثناء الإدراج، نقل الصف إلى الأخطاء
                array_pop($this->validRows);
                $this->errors[] = [
                    'row_number' => $rowNumber,
                    'data' => $data,
                    'errors' => ['فشل التنفيذ: ' . $e->getMessage()],
                ];
            }
        }

        // إعادة احتساب مسودات كل مشترك تأثَّر (مرة واحدة لكل مشترك)
        if (!$this->previewMode) {
            foreach (array_keys($touchedSubscribers) as $subscriberId) {
                Invoice::refreshDraftsForSubscriber((int) $subscriberId);
            }
        }

        return $this;
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            if ($header === null) {
                return 'column_' . uniqid();
            }
            $header = trim((string) $header);
            $header = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $header);
            $header = mb_strtolower($header, 'UTF-8');
            $header = str_replace(' ', '_', $header);
            return $header;
        }, $headers);
    }

    protected function isEmptyRow($row): bool
    {
        if (!is_array($row)) {
            return true;
        }
        $filtered = array_filter($row, fn($v) => !(is_null($v) || $v === ''));
        return empty($filtered);
    }

    protected function mapRowData(array $row): array
    {
        $invoiceNumber = trim((string) ($row['رقم_الفاتورة'] ?? $row['invoice_number'] ?? $row['invoice_no'] ?? ''));
        $subscriptionNumber = trim((string) ($row['رقم_الاشتراك'] ?? $row['subscription_number'] ?? ''));
        $idNumber = trim((string) ($row['رقم_الهوية'] ?? $row['id_number'] ?? $row['subscriber_id_number'] ?? ''));
        $rawDate = trim((string) ($row['تاريخ_الدفع'] ?? $row['payment_date'] ?? $row['date'] ?? ''));
        $rawAmount = $row['المبلغ'] ?? $row['amount_paid'] ?? $row['amount'] ?? null;
        $rawMethod = trim((string) ($row['طريقة_الدفع'] ?? $row['payment_method'] ?? $row['method'] ?? ''));

        return [
            'invoice_number'      => $invoiceNumber,
            'subscription_number' => $subscriptionNumber,
            'id_number'           => $idNumber,
            'payment_date'        => $this->parseDate($rawDate),
            'amount_paid'         => $this->parseAmount($rawAmount),
            'payment_method'      => $this->mapMethod($rawMethod),
            'raw_payment_method'  => $rawMethod,
        ];
    }

    protected function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // أرقام Excel serial dates
        if (is_numeric($value)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);
                return Carbon::instance($dt)->toDateString();
            } catch (\Throwable $e) {
                // fallback
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseAmount($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        // إزالة الفواصل والأحرف غير الرقمية (يحافظ على النقطة والسالب)
        $clean = preg_replace('/[^\d\.\-]/', '', (string) $value);
        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }
        return round((float) $clean, 2);
    }

    protected function mapMethod(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $value);
        $normalized = mb_strtolower(trim($normalized), 'UTF-8');

        return $this->methodMap[$normalized] ?? null;
    }

    protected function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];

        // يجب تعبئة واحد على الأقل من أعمدة التعريف
        if (empty($data['invoice_number']) && empty($data['subscription_number']) && empty($data['id_number'])) {
            $errors[] = 'يجب تعبئة رقم الفاتورة أو رقم الاشتراك أو رقم الهوية';
        }

        if ($data['amount_paid'] === null) {
            $errors[] = 'المبلغ مطلوب ورقمي';
        } elseif ($data['amount_paid'] <= 0) {
            $errors[] = 'المبلغ يجب أن يكون أكبر من صفر';
        }

        if (empty($data['payment_date'])) {
            $errors[] = 'تاريخ الدفع مطلوب وبصيغة صحيحة';
        }

        if (empty($data['raw_payment_method'])) {
            $errors[] = 'طريقة الدفع مطلوبة';
        } elseif (empty($data['payment_method'])) {
            $errors[] = 'طريقة الدفع غير معروفة (' . $data['raw_payment_method'] . ') — استخدم: نقداً / تحويل بنكي / شيك / دفع إلكتروني';
        }

        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }

        // === مطابقة الفاتورة — الأولوية: رقم الفاتورة ← رقم الاشتراك ← رقم الهوية ===
        $invoice = null;

        if (!empty($data['invoice_number'])) {
            $invoice = Invoice::with('subscriber.generationUnits')
                ->where('invoice_number', $data['invoice_number'])
                ->first();

            if (!$invoice) {
                return ['valid' => false, 'errors' => ['رقم الفاتورة غير موجود']];
            }

            if ($invoice->invoice_status !== Invoice::STATUS_ISSUED) {
                $statusLabel = $this->statusLabel($invoice->invoice_status);
                return ['valid' => false, 'errors' => ["لا يمكن تسجيل دفعة: الفاتورة ({$statusLabel}) — مسموح فقط للفواتير الجديدة"]];
            }
        } else {
            // بحث عن مشترك
            $subscriber = null;

            if (!empty($data['subscription_number'])) {
                $subscriber = Subscriber::with('generationUnits')
                    ->where('subscription_number', $data['subscription_number'])
                    ->first();
                if (!$subscriber) {
                    return ['valid' => false, 'errors' => ['رقم الاشتراك غير موجود']];
                }
            } else {
                // id_number
                $matches = Subscriber::with('generationUnits')
                    ->where('subscriber_id_number', $data['id_number'])
                    ->get();
                if ($matches->isEmpty()) {
                    return ['valid' => false, 'errors' => ['رقم الهوية غير موجود']];
                }
                if ($matches->count() > 1) {
                    return ['valid' => false, 'errors' => ['رقم الهوية مكرَّر لأكثر من مشترك — يرجى تحديد رقم الاشتراك']];
                }
                $subscriber = $matches->first();
            }

            // أقدم فاتورة جديدة للمشترك
            $invoice = Invoice::with('subscriber.generationUnits')
                ->where('subscriber_id', $subscriber->id)
                ->where('invoice_status', Invoice::STATUS_ISSUED)
                ->orderBy('invoice_date')
                ->orderBy('id')
                ->first();

            if (!$invoice) {
                return ['valid' => false, 'errors' => ['لا توجد فاتورة جديدة لهذا المشترك']];
            }
        }

        // نطاق المشغل — حماية: المستخدم المقيَّد بمشغل لا يقدر يسدد لمشترك خارج نطاقه
        if ($this->scopeByOperator) {
            if (empty($this->allowedOperatorIds)) {
                return ['valid' => false, 'errors' => ['لا تملك مشغلاً مرتبطاً — لا يمكنك تسجيل دفعات']];
            }
            $opIds = $invoice->subscriber?->generationUnits?->pluck('operator_id')->unique()->toArray() ?? [];
            if (empty($opIds)) {
                return ['valid' => false, 'errors' => ['المشترك غير مرتبط بأي مشغل']];
            }
            if (empty(array_intersect($opIds, $this->allowedOperatorIds))) {
                return ['valid' => false, 'errors' => ['المشترك لا ينتمي لمشغلك — لا يمكنك تسجيل دفعة على فاتورته']];
            }
        }

        $remaining = $invoice->remainingAmount();
        if ($remaining > 0 && $data['amount_paid'] > $remaining * 2) {
            return ['valid' => false, 'errors' => ['مبلغ الدفعة يتجاوز ضعف المتبقي (' . number_format($remaining, 2) . ')']];
        }

        return ['valid' => true, 'errors' => [], 'invoice' => $invoice];
    }

    protected function statusLabel(int $status): string
    {
        return [
            Invoice::STATUS_DRAFT     => 'مسودة',
            Invoice::STATUS_ISSUED    => 'جديدة',
            Invoice::STATUS_PARTIAL   => 'مسددة جزئياً',
            Invoice::STATUS_PAID      => 'مسددة بالكامل',
            Invoice::STATUS_CANCELLED => 'ملغاة',
        ][$status] ?? 'غير معروفة';
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getValidRows(): array
    {
        return $this->validRows;
    }

    public function getResults(): array
    {
        return [
            'valid'       => $this->validRows,
            'errors'      => $this->errors,
            'total'       => count($this->validRows) + count($this->errors),
            'valid_count' => count($this->validRows),
            'error_count' => count($this->errors),
        ];
    }
}
