<?php

namespace App\Support;

use Illuminate\Support\Str;

final class VietnamProvinces
{
    /**
     * Danh mục cấp tỉnh áp dụng thống nhất từ 01/07/2025
     * theo Quyết định 19/2025/QĐ-TTg.
     *
     * @return array<int, string>
     */
    public static function list(): array
    {
        return [
            'Hà Nội',
            'Cao Bằng',
            'Tuyên Quang',
            'Điện Biên',
            'Lai Châu',
            'Sơn La',
            'Lào Cai',
            'Thái Nguyên',
            'Lạng Sơn',
            'Quảng Ninh',
            'Bắc Ninh',
            'Phú Thọ',
            'Hải Phòng',
            'Hưng Yên',
            'Ninh Bình',
            'Thanh Hóa',
            'Nghệ An',
            'Hà Tĩnh',
            'Quảng Trị',
            'Huế',
            'Đà Nẵng',
            'Quảng Ngãi',
            'Gia Lai',
            'Khánh Hòa',
            'Đắk Lắk',
            'Lâm Đồng',
            'Đồng Nai',
            'TP. Hồ Chí Minh',
            'Tây Ninh',
            'Đồng Tháp',
            'Vĩnh Long',
            'An Giang',
            'Cần Thơ',
            'Cà Mau',
        ];
    }

    /**
     * Tên cấp tỉnh cũ => tên đơn vị mới sau sắp xếp.
     *
     * @return array<string, string>
     */
    public static function legacyMap(): array
    {
        return [
            'Hà Giang' => 'Tuyên Quang',
            'Yên Bái' => 'Lào Cai',
            'Bắc Kạn' => 'Thái Nguyên',
            'Vĩnh Phúc' => 'Phú Thọ',
            'Hòa Bình' => 'Phú Thọ',
            'Bắc Giang' => 'Bắc Ninh',
            'Thái Bình' => 'Hưng Yên',
            'Hải Dương' => 'Hải Phòng',
            'Hà Nam' => 'Ninh Bình',
            'Nam Định' => 'Ninh Bình',
            'Quảng Bình' => 'Quảng Trị',
            'Quảng Nam' => 'Đà Nẵng',
            'Kon Tum' => 'Quảng Ngãi',
            'Bình Định' => 'Gia Lai',
            'Ninh Thuận' => 'Khánh Hòa',
            'Phú Yên' => 'Đắk Lắk',
            'Đắk Nông' => 'Lâm Đồng',
            'Bình Thuận' => 'Lâm Đồng',
            'Bình Phước' => 'Đồng Nai',
            'Bà Rịa - Vũng Tàu' => 'TP. Hồ Chí Minh',
            'Bà Rịa – Vũng Tàu' => 'TP. Hồ Chí Minh',
            'Bình Dương' => 'TP. Hồ Chí Minh',
            'Long An' => 'Tây Ninh',
            'Tiền Giang' => 'Đồng Tháp',
            'Bến Tre' => 'Vĩnh Long',
            'Trà Vinh' => 'Vĩnh Long',
            'Kiên Giang' => 'An Giang',
            'Sóc Trăng' => 'Cần Thơ',
            'Hậu Giang' => 'Cần Thơ',
            'Bạc Liêu' => 'Cà Mau',
        ];
    }

    /**
     * Bao gồm tên hiện hành, tên cũ và một số cách viết phổ biến.
     *
     * @return array<string, string>
     */
    public static function aliases(): array
    {
        $aliases = array_map(static fn (string $province): string => $province, array_combine(self::list(), self::list()));

        return array_merge($aliases, self::legacyMap(), [
            'Hồ Chí Minh' => 'TP. Hồ Chí Minh',
            'TP Hồ Chí Minh' => 'TP. Hồ Chí Minh',
            'TP.Hồ Chí Minh' => 'TP. Hồ Chí Minh',
            'Thành phố Hồ Chí Minh' => 'TP. Hồ Chí Minh',
            'Thành phố Hà Nội' => 'Hà Nội',
            'Thành phố Hải Phòng' => 'Hải Phòng',
            'Thành phố Huế' => 'Huế',
            'Thành phố Đà Nẵng' => 'Đà Nẵng',
            'Thành phố Cần Thơ' => 'Cần Thơ',
        ]);
    }

    public static function canonicalize(?string $province): ?string
    {
        $province = trim((string) $province);

        if ($province === '') {
            return null;
        }

        $normalized = self::normalize($province);

        foreach (self::aliases() as $alias => $canonical) {
            if (self::normalize($alias) === $normalized) {
                return $canonical;
            }
        }

        return $province;
    }

    public static function normalize(string $value): string
    {
        return (string) Str::of(Str::ascii($value))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish();
    }
}
