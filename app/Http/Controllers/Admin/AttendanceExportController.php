<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Services\AttendanceExportService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceExportController extends Controller
{
    public function __construct(private AttendanceExportService $exportService) {}

    public function export(string $month): StreamedResponse
    {
        abort_unless(auth()->user()->can(Permission::CHAM_CONG_EXPORT->value), 403);

        $spreadsheet = $this->exportService->buildSummarySpreadsheet($month);
        $writer      = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            'bang-cham-cong-' . $month . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'max-age=0']
        );
    }

    public function exportDetail(string $month): StreamedResponse
    {
        abort_unless(auth()->user()->can(Permission::CHAM_CONG_EXPORT->value), 403);

        $spreadsheet = $this->exportService->buildDetailSpreadsheet($month);
        $writer      = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            'chi-tiet-cham-cong-' . $month . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'max-age=0']
        );
    }
}
