<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GoogleSheetTotalExtractor
{
    private const CACHE_TTL_MINUTES = 10;

    public function extractTotalFromUrl(string $sheetUrl, bool $forceRefresh = false): int
    {
        $sheetUrl = trim($sheetUrl);
        $cacheKey = 'google_sheet_total:' . md5($sheetUrl);

        if ($forceRefresh) {
            Cache::forget($cacheKey);

            $amount = $this->fetchTotalFromUrl($sheetUrl);
            Cache::put($cacheKey, $amount, now()->addMinutes(self::CACHE_TTL_MINUTES));

            return $amount;
        }

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($sheetUrl) {
            return $this->fetchTotalFromUrl($sheetUrl);
        });
    }

    private function fetchTotalFromUrl(string $sheetUrl): int
    {
        $csvUrl = $this->buildCsvExportUrl($sheetUrl);

        try {
            $response = Http::timeout(15)
                ->retry(2, 250)
                ->accept('text/csv,text/plain,*/*')
                ->get($csvUrl);

            if (! $response->successful()) {
                Log::warning('GoogleSheetTotalExtractor request failed', [
                    'sheet_url' => $sheetUrl,
                    'csv_url' => $csvUrl,
                    'status' => $response->status(),
                    'body_preview' => Str::limit($response->body(), 500),
                ]);

                throw new RuntimeException('Không đọc được Google Sheet. Hãy kiểm tra link chia sẻ công khai.');
            }

            return $this->extractTotalFromCsv($response->body());
        } catch (Throwable $e) {
            Log::error('GoogleSheetTotalExtractor failed', [
                'sheet_url' => $sheetUrl,
                'csv_url' => $csvUrl,
                'message' => $e->getMessage(),
            ]);

            if ($e instanceof RuntimeException) {
                throw $e;
            }

            throw new RuntimeException('Không đọc được Google Sheet. Hãy kiểm tra link chia sẻ công khai.', previous: $e);
        }
    }

    public function buildCsvExportUrl(string $sheetUrl): string
    {
        $sheetUrl = trim($sheetUrl);

        if (! preg_match('~spreadsheets/d/([a-zA-Z0-9-_]+)~', $sheetUrl, $matches)) {
            throw new RuntimeException('Link Google Sheet không hợp lệ.');
        }

        $spreadsheetId = $matches[1];
        $gid = $this->extractGid($sheetUrl);

        return "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}";
    }

    public function extractTotalFromCsv(string $csvContent): int
    {
        $rows = preg_split('/\r\n|\r|\n/', $csvContent) ?: [];

        foreach ($rows as $row) {
            if (trim($row) === '') {
                continue;
            }

            $cells = str_getcsv($row);
            $totalLabelIndex = $this->findTotalLabelIndex($cells);
            if ($totalLabelIndex === null) {
                continue;
            }

            $amount = $this->extractAmountFromCells($cells, $totalLabelIndex);
            if ($amount !== null) {
                return (int) round($amount, 0, PHP_ROUND_HALF_UP);
            }
        }

        throw new RuntimeException('Không tìm thấy dòng chứa "Tổng Cộng" hoặc chưa có số tiền hợp lệ trong sheet.');
    }

    private function extractGid(string $sheetUrl): string
    {
        $parts = parse_url($sheetUrl);
        $queryGid = null;
        $fragmentGid = null;

        if (! empty($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            $queryGid = $queryParams['gid'] ?? null;
        }

        if (! empty($parts['fragment']) && preg_match('/(?:^|&)gid=(\d+)/', $parts['fragment'], $matches)) {
            $fragmentGid = $matches[1];
        }

        return (string) ($queryGid ?? $fragmentGid ?? '0');
    }

    private function findTotalLabelIndex(array $cells): ?int
    {
        foreach ($cells as $index => $cell) {
            $normalized = $this->normalizeText((string) $cell);
            if (Str::contains($normalized, 'tong cong')) {
                return $index;
            }
        }

        return null;
    }

    private function extractAmountFromCells(array $cells, int $labelIndex): ?float
    {
        for ($index = count($cells) - 1; $index >= 0; $index--) {
            if ($index <= $labelIndex) {
                continue;
            }

            $amount = $this->extractAmountFromCell((string) $cells[$index]);
            if ($amount !== null) {
                return $amount;
            }
        }

        return null;
    }

    private function extractAmountFromCell(string $cell): ?float
    {
        $cell = trim(html_entity_decode($cell, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($cell === '' || ! preg_match('/\d/', $cell)) {
            return null;
        }

        $normalized = $this->normalizeNumericString($cell);
        if ($normalized === null) {
            return null;
        }

        return (float) $normalized;
    }

    private function normalizeNumericString(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $negative = false;

        if (preg_match('/^\((.*)\)$/', $value, $matches)) {
            $negative = true;
            $value = $matches[1];
        }

        if (str_starts_with($value, '-')) {
            $negative = true;
            $value = substr($value, 1);
        }

        $value = preg_replace('/[^\d.,]/', '', $value);
        if ($value === null || $value === '' || ! preg_match('/\d/', $value)) {
            return null;
        }

        $decimalSeparator = $this->detectDecimalSeparator($value);

        if ($decimalSeparator !== null) {
            $lastSeparatorPosition = strrpos($value, $decimalSeparator);
            if ($lastSeparatorPosition === false) {
                return null;
            }

            $integerPart = preg_replace('/\D/', '', substr($value, 0, $lastSeparatorPosition));
            $fractionPart = preg_replace('/\D/', '', substr($value, $lastSeparatorPosition + 1));

            if ($integerPart === null || $fractionPart === null || $fractionPart === '') {
                return null;
            }

            $normalized = ($integerPart !== '' ? $integerPart : '0') . '.' . $fractionPart;
        } else {
            $normalized = preg_replace('/\D/', '', $value);
            if ($normalized === null || $normalized === '') {
                return null;
            }
        }

        if ($negative && $normalized !== '0') {
            $normalized = '-' . $normalized;
        }

        return $normalized;
    }

    private function detectDecimalSeparator(string $value): ?string
    {
        $lastDot = strrpos($value, '.');
        $lastComma = strrpos($value, ',');

        if ($lastDot !== false && $lastComma !== false) {
            return $lastDot > $lastComma ? '.' : ',';
        }

        $separator = $lastDot !== false ? '.' : ($lastComma !== false ? ',' : null);
        if ($separator === null) {
            return null;
        }

        $lastPosition = strrpos($value, $separator);
        if ($lastPosition === false) {
            return null;
        }

        $digitsAfterSeparator = strlen($value) - $lastPosition - 1;

        return ($digitsAfterSeparator >= 1 && $digitsAfterSeparator <= 2) ? $separator : null;
    }

    private function normalizeText(string $value): string
    {
        return (string) Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->trim();
    }
}
