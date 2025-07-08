<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\FailoverService;
use PHPUnit\Framework\TestCase;

class FailoverServiceTest extends TestCase
{
    private function createService(array $responses): FailoverService
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
        
        return new FailoverService($client);
    }

    public function test_activate_with_minimal_config(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Failover activated.'
            ]))
        ]);

        $config = [
            'check_type' => 'tcp',
            'down_event_handler' => 'none',
            'up_event_handler' => 'none',
            'main_ip' => '192.168.1.1'
        ];

        $result = $service->activate('example.com', 12345, $config);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_activate_with_full_config(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Failover activated.'
            ]))
        ]);

        $config = [
            'check_type' => 'http',
            'down_event_handler' => 'webhook',
            'up_event_handler' => 'email',
            'main_ip' => '192.168.1.1',
            'monitoring_region' => 'global',
            'backup_ip_1' => '192.168.1.2',
            'backup_ip_2' => '192.168.1.3',
            'backup_ip_3' => '192.168.1.4',
            'backup_ip_4' => '192.168.1.5'
        ];

        $result = $service->activate('example.com', 12345, $config);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_deactivate(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Failover deactivated.'
            ]))
        ]);

        $result = $service->deactivate('example.com', 12345);

        $this->assertEquals('Success', $result['status']);
    }
}