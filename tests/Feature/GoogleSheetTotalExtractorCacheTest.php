<?php

namespace Tests\Feature;

use App\Services\GoogleSheetTotalExtractor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleSheetTotalExtractorCacheTest extends TestCase
{
    public function test_force_refresh_bypasses_cached_sheet_total(): void
    {
        $sheetUrl = 'https://docs.google.com/spreadsheets/d/1oHGudNXLg8xOovSuU48KVyfcEOdT1vfaw7Vanch-kz4/edit?gid=0#gid=0';
        $cacheKey = 'google_sheet_total:' . md5($sheetUrl);

        Cache::put($cacheKey, 111000, now()->addMinutes(10));

        Http::fake([
            'docs.google.com/*' => Http::response(<<<'CSV'
Muc,So tien
"Tổng Cộng",222.000
CSV),
        ]);

        $amount = app(GoogleSheetTotalExtractor::class)->extractTotalFromUrl($sheetUrl, true);

        $this->assertSame(222000, $amount);
        $this->assertSame(222000, Cache::get($cacheKey));
    }
}
