<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Enums;

use LJPc\ClouDNS\Enums\RowsPerPage;
use PHPUnit\Framework\TestCase;

class RowsPerPageTest extends TestCase
{
    public function test_rows_per_page_values(): void
    {
        $this->assertEquals(10, RowsPerPage::TEN->value);
        $this->assertEquals(20, RowsPerPage::TWENTY->value);
        $this->assertEquals(30, RowsPerPage::THIRTY->value);
        $this->assertEquals(50, RowsPerPage::FIFTY->value);
        $this->assertEquals(100, RowsPerPage::HUNDRED->value);
    }

    public function test_rows_per_page_cases(): void
    {
        $cases = RowsPerPage::cases();
        
        $this->assertCount(5, $cases);
        $this->assertContainsOnlyInstancesOf(RowsPerPage::class, $cases);
        $this->assertContains(RowsPerPage::TEN, $cases);
        $this->assertContains(RowsPerPage::TWENTY, $cases);
        $this->assertContains(RowsPerPage::THIRTY, $cases);
        $this->assertContains(RowsPerPage::FIFTY, $cases);
        $this->assertContains(RowsPerPage::HUNDRED, $cases);
    }

    public function test_try_from_valid_value(): void
    {
        $this->assertSame(RowsPerPage::TEN, RowsPerPage::tryFrom(10));
        $this->assertSame(RowsPerPage::TWENTY, RowsPerPage::tryFrom(20));
        $this->assertSame(RowsPerPage::THIRTY, RowsPerPage::tryFrom(30));
        $this->assertSame(RowsPerPage::FIFTY, RowsPerPage::tryFrom(50));
        $this->assertSame(RowsPerPage::HUNDRED, RowsPerPage::tryFrom(100));
    }

    public function test_try_from_invalid_value(): void
    {
        $this->assertNull(RowsPerPage::tryFrom(15));
        $this->assertNull(RowsPerPage::tryFrom(0));
        $this->assertNull(RowsPerPage::tryFrom(-1));
        $this->assertNull(RowsPerPage::tryFrom(200));
    }

    public function test_from_valid_value(): void
    {
        $this->assertSame(RowsPerPage::TEN, RowsPerPage::from(10));
        $this->assertSame(RowsPerPage::TWENTY, RowsPerPage::from(20));
        $this->assertSame(RowsPerPage::THIRTY, RowsPerPage::from(30));
        $this->assertSame(RowsPerPage::FIFTY, RowsPerPage::from(50));
        $this->assertSame(RowsPerPage::HUNDRED, RowsPerPage::from(100));
    }

    public function test_from_invalid_value_throws_exception(): void
    {
        $this->expectException(\ValueError::class);
        RowsPerPage::from(15);
    }

    public function test_enum_is_backed_by_int(): void
    {
        $reflection = new \ReflectionEnum(RowsPerPage::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertEquals('int', $reflection->getBackingType()->getName());
    }

    public function test_enum_name_property(): void
    {
        $this->assertEquals('TEN', RowsPerPage::TEN->name);
        $this->assertEquals('TWENTY', RowsPerPage::TWENTY->name);
        $this->assertEquals('THIRTY', RowsPerPage::THIRTY->name);
        $this->assertEquals('FIFTY', RowsPerPage::FIFTY->name);
        $this->assertEquals('HUNDRED', RowsPerPage::HUNDRED->name);
    }

    public function test_values_static_method(): void
    {
        $values = RowsPerPage::values();
        
        $this->assertIsArray($values);
        $this->assertCount(5, $values);
        $this->assertContains(10, $values);
        $this->assertContains(20, $values);
        $this->assertContains(30, $values);
        $this->assertContains(50, $values);
        $this->assertContains(100, $values);
    }

    public function test_is_valid(): void
    {
        $this->assertTrue(RowsPerPage::isValid(10));
        $this->assertTrue(RowsPerPage::isValid(20));
        $this->assertTrue(RowsPerPage::isValid(30));
        $this->assertTrue(RowsPerPage::isValid(50));
        $this->assertTrue(RowsPerPage::isValid(100));
        
        $this->assertFalse(RowsPerPage::isValid(15));
        $this->assertFalse(RowsPerPage::isValid(0));
        $this->assertFalse(RowsPerPage::isValid(-1));
        $this->assertFalse(RowsPerPage::isValid(200));
        $this->assertFalse(RowsPerPage::isValid(99));
    }

    public function test_get_default(): void
    {
        $default = RowsPerPage::getDefault();
        
        $this->assertSame(RowsPerPage::THIRTY, $default);
        $this->assertEquals(30, $default->value);
    }
}