<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\InspectionViolationCasesImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InspectionViolationCaseImportController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\InspectionViolationCase::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'يرجى اختيار ملف للاستيراد',
            'file.mimes' => 'يجب أن يكون الملف بصيغة Excel أو CSV',
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 5 ميجابايت',
        ]);

        try {
            $file = $request->file('file');
            $importDir = storage_path('app/imports/temp');
            if (!file_exists($importDir)) {
                mkdir($importDir, 0755, true);
            }
            $fileName = uniqid('import_') . '.' . $file->getClientOriginalExtension();
            $file->move($importDir, $fileName);
            $fullPath = $importDir . DIRECTORY_SEPARATOR . $fileName;

            $import = new InspectionViolationCasesImport(auth()->id(), true);
            $import->import($fullPath);
            $results = $import->getResults();

            session(['inspection_import_file_path' => $fullPath]);

            return response()->json([
                'success' => true,
                'results' => array_merge($results, ['file_path' => $fullPath]),
            ]);
        } catch (\Exception $e) {
            \Log::error('Inspection cases import preview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء قراءة الملف: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\InspectionViolationCase::class);

        $request->validate(['file_path' => 'required|string']);

        try {
            $filePath = $request->file_path;
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية الملف، يرجى رفعه مرة أخرى',
                ], 400);
            }

            $import = new InspectionViolationCasesImport(auth()->id(), false);
            $import->import($filePath);
            $results = $import->getResults();

            @unlink($filePath);
            session()->forget('inspection_import_file_path');

            return response()->json([
                'success' => true,
                'message' => 'تم استيراد ' . $results['valid_count'] . ' قضية بنجاح',
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            \Log::error('Inspection cases import: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $this->authorize('create', \App\Models\InspectionViolationCase::class);

        $headers = ['المحافظة', 'رقم_الاشتراك', 'اسم_المشترك', 'اسم_المنتفع', 'تاريخ_القضية', 'حالة_القضية', 'بيان_القضية'];
        $exampleData = [
            ['غزة', 'SUB001', 'أحمد محمد', 'محمد أحمد', '2025-01-15', 1, 'تعدي على الخط'],
            ['الشمال', 'SUB002', 'خالد علي', 'علي خالد', '2025-02-01', 2, 'قضية محكومة'],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $columns = range('A', 'G');
        foreach ($headers as $i => $header) {
            $cell = $columns[$i] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
        $row = 2;
        foreach ($exampleData as $data) {
            foreach ($data as $colIndex => $value) {
                $sheet->setCellValue($columns[$colIndex] . $row, $value);
            }
            $row++;
        }
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'نموذج_قضايا_التفتيش_والتعدي.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
