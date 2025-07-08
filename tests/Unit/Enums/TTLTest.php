<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Enums;

use LJPc\ClouDNS\Enums\TTL;
use PHPUnit\Framework\TestCase;

class TTLTest extends TestCase
{
    public function test_values_returns_all_ttl_values(): void
    {
        $values = TTL::values();
        
        $this->assertIsArray($values);
        $this->assertContains(60, $values);
        $this->assertContains(300, $values);
        $this->assertContains(3600, $values);
        $this->assertContains(86400, $values);
    }

    public function test_is_valid(): void
    {
        $this->assertTrue(TTL::isValid(60));
        $this->assertTrue(TTL::isValid(3600));
        $this->assertTrue(TTL::isValid(86400));
        $this->assertFalse(TTL::isValid(123));
        $this->assertFalse(TTL::isValid(999999));
    }

    public function test_get_default(): void
    {
        $default = TTL::getDefault();
        $this->assertSame(TTL::HOUR_1, $default);
        $this->assertEquals(3600, $default->value);
    }

    public function test_get_label(): void
    {
        $this->assertEquals('1 minute', TTL::MINUTE_1->getLabel());
        $this->assertEquals('5 minutes', TTL::MINUTES_5->getLabel());
        $this->assertEquals('1 hour', TTL::HOUR_1->getLabel());
        $this->assertEquals('1 day', TTL::DAY_1->getLabel());
        $this->assertEquals('1 week', TTL::WEEK_1->getLabel());
        $this->assertEquals('1 month', TTL::MONTH_1->getLabel());
    }

    public function test_from_value(): void
    {
        $ttl = TTL::from(3600);
        $this->assertSame(TTL::HOUR_1, $ttl);
        
        $ttl = TTL::from(86400);
        $this->assertSame(TTL::DAY_1, $ttl);
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $ttl = TTL::tryFrom(999);
        $this->assertNull($ttl);
    }
}