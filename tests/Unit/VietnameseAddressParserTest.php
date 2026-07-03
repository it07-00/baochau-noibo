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

    public function test_it_canonicalizes_wards(): void
    {
        $this->assertSame('Phường Bình Hòa', VietnameseAddressParser::canonicalizeWard('phường Bình Hòa'));
        $this->assertSame('Xã Long Hậu', VietnameseAddressParser::canonicalizeWard('xã Long Hậu'));
        $this->assertSame('Đặc khu Côn Đảo', VietnameseAddressParser::canonicalizeWard('đặc khu Côn Đảo'));
        $this->assertNull(VietnameseAddressParser::canonicalizeWard(''));
        $this->assertNull(VietnameseAddressParser::canonicalizeWard(null));
    }

    public function test_it_canonicalizes_industrial_parks(): void
    {
        $this->assertSame('Khu công nghiệp Việt Nam - Singapore', VietnameseAddressParser::canonicalizeIndustrialPark('khu công nghiệp Việt Nam - Singapore'));
        $this->assertSame('Khu công nghiệp Việt Nam - Singapore', VietnameseAddressParser::canonicalizeIndustrialPark('Khu công nghiệp Việt Nam- Singapore'));
        $this->assertSame('Khu công nghiệp Việt Nam - Singapore', VietnameseAddressParser::canonicalizeIndustrialPark('khu công nghiệp Việt Nam–Singapore'));
        $this->assertSame('KCN Đông An', VietnameseAddressParser::canonicalizeIndustrialPark('kcn Đông An'));
        $this->assertSame('KCN Đông An', VietnameseAddressParser::canonicalizeIndustrialPark('KCN  Đông An'));
        $this->assertNull(VietnameseAddressParser::canonicalizeIndustrialPark(''));
        $this->assertNull(VietnameseAddressParser::canonicalizeIndustrialPark(null));
    }
}
