<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\DNSSECService;
use LJPc\ClouDNS\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DNSSECServiceTest extends TestCase
{
    private DNSSECService $service;
    private ClouDNSClient&MockObject $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockClient = $this->createMock(ClouDNSClient::class);
        $this->service = new DNSSECService($this->mockClient);
    }

    public function test_activate_dnssec(): void
    {
        $expectedResponse = [
            'status' => 'Success',
            'statusDescription' => 'DNSSEC activated successfully'
        ];

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with('dns/activate-dnssec', ['domain-name' => 'example.com'])
            ->willReturn($expectedResponse);

        $result = $this->service->activate('example.com');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_deactivate_dnssec(): void
    {
        $expectedResponse = [
            'status' => 'Success',
            'statusDescription' => 'DNSSEC deactivated successfully'
        ];

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with('dns/deactivate-dnssec', ['domain-name' => 'example.com'])
            ->willReturn($expectedResponse);

        $result = $this->service->deactivate('example.com');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_ds_records(): void
    {
        $expectedResponse = [
            'status' => 'Success',
            'ds' => [
                [
                    'digest_type' => 2,
                    'algorithm' => 8,
                    'digest' => '1234567890ABCDEF',
                    'key_tag' => 12345
                ]
            ]
        ];

        $this->mockClient->expects($this->once())
            ->method('get')
            ->with('dns/get-dnssec-ds-records', ['domain-name' => 'example.com'])
            ->willReturn($expectedResponse);

        $result = $this->service->getDsRecords('example.com');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_is_available_returns_true(): void
    {
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with('dns/is-dnssec-available', ['domain-name' => 'example.com'])
            ->willReturn(['available' => '1']);

        $result = $this->service->isAvailable('example.com');

        $this->assertTrue($result);
    }

    public function test_is_available_returns_false(): void
    {
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with('dns/is-dnssec-available', ['domain-name' => 'example.com'])
            ->willReturn(['available' => '0']);

        $result = $this->service->isAvailable('example.com');

        $this->assertFalse($result);
    }

    public function test_add_ds_record(): void
    {
        $expectedResponse = [
            'status' => 'Success',
            'statusDescription' => 'DS record added successfully'
        ];

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with('dns/add-dnssec-ds-record', [
                'domain-name' => 'example.com',
                'key-tag' => 12345,
                'algorithm' => 8,
                'digest-type' => 2,
                'digest' => '1234567890ABCDEF'
            ])
            ->willReturn($expectedResponse);

        $result = $this->service->addDsRecord('example.com', 12345, 8, 2, '1234567890ABCDEF');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_remove_ds_record_with_key_tag(): void
    {
        $expectedResponse = [
            'status' => 'Success',
            'statusDescription' => 'DS record removed successfully'
        ];

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with('dns/remove-dnssec-ds-record', [
                'domain-name' => 'example.com',
                'key-tag' => 12345
            ])
            ->willReturn($expectedResponse);

        $result = $this->service->removeDsRecord('example.com', 12345);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_remove_ds_record_without_key_tag(): void
    {
        $expectedResponse = [
            'status' => 'Success',
            'statusDescription' => 'All DS records removed successfully'
        ];

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with('dns/remove-dnssec-ds-record', [
                'domain-name' => 'example.com'
            ])
            ->willReturn($expectedResponse);

        $result = $this->service->removeDsRecord('example.com');

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_set_opt_out_enabled(): void
    {
        $expectedResponse = [
            'status' => 'Success',
            'statusDescription' => 'DNSSEC OPTOUT enabled'
        ];

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with('dns/set-dnssec-optout', [
                'domain-name' => 'example.com',
                'status' => '1'
            ])
            ->willReturn($expectedResponse);

        $result = $this->service->setOptOut('example.com', true);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_set_opt_out_disabled(): void
    {
        $expectedResponse = [
            'status' => 'Success',
            'statusDescription' => 'DNSSEC OPTOUT disabled'
        ];

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with('dns/set-dnssec-optout', [
                'domain-name' => 'example.com',
                'status' => '0'
            ])
            ->willReturn($expectedResponse);

        $result = $this->service->setOptOut('example.com', false);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_is_active_returns_true(): void
    {
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with('dns/get-dnssec-ds-records', ['domain-name' => 'example.com'])
            ->willReturn(['status' => 'Success', 'ds' => []]);

        $result = $this->service->isActive('example.com');

        $this->assertTrue($result);
    }

    public function test_is_active_returns_false_on_exception(): void
    {
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with('dns/get-dnssec-ds-records', ['domain-name' => 'example.com'])
            ->willThrowException(new \Exception('DNSSEC not active'));

        $result = $this->service->isActive('example.com');

        $this->assertFalse($result);
    }

    public function test_get_available_algorithms(): void
    {
        $algorithms = $this->service->getAvailableAlgorithms();

        $this->assertIsArray($algorithms);
        $this->assertArrayHasKey(8, $algorithms);
        $this->assertEquals('RSA/SHA-256', $algorithms[8]);
        $this->assertArrayHasKey(13, $algorithms);
        $this->assertEquals('ECDSA Curve P-256 with SHA-256', $algorithms[13]);
    }

    public function test_get_status_when_available_and_active(): void
    {
        $this->mockClient->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['dns/is-dnssec-available', ['domain-name' => 'example.com'], ['available' => '1']],
                ['dns/get-dnssec-ds-records', ['domain-name' => 'example.com'], [
                    'status' => 'Success',
                    'ds' => [
                        [
                            'digest_type' => 2,
                            'algorithm' => 8,
                            'digest' => '1234567890ABCDEF',
                            'key_tag' => 12345
                        ]
                    ]
                ]]
            ]);

        $result = $this->service->getStatus('example.com');

        $this->assertTrue($result['available']);
        $this->assertTrue($result['active']);
        $this->assertCount(1, $result['ds_records']);
    }

    public function test_get_status_when_available_but_not_active(): void
    {
        $this->mockClient->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(function ($endpoint, $params) {
                if ($endpoint === 'dns/is-dnssec-available') {
                    return ['available' => '1'];
                }
                if ($endpoint === 'dns/get-dnssec-ds-records') {
                    throw new \Exception('DNSSEC not active');
                }
            });

        $result = $this->service->getStatus('example.com');

        $this->assertTrue($result['available']);
        $this->assertFalse($result['active']);
        $this->assertEmpty($result['ds_records']);
    }

    public function test_get_status_when_not_available(): void
    {
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with('dns/is-dnssec-available', ['domain-name' => 'example.com'])
            ->willReturn(['available' => '0']);

        $result = $this->service->getStatus('example.com');

        $this->assertFalse($result['available']);
        $this->assertFalse($result['active']);
        $this->assertEmpty($result['ds_records']);
    }
}