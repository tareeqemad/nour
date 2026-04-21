<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\PaymentsImport;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentImportController extends Controller
{
    /**
     * التحقق من صلاحية استيراد الدفعات
     */
    protected function authorizeImport(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperAdmin() && !$user->hasPermission('invoices.import_payments'))) {
            abort(403, 'لا تملك صلاحية استيراد دفعات.');
        }
    }

    /**
     * معاينة ملف Excel قبل التنفيذ
     */
    public function preview(Request $request): JsonResponse
    {
        $this->authorizeImport();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'يرجى اختيار ملف للاستيراد',
            'file.mimes'    => 'يجب أن يكون الملف بصيغة Excel أو CSV',
            'file.max'      => 'حجم الملف يجب أن لا يتجاوز 5 ميجابايت',
        ]);

        try {
            $file = $request->file('file');
            $userId = auth()->id();

            $importDir = storage_path('app/imports/temp');
            if (!file_exists($importDir)) {
                mkdir($importDir, 0755, true);
            }

            $fileName = uniqid('payments_import_') . '.' . $file->getClientOriginalExtension();
            $file->move($importDir, $fileName);
            $fullPath = $importDir . DIRECTORY_SEPARATOR . $fileName;

            [$allowedOperatorIds, $scopeByOperator] = $this->getUserOperatorScope();

            $import = new PaymentsImport($userId, true, $allowedOperatorIds, $scopeByOperator);
            $import->import($fullPath);

            $results = $import->getResults();

            session([
                'payments_import_file_path' => $fullPath,
            ]);

            return response()->json([
                'success'   => true,
                'results'   => $results,
                'file_path' => $fullPath,
            ]);
        } catch (\Exception $e) {
            \Log::error('Payments Excel Preview Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء قراءة الملف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تنفيذ الاستيراد النهائي
     */
    public function import(Request $request): JsonResponse
    {
        $this->authorizeImport();

        $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            $filePath = $request->file_path;
            $userId = auth()->id();

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية الملف، يرجى رفعه مرة أخرى',
                ], 400);
            }

            [$allowedOperatorIds, $scopeByOperator] = $this->getUserOperatorScope();

            $import = new PaymentsImport($userId, false, $allowedOperatorIds, $scopeByOperator);
            $import->import($filePath);

            $results = $import->getResults();

            @unlink($filePath);
            session()->forget('payments_import_file_path');

            return response()->json([
                'success' => true,
                'message' => "تم تسجيل {$results['valid_count']} دفعة بنجاح" . ($results['error_count'] ? " مع {$results['error_count']} أخطاء" : ''),
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            \Log::error('Payments Excel Import Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تحميل قالب Excel فارغ
     */
    public function downloadTemplate()
    {
        $this->authorizeImport();

        $methods = array_values(Payment::methodLabels());

        $headers = [
            'رقم_الفاتورة',
            'رقم_الاشتراك',
            'رقم_الهوية',
            'تاريخ_الدفع',
            'المبلغ',
            'طريقة_الدفع',
        ];

        $example = [
            ['INV-000001', '',           '',          now()->toDateString(), '150.00', $methods[0] ?? 'نقداً'],
            ['',           'SUB-0001',   '',          now()->toDateString(), '75.50',  $methods[1] ?? 'تحويل بنكي'],
            ['',           '',           '402111222', now()->toDateString(), '200.00', $methods[0] ?? 'نقداً'],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('دفعات');
        $sheet->setRightToLeft(true);

        $columns = range('A', 'F');
        foreach ($headers as $i => $h) {
            $cell = $columns[$i] . '1';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $rowNum = 2;
        foreach ($example as $data) {
            foreach ($data as $colIdx => $value) {
                $cell = $columns[$colIdx] . $rowNum;
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
            $rowNum++;
        }

        // Dropdown لطريقة الدفع (العمود F)
        $maxRow = 500;
        $validation = $sheet->getCell('F2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"' . implode(',', $methods) . '"');
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('قيمة غير صحيحة');
        $validation->setError('يرجى اختيار طريقة دفع من القائمة');
        for ($r = 3; $r <= $maxRow; $r++) {
            $sheet->getCell("F{$r}")->setDataValidation(clone $validation);
        }

        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'نموذج_استيراد_دفعات.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * نطاق المشغلين المسموح للمستخدم الحالي
     *
     * @return array{0: int[], 1: bool}
     */
    protected function getUserOperatorScope(): array
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            return [[], false];
        }

        if ($user->isCompanyOwner()) {
            $ids = $user->ownedOperators()->pluck('id')->toArray();
            return [$ids, true];
        }

        // موظف / فني
        $ids = $user->operators()->pluck('operators.id')->toArray();
        return [$ids, true];
    }
}
