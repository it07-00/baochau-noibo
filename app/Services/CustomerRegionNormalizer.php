<?php

namespace App\Services;

use App\Models\Customer;
use App\Support\VietnameseAddressParser;
use App\Support\VietnamProvinces;

final class CustomerRegionNormalizer
{
    /**
     * @return array{
     *     total: int,
     *     changed: int,
     *     province_changed: int,
     *     ward_detected: int,
     *     industrial_park_detected: int,
     *     needs_review: int
     * }
     */
    public function run(bool $apply = false): array
    {
        $report = [
            'total' => 0,
            'changed' => 0,
            'province_changed' => 0,
            'ward_detected' => 0,
            'industrial_park_detected' => 0,
            'needs_review' => 0,
        ];

        Customer::query()
            ->select(['id', 'address', 'province', 'ward', 'industrial_park'])
            ->orderBy('id')
            ->chunkById(200, function ($customers) use (&$report, $apply): void {
                foreach ($customers as $customer) {
                    $report['total']++;
                    $detected = VietnameseAddressParser::parse($customer->address);
                    $newValues = [
                        'province' => VietnamProvinces::canonicalize($customer->province) ?? $detected['province'],
                        'ward' => VietnameseAddressParser::canonicalizeWard($customer->ward) ?: $detected['ward'],
                        'industrial_park' => VietnameseAddressParser::canonicalizeIndustrialPark($customer->industrial_park) ?: $detected['industrial_park'],
                    ];

                    $changes = [];

                    foreach ($newValues as $field => $value) {
                        if (filled($value) && trim((string) $customer->{$field}) !== trim((string) $value)) {
                            $changes[$field] = $value;
                        }
                    }

                    if (array_key_exists('province', $changes)) {
                        $report['province_changed']++;
                    }

                    if (array_key_exists('ward', $changes)) {
                        $report['ward_detected']++;
                    }

                    if (array_key_exists('industrial_park', $changes)) {
                        $report['industrial_park_detected']++;
                    }

                    if ($changes !== []) {
                        $report['changed']++;

                        if ($apply) {
                            Customer::withoutEvents(
                                fn () => Customer::query()->whereKey($customer->id)->update($changes)
                            );
                        }
                    }

                    if (blank($newValues['ward'])) {
                        $report['needs_review']++;
                    }
                }
            });

        return $report;
    }
}
