<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\DTOs\Requests;

use LJPc\ClouDNS\DTOs\Requests\CreateZoneRequest;
use LJPc\ClouDNS\Enums\ZoneType;
use LJPc\ClouDNS\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class CreateZoneRequestTest extends TestCase
{
    public function test_create_master_zone(): void
    {
        $request = new CreateZoneRequest(
            domainName: 'example.com',
            zoneType: ZoneType::MASTER,
            nameservers: ['ns1.example.com', 'ns2.example.com']
        );

        $array = $request->toArray();

        $this->assertEquals('example.com', $array['domain-name']);
        $this->assertEquals('master', $array['zone-type']);
        $this->assertEquals(['ns1.example.com', 'ns2.example.com'], $array['ns']);
        $this->assertArrayNotHasKey('master-ip', $array);
    }

    public function test_create_slave_zone_with_master_ip(): void
    {
        $request = new CreateZoneRequest(
            domainName: 'slave.example.com',
            zoneType: ZoneType::SLAVE,
            masterIp: '192.168.1.1'
        );

        $array = $request->toArray();

        $this->assertEquals('slave.example.com', $array['domain-name']);
        $this->assertEquals('slave', $array['zone-type']);
        $this->assertEquals('192.168.1.1', $array['master-ip']);
        $this->assertArrayNotHasKey('ns', $array);
    }

    public function test_throws_exception_for_empty_domain_name(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Domain name is required');

        new CreateZoneRequest(
            domainName: '',
            zoneType: ZoneType::MASTER
        );
    }

    public function test_throws_exception_for_slave_without_master_ip(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Zone type slave requires master IP');

        new CreateZoneRequest(
            domainName: 'slave.example.com',
            zoneType: ZoneType::SLAVE
        );
    }

    public function test_throws_exception_for_invalid_master_ip(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid master IP address');

        new CreateZoneRequest(
            domainName: 'slave.example.com',
            zoneType: ZoneType::SLAVE,
            masterIp: 'not-an-ip'
        );
    }

    public function test_throws_exception_for_invalid_nameserver(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid nameserver: -invalid-.nameserver');

        new CreateZoneRequest(
            domainName: 'example.com',
            zoneType: ZoneType::MASTER,
            nameservers: ['ns1.example.com', '-invalid-.nameserver']
        );
    }

    public function test_create_parked_zone(): void
    {
        $request = new CreateZoneRequest(
            domainName: 'parked.example.com',
            zoneType: ZoneType::PARKED
        );

        $array = $request->toArray();

        $this->assertEquals('parked.example.com', $array['domain-name']);
        $this->assertEquals('parked', $array['zone-type']);
    }

    public function test_create_geodns_zone(): void
    {
        $request = new CreateZoneRequest(
            domainName: 'geo.example.com',
            zoneType: ZoneType::GEODNS
        );

        $array = $request->toArray();

        $this->assertEquals('geo.example.com', $array['domain-name']);
        $this->assertEquals('geodns', $array['zone-type']);
    }

    public function test_create_reverse_zone(): void
    {
        $request = new CreateZoneRequest(
            domainName: '1.168.192.in-addr.arpa',
            zoneType: ZoneType::REVERSE
        );

        $array = $request->toArray();

        $this->assertEquals('1.168.192.in-addr.arpa', $array['domain-name']);
        $this->assertEquals('reverse', $array['zone-type']);
    }
}