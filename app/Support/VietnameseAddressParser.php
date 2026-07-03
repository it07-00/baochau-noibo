<?php

namespace App\Support;

use Illuminate\Support\Str;

final class VietnameseAddressParser
{
    /**
     * @return array{province: ?string, ward: ?string, industrial_park: ?string}
     */
    public static function parse(?string $address): array
    {
        $address = trim((string) $address);

        if ($address === '') {
            return [
                'province' => null,
                'ward' => null,
                'industrial_park' => null,
            ];
        }

        $province = self::province($address);

        return [
            'province' => $province,
            'ward' => self::ward($address),
            'industrial_park' => self::industrialPark($address),
        ];
    }

    private static function province(string $address): ?string
    {
        $normalizedAddress = self::normalize($address);
        $aliases = VietnamProvinces::aliases();
        $provinceNames = array_keys($aliases);

        usort($provinceNames, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        foreach ($provinceNames as $provinceName) {
            $normalizedProvince = self::normalize($provinceName);
            $withoutCityPrefix = preg_replace('/^tp\s+/', '', $normalizedProvince);

            if (
                str_contains($normalizedAddress, $normalizedProvince)
                || ($withoutCityPrefix && str_contains($normalizedAddress, $withoutCityPrefix))
            ) {
                return $aliases[$provinceName];
            }
        }

        return null;
    }

    private static function ward(string $address): ?string
    {
        if (preg_match('/\b(quận|huyện|thị xã|thị trấn)\b/iu', $address) === 1) {
            return null;
        }

        $parts = preg_split('/[,;\n]+/u', $address) ?: [];

        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match('/\b(phường|xã|đặc khu)\s+.+$/iu', $part, $matches) !== 1) {
                continue;
            }

            return trim($matches[0], " \t\n\r\0\x0B.");
        }

        return null;
    }

    private static function industrialPark(string $address): ?string
    {
        if (preg_match('/\b(KCN|Khu công nghiệp|CCN|Cụm công nghiệp|KCX|Khu chế xuất)\s+[^,;\n]+/iu', $address, $matches) !== 1) {
            return null;
        }

        return trim($matches[0], " \t\n\r\0\x0B.");
    }

    private static function normalize(string $value): string
    {
        return (string) Str::of(Str::ascii($value))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish();
    }
}
