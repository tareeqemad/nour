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
        $headers = [
            'رقم_الهوية',
            'اسم_المشترك', 
            'رقم_الموبايل',
            'العنوان',
            'رقم_العداد',
            'تصنيف_الاشتراك',
            'نوع_الفاز',
            'نوع_الخدمة'
        ];

        $exampleData = [
            ['402111222', 'أحمد محمد علي', '0591234567', 'غزة - الرمال', 'MTR001', 'منزلي', '1 فاز', 'مولد'],
            ['402333444', 'محمود خالد سعيد', '0567654321', 'غزة - النصيرات', 'MTR002', 'تجاري', '3 فاز', 'شبكة'],
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        // إضافة العناوين
        $columns = range('A', 'H');
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

        // ضبط عرض الأعمدة
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // إضافة sheet للتعليمات
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('تعليمات');
        $instructionSheet->setRightToLeft(true);
        
        $instructions = [
            ['التعليمات والملاحظات'],
            [''],
            ['الحقول المطلوبة:'],
            ['- رقم_الهوية: رقم هوية المشترك (مطلوب، يجب أن يكون فريداً)'],
            ['- اسم_المشترك: الاسم الكامل للمشترك (مطلوب)'],
            ['- رقم_الموبايل: يجب أن يبدأ بـ 059 أو 056 (مطلوب، 10 أرقام)'],
            ['- العنوان: عنوان المشترك (مطلوب)'],
            ['- رقم_العداد: رقم العداد الكهربائي (مطلوب، يجب أن يكون فريداً)'],
            [''],
            ['الحقول الاختيارية:'],
            ['- تصنيف_الاشتراك: منزلي / تجاري / صناعي / زراعي (الافتراضي: منزلي)'],
            ['- نوع_الفاز: 1 فاز / 3 فاز (الافتراضي: 1 فاز)'],
            ['- نوع_الخدمة: مولد / شبكة / مختلط (الافتراضي: مولد)'],
            [''],
            ['ملاحظات هامة:'],
            ['- تأكد من عدم وجود أرقام هوية أو موبايل أو عداد مكررة'],
            ['- الملف يدعم صيغ: xlsx, xls, csv'],
            ['- الحد الأقصى لحجم الملف: 5 ميجابايت'],
        ];

        $row = 1;
        foreach ($instructions as $instruction) {
            $instructionSheet->setCellValue('A' . $row, $instruction[0]);
            if ($row === 1) {
                $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            }
            $row++;
        }
        $instructionSheet->getColumnDimension('A')->setWidth(60);

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
