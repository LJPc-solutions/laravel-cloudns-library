<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Enums;

use LJPc\ClouDNS\Enums\MonitoringType;
use PHPUnit\Framework\TestCase;

class MonitoringTypeTest extends TestCase
{
    public function test_monitoring_type_values(): void
    {
        $this->assertEquals('dns', MonitoringType::DNS->value);
        $this->assertEquals('tcp', MonitoringType::TCP->value);
        $this->assertEquals('udp', MonitoringType::UDP->value);
        $this->assertEquals('icmp', MonitoringType::ICMP->value);
        $this->assertEquals('smtp', MonitoringType::SMTP->value);
        $this->assertEquals('http', MonitoringType::HTTP->value);
        $this->assertEquals('https', MonitoringType::HTTPS->value);
        $this->assertEquals('ssl', MonitoringType::SSL->value);
    }

    public function test_monitoring_type_cases(): void
    {
        $cases = MonitoringType::cases();
        
        $this->assertCount(8, $cases);
        $this->assertContainsOnlyInstancesOf(MonitoringType::class, $cases);
        $this->assertContains(MonitoringType::DNS, $cases);
        $this->assertContains(MonitoringType::TCP, $cases);
        $this->assertContains(MonitoringType::UDP, $cases);
        $this->assertContains(MonitoringType::ICMP, $cases);
        $this->assertContains(MonitoringType::SMTP, $cases);
        $this->assertContains(MonitoringType::HTTP, $cases);
        $this->assertContains(MonitoringType::HTTPS, $cases);
        $this->assertContains(MonitoringType::SSL, $cases);
    }

    public function test_requires_port(): void
    {
        $this->assertTrue(MonitoringType::TCP->requiresPort());
        $this->assertTrue(MonitoringType::UDP->requiresPort());
        $this->assertTrue(MonitoringType::SMTP->requiresPort());
        $this->assertTrue(MonitoringType::SSL->requiresPort());
        
        $this->assertFalse(MonitoringType::DNS->requiresPort());
        $this->assertFalse(MonitoringType::ICMP->requiresPort());
        $this->assertFalse(MonitoringType::HTTP->requiresPort());
        $this->assertFalse(MonitoringType::HTTPS->requiresPort());
    }

    public function test_requires_path(): void
    {
        $this->assertTrue(MonitoringType::HTTP->requiresPath());
        $this->assertTrue(MonitoringType::HTTPS->requiresPath());
        
        $this->assertFalse(MonitoringType::DNS->requiresPath());
        $this->assertFalse(MonitoringType::TCP->requiresPath());
        $this->assertFalse(MonitoringType::UDP->requiresPath());
        $this->assertFalse(MonitoringType::ICMP->requiresPath());
        $this->assertFalse(MonitoringType::SMTP->requiresPath());
        $this->assertFalse(MonitoringType::SSL->requiresPath());
    }

    public function test_get_default_port(): void
    {
        $this->assertEquals(25, MonitoringType::SMTP->getDefaultPort());
        $this->assertEquals(80, MonitoringType::HTTP->getDefaultPort());
        $this->assertEquals(443, MonitoringType::HTTPS->getDefaultPort());
        $this->assertEquals(443, MonitoringType::SSL->getDefaultPort());
        
        $this->assertNull(MonitoringType::DNS->getDefaultPort());
        $this->assertNull(MonitoringType::TCP->getDefaultPort());
        $this->assertNull(MonitoringType::UDP->getDefaultPort());
        $this->assertNull(MonitoringType::ICMP->getDefaultPort());
    }

    public function test_values_static_method(): void
    {
        $values = MonitoringType::values();
        
        $this->assertIsArray($values);
        $this->assertCount(8, $values);
        $this->assertContains('dns', $values);
        $this->assertContains('tcp', $values);
        $this->assertContains('udp', $values);
        $this->assertContains('icmp', $values);
        $this->assertContains('smtp', $values);
        $this->assertContains('http', $values);
        $this->assertContains('https', $values);
        $this->assertContains('ssl', $values);
    }

    public function test_try_from_valid_value(): void
    {
        $this->assertSame(MonitoringType::DNS, MonitoringType::tryFrom('dns'));
        $this->assertSame(MonitoringType::TCP, MonitoringType::tryFrom('tcp'));
        $this->assertSame(MonitoringType::UDP, MonitoringType::tryFrom('udp'));
        $this->assertSame(MonitoringType::ICMP, MonitoringType::tryFrom('icmp'));
        $this->assertSame(MonitoringType::SMTP, MonitoringType::tryFrom('smtp'));
        $this->assertSame(MonitoringType::HTTP, MonitoringType::tryFrom('http'));
        $this->assertSame(MonitoringType::HTTPS, MonitoringType::tryFrom('https'));
        $this->assertSame(MonitoringType::SSL, MonitoringType::tryFrom('ssl'));
    }

    public function test_try_from_invalid_value(): void
    {
        $this->assertNull(MonitoringType::tryFrom('invalid'));
        $this->assertNull(MonitoringType::tryFrom(''));
    }

    public function test_from_valid_value(): void
    {
        $this->assertSame(MonitoringType::DNS, MonitoringType::from('dns'));
        $this->assertSame(MonitoringType::TCP, MonitoringType::from('tcp'));
    }

    public function test_from_invalid_value_throws_exception(): void
    {
        $this->expectException(\ValueError::class);
        MonitoringType::from('invalid');
    }

    public function test_enum_is_backed_by_string(): void
    {
        $reflection = new \ReflectionEnum(MonitoringType::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertEquals('string', $reflection->getBackingType()->getName());
    }

    public function test_enum_name_property(): void
    {
        $this->assertEquals('DNS', MonitoringType::DNS->name);
        $this->assertEquals('TCP', MonitoringType::TCP->name);
        $this->assertEquals('UDP', MonitoringType::UDP->name);
        $this->assertEquals('ICMP', MonitoringType::ICMP->name);
        $this->assertEquals('SMTP', MonitoringType::SMTP->name);
        $this->assertEquals('HTTP', MonitoringType::HTTP->name);
        $this->assertEquals('HTTPS', MonitoringType::HTTPS->name);
        $this->assertEquals('SSL', MonitoringType::SSL->name);
    }
}