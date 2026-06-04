<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\QuotationDocument;
use App\Services\Quotations\QuotationDocumentExportService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Http\Controllers\Controller;

class QuotationDocumentController extends Controller
{
    private function authorizeDocumentView(QuotationDocument $doc): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if (! $user->hasRole(Role::IT->value)) {
            abort_unless($user->can(Permission::QUOTATION_TRACKING_VIEW->value), 403);
        }

        if ($user->hasRole(Role::KINH_DOANH->value) && (int) $doc->staff_id !== (int) $user->id) {
            abort(403, 'Bạn chỉ được xem báo giá do bạn phụ trách.');
        }
    }

    public function exportWord(int $id, QuotationDocumentExportService $service): StreamedResponse
    {
        $doc = QuotationDocument::with('items', 'staff')->findOrFail($id);
        $this->authorizeDocumentView($doc);

        $storagePath = $service->exportDocx($doc);
        $fileName = $service->downloadFileName($doc, 'docx');

        return Storage::disk(config('filesystems.upload_disk', 'public'))->download($storagePath, $fileName);
    }

    public function exportPdf(int $id, QuotationDocumentExportService $service): \Illuminate\Http\Response
    {
        $doc = QuotationDocument::with('items', 'staff')->findOrFail($id);
        $this->authorizeDocumentView($doc);

        $content = $service->generatePdfContent($doc);
        $fileName = $service->downloadFileName($doc, 'pdf');

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}
