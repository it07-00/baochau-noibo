<?php

namespace App\Support\Quotations;

final class EnvironmentalMonitoringPriceCatalog
{
    private const DATA_PATH = 'data/quotation-price-catalogs/environmental_monitoring_2026.json';

    private const MANUAL_GROUPS = [
        'Chi phí khác',
    ];

    private static ?array $data = null;

    public static function subcontractors(): array
    {
        return self::data()['subcontractors'] ?? [];
    }

    public static function defaultSubcontractor(): string
    {
        $keys = array_keys(self::subcontractors());

        return $keys[0] ?? 'dai_phu';
    }

    public static function groups(?string $subcontractor = null): array
    {
        $groups = self::data()['groups'] ?? [];
        $subcontractor = self::normalizeSubcontractor($subcontractor);

        if ($subcontractor !== '') {
            $groups = collect(self::data()['items'] ?? [])
                ->filter(fn (array $item): bool => self::priceFor($item, $subcontractor) !== null)
                ->pluck('group_name')
                ->unique()
                ->values()
                ->all();
        }

        return array_values(array_unique(array_merge($groups, self::MANUAL_GROUPS)));
    }

    public static function all(?string $subcontractor = null): array
    {
        return array_values(array_filter(array_map(
            fn (array $item): ?array => self::withSelectedPrice($item, $subcontractor),
            self::data()['items'] ?? []
        )));
    }

    public static function forGroup(?string $groupName, ?string $subcontractor = null): array
    {
        $needle = self::normalizeGroup($groupName);

        if ($needle === '') {
            return self::all($subcontractor);
        }

        return array_values(array_filter(
            self::all($subcontractor),
            fn (array $item): bool => self::normalizeGroup($item['group_name'] ?? '') === $needle
        ));
    }

    public static function findByDescription(
        ?string $description,
        ?string $groupName = null,
        ?string $subcontractor = null
    ): ?array {
        $needle = self::normalizeDescription($description);
        if ($needle === '') {
            return null;
        }

        foreach (self::forGroup($groupName, $subcontractor) as $item) {
            if (self::normalizeDescription($item['description'] ?? '') === $needle) {
                return $item;
            }
        }

        return null;
    }

    public static function toDetailItem(array $catalogItem, int|float $quantity = 1): array
    {
        $unitPrice = (int) ($catalogItem['unit_price'] ?? 0);
        $quantity = max(0, (int) round((float) $quantity));

        return [
            'group_name' => $catalogItem['group_name'] ?? '',
            'description' => $catalogItem['description'] ?? '',
            'unit' => $catalogItem['unit'] ?? 'Mẫu',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => (int) round($quantity * $unitPrice),
            'note' => $catalogItem['note'] ?? '',
        ];
    }

    public static function normalizeDescription(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return mb_strtolower($value, 'UTF-8');
    }

    private static function data(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $path = resource_path(self::DATA_PATH);
        if (! is_file($path)) {
            return self::$data = [
                'subcontractors' => [],
                'groups' => self::MANUAL_GROUPS,
                'items' => [],
            ];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return self::$data = is_array($decoded) ? $decoded : [
            'subcontractors' => [],
            'groups' => self::MANUAL_GROUPS,
            'items' => [],
        ];
    }

    private static function withSelectedPrice(array $item, ?string $subcontractor): ?array
    {
        $subcontractor = self::normalizeSubcontractor($subcontractor) ?: self::defaultSubcontractor();
        $unitPrice = self::priceFor($item, $subcontractor);

        if ($unitPrice === null) {
            return null;
        }

        $notes = is_array($item['notes'] ?? null) ? $item['notes'] : [];

        return $item + [
            'unit_price' => $unitPrice,
            'subcontractor' => $subcontractor,
            'note' => $notes[$subcontractor] ?? '',
        ];
    }

    private static function priceFor(array $item, string $subcontractor): ?int
    {
        $prices = is_array($item['unit_prices'] ?? null) ? $item['unit_prices'] : [];
        $price = $prices[$subcontractor] ?? null;

        return is_numeric($price) && (int) $price > 0 ? (int) $price : null;
    }

    private static function normalizeSubcontractor(?string $value): string
    {
        $value = trim((string) $value);

        return array_key_exists($value, self::subcontractors()) ? $value : '';
    }

    private static function normalizeGroup(?string $value): string
    {
        return self::normalizeDescription($value);
    }
}
