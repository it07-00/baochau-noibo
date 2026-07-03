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

    public static function canonicalizeWard(?string $ward): ?string
    {
        $ward = trim((string) $ward);
        if ($ward === '') {
            return null;
        }

        $prefixes = [
            'Phường' => '/^phường\b/iu',
            'Xã' => '/^xã\b/iu',
            'Đặc khu' => '/^đặc\s+khu\b/iu',
        ];

        foreach ($prefixes as $canonicalPrefix => $pattern) {
            if (preg_match($pattern, $ward)) {
                $ward = preg_replace($pattern, $canonicalPrefix, $ward);
                break;
            }
        }

        $ward = preg_replace('/\s+/', ' ', $ward);

        return trim($ward, " \t\n\r\0\x0B.");
    }

    public static function canonicalizeIndustrialPark(?string $ip): ?string
    {
        $ip = trim((string) $ip);
        if ($ip === '') {
            return null;
        }

        $prefixes = [
            'KCN' => '/^kcn\b/iu',
            'Khu công nghiệp' => '/^khu\s+công\s+nghiệp\b/iu',
            'CCN' => '/^ccn\b/iu',
            'Cụm công nghiệp' => '/^cụm\s+công\s+nghiệp\b/iu',
            'KCX' => '/^kcx\b/iu',
            'Khu chế xuất' => '/^khu\s+chế\s+xuất\b/iu',
        ];

        foreach ($prefixes as $canonicalPrefix => $pattern) {
            if (preg_match($pattern, $ip)) {
                $ip = preg_replace($pattern, $canonicalPrefix, $ip);
                break;
            }
        }

        $ip = preg_replace('/\s*[-\x{2013}\x{2014}]\s*/u', ' - ', $ip);
        $ip = preg_replace('/\s+/', ' ', $ip);

        return trim($ip, " \t\n\r\0\x0B.");
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

            return self::canonicalizeWard($matches[0]);
        }

        return null;
    }

    private static function industrialPark(string $address): ?string
    {
        if (preg_match('/\b(KCN|Khu công nghiệp|CCN|Cụm công nghiệp|KCX|Khu chế xuất)\s+[^,;\n]+/iu', $address, $matches) !== 1) {
            return null;
        }

        return self::canonicalizeIndustrialPark($matches[0]);
    }

    private static function normalize(string $value): string
    {
        return (string) Str::of(Str::ascii($value))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish();
    }
}
