<?php

namespace App\Services;

use App\Models\Attendance;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceExporter
{
    public function downloadAll(): StreamedResponse
    {
        $attendances = Attendance::query()->with('user')->orderBy('created_at')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance');

        $headers = ['#', 'Name', 'Phone', 'Email', 'Designation', 'Other Specify', 'Submitted By', 'Registered At'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($attendances as $index => $attendance) {
            $sheet->fromArray([
                $index + 1,
                $attendance->name,
                $attendance->phone,
                $attendance->email,
                $attendance->designation,
                $attendance->designation_other ?? '',
                $attendance->user?->name ?? '',
                $attendance->created_at->format('Y-m-d H:i:s'),
            ], null, 'A'.$row);
            $row++;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'attendance_'.now()->format('Y-m-d_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
