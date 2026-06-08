<?php

namespace App\Support\Quotations;

final class LaborMonitoringPriceCatalog
{
    private const UNIT = 'Mẫu';

    private const GROUPS = [
        'I. YẾU TỐ VI KHÍ HẬU',
        'II. YẾU TỐ VẬT LÝ',
        'III. YẾU TỐ TIẾP XÚC',
        'IV. YẾU TỐ TÂM SINH LÝ VÀ ECGONOMI',
        'V. YẾU TỐ BỤI CÁC LOẠI',
        'VI. YẾU TỐ HÓA HỌC',
        'VII. CHI PHÍ KHÁC',
    ];

    private const DESCRIPTION_ALIASES = [
        'voc' => 'VOCs',
        'formaldehyde (hcho)' => 'Formandehyde (HCHO)',
    ];

    private const ITEMS = [
        ['group_name' => 'I. YẾU TỐ VI KHÍ HẬU', 'description' => 'Nhiệt độ', 'unit_price' => 12000, 'note' => ''],
        ['group_name' => 'I. YẾU TỐ VI KHÍ HẬU', 'description' => 'Độ ẩm', 'unit_price' => 12000, 'note' => ''],
        ['group_name' => 'I. YẾU TỐ VI KHÍ HẬU', 'description' => 'Tốc độ gió', 'unit_price' => 12000, 'note' => ''],
        ['group_name' => 'I. YẾU TỐ VI KHÍ HẬU', 'description' => 'Bức xạ nhiệt', 'unit_price' => 50000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Ánh sáng', 'unit_price' => 15000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Tiếng ồn theo dải tần', 'unit_price' => 85000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Rung chuyển theo dải tần', 'unit_price' => 100000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Tiếng ồn chung', 'unit_price' => 50000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Vận tốc rung đứng hoặc ngang', 'unit_price' => 85000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Phóng xạ, tia X', 'unit_price' => 85000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Điện từ trường tần số cao', 'unit_price' => 85000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Điện từ trường CN', 'unit_price' => 85000, 'note' => ''],
        ['group_name' => 'II. YẾU TỐ VẬT LÝ', 'description' => 'Bức xạ từ ngoại', 'unit_price' => 85000, 'note' => ''],
        ['group_name' => 'III. YẾU TỐ TIẾP XÚC', 'description' => 'Yếu tố gây dị ứng, mẫn cảm', 'unit_price' => 300000, 'note' => ''],
        ['group_name' => 'III. YẾU TỐ TIẾP XÚC', 'description' => 'Yếu tố vi sinh vật', 'unit_price' => 300000, 'note' => 'Xét nghiệm mẫu vi sinh'],
        ['group_name' => 'IV. YẾU TỐ TÂM SINH LÝ VÀ ECGONOMI', 'description' => 'Đánh giá gánh nặng lao động thể lực', 'unit_price' => 100000, 'note' => ''],
        ['group_name' => 'IV. YẾU TỐ TÂM SINH LÝ VÀ ECGONOMI', 'description' => 'Đánh giá căng thẳng thần kinh tâm lý', 'unit_price' => 100000, 'note' => ''],
        ['group_name' => 'IV. YẾU TỐ TÂM SINH LÝ VÀ ECGONOMI', 'description' => 'Đánh giá ecgonomy vị trí lao động', 'unit_price' => 100000, 'note' => ''],
        ['group_name' => 'IV. YẾU TỐ TÂM SINH LÝ VÀ ECGONOMI', 'description' => 'Đánh giá ecgonomy tư thế lao động theo phương pháp Owas', 'unit_price' => 100000, 'note' => ''],
        ['group_name' => 'V. YẾU TỐ BỤI CÁC LOẠI', 'description' => 'Bụi toàn phần', 'unit_price' => 100000, 'note' => ''],
        ['group_name' => 'V. YẾU TỐ BỤI CÁC LOẠI', 'description' => 'Bụi hô hấp', 'unit_price' => 120000, 'note' => ''],
        ['group_name' => 'V. YẾU TỐ BỤI CÁC LOẠI', 'description' => 'Bụi than', 'unit_price' => 300000, 'note' => ''],
        ['group_name' => 'V. YẾU TỐ BỤI CÁC LOẠI', 'description' => 'Bụi bông', 'unit_price' => 300000, 'note' => 'ĐO 5 MẪU/NGÀY'],
        ['group_name' => 'V. YẾU TỐ BỤI CÁC LOẠI', 'description' => 'Bụi kim loại', 'unit_price' => 300000, 'note' => ''],
        ['group_name' => 'V. YẾU TỐ BỤI CÁC LOẠI', 'description' => 'Bụi silic', 'unit_price' => 300000, 'note' => ''],
        ['group_name' => 'V. YẾU TỐ BỤI CÁC LOẠI', 'description' => 'Bụi amiang', 'unit_price' => 500000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'VOCs', 'unit_price' => 150000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'NO2', 'unit_price' => 80000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'SO2', 'unit_price' => 80000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'CO', 'unit_price' => 80000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'CO2', 'unit_price' => 80000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'O3', 'unit_price' => 150000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'O2', 'unit_price' => 150000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Hơi dung môi hữu cơ (Benzen)', 'unit_price' => 350000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Hơi dung môi hữu cơ (Toluen)', 'unit_price' => 350000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Hơi dung môi hữu cơ (Xylen)', 'unit_price' => 350000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Hg', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Cd', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Co', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Zn', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Cu', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Al', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Se', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Pb', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Mg', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Mn', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Ni', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'As', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Flo (F2)', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Cl2', 'unit_price' => 200000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'NH3', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'H2S', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Hơi kiềm', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Xút', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'CH4', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'H2SO4', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'HCl', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'HNO3', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'HCN', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'HF', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Axit acetic', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Ethanol', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Ethyl ether', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Ethyl mercaptan', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Aceton', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Phospho', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Acetylene', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Isopropyl alcohol', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Crom (III) (Cr3+)', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Crom (VI) (Cr6+)', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Phosphin (PH3)', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Butanol', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Styren', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Cyclohexanon', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Methyl Ethyl Keton', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Phenol', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Methanol', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Methylmercaptan', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Acetaldehyde (CH3CHO)', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Formandehyde (HCHO)', 'unit_price' => 400000, 'note' => 'Viện: 200k + ống than 35k + di chuyển 60k'],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'N-N dimethyl formamide', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Butan', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Vinyl clorua', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Ethylen glycol', 'unit_price' => 400000, 'note' => ''],
        ['group_name' => 'VI. YẾU TỐ HÓA HỌC', 'description' => 'Hydrocacbon', 'unit_price' => 400000, 'note' => ''],
    ];

    public static function groups(?string $subcontractor = null): array
    {
        return self::GROUPS;
    }

    public static function all(?string $subcontractor = null): array
    {
        return array_map(
            fn (array $item): array => $item + ['unit' => self::UNIT],
            self::ITEMS
        );
    }

    public static function forGroup(?string $groupName, ?string $subcontractor = null): array
    {
        $needle = self::normalizeGroup($groupName);

        if ($needle === '') {
            return self::all();
        }

        return array_values(array_filter(
            self::all(),
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

        if (isset(self::DESCRIPTION_ALIASES[$needle])) {
            $needle = self::normalizeDescription(self::DESCRIPTION_ALIASES[$needle]);
        }

        foreach (self::forGroup($groupName) as $item) {
            if (self::normalizeDescription($item['description']) === $needle) {
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
            'group_name' => $catalogItem['group_name'] ?? self::GROUPS[0],
            'description' => $catalogItem['description'] ?? '',
            'unit' => $catalogItem['unit'] ?? self::UNIT,
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

    private static function normalizeGroup(?string $value): string
    {
        $value = self::normalizeDescription($value);
        $value = preg_replace('/^[ivxlcdm]+\.\s*/iu', '', $value) ?? $value;

        return $value;
    }
}
