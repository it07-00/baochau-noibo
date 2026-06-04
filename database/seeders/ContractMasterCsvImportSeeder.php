<?php

namespace Database\Seeders;

use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContractMasterCsvImportSeeder extends Seeder
{
    private const DEFAULT_CSV_PATH = 'import.csv';

    private const CSV_KEYS = [
        'id',
        'category',
        'customer_name',
        'shd_bc',
        'shd_cxl',
        'service_content',
        'report_number',
        'offset_room_fund',
        'ncc_payment',
        'revenue',
        'note',
        'contract_value',
        'commission',
        'info_source',
        'effective_date',
        'signed_date',
        'end_date',
        'invoice_date',
        'assignees',
        'representative',
        'handler_name',
        'staff_name',
        'department_name',
        'payment_method',
        'status',
        'voucher_status',
        'province',
        'mailing_address',
        'execution_address',
        'billing_address',
        'attachment_links',
        'download_paths',
    ];

    public function run(): void
    {
        $csvPath = env('CONTRACT_IMPORT_CSV_PATH', self::DEFAULT_CSV_PATH);

        if (! is_file($csvPath)) {
            $this->command?->error('Khong tim thay file CSV: '.$csvPath);
            return;
        }

        $departmentId = $this->resolveDepartmentId();
        $defaultStaffId = $this->resolveDefaultStaffId();
        $defaultHandlerId = $this->resolveDefaultHandlerId();

        DB::beginTransaction();

        try {
            $this->clearAllContractData();

            $handle = fopen($csvPath, 'rb');
            if ($handle === false) {
                throw new \RuntimeException('Khong the mo file CSV: '.$csvPath);
            }

            $header = fgetcsv($handle);
            if (! is_array($header)) {
                fclose($handle);
                throw new \RuntimeException('CSV khong co dong header hop le.');
            }

            $counts = [
                'consulting' => 0,
                'waste' => 0,
                'project' => 0,
                'energy' => 0,
                'commercial' => 0,
                'sustainability' => 0,
                'skipped' => 0,
            ];

            while (($row = fgetcsv($handle)) !== false) {
                if (! is_array($row)) {
                    continue;
                }

                $data = $this->rowToAssoc($row);
                if ($this->isEmptyRow($data)) {
                    continue;
                }

                $category = $this->normalizeForMatch($data['category']);
                $customerName = $this->str($data['customer_name']);

                if ($category === '' || $customerName === '') {
                    $counts['skipped']++;
                    continue;
                }

                $customer = Customer::firstOrCreate(
                    ['name' => $customerName],
                    [
                        'address' => $this->nullIfEmpty($data['execution_address']),
                        'representative' => $this->extractRepresentative($data['representative']),
                    ]
                );

                $handlerId = $this->resolveHandlerId($data['handler_name'], $defaultHandlerId);
                $staffId = $this->resolveStaffId($data, $defaultStaffId);

                $contractData = $this->buildBaseContractData($data, $customer->id, $staffId, $departmentId, $handlerId);

                if ($category === 'phap ly ho so moi truong') {
                    ContractLegal::create(array_merge($contractData, [
                        'report_number' => $this->nullIfEmpty($data['report_number']),
                    ]));
                    $counts['consulting']++;
                    continue;
                }

                if ($category === 'chat thai tieng on') {
                    $wasteData = $contractData;
                    unset($wasteData['info_source'], $wasteData['notes']);

                    ContractWaste::create(array_merge($wasteData, [
                        'service_type' => $this->detectWasteServiceType($data['service_content']),
                        'waste_type' => $this->detectWasteType($data['service_content']),
                        'content' => $this->nullIfEmpty($data['note']),
                        'source' => $this->nullIfEmpty($data['info_source']),
                        'effective_at' => $this->parseDate($data['effective_date']),
                        'end_at' => $this->parseDate($data['end_date']),
                        'billing_address' => $this->nullIfEmpty($data['billing_address']),
                        'execution_address' => $this->nullIfEmpty($data['execution_address']),
                        'mailing_address' => $this->nullIfEmpty($data['mailing_address']),
                        'note' => $this->buildNote($data),
                    ]));
                    $counts['waste']++;
                    continue;
                }

                if ($category === 'ky thuat ung pho su co') {
                    ContractTechnical::create($contractData);
                    $counts['project']++;
                    continue;
                }

                if ($category === 'phat thai nang luong') {
                    ContractEmission::create($contractData);
                    $counts['energy']++;
                    continue;
                }

                if ($category === 'nghien cuu thuong mai') {
                    ContractResearch::create(array_merge($contractData, [
                        'report_number' => $this->nullIfEmpty($data['report_number']),
                    ]));
                    $counts['commercial']++;
                    continue;
                }

                if ($category === 'phat trien ben vung') {
                    ContractSustainability::create($contractData);
                    $counts['sustainability']++;
                    continue;
                }

                $counts['skipped']++;
            }

            fclose($handle);
            DB::commit();

            $this->command?->info('Da xoa du lieu hop dong cu va import tu CSV thanh cong.');
            $this->command?->line(sprintf(
                'Ket qua import: consulting=%d, waste=%d, project=%d, energy=%d, commercial=%d, sustainability=%d, skipped=%d',
                $counts['consulting'],
                $counts['waste'],
                $counts['project'],
                $counts['energy'],
                $counts['commercial'],
                $counts['sustainability'],
                $counts['skipped']
            ));
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command?->error('Import that bai: '.$e->getMessage());
            throw $e;
        }
    }

    private function clearAllContractData(): void
    {
        DB::table('contract_assignments')->delete();
        DB::table('contract_workflow_steps')->delete();
        DB::table('contract_milestone_files')->delete();
        DB::table('contract_progress_notes')->delete();

        $scheduleIds = DB::table('contract_payment_schedules')->pluck('id')->all();
        if ($scheduleIds !== [] && Schema::hasTable('sales_progressives')) {
            DB::table('sales_progressives')->whereIn('payment_schedule_id', $scheduleIds)->delete();
        }
        DB::table('contract_payment_schedules')->delete();

        DB::table('contract_wastes')->delete();
        DB::table('contract_consultings')->delete();
        DB::table('contract_projects')->delete();
        DB::table('contract_commercials')->delete();
        DB::table('contract_sustainabilities')->delete();
        DB::table('contract_energies')->delete();
    }

    private function resolveDepartmentId(): int
    {
        $departmentId = Department::query()->where('slug', 'kinh-doanh')->value('id');
        if (! $departmentId) {
            $departmentId = Department::query()->value('id');
        }

        if (! $departmentId) {
            throw new \RuntimeException('Khong tim thay phong ban de gan hop dong.');
        }

        return (int) $departmentId;
    }

    private function resolveDefaultStaffId(): int
    {
        $staffId = User::query()->where('username', 'kinhdoanh')->value('id');
        if (! $staffId) {
            $staffId = User::query()->value('id');
        }

        if (! $staffId) {
            throw new \RuntimeException('Khong tim thay user de gan staff_id.');
        }

        return (int) $staffId;
    }

    private function resolveDefaultHandlerId(): int
    {
        $handler = Handler::firstOrCreate(
            ['name' => 'Cong ty CP Cong Nghe Moi Truong Trai Dat Xanh'],
            [
                'phone' => null,
                'address' => null,
            ]
        );

        return (int) $handler->id;
    }

    private function resolveHandlerId(?string $value, int $defaultHandlerId): int
    {
        $name = $this->str($value);
        if ($name === '' || $name === '-' || $this->normalizeForMatch($name) === 'chua chon') {
            return $defaultHandlerId;
        }

        $handler = Handler::firstOrCreate(['name' => $name]);

        return (int) $handler->id;
    }

    private function resolveStaffId(array $data, int $defaultStaffId): int
    {
        $candidates = [
            $data['staff_name'] ?? null,
            $data['assignees'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $name = $this->extractPersonName($candidate);
            if ($name === '') {
                continue;
            }

            $user = User::query()
                ->where('name', 'like', '%'.$name.'%')
                ->orWhere('username', 'like', '%'.$name.'%')
                ->first();

            if ($user) {
                return (int) $user->id;
            }

            // Create user if not found in the system
            $deptName = trim($data['department_name'] ?? '');
            $department = null;
            if ($deptName !== '') {
                $department = Department::where('name', 'like', '%' . $deptName . '%')->first();
            }
            if (!$department) {
                $department = Department::where('slug', 'kinh-doanh')->first();
            }

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
            if ($department) {
                if ($department->slug === 'tu-van-cskh') {
                    $role = \App\Enums\Role::TU_VAN->value;
                } elseif ($department->slug === 'ky-thuat') {
                    $role = \App\Enums\Role::KY_THUAT->value;
                } elseif ($department->slug === 'ke-toan') {
                    $role = \App\Enums\Role::KE_TOAN->value;
                } elseif ($department->slug === 'marketing') {
                    $role = \App\Enums\Role::MARKETING->value;
                }
            }

            $newUser = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $username . '@baochauenv.vn',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'department_id' => $department?->id,
                'is_active' => true,
            ]);

            $newUser->syncRoles([$role]);

            $this->command?->info("Created new user: {$name} (username: {$username}, role: {$role})");

            return (int) $newUser->id;
        }

        return $defaultStaffId;
    }

    private function buildBaseContractData(array $data, int $customerId, int $staffId, int $departmentId, int $handlerId): array
    {
        $source = $this->nullIfEmpty($data['info_source']);

        return [
            'shd_bc' => $this->nullIfEmpty($data['shd_bc']),
            'shd_cxl' => $this->nullIfEmpty($data['shd_cxl']),
            'customer_id' => $customerId,
            'handler_id' => $handlerId,
            'staff_id' => $staffId,
            'department_id' => $departmentId,
            'signed_at' => $this->parseDate($data['signed_date']),
            'submitted_at' => $this->parseDate($data['invoice_date']),
            'value' => $this->parseMoney($data['contract_value']),
            'commission' => $this->parseMoney($data['commission']),
            'revenue' => $this->parseMoney($data['revenue']),
            'ncc_payment' => $this->parseMoney($data['ncc_payment']),
            'province' => $this->nullIfEmpty($data['province']),
            'info_source' => $source,
            'payment_method' => $this->nullIfEmpty($data['payment_method']),
            'status' => $this->nullIfEmpty($data['status']),
            'renewal_status' => $this->detectRenewalStatus($source),
            'voucher_status' => $this->cleanVoucherStatus($data['voucher_status']),
            'is_offset' => $this->parseMoney($data['offset_room_fund']) > 0,
            'has_room_fund' => false,
            'is_overdue' => false,
            'is_renewal' => $this->isRenewal($source),
            'loai_dich_vu' => $this->nullIfEmpty($data['service_content']),
            'notes' => $this->buildNote($data),
        ];
    }

    private function buildNote(array $data): ?string
    {
        $parts = [
            $this->nullIfEmpty($data['note']),
            $this->nullIfEmpty($data['attachment_links']),
            $this->nullIfEmpty($data['download_paths']),
        ];

        $parts = array_values(array_filter($parts, fn ($v) => $v !== null));

        return $parts === [] ? null : implode("\n", $parts);
    }

    private function detectWasteServiceType(?string $value): string
    {
        $text = $this->normalizeForMatch($value);

        if (str_contains($text, 'huy')) {
            return 'Hủy hàng';
        }

        if (str_contains($text, 'ctcn')) {
            return 'CTCN';
        }

        return 'Thu gom CTNH';
    }

    private function detectWasteType(?string $value): string
    {
        $text = $this->normalizeForMatch($value);
        $hasCtnh = str_contains($text, 'ctnh');
        $hasCtcn = str_contains($text, 'ctcn');

        if ($hasCtnh && $hasCtcn) {
            return 'CTNH & CTCN';
        }

        if ($hasCtcn) {
            return 'CTCN';
        }

        return 'CTNH';
    }

    private function detectRenewalStatus(?string $source): ?string
    {
        if ($this->isRenewal($source)) {
            return 'ĐÃ TÁI KÝ';
        }

        return null;
    }

    private function isRenewal(?string $source): bool
    {
        $text = $this->normalizeForMatch($source);

        return str_contains($text, 'tai ky');
    }

    private function cleanVoucherStatus(?string $value): ?string
    {
        $text = $this->str($value);

        if ($text === '' || $text === '-') {
            return null;
        }

        return $text;
    }

    private function extractRepresentative(?string $value): ?string
    {
        $text = $this->str($value);
        if ($text === '' || $text === '-') {
            return null;
        }

        $parts = preg_split('/—|-/u', $text);
        if (! is_array($parts) || $parts === []) {
            return $this->nullIfEmpty($text);
        }

        return $this->nullIfEmpty(trim($parts[0]));
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

    private function rowToAssoc(array $row): array
    {
        $assoc = [];
        foreach (self::CSV_KEYS as $index => $key) {
            $assoc[$key] = isset($row[$index]) ? trim((string) $row[$index]) : '';
        }

        return $assoc;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->str($value) !== '') {
                return false;
            }
        }

        return true;
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
