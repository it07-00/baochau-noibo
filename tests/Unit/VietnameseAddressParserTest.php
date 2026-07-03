<?php

namespace Tests\Unit;

use App\Support\VietnameseAddressParser;
use App\Support\VietnamProvinces;
use PHPUnit\Framework\TestCase;

class VietnameseAddressParserTest extends TestCase
{
    public function test_it_extracts_new_two_tier_administrative_units_from_an_address(): void
    {
        $region = VietnameseAddressParser::parse(
            'Ô 04, Lô A, Đường số 1, Khu công nghiệp Đông An, Phường Bình Hòa, TP Hồ Chí Minh'
        );

        $this->assertSame('TP. Hồ Chí Minh', $region['province']);
        $this->assertSame('Phường Bình Hòa', $region['ward']);
        $this->assertSame('Khu công nghiệp Đông An', $region['industrial_park']);
    }

    public function test_it_does_not_treat_an_old_district_address_as_a_new_ward(): void
    {
        $region = VietnameseAddressParser::parse(
            '201B Nguyễn Chí Thanh, Phường 12, Quận 5, Hồ Chí Minh'
        );

        $this->assertSame('TP. Hồ Chí Minh', $region['province']);
        $this->assertNull($region['ward']);
        $this->assertNull($region['industrial_park']);
    }

    public function test_it_converts_legacy_province_names_to_the_new_province(): void
    {
        $this->assertSame('Tây Ninh', VietnamProvinces::canonicalize('Long An'));
        $this->assertSame('TP. Hồ Chí Minh', VietnamProvinces::canonicalize('Bình Dương'));
        $this->assertSame('Cà Mau', VietnamProvinces::canonicalize('Bạc Liêu'));
        $this->assertCount(34, VietnamProvinces::list());
    }
}
