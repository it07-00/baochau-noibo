<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\CustomerRegionNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRegionNormalizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_previews_and_applies_legacy_customer_region_normalization(): void
    {
        $convertible = Customer::create([
            'name' => 'Khách hàng Long An cũ',
            'province' => 'Long An',
            'address' => 'KCN Long Hậu, Xã Long Hậu, Long An',
        ]);
        Customer::create([
            'name' => 'Khách hàng thiếu địa chỉ',
            'province' => 'Đồng Nai',
        ]);

        $normalizer = app(CustomerRegionNormalizer::class);
        $preview = $normalizer->run();

        $this->assertSame(2, $preview['total']);
        $this->assertSame(1, $preview['changed']);
        $this->assertSame(1, $preview['province_changed']);
        $this->assertSame(1, $preview['ward_detected']);
        $this->assertSame(1, $preview['industrial_park_detected']);
        $this->assertSame('Long An', $convertible->fresh()->province);

        $applied = $normalizer->run(apply: true);
        $convertible->refresh();

        $this->assertSame(1, $applied['changed']);
        $this->assertSame('Tây Ninh', $convertible->province);
        $this->assertSame('Xã Long Hậu', $convertible->ward);
        $this->assertSame('KCN Long Hậu', $convertible->industrial_park);
    }
}
