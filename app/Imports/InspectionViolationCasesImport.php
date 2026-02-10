<?php

namespace App\Imports;

use App\Models\InspectionViolationCase;
use App\Helpers\ConstantsHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InspectionViolationCasesImport
{
    protected $errors = [];
    protected $validRows = [];
    protected $previewMode = false;
    protected $userId;

    public function __construct($userId = null, $previewMode = false)
    {
        $this->userId = $userId;
        $this->previewMode = $previewMode;
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
        $rowNumber = 1;
        foreach ($rows as $rowData) {
            $rowNumber++;
            if ($this->isEmptyRow($rowData)) {
                continue;
            }
            $row = array_combine($headers, array_values($rowData));
            $data = $this->mapRowData($row);
            $validation = $this->validateRow($data, $rowNumber);

            if ($validation['valid']) {
                $this->validRows[] = array_merge(['row_number' => $rowNumber], $data);
                if (!$this->previewMode) {
                    $this->createCase($data);
                }
            } else {
                $this->errors[] = [
                    'row_number' => $rowNumber,
                    'data' => $data,
                    'errors' => $validation['errors'],
                ];
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
            $header = trim(preg_replace('/\s+/', '_', $header));
            return $header;
        }, $headers);
    }

    protected function isEmptyRow($row): bool
    {
        if (!is_array($row)) {
            return true;
        }
        $values = array_filter(array_map('trim', $row));
        return empty($values);
    }

    protected function mapRowData(array $row): array
    {
        $governorateId = null;
        if (!empty($row['المحافظة'] ?? $row['governorate'] ?? null)) {
            $govLabel = $row['المحافظة'] ?? $row['governorate'] ?? '';
            $governorates = ConstantsHelper::get(1);
            $found = $governorates->firstWhere('label', $govLabel);
            if ($found) {
                $governorateId = $found->id;
            }
        }

        $caseDate = $row['تاريخ_القضية'] ?? $row['case_date'] ?? null;
        if ($caseDate && is_numeric($caseDate)) {
            $caseDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($caseDate)->format('Y-m-d');
        }

        $status = (int) ($row['حالة_القضية'] ?? $row['status'] ?? 1);
        if (!in_array($status, [1, 2, 3])) {
            $status = 1;
        }

        return [
            'governorate_id' => $governorateId,
            'subscription_number' => trim((string) ($row['رقم_الاشتراك'] ?? $row['subscription_number'] ?? '')),
            'subscriber_name' => trim((string) ($row['اسم_المشترك'] ?? $row['subscriber_name'] ?? '')),
            'beneficiary_name' => trim((string) ($row['اسم_المنتفع'] ?? $row['beneficiary_name'] ?? '')),
            'case_date' => $caseDate,
            'status' => $status,
            'statement' => trim((string) ($row['بيان_القضية'] ?? $row['statement'] ?? '')),
        ];
    }

    protected function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];
        if (empty($data['subscriber_name'])) {
            $errors[] = 'اسم المشترك مطلوب';
        }
        if (empty($data['case_date'])) {
            $errors[] = 'تاريخ القضية مطلوب';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['case_date'])) {
            $errors[] = 'صيغة تاريخ غير صحيحة';
        }
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function createCase(array $data): void
    {
        InspectionViolationCase::create([
            'governorate_id' => $data['governorate_id'] ?? null,
            'subscription_number' => $data['subscription_number'] ?: null,
            'subscriber_name' => $data['subscriber_name'],
            'beneficiary_name' => $data['beneficiary_name'] ?: null,
            'case_date' => $data['case_date'],
            'status' => $data['status'],
            'statement' => $data['statement'] ?: null,
            'created_by' => $this->userId,
        ]);
    }

    public function getResults(): array
    {
        return [
            'valid_count' => count($this->validRows),
            'error_count' => count($this->errors),
            'errors' => $this->errors,
            'valid_rows' => $this->validRows,
        ];
    }
}
