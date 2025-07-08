<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Enums\RowsPerPage;
use LJPc\ClouDNS\Services\MonitoringService;
use PHPUnit\Framework\TestCase;

class MonitoringServiceTest extends TestCase
{
    private function createService(array $responses): MonitoringService
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack, 'http_errors' => false]);
        
        $client = new ClouDNSClient(
            authId: 'test-id',
            authPassword: 'test-pass',
            cacheEnabled: false,
            logEnabled: false
        );
        
        $client->setHttpClient($httpClient);
        
        return new MonitoringService($client);
    }

    public function test_create_minimal_check(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'id' => '12345',
                'name' => 'Test Check'
            ]))
        ]);

        $config = [
            'name' => 'Test Check',
            'ip' => '192.168.1.1',
            'monitoring_type' => 'icmp',
            'check_period' => 300
        ];

        $result = $service->create($config);

        $this->assertEquals('12345', $result['id']);
        $this->assertEquals('Test Check', $result['name']);
    }

    public function test_create_full_check(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'id' => '12346',
                'name' => 'HTTP Check'
            ]))
        ]);

        $config = [
            'name' => 'HTTP Check',
            'ip' => '192.168.1.1',
            'monitoring_type' => 'http',
            'check_period' => 60,
            'port' => 80,
            'path' => '/health',
            'content' => 'OK',
            'timeout' => 10,
            'check_region' => 'global'
        ];

        $result = $service->create($config);

        $this->assertEquals('12346', $result['id']);
        $this->assertEquals('HTTP Check', $result['name']);
    }

    public function test_list_checks(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'page' => 1,
                'pages' => 1,
                'checks' => [
                    [
                        'id' => '123',
                        'name' => 'Check 1',
                        'type' => 'tcp',
                        'ip' => '192.168.1.1'
                    ],
                    [
                        'id' => '124',
                        'name' => 'Check 2',
                        'type' => 'http',
                        'ip' => '192.168.1.2'
                    ]
                ]
            ]))
        ]);

        $result = $service->list(1, RowsPerPage::TWENTY, 'Check');

        $this->assertEquals(1, $result['page']);
        $this->assertEquals(1, $result['pages']);
        $this->assertCount(2, $result['checks']);
    }

    public function test_delete_check(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Monitoring check deleted.'
            ]))
        ]);

        $result = $service->delete(123);

        $this->assertEquals('Success', $result['status']);
    }
}