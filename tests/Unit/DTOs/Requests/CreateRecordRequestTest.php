<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\DTOs\Requests;

use LJPc\ClouDNS\DTOs\Requests\CreateRecordRequest;
use LJPc\ClouDNS\Enums\RecordType;
use LJPc\ClouDNS\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class CreateRecordRequestTest extends TestCase
{
    public function test_creates_simple_a_record_request(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::A,
            host: 'www',
            record: '192.168.1.1',
            ttl: 3600
        );

        $array = $request->toArray();

        $this->assertEquals('example.com', $array['domain-name']);
        $this->assertEquals('A', $array['record-type']);
        $this->assertEquals('www', $array['host']);
        $this->assertEquals('192.168.1.1', $array['record']);
        $this->assertEquals(3600, $array['ttl']);
    }

    public function test_creates_mx_record_with_priority(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::MX,
            host: '@',
            record: 'mail.example.com',
            ttl: 3600,
            priority: 10
        );

        $array = $request->toArray();

        $this->assertEquals('MX', $array['record-type']);
        $this->assertEquals(10, $array['priority']);
    }

    public function test_throws_exception_for_mx_without_priority(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Record type MX requires priority');

        new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::MX,
            host: '@',
            record: 'mail.example.com',
            ttl: 3600
        );
    }

    public function test_creates_srv_record_with_all_fields(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::SRV,
            host: '_service._tcp',
            record: 'server.example.com',
            ttl: 3600,
            priority: 10,
            weight: 60,
            port: 5060
        );

        $array = $request->toArray();

        $this->assertEquals('SRV', $array['record-type']);
        $this->assertEquals(10, $array['priority']);
        $this->assertEquals(60, $array['weight']);
        $this->assertEquals(5060, $array['port']);
    }

    public function test_throws_exception_for_invalid_ttl(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid TTL value');

        new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::A,
            host: 'www',
            record: '192.168.1.1',
            ttl: 999
        );
    }

    public function test_creates_caa_record(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::CAA,
            host: '@',
            record: 'dummy',
            ttl: 3600,
            caaFlag: '0',
            caaTag: 'issue',
            caaValue: 'letsencrypt.org'
        );

        $array = $request->toArray();

        $this->assertEquals('0 issue letsencrypt.org', $array['record']);
    }

    public function test_throws_exception_for_caa_without_required_fields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('CAA record requires flag, tag, and value');

        new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::CAA,
            host: '@',
            record: 'dummy',
            ttl: 3600
        );
    }

    public function test_creates_web_redirect_record(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::WR,
            host: 'redirect',
            record: 'https://new.example.com',
            ttl: 3600,
            frame: '0',
            redirectType: 301,
            savePath: true
        );

        $array = $request->toArray();

        $this->assertEquals('WR', $array['record-type']);
        $this->assertEquals('0', $array['frame']);
        $this->assertEquals(301, $array['redirect-type']);
        $this->assertEquals('1', $array['save-path']);
    }

    public function test_creates_web_redirect_record_with_save_path_false(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::WR,
            host: 'redirect',
            record: 'https://new.example.com',
            ttl: 3600,
            savePath: false
        );

        $array = $request->toArray();

        $this->assertEquals('0', $array['save-path']);
    }

    public function test_creates_sshfp_record(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::SSHFP,
            host: '@',
            record: 'dummy',
            ttl: 3600,
            sshfpAlgorithm: 1,
            sshfpFpType: 2,
            sshfpFingerprint: 'abcdef0123456789'
        );

        $array = $request->toArray();

        $this->assertEquals('1 2 abcdef0123456789', $array['record']);
    }

    public function test_throws_exception_for_sshfp_without_required_fields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('SSHFP record requires algorithm, fingerprint type, and fingerprint');

        new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::SSHFP,
            host: '@',
            record: 'dummy',
            ttl: 3600
        );
    }

    public function test_creates_tlsa_record(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::TLSA,
            host: '_443._tcp.www',
            record: 'dummy',
            ttl: 3600,
            tlsaUsage: 3,
            tlsaSelector: 1,
            tlsaMatchingType: 1,
            tlsaCertificate: 'abc123def456'
        );

        $array = $request->toArray();

        $this->assertEquals('3 1 1 abc123def456', $array['record']);
    }

    public function test_throws_exception_for_tlsa_without_required_fields(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('TLSA record requires usage, selector, matching type, and certificate');

        new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::TLSA,
            host: '_443._tcp.www',
            record: 'dummy',
            ttl: 3600
        );
    }

    public function test_throws_exception_for_srv_without_port(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Record type SRV requires port');

        new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::SRV,
            host: '_service._tcp',
            record: 'server.example.com',
            ttl: 3600,
            priority: 10,
            weight: 60
            // Missing port
        );
    }

    public function test_throws_exception_for_srv_without_weight(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Record type SRV requires weight');

        new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::SRV,
            host: '_service._tcp',
            record: 'server.example.com',
            ttl: 3600,
            priority: 10,
            port: 5060
            // Missing weight
        );
    }

    public function test_creates_record_with_geodns_location(): void
    {
        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::A,
            host: 'geo',
            record: '192.168.1.1',
            ttl: 3600,
            geodnsLocation: 'EU'
        );

        $array = $request->toArray();

        $this->assertEquals('EU', $array['geodns-location']);
    }
}