<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\DTOs\Responses;

use LJPc\ClouDNS\DTOs\Responses\Record;
use LJPc\ClouDNS\Enums\RecordType;
use PHPUnit\Framework\TestCase;

class RecordTest extends TestCase
{
    public function test_create_record_with_all_properties(): void
    {
        $record = new Record(
            id: 12345,
            type: RecordType::A,
            host: 'www',
            record: '192.168.1.1',
            ttl: 3600,
            priority: 10,
            weight: 20,
            port: 80,
            frame: '0',
            frameTitle: 'Title',
            frameKeywords: 'keywords',
            frameDescription: 'description',
            savePath: true,
            redirectType: 301,
            geodnsLocation: 'NA',
            isActive: true,
            created: '2023-01-01 10:00:00',
            modified: '2023-01-02 10:00:00'
        );

        $this->assertEquals(12345, $record->id);
        $this->assertSame(RecordType::A, $record->type);
        $this->assertEquals('www', $record->host);
        $this->assertEquals('192.168.1.1', $record->record);
        $this->assertEquals(3600, $record->ttl);
        $this->assertEquals(10, $record->priority);
        $this->assertEquals(20, $record->weight);
        $this->assertEquals(80, $record->port);
        $this->assertEquals('0', $record->frame);
        $this->assertEquals('Title', $record->frameTitle);
        $this->assertEquals('keywords', $record->frameKeywords);
        $this->assertEquals('description', $record->frameDescription);
        $this->assertTrue($record->savePath);
        $this->assertEquals(301, $record->redirectType);
        $this->assertEquals('NA', $record->geodnsLocation);
        $this->assertTrue($record->isActive);
        $this->assertEquals('2023-01-01 10:00:00', $record->created);
        $this->assertEquals('2023-01-02 10:00:00', $record->modified);
    }

    public function test_create_record_with_minimal_properties(): void
    {
        $record = new Record(
            id: 54321,
            type: RecordType::CNAME,
            host: 'alias',
            record: 'target.example.com',
            ttl: 7200
        );

        $this->assertEquals(54321, $record->id);
        $this->assertSame(RecordType::CNAME, $record->type);
        $this->assertEquals('alias', $record->host);
        $this->assertEquals('target.example.com', $record->record);
        $this->assertEquals(7200, $record->ttl);
        $this->assertNull($record->priority);
        $this->assertNull($record->weight);
        $this->assertNull($record->port);
    }

    public function test_from_array_with_all_fields(): void
    {
        $data = [
            'id' => '12345',
            'type' => 'MX',
            'host' => '@',
            'record' => 'mail.example.com',
            'ttl' => '3600',
            'priority' => '10',
            'weight' => '20',
            'port' => '25',
            'frame' => '1',
            'frame_title' => 'Mail Server',
            'frame_keywords' => 'mail, server',
            'frame_description' => 'Main mail server',
            'save_path' => '1',
            'redirect_type' => '302',
            'geodns_location' => 'EU',
            'is_active' => '1',
            'created' => '2023-01-01 10:00:00',
            'modified' => '2023-01-02 10:00:00'
        ];

        $record = Record::fromArray($data);

        $this->assertEquals(12345, $record->id);
        $this->assertSame(RecordType::MX, $record->type);
        $this->assertEquals('@', $record->host);
        $this->assertEquals('mail.example.com', $record->record);
        $this->assertEquals(3600, $record->ttl);
        $this->assertEquals(10, $record->priority);
        $this->assertEquals(20, $record->weight);
        $this->assertEquals(25, $record->port);
        $this->assertEquals('1', $record->frame);
        $this->assertEquals('Mail Server', $record->frameTitle);
        $this->assertEquals('mail, server', $record->frameKeywords);
        $this->assertEquals('Main mail server', $record->frameDescription);
        $this->assertTrue($record->savePath);
        $this->assertEquals(302, $record->redirectType);
        $this->assertEquals('EU', $record->geodnsLocation);
        $this->assertTrue($record->isActive);
        $this->assertEquals('2023-01-01 10:00:00', $record->created);
        $this->assertEquals('2023-01-02 10:00:00', $record->modified);
    }

    public function test_from_array_with_minimal_fields(): void
    {
        $data = [
            'id' => '54321',
            'type' => 'TXT',
            'host' => '_dmarc',
            'record' => 'v=DMARC1; p=none;',
            'ttl' => '86400'
        ];

        $record = Record::fromArray($data);

        $this->assertEquals(54321, $record->id);
        $this->assertSame(RecordType::TXT, $record->type);
        $this->assertEquals('_dmarc', $record->host);
        $this->assertEquals('v=DMARC1; p=none;', $record->record);
        $this->assertEquals(86400, $record->ttl);
        $this->assertNull($record->priority);
        $this->assertNull($record->isActive);
    }

    public function test_to_array_with_all_properties(): void
    {
        $record = new Record(
            id: 12345,
            type: RecordType::SRV,
            host: '_service._tcp',
            record: 'server.example.com',
            ttl: 3600,
            priority: 10,
            weight: 60,
            port: 5060,
            geodnsLocation: 'ASIA',
            isActive: true
        );

        $array = $record->toArray();

        $this->assertEquals([
            'id' => 12345,
            'type' => 'SRV',
            'host' => '_service._tcp',
            'record' => 'server.example.com',
            'ttl' => 3600,
            'priority' => 10,
            'weight' => 60,
            'port' => 5060,
            'geodns_location' => 'ASIA',
            'is_active' => true
        ], $array);
    }

    public function test_to_array_excludes_null_values(): void
    {
        $record = new Record(
            id: 99999,
            type: RecordType::AAAA,
            host: 'ipv6',
            record: '2001:db8::1',
            ttl: 3600
        );

        $array = $record->toArray();

        $this->assertEquals([
            'id' => 99999,
            'type' => 'AAAA',
            'host' => 'ipv6',
            'record' => '2001:db8::1',
            'ttl' => 3600
        ], $array);

        $this->assertArrayNotHasKey('priority', $array);
        $this->assertArrayNotHasKey('weight', $array);
        $this->assertArrayNotHasKey('port', $array);
        $this->assertArrayNotHasKey('frame', $array);
        $this->assertArrayNotHasKey('is_active', $array);
    }
}