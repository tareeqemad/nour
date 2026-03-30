<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SubscribersImport;
use App\Models\GenerationUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriberImportController extends Controller
{
    /**
     * عرض modal الاستيراد
     */
    public function showImportModal(Request $request)
    {
        $user = auth()->user();
        $generationUnits = collect();

        if ($user->isSuperAdmin()) {
            $generationUnits = GenerationUnit::with('operator:id,name')
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('name')
                ->get();
        } elseif ($user->isCompanyOwner()) {
            $operatorIds = $user->ownedOperators()->pluck('id');
            $generationUnits = GenerationUnit::whereIn('operator_id', $operatorIds)
                ->with('operator:id,name')
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('name')
                ->get();
        } else {
            $operatorIds = $user->operators()->pluck('operators.id');
            $generationUnits = GenerationUnit::whereIn('operator_id', $operatorIds)
                ->with('operator:id,name')
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('name')
                ->get();
        }

        return response()->json([
            'success' => true,
            'generation_units' => $generationUnits
        ]);
    }

    /**
     * معاينة ملف Excel قبل الاستيراد
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'generation_unit_id' => 'required|exists:generation_units,id'
        ], [
            'file.required' => 'يرجى اختيار ملف للاستيراد',
            'file.mimes' => 'يجب أن يكون الملف بصيغة Excel أو CSV',
            'file.max' => 'حجم الملف يجب أن لا يتجاوز 5 ميجابايت',
            'generation_unit_id.required' => 'يرجى اختيار وحدة التوليد',
            'generation_unit_id.exists' => 'وحدة التوليد المحددة غير موجودة'
        ]);

        try {
            $file = $request->file('file');
            $userId = auth()->id();
            $generationUnitId = $request->generation_unit_id;

            // التأكد من وجود المجلد
            $importDir = storage_path('app/imports/temp');
            if (!file_exists($importDir)) {
                mkdir($importDir, 0755, true);
            }

            // حفظ الملف مؤقتاً
            $fileName = uniqid('import_') . '.' . $file->getClientOriginalExtension();
            $file->move($importDir, $fileName);
            $fullPath = $importDir . DIRECTORY_SEPARATOR . $fileName;
            
            // قراءة الملف في وضع المعاينة
            $import = new SubscribersImport($generationUnitId, $userId, true);
            $import->import($fullPath);

            $results = $import->getResults();

            // حفظ المسار في الجلسة للاستيراد النهائي
            session(['import_file_path' => $fullPath, 'import_generation_unit_id' => $generationUnitId]);

            return response()->json([
                'success' => true,
                'results' => $results,
                'file_path' => $fullPath
            ]);

        } catch (\Exception $e) {
            \Log::error('Excel Preview Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء قراءة الملف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تنفيذ الاستيراد النهائي
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string'
        ]);

        try {
            $filePath = $request->file_path;
            $generationUnitId = session('import_generation_unit_id');
            $userId = auth()->id();

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية الملف، يرجى رفعه مرة أخرى'
                ], 400);
            }

            // تنفيذ الاستيراد الفعلي
            $import = new SubscribersImport($generationUnitId, $userId, false);
            $import->import($filePath);

            $results = $import->getResults();

            // حذف الملف المؤقت
            @unlink($filePath);
            session()->forget(['import_file_path', 'import_generation_unit_id']);

            return response()->json([
                'success' => true,
                'message' => "تم استيراد {$results['valid_count']} مشترك بنجاح",
                'results' => $results
            ]);

        } catch (\Exception $e) {
            \Log::error('Excel Import Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحميل نموذج Excel
     */
    public function downloadTemplate()
    {
        // جلب القيم المتاحة من الثوابت
        $categories = \App\Helpers\ConstantsHelper::get(23);   // تصنيف الاشتراك
        $phaseTypes = \App\Helpers\ConstantsHelper::get(24);   // نوع الفاز
        $serviceTypes = \App\Helpers\ConstantsHelper::get(26); // نوع الخدمة
        $ampereValues = \App\Helpers\ConstantsHelper::get(31); // قيم الأمبير

        $categoryLabels = $categories->pluck('label')->toArray();
        $phaseLabels = $phaseTypes->pluck('label')->toArray();
        $serviceLabels = $serviceTypes->pluck('label')->toArray();
        $ampereLabels = $ampereValues->pluck('label')->toArray();

        $headers = [
            'رقم_الهوية',
            'اسم_المشترك',
            'رقم_الموبايل',
            'رقم_جوال_بديل',
            'العنوان',
            'رقم_الصندوق',
            'رقم_العداد',
            'الأمبير',
            'القراءة_الافتتاحية',
            'تصنيف_الاشتراك',
            'نوع_الفاز',
            'نوع_الخدمة',
            'اشتراك_موظف',
            'تاريخ_الطلب',
            'ملاحظات'
        ];

        $firstCategory = $categoryLabels[0] ?? 'منزلي';
        $secondCategory = $categoryLabels[1] ?? 'تجاري';
        $firstPhase = $phaseLabels[0] ?? '1 فاز';
        $secondPhase = $phaseLabels[1] ?? '3 فاز';
        $firstService = $serviceLabels[0] ?? 'مولد';
        $secondService = $serviceLabels[1] ?? 'شبكة';
        $firstAmpere = $ampereLabels[0] ?? '1 أمبير';
        $secondAmpere = $ampereLabels[2] ?? '3 أمبير';

        $exampleData = [
            ['402111222', 'أحمد محمد علي', '0591234567', '0562345678', 'غزة - الرمال', '1234', 'MTR001', $firstAmpere, '100', $firstCategory, $firstPhase, $firstService, 'لا', '2026-03-01', 'ملاحظة تجريبية'],
            ['402333444', 'محمود خالد سعيد', '0567654321', '', 'غزة - النصيرات', '', 'MTR002', $secondAmpere, '0', $secondCategory, $secondPhase, $secondService, 'نعم', '', ''],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('بيانات');
        $sheet->setRightToLeft(true);

        // إضافة العناوين
        $columns = range('A', 'O');
        foreach ($headers as $index => $header) {
            $cell = $columns[$index] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }

        // إضافة البيانات النموذجية
        $rowNum = 2;
        foreach ($exampleData as $data) {
            foreach ($data as $colIndex => $value) {
                $cell = $columns[$colIndex] . $rowNum;
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
            $rowNum++;
        }

        // إضافة Data Validation (dropdown) للأعمدة اللي فيها قيم ثابتة
        $maxRow = 500; // عدد الصفوف اللي يشتغل عليها الـ dropdown
        $validations = [
            'H' => implode(',', $ampereLabels),     // الأمبير
            'J' => implode(',', $categoryLabels),   // تصنيف الاشتراك
            'K' => implode(',', $phaseLabels),      // نوع الفاز
            'L' => implode(',', $serviceLabels),    // نوع الخدمة
            'M' => 'نعم,لا',                        // اشتراك موظف
        ];

        foreach ($validations as $col => $list) {
            $validation = $sheet->getCell("{$col}2")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_WARNING);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . $list . '"');
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('قيمة غير صحيحة');
            $validation->setError('يرجى اختيار قيمة من القائمة المتاحة');

            // نسخ الـ validation لباقي الصفوف
            for ($r = 3; $r <= $maxRow; $r++) {
                $sheet->getCell("{$col}{$r}")->setDataValidation(clone $validation);
            }
        }

        // ضبط عرض الأعمدة
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $fileName = 'نموذج_استيراد_المشتركين.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);
        
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
