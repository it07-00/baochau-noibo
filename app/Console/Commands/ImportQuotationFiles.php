<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quotation;
use App\Models\QuotationFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImportQuotationFiles extends Command
{
    protected $signature = 'quotations:import-files {--dir=quotations : Thư mục chứa các file PDF báo giá} {--disk= : Disk để lưu file (mặc định lấy theo config UPLOAD_DISK)}';

    protected $description = 'Import các file PDF báo giá từ thư mục local vào storage và liên kết với DB';

    public function handle(): int
    {
        $dir = $this->option('dir');
        $disk = $this->option('disk') ?: config('filesystems.upload_disk', 'public');
        
        // Resolve absolute path relative to base path
        $absolutePath = base_path($dir);
        if (!File::isDirectory($absolutePath)) {
            $this->error("Thư mục không tồn tại: {$absolutePath}");
            return self::FAILURE;
        }

        $files = File::files($absolutePath);
        $pdfFiles = array_filter($files, function ($file) {
            return strtolower($file->getExtension()) === 'pdf';
        });

        if (empty($pdfFiles)) {
            $this->info("Không tìm thấy file PDF nào trong thư mục: {$absolutePath} trên disk [{$disk}]");
            return self::SUCCESS;
        }

        $this->info("Tìm thấy " . count($pdfFiles) . " file PDF. Bắt đầu xử lý import lên disk [{$disk}]...");
        
        $copiedCount = 0;
        $linkedCount = 0;
        $skippedCount = 0;

        foreach ($pdfFiles as $file) {
            $fileName = $file->getFilename();
            // Match pattern: quotation_{id}_{hash}.pdf
            if (preg_match('/^quotation_(\d+)_([A-Za-z0-9]+)\.pdf$/i', $fileName, $matches)) {
                $quotationId = (int)$matches[1];
            } else {
                $this->warn("Tên file không đúng định dạng (quotation_{id}_{hash}.pdf): {$fileName}");
                $skippedCount++;
                continue;
            }

            $quotation = Quotation::find($quotationId);
            if (!$quotation) {
                $this->error("Không tìm thấy báo giá ID: {$quotationId} cho file: {$fileName}");
                $skippedCount++;
                continue;
            }

            // Define target path in public storage
            $targetPath = 'quotations/' . $fileName;

            // Copy file to selected disk storage
            $copied = false;
            if (!Storage::disk($disk)->exists($targetPath)) {
                Storage::disk($disk)->put($targetPath, file_get_contents($file->getRealPath()));
                $copiedCount++;
                $copied = true;
            }

            // Create record in quotation_files if it doesn't exist
            $existsInDb = QuotationFile::where('quotation_id', $quotationId)
                ->where('path', $targetPath)
                ->exists();

            if (!$existsInDb) {
                QuotationFile::create([
                    'quotation_id' => $quotationId,
                    'path' => $targetPath,
                    'original_name' => $fileName,
                ]);
                $linkedCount++;
            }

            // Clear remote pdf_path and update to local path
            if ($quotation->pdf_path !== $targetPath) {
                $quotation->update(['pdf_path' => $targetPath]);
            }

            $this->line("  - Đã xử lý file: {$fileName} -> Báo giá ID: {$quotationId} (" . ($copied ? "Sao chép mới lên [{$disk}]" : "File đã tồn tại trên [{$disk}]") . ")");
        }

        $this->newLine();
        $this->info("Hoàn thành: Đã copy {$copiedCount} file lên disk [{$disk}], đã liên kết {$linkedCount} bản ghi DB, bỏ qua/lỗi {$skippedCount} file.");

        return self::SUCCESS;
    }
}
