<?php

namespace App\Imports;

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
                    'category' => $this->getCategoryText($data['subscription_category']),
                    'phase_type' => $this->getPhaseTypeText($data['phase_type']),
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
        return [
            'subscriber_id_number' => trim($row['رقم_الهوية'] ?? $row['subscriber_id_number'] ?? $row['id_number'] ?? ''),
            'subscriber_name' => trim($row['اسم_المشترك'] ?? $row['subscriber_name'] ?? $row['name'] ?? ''),
            'phone' => $this->normalizePhone($row['رقم_الموبايل'] ?? $row['phone'] ?? $row['mobile'] ?? ''),
            'address' => trim($row['العنوان'] ?? $row['address'] ?? ''),
            'meter_number' => trim($row['رقم_العداد'] ?? $row['meter_number'] ?? ''),
            'subscription_category' => $this->mapCategory($row['تصنيف_الاشتراك'] ?? $row['subscription_category'] ?? $row['category'] ?? ''),
            'phase_type' => $this->mapPhaseType($row['نوع_الفاز'] ?? $row['phase_type'] ?? ''),
            'service_type' => $this->mapServiceType($row['نوع_الخدمة'] ?? $row['service_type'] ?? ''),
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

    protected function mapCategory($value): int
    {
        $map = [
            'منزلي' => 1, 'سكني' => 1, '1' => 1, 1 => 1,
            'تجاري' => 2, '2' => 2, 2 => 2,
            'صناعي' => 3, '3' => 3, 3 => 3,
            'زراعي' => 4, '4' => 4, 4 => 4,
        ];
        
        return $map[$value] ?? 1;
    }

    protected function mapPhaseType($value): int
    {
        $map = [
            '1 فاز' => 1, 'فاز واحد' => 1, '1' => 1, 1 => 1, 'واحد' => 1,
            '3 فاز' => 2, 'ثلاث فاز' => 2, '3' => 2, 3 => 2, 2 => 2, 'ثلاثة' => 2,
        ];
        
        return $map[$value] ?? 1;
    }

    protected function mapServiceType($value): int
    {
        $map = [
            'مولد' => 1, 'مولّد' => 1, '1' => 1, 1 => 1,
            'شبكة' => 2, '2' => 2, 2 => 2,
            'مختلط' => 3, '3' => 3, 3 => 3,
        ];
        
        return $map[$value] ?? 1;
    }

    protected function getCategoryText($value): string
    {
        $map = [
            1 => 'residential',
            2 => 'commercial',
            3 => 'industrial',
            4 => 'agricultural',
        ];
        
        return $map[$value] ?? 'residential';
    }

    protected function getPhaseTypeText($value): string
    {
        $map = [
            1 => 'single',
            2 => 'three',
        ];
        
        return $map[$value] ?? 'single';
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
        
        // الحصول على اسم المحافظة من وحدة التوليد
        $governorateName = null;
        $generationUnit = GenerationUnit::with('governorateDetail')->find($this->generationUnitId);
        if ($generationUnit && $generationUnit->governorateDetail) {
            try {
                $governorateEnum = \App\Governorate::fromValue((int) $generationUnit->governorateDetail->value);
                $governorateName = $governorateEnum->label();
            } catch (\Exception $e) {
                $governorateName = $generationUnit->governorateDetail->label;
            }
        }

        $subscriber = Subscriber::create([
            'subscriber_id_number' => $data['subscriber_id_number'],
            'subscriber_name' => $data['subscriber_name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'governorate_name' => $governorateName,
            'subscription_date' => now(),
            'subscription_category' => $data['subscription_category'],
            'phase_type' => $data['phase_type'],
            'subscription_status' => 1, // نشط
            'meter_number' => $data['meter_number'],
            'service_type' => $data['service_type'],
            'subscription_number' => $subscriptionNumber,
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
