<?php

namespace App\Imports;

use App\Helpers\ConstantsHelper;
use App\Models\Subscriber;
use App\Models\GenerationUnit;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SubscribersImport
{
    protected $errors = [];
    protected $validRows = [];
    protected $previewMode = false;
    protected $generationUnitId;
    protected $userId;

    public function __construct($generationUnitId = null, $userId = null, $previewMode = false)
    {
        $this->generationUnitId = $generationUnitId;
        $this->userId = $userId;
        $this->previewMode = $previewMode;
    }

    /**
     * Import from file path
     */
    public function import(string $filePath): self
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        
        if (empty($rows)) {
            return $this;
        }

        // Get headers from first row
        $headers = array_shift($rows);
        $headers = $this->normalizeHeaders($headers);
        
        $rowNumber = 1;
        foreach ($rows as $rowData) {
            $rowNumber++;
            
            // Skip empty rows
            if ($this->isEmptyRow($rowData)) {
                continue;
            }

            // Map row data with headers
            $row = array_combine($headers, array_values($rowData));
            $data = $this->mapRowData($row);
            $validation = $this->validateRow($data, $rowNumber);
            
            if ($validation['valid']) {
                $this->validRows[] = array_merge([
                    'row_number' => $rowNumber,
                ], $data, [
                    'name' => $data['subscriber_name'],
                    'category_label' => $this->getLabelFromConstant(23, $data['subscription_category']),
                    'phase_label' => $this->getLabelFromConstant(24, $data['phase_type']),
                    'service_label' => $this->getLabelFromConstant(26, $data['service_type']),
                    'ampere_label' => $data['ampere'] ? $data['ampere'] . ' A' : '-',
                    'employee_label' => ($data['is_employee_subscription'] ?? false) ? 'نعم' : 'لا',
                ]);
                
                // If not in preview mode, create the subscriber
                if (!$this->previewMode) {
                    $this->createSubscriber($data);
                }
            } else {
                $this->errors[] = [
                    'row_number' => $rowNumber,
                    'data' => $data,
                    'errors' => $validation['errors']
                ];
            }
        }

        return $this;
    }

    /**
     * Normalize headers to lowercase with underscores
     */
    protected function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            if ($header === null) {
                return 'column_' . uniqid();
            }
            // Remove extra spaces and convert to lowercase
            $header = trim(strtolower($header));
            // Replace spaces with underscores
            $header = str_replace(' ', '_', $header);
            return $header;
        }, $headers);
    }

    protected function isEmptyRow($row): bool
    {
        if (!is_array($row)) {
            return true;
        }
        
        $filtered = array_filter($row, function ($value) {
            return !empty($value) && $value !== null;
        });
        
        return empty($filtered);
    }

    protected function mapRowData($row): array
    {
        $altPhone = trim($row['رقم_جوال_بديل'] ?? $row['alt_phone'] ?? '');
        $boxNumber = trim($row['رقم_الصندوق'] ?? $row['box_number'] ?? '');
        $requestDate = trim($row['تاريخ_الطلب'] ?? $row['request_date'] ?? '');
        $notes = trim($row['ملاحظات'] ?? $row['notes'] ?? '');
        $ampere = trim($row['الأمبير'] ?? $row['ampere'] ?? '');
        $openingReading = trim($row['القراءة_الافتتاحية'] ?? $row['opening_reading'] ?? '');
        $isEmployee = trim($row['اشتراك_موظف'] ?? $row['is_employee_subscription'] ?? $row['is_employee'] ?? '');

        return [
            'subscriber_id_number' => trim($row['رقم_الهوية'] ?? $row['subscriber_id_number'] ?? $row['id_number'] ?? ''),
            'subscriber_name' => trim($row['اسم_المشترك'] ?? $row['subscriber_name'] ?? $row['name'] ?? ''),
            'phone' => $this->normalizePhone($row['رقم_الموبايل'] ?? $row['phone'] ?? $row['mobile'] ?? ''),
            'alt_phone' => $altPhone ? $this->normalizePhone($altPhone) : '',
            'address' => trim($row['العنوان'] ?? $row['address'] ?? ''),
            'box_number' => $boxNumber,
            'meter_number' => trim($row['رقم_العداد'] ?? $row['meter_number'] ?? ''),
            'ampere' => $this->mapAmpere($ampere),
            'opening_reading' => is_numeric($openingReading) ? (float) $openingReading : null,
            'subscription_category' => $this->mapCategory($row['تصنيف_الاشتراك'] ?? $row['subscription_category'] ?? $row['category'] ?? ''),
            'phase_type' => $this->mapPhaseType($row['نوع_الفاز'] ?? $row['phase_type'] ?? ''),
            'service_type' => $this->mapServiceType($row['نوع_الخدمة'] ?? $row['service_type'] ?? ''),
            'is_employee_subscription' => $this->mapBoolean($isEmployee),
            'request_date' => $requestDate,
            'notes' => $notes,
        ];
    }

    protected function normalizePhone($phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 9 && substr($phone, 0, 2) === '59') {
            $phone = '0' . $phone;
        }
        if (strlen($phone) === 9 && substr($phone, 0, 2) === '56') {
            $phone = '0' . $phone;
        }
        
        return $phone;
    }

    /**
     * بناء map ديناميكي من الثوابت: label → value, value → value
     */
    protected function buildConstantMap(int $constantNumber, int $default = 1): array
    {
        $map = [];
        foreach (ConstantsHelper::get($constantNumber) as $item) {
            $map[$item->label] = (int) $item->value;
            $map[(string) $item->value] = (int) $item->value;
            $map[(int) $item->value] = (int) $item->value;
        }
        $map['__default'] = $default;
        return $map;
    }

    protected function mapCategory($value): int
    {
        $map = $this->buildConstantMap(23, 1);
        return $map[$value] ?? $map['__default'];
    }

    protected function mapPhaseType($value): int
    {
        $map = $this->buildConstantMap(24, 1);
        return $map[$value] ?? $map['__default'];
    }

    protected function mapServiceType($value): int
    {
        $map = $this->buildConstantMap(26, 1);
        return $map[$value] ?? $map['__default'];
    }

    protected function mapAmpere($value): ?float
    {
        if (empty($value)) return null;

        // إذا كان رقم مباشرة
        $numeric = preg_replace('/[^0-9.]/', '', $value);
        if (is_numeric($numeric) && $numeric > 0) {
            return (float) $numeric;
        }

        // محاولة المطابقة من الثوابت
        $map = $this->buildConstantMap(31, 0);
        $mapped = $map[$value] ?? 0;
        return $mapped > 0 ? (float) $mapped : null;
    }

    protected function mapBoolean($value): bool
    {
        if (empty($value)) return false;
        $trueValues = ['نعم', 'yes', '1', 'true', 1, true];
        return in_array($value, $trueValues, false);
    }

    /**
     * جلب label من الثوابت حسب value
     */
    protected function getLabelFromConstant(int $constantNumber, $value): string
    {
        $details = ConstantsHelper::get($constantNumber);
        $match = $details->firstWhere('value', (string) $value);
        return $match ? $match->label : '-';
    }

    protected function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];

        // التحقق من الحقول المطلوبة
        if (empty($data['subscriber_id_number'])) {
            $errors[] = 'رقم الهوية مطلوب';
        }
        
        if (empty($data['subscriber_name'])) {
            $errors[] = 'اسم المشترك مطلوب';
        }
        
        if (empty($data['phone'])) {
            $errors[] = 'رقم الموبايل مطلوب';
        } elseif (!preg_match('/^05[69]\d{7}$/', $data['phone'])) {
            $errors[] = 'رقم الموبايل غير صحيح (يجب أن يبدأ بـ 059 أو 056)';
        }
        
        if (empty($data['address'])) {
            $errors[] = 'العنوان مطلوب';
        }
        
        if (empty($data['meter_number'])) {
            $errors[] = 'رقم العداد مطلوب';
        }

        // التحقق من رقم الجوال البديل
        if (!empty($data['alt_phone']) && !preg_match('/^05[69]\d{7}$/', $data['alt_phone'])) {
            $errors[] = 'رقم الجوال البديل غير صحيح (يجب أن يبدأ بـ 059 أو 056)';
        }

        // التحقق من رقم الصندوق
        if (!empty($data['box_number']) && !preg_match('/^\d{4}$/', $data['box_number'])) {
            $errors[] = 'رقم الصندوق يجب أن يكون 4 أرقام';
        }

        // التحقق من تاريخ الطلب
        if (!empty($data['request_date'])) {
            $date = date_parse($data['request_date']);
            if (!checkdate($date['month'] ?? 0, $date['day'] ?? 0, $date['year'] ?? 0)) {
                $errors[] = 'تاريخ الطلب غير صحيح (يجب أن يكون بصيغة YYYY-MM-DD)';
            }
        }

        // التحقق من التكرار في قاعدة البيانات
        if (!empty($data['subscriber_id_number'])) {
            if (Subscriber::where('subscriber_id_number', $data['subscriber_id_number'])->exists()) {
                $errors[] = 'رقم الهوية موجود مسبقاً';
            }
        }
        
        if (!empty($data['phone'])) {
            if (Subscriber::where('phone', $data['phone'])->exists()) {
                $errors[] = 'رقم الموبايل موجود مسبقاً';
            }
        }
        
        if (!empty($data['meter_number'])) {
            if (Subscriber::where('meter_number', $data['meter_number'])->exists()) {
                $errors[] = 'رقم العداد موجود مسبقاً';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    protected function createSubscriber(array $data): void
    {
        // توليد رقم الاشتراك
        $subscriptionNumber = Subscriber::generateSubscriptionNumber($this->generationUnitId, $data['phase_type']);
        
        // الحصول على رقم المحافظة (value) من وحدة التوليد
        $subscriber = Subscriber::create([
            'subscriber_id_number' => $data['subscriber_id_number'],
            'subscriber_name' => $data['subscriber_name'],
            'phone' => $data['phone'],
            'alt_phone' => !empty($data['alt_phone']) ? $data['alt_phone'] : null,
            'address' => $data['address'],
            'box_number' => !empty($data['box_number']) ? $data['box_number'] : null,
            'subscription_date' => now(),
            'request_date' => !empty($data['request_date']) ? $data['request_date'] : null,
            'subscription_category' => $data['subscription_category'],
            'phase_type' => $data['phase_type'],
            'subscription_status' => 1, // نشط
            'meter_number' => $data['meter_number'],
            'ampere' => $data['ampere'] ?? null,
            'opening_reading' => $data['opening_reading'] ?? null,
            'service_type' => $data['service_type'],
            'is_employee_subscription' => $data['is_employee_subscription'] ?? false,
            'subscription_number' => $subscriptionNumber,
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
            'created_by' => $this->userId,
            'last_updated_by' => $this->userId,
        ]);

        // ربط بوحدة التوليد
        $subscriber->generationUnits()->attach($this->generationUnitId);
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
            'valid' => $this->validRows,
            'errors' => $this->errors,
            'total' => count($this->validRows) + count($this->errors),
            'valid_count' => count($this->validRows),
            'error_count' => count($this->errors),
        ];
    }
}
