<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\DTOs\Responses;

use LJPc\ClouDNS\DTOs\Responses\Zone;
use LJPc\ClouDNS\Enums\ZoneType;
use PHPUnit\Framework\TestCase;

class ZoneTest extends TestCase
{
    public function test_create_zone_with_all_properties(): void
    {
        $zone = new Zone(
            name: 'example.com',
            type: ZoneType::MASTER,
            status: 'active',
            recordsCount: 25,
            masterIp: '192.168.1.1',
            isUpdated: true,
            lastUpdate: '2023-01-01 10:00:00',
            created: '2022-01-01 10:00:00',
            groupId: 123
        );

        $this->assertEquals('example.com', $zone->name);
        $this->assertSame(ZoneType::MASTER, $zone->type);
        $this->assertEquals('active', $zone->status);
        $this->assertEquals(25, $zone->recordsCount);
        $this->assertEquals('192.168.1.1', $zone->masterIp);
        $this->assertTrue($zone->isUpdated);
        $this->assertEquals('2023-01-01 10:00:00', $zone->lastUpdate);
        $this->assertEquals('2022-01-01 10:00:00', $zone->created);
        $this->assertEquals(123, $zone->groupId);
    }

    public function test_create_zone_with_minimal_properties(): void
    {
        $zone = new Zone(
            name: 'minimal.com',
            type: ZoneType::SLAVE,
            status: 'inactive',
            recordsCount: 0
        );

        $this->assertEquals('minimal.com', $zone->name);
        $this->assertSame(ZoneType::SLAVE, $zone->type);
        $this->assertEquals('inactive', $zone->status);
        $this->assertEquals(0, $zone->recordsCount);
        $this->assertNull($zone->masterIp);
        $this->assertNull($zone->isUpdated);
        $this->assertNull($zone->lastUpdate);
        $this->assertNull($zone->created);
        $this->assertNull($zone->groupId);
    }

    public function test_from_array_with_all_fields(): void
    {
        $data = [
            'name' => 'example.com',
            'type' => 'master',
            'status' => 'active',
            'records' => 25,
            'master_ip' => '192.168.1.1',
            'is_updated' => 1,
            'last_update' => '2023-01-01 10:00:00',
            'created' => '2022-01-01 10:00:00',
            'group_id' => 123
        ];

        $zone = Zone::fromArray($data);

        $this->assertEquals('example.com', $zone->name);
        $this->assertSame(ZoneType::MASTER, $zone->type);
        $this->assertEquals('active', $zone->status);
        $this->assertEquals(25, $zone->recordsCount);
        $this->assertEquals('192.168.1.1', $zone->masterIp);
        $this->assertTrue($zone->isUpdated);
        $this->assertEquals('2023-01-01 10:00:00', $zone->lastUpdate);
        $this->assertEquals('2022-01-01 10:00:00', $zone->created);
        $this->assertEquals(123, $zone->groupId);
    }

    public function test_from_array_with_minimal_fields(): void
    {
        $data = [
            'name' => 'minimal.com',
            'type' => 'parked',
            'records' => '5'
        ];

        $zone = Zone::fromArray($data);

        $this->assertEquals('minimal.com', $zone->name);
        $this->assertSame(ZoneType::PARKED, $zone->type);
        $this->assertEquals('active', $zone->status); // Default value
        $this->assertEquals(5, $zone->recordsCount);
        $this->assertNull($zone->masterIp);
        $this->assertNull($zone->isUpdated);
    }

    public function test_to_array_with_all_properties(): void
    {
        $zone = new Zone(
            name: 'example.com',
            type: ZoneType::GEODNS,
            status: 'active',
            recordsCount: 25,
            masterIp: '192.168.1.1',
            isUpdated: true,
            lastUpdate: '2023-01-01 10:00:00',
            created: '2022-01-01 10:00:00',
            groupId: 123
        );

        $array = $zone->toArray();

        $this->assertEquals([
            'name' => 'example.com',
            'type' => 'geodns',
            'status' => 'active',
            'records' => 25,
            'master_ip' => '192.168.1.1',
            'is_updated' => true,
            'last_update' => '2023-01-01 10:00:00',
            'created' => '2022-01-01 10:00:00',
            'group_id' => 123
        ], $array);
    }

    public function test_to_array_excludes_null_values(): void
    {
        $zone = new Zone(
            name: 'minimal.com',
            type: ZoneType::REVERSE,
            status: 'active',
            recordsCount: 0
        );

        $array = $zone->toArray();

        $this->assertEquals([
            'name' => 'minimal.com',
            'type' => 'reverse',
            'status' => 'active',
            'records' => 0
        ], $array);
        
        $this->assertArrayNotHasKey('master_ip', $array);
        $this->assertArrayNotHasKey('is_updated', $array);
        $this->assertArrayNotHasKey('last_update', $array);
        $this->assertArrayNotHasKey('created', $array);
        $this->assertArrayNotHasKey('group_id', $array);
    }
}