<?php

namespace Database\Seeders;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class QuotationMasterCsvImportSeeder extends Seeder
{
    private const DEFAULT_CSV_PATH = 'quotation_import.csv';

    public function run(): void
    {
        $csvPath = env('QUOTATION_IMPORT_CSV_PATH', self::DEFAULT_CSV_PATH);

        if (!is_file($csvPath)) {
            $this->command?->error('Không tìm thấy file CSV: ' . $csvPath);
            return;
        }

        $defaultStaff = User::where('username', 'kinhdoanh')->first();
        $defaultStaffId = $defaultStaff ? $defaultStaff->id : User::first()->id;

        DB::beginTransaction();

        try {
            Quotation::query()->delete();

            $handle = fopen($csvPath, 'rb');
            if ($handle === false) {
                throw new \RuntimeException('Không thể mở file CSV: ' . $csvPath);
            }

            // Skip header
            fgetcsv($handle);

            $count = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (!is_array($row) || count($row) < 11) {
                    continue;
                }

                $id = (int) $row[0];
                $quotationNumber = $this->nullIfEmpty($row[1]);
                $staffRaw = $row[2];
                $companyName = $this->nullIfEmpty($row[3]);

                if (!$companyName) {
                    continue;
                }

                $staffName = $this->extractPersonName($staffRaw);
                $date = $this->extractQuotationDate($staffRaw) ?: date('Y-m-d');

                $staffId = $this->resolveStaffId($staffName, $defaultStaffId);

                $originalValue = $this->parseMoney($row[7]);
                $commissionValue = $this->parseMoney($row[8]);
                $commissionTax = $this->parseMoney($row[9]);
                $totalValue = $this->parseMoney($row[10]);
                $valueIncVat = $totalValue + $commissionValue;

                $pdfPath = null;
                $localPathsRaw = $this->nullIfEmpty($row[19]);
                if ($localPathsRaw) {
                    $paths = explode('|', $localPathsRaw);
                    foreach ($paths as $pathPart) {
                        $pathPart = trim($pathPart);
                        if ($pathPart === '') {
                            continue;
                        }
                        $fileName = basename($pathPart);
                        $pdfPath = 'quotations/' . $fileName;
                        break;
                    }
                }

                Quotation::create([
                    'id' => $id,
                    'date' => $date,
                    'quotation_number' => $quotationNumber,
                    'staff_id' => $staffId,
                    'source' => $this->nullIfEmpty($row[13]),
                    'company_name' => $companyName,
                    'address' => $this->nullIfEmpty($row[16]),
                    'work_address' => $this->nullIfEmpty($row[17]),
                    'province' => $this->nullIfEmpty($row[5]),
                    'industry' => $this->nullIfEmpty($row[14]),
                    'service' => $this->nullIfEmpty($row[4]),
                    'contact_person' => $this->nullIfEmpty($row[12]),
                    'work_description' => $this->nullIfEmpty($row[4]),
                    'status' => $this->nullIfEmpty($row[6]),
                    'original_value' => $originalValue,
                    'value_inc_vat' => $valueIncVat,
                    'commission_value' => $commissionValue,
                    'commission_tax' => $commissionTax,
                    'total_value' => $totalValue,
                    'notes' => $this->nullIfEmpty($row[11]),
                    'pdf_path' => $pdfPath,
                ]);

                $count++;
            }

            fclose($handle);
            DB::commit();

            $this->command?->info("Đã nhập thành công {$count} báo giá từ CSV.");

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command?->error('Import báo giá thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    private function resolveStaffId(string $name, int $defaultStaffId): int
    {
        if ($name === '') {
            return $defaultStaffId;
        }

        $user = User::query()
            ->where('name', 'like', '%' . $name . '%')
            ->orWhere('username', 'like', '%' . $name . '%')
            ->first();

        if ($user) {
            return (int) $user->id;
        }

        // Create user if not found
        $department = Department::where('slug', 'kinh-doanh')->first();

        $baseUsername = Str::slug($name, '');
        if ($baseUsername === '') {
            $baseUsername = 'user';
        }
        $username = $baseUsername;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $i;
            $i++;
        }

        $role = \App\Enums\Role::KINH_DOANH->value;

        $newUser = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $username . '@baochauenv.vn',
            'password' => Hash::make('password'),
            'department_id' => $department?->id,
            'is_active' => true,
        ]);

        $newUser->syncRoles([$role]);

        $this->command?->info("Created new user: {$name} (username: {$username}, role: {$role})");

        return (int) $newUser->id;
    }

    private function extractPersonName(?string $value): string
    {
        $text = $this->str($value);
        if ($text === '' || $text === '-' || str_contains($this->normalizeForMatch($text), 'chua giao viec')) {
            return '';
        }

        $pieces = preg_split('/—|-/u', $text);
        $name = is_array($pieces) && $pieces !== [] ? trim($pieces[0]) : $text;

        $name = preg_replace('/\s*\(.*?\)$/u', '', $name) ?? $name;

        return trim($name);
    }

    private function extractQuotationDate(?string $value): ?string
    {
        $text = $this->str($value);
        if (preg_match('/\((.*?)\)/u', $text, $matches)) {
            return $this->parseDate($matches[1]);
        }
        return null;
    }

    private function parseDate(?string $value): ?string
    {
        $text = $this->str($value);
        if ($text === '' || $text === '-') {
            return null;
        }

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d'];
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $text);
            if ($dt instanceof \DateTime) {
                return $dt->format('Y-m-d');
            }
        }

        return null;
    }

    private function parseMoney(?string $value): int
    {
        $text = $this->str($value);
        if ($text === '' || $text === '-') {
            return 0;
        }

        $clean = preg_replace('/[^\d-]/', '', $text);
        if ($clean === null || $clean === '' || $clean === '-') {
            return 0;
        }

        return (int) $clean;
    }

    private function normalizeForMatch(?string $value): string
    {
        $text = $this->str($value);
        if ($text === '') {
            return '';
        }

        $ascii = Str::of($text)->ascii()->lower()->toString();
        $ascii = mb_strtolower($ascii, 'UTF-8');
        $ascii = preg_replace('/[^a-z0-9]+/', ' ', $ascii) ?? $ascii;

        return trim(preg_replace('/\s+/', ' ', $ascii) ?? $ascii);
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $text = $this->str($value);

        return $text === '' || $text === '-' ? null : $text;
    }

    private function str(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }
}
