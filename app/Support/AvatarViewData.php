<?php

namespace App\Support;

use Illuminate\Support\Str;

class AvatarViewData
{
    public function name(?string $name): string
    {
        $value = trim((string) $name);

        return $value !== '' ? $value : '?';
    }

    public function initials(?string $name): string
    {
        $safeName = $this->name($name);

        return collect(explode(' ', $safeName))
            ->filter()
            ->map(fn ($word) => Str::upper(mb_substr($word, 0, 1)))
            ->take(2)
            ->join('');
    }

    public function backgroundColor(?string $name): string
    {
        $palette = ['4f46e5', '0891b2', '059669', 'b45309', 'dc2626', '7c3aed', '0284c7', 'c026d3'];
        $safeName = $this->name($name);

        return $palette[abs(crc32($safeName)) % count($palette)];
    }

    public function fontSize(int $size): int
    {
        return (int) round($size * 0.38);
    }
}
