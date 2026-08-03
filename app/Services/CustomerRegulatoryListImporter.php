<?php

namespace App\Services;

use App\Models\Customer;
use App\Support\VietnameseAddressParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CustomerRegulatoryListImporter
{
    /**
     * @return array{created: int, updated: int, unchanged: int, skipped: int, ghg_rows: int, energy_rows: int}
     */
    public function import(string $ghgPath, string $energyPath): array
    {
        $this->ensureReadable($ghgPath);
        $this->ensureReadable($energyPath);

        return DB::transaction(function () use ($ghgPath, $energyPath): array {
            $customersByName = Customer::withTrashed()
                ->get()
                ->keyBy(fn (Customer $customer): string => $this->nameKey($customer->name));

            $result = [
                'created' => 0,
                'updated' => 0,
                'unchanged' => 0,
                'skipped' => 0,
                'ghg_rows' => 0,
                'energy_rows' => 0,
            ];

            foreach ($this->csvRows($ghgPath) as $row) {
                $name = $this->clean($row['Tên cơ sở'] ?? null);
                if ($name === null) {
                    $result['skipped']++;

                    continue;
                }

                $result['ghg_rows']++;
                $this->storeFacility(
                    $customersByName,
                    $result,
                    $name,
                    $this->clean($row['Địa chỉ'] ?? null),
                    $this->clean($row['Tỉnh / Thành phố'] ?? null),
                    'is_ghg_inventory'
                );
            }

            $currentProvince = null;
            foreach ($this->csvRows($energyPath) as $row) {
                $name = $this->clean($row['Tên cơ sở'] ?? null);
                $address = $this->clean($row['Địa chỉ'] ?? null);

                if ($name === null && $address === null) {
                    $currentProvince = $this->provinceFromHeading($row['STT'] ?? null) ?? $currentProvince;

                    continue;
                }

                if ($name === null) {
                    $result['skipped']++;

                    continue;
                }

                $result['energy_rows']++;
                $this->storeFacility(
                    $customersByName,
                    $result,
                    $name,
                    $address,
                    $currentProvince,
                    'is_energy_audit'
                );
            }

            return $result;
        });
    }

    /**
     * @param  Collection<string, Customer>  $customersByName
     * @param  array{created: int, updated: int, unchanged: int, skipped: int, ghg_rows: int, energy_rows: int}  $result
     */
    private function storeFacility(
        Collection $customersByName,
        array &$result,
        string $name,
        ?string $address,
        ?string $sourceProvince,
        string $listColumn
    ): void {
        $key = $this->nameKey($name);
        /** @var Customer|null $customer */
        $customer = $customersByName->get($key);

        if ($customer?->trashed()) {
            $result['skipped']++;

            return;
        }

        $detected = VietnameseAddressParser::parse(implode(', ', array_filter([$address, $sourceProvince])));

        if ($customer === null) {
            $customer = new Customer([
                'name' => $name,
                'address' => $address,
                'province' => $detected['province'],
                'ward' => $detected['ward'],
                'industrial_park' => $detected['industrial_park'],
                $listColumn => true,
            ]);
            $customer->disableLogging();
            $customer->save();
            $customersByName->put($key, $customer);
            $result['created']++;

            return;
        }

        $customer->setAttribute($listColumn, true);
        foreach ([
            'address' => $address,
            'province' => $detected['province'],
            'ward' => $detected['ward'],
            'industrial_park' => $detected['industrial_park'],
        ] as $field => $value) {
            if (blank($customer->getAttribute($field)) && filled($value)) {
                $customer->setAttribute($field, $value);
            }
        }

        if (! $customer->isDirty()) {
            $result['unchanged']++;

            return;
        }

        $customer->disableLogging();
        $customer->save();
        $result['updated']++;
    }

    /** @return \Generator<int, array<string, string|null>> */
    private function csvRows(string $path): \Generator
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Không thể mở file CSV: '.$path);
        }

        try {
            $header = fgetcsv($handle, escape: '');
            if (! is_array($header)) {
                throw new RuntimeException('File CSV không có dòng tiêu đề hợp lệ: '.$path);
            }

            $header = array_map(fn ($value): string => trim((string) $value), $header);
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");

            while (($row = fgetcsv($handle, escape: '')) !== false) {
                $row = array_slice(array_pad($row, count($header), null), 0, count($header));
                $combined = array_combine($header, $row);
                if (is_array($combined)) {
                    yield $combined;
                }
            }
        } finally {
            fclose($handle);
        }
    }

    private function provinceFromHeading(mixed $heading): ?string
    {
        $heading = trim((string) $heading);
        if (preg_match('/^\d+\.\s*(.+)$/u', $heading, $matches) !== 1) {
            return null;
        }

        return preg_match('/\b(tỉnh|thành phố|tp\.?)\b/iu', $matches[1]) === 1
            ? trim($matches[1])
            : null;
    }

    private function nameKey(string $name): string
    {
        return Str::lower((string) Str::of($name)->squish());
    }

    private function clean(mixed $value): ?string
    {
        $value = (string) Str::of((string) $value)->squish();

        return $value === '' ? null : $value;
    }

    private function ensureReadable(string $path): void
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Không tìm thấy hoặc không đọc được file CSV: '.$path);
        }
    }
}
