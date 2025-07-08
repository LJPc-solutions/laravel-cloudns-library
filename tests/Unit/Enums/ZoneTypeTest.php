<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Enums;

use LJPc\ClouDNS\Enums\ZoneType;
use PHPUnit\Framework\TestCase;

class ZoneTypeTest extends TestCase
{
    public function test_values_returns_all_zone_types(): void
    {
        $values = ZoneType::values();
        
        $this->assertIsArray($values);
        $this->assertContains('master', $values);
        $this->assertContains('slave', $values);
        $this->assertContains('parked', $values);
        $this->assertContains('geodns', $values);
        $this->assertContains('reverse', $values);
        $this->assertCount(5, $values);
    }

    public function test_get_description(): void
    {
        $this->assertEquals('Master DNS zone', ZoneType::MASTER->getDescription());
        $this->assertEquals('Slave DNS zone', ZoneType::SLAVE->getDescription());
        $this->assertEquals('Parked domain', ZoneType::PARKED->getDescription());
        $this->assertEquals('GeoDNS zone', ZoneType::GEODNS->getDescription());
        $this->assertEquals('Reverse DNS zone', ZoneType::REVERSE->getDescription());
    }

    public function test_requires_master_ip(): void
    {
        $this->assertFalse(ZoneType::MASTER->requiresMasterIp());
        $this->assertTrue(ZoneType::SLAVE->requiresMasterIp());
        $this->assertFalse(ZoneType::PARKED->requiresMasterIp());
        $this->assertFalse(ZoneType::GEODNS->requiresMasterIp());
        $this->assertFalse(ZoneType::REVERSE->requiresMasterIp());
    }

    public function test_from_string(): void
    {
        $zoneType = ZoneType::from('master');
        $this->assertSame(ZoneType::MASTER, $zoneType);

        $zoneType = ZoneType::from('slave');
        $this->assertSame(ZoneType::SLAVE, $zoneType);
    }

    public function test_try_from_returns_null_for_invalid_type(): void
    {
        $zoneType = ZoneType::tryFrom('invalid');
        $this->assertNull($zoneType);
    }
}