<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SubscribersExport
{
    protected Collection $subscribers;

    public function __construct(Collection $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        $headers = [
            'رقم الاشتراك', 'رقم الهوية', 'اسم المشترك', 'رقم الجوال', 'رقم جوال بديل',
            'العنوان', 'رقم الصندوق', 'المحافظة', 'رقم العداد', 'تصنيف الاشتراك',
            'نوع الفاز', 'حالة الاشتراك', 'نوع الخدمة', 'قيمة الأمبير', 'القراءة الافتتاحية',
            'اشتراك موظف', 'تاريخ الاشتراك', 'تاريخ الطلب', 'وحدات التوليد', 'ملاحظات',
        ];

        $columns = range('A', 'T');

        // Headers
        foreach ($headers as $index => $header) {
            $cell = $columns[$index] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        // Data
        $row = 2;
        foreach ($this->subscribers as $s) {
            $sheet->setCellValue('A' . $row, $s->subscription_number);
            $sheet->setCellValue('B' . $row, $s->subscriber_id_number);
            $sheet->setCellValue('C' . $row, $s->subscriber_name);
            $sheet->setCellValue('D' . $row, $s->phone);
            $sheet->setCellValue('E' . $row, $s->alt_phone ?? '');
            $sheet->setCellValue('F' . $row, $s->address);
            $sheet->setCellValue('G' . $row, $s->box_number ?? '');
            $sheet->setCellValue('H' . $row, $s->governorate_display_name);
            $sheet->setCellValue('I' . $row, $s->meter_number ?? '');
            $sheet->setCellValue('J' . $row, $s->subscription_category_name ?? '');
            $sheet->setCellValue('K' . $row, $s->phase_type_name ?? '');
            $sheet->setCellValue('L' . $row, $s->subscription_status_name ?? '');
            $sheet->setCellValue('M' . $row, $s->service_type_name ?? '');
            $sheet->setCellValue('N' . $row, $s->ampere ?? '');
            $sheet->setCellValue('O' . $row, $s->opening_reading ?? '');
            $sheet->setCellValue('P' . $row, $s->is_employee_subscription ? 'نعم' : 'لا');
            $sheet->setCellValue('Q' . $row, $s->subscription_date?->format('Y-m-d') ?? '');
            $sheet->setCellValue('R' . $row, $s->request_date?->format('Y-m-d') ?? '');
            $sheet->setCellValue('S' . $row, $s->generationUnits->pluck('name')->implode(', '));
            $sheet->setCellValue('T' . $row, $s->notes ?? '');

            foreach ($columns as $col) {
                $sheet->getStyle($col . $row)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
            $row++;
        }

        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'المشتركين_' . date('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
