<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OperatorsExport
{
    public function __construct(private Collection $operators)
    {
    }

    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        $headers = [
            'اسم المشغل',
            'رقم المشغل',
            'اسم المالك',
            'رقم هوية المالك',
            'رقم هوية المشغل',
            'رقم الهاتف',
            'البريد الإلكتروني',
            'الحالة',
            'الاعتماد',
            'اكتمال الملف',
            'وحدات التوليد',
            'المولدات',
            'الموظفون',
            'المرفقات',
            'تاريخ الإنشاء',
        ];

        $columns = range('A', 'O');

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

        $row = 2;
        foreach ($this->operators as $operator) {
            $sheet->setCellValue('A' . $row, $operator->name ?? '');
            $sheet->setCellValue('B' . $row, $operator->unit_number ?? '');
            $sheet->setCellValue('C' . $row, $operator->owner_name ?? $operator->owner?->name ?? '');
            $sheet->setCellValue('D' . $row, $operator->owner_id_number ?? '');
            $sheet->setCellValue('E' . $row, $operator->operator_id_number ?? '');
            $sheet->setCellValue('F' . $row, $operator->phone ?? $operator->owner?->phone ?? '');
            $sheet->setCellValue('G' . $row, $operator->owner?->email ?? '');
            $sheet->setCellValue('H' . $row, $operator->status === 'active' ? 'فعال' : 'غير فعال');
            $sheet->setCellValue('I' . $row, $operator->is_approved ? 'معتمد' : 'غير معتمد');
            $sheet->setCellValue('J' . $row, $operator->isProfileComplete() ? 'مكتمل' : 'غير مكتمل');
            $sheet->setCellValue('K' . $row, (int) ($operator->generation_units_count ?? 0));
            $sheet->setCellValue('L' . $row, (int) ($operator->generators_count ?? 0));
            $sheet->setCellValue('M' . $row, (int) ($operator->employees_count ?? $operator->users_count ?? 0));
            $sheet->setCellValue('N' . $row, (int) ($operator->attachments_count ?? 0));
            $sheet->setCellValue('O' . $row, $operator->created_at?->format('Y-m-d H:i') ?? '');

            foreach ($columns as $column) {
                $sheet->getStyle($column . $row)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }

            $row++;
        }

        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'operators_' . date('Y-m-d_His') . '.xlsx';
        $tempDir = storage_path('app/temp');
        $tempPath = $tempDir . '/' . $fileName;

        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
}
