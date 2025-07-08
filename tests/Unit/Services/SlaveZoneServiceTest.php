<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\SlaveZoneService;
use PHPUnit\Framework\TestCase;

class SlaveZoneServiceTest extends TestCase
{
    private function createService(array $responses): SlaveZoneService
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
        
        return new SlaveZoneService($client);
    }

    public function test_add_master_server(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Master server added successfully.'
            ]))
        ]);

        $result = $service->addMasterServer('slave.example.com', '192.168.1.10');

        $this->assertEquals('Success', $result['status']);
    }

    public function test_delete_master_server(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Master server deleted successfully.'
            ]))
        ]);

        $result = $service->deleteMasterServer('slave.example.com', 123);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_list_master_servers(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                '1' => [
                    'id' => '1',
                    'ip' => '192.168.1.10'
                ],
                '2' => [
                    'id' => '2',
                    'ip' => '192.168.1.11'
                ]
            ]))
        ]);

        $result = $service->listMasterServers('slave.example.com');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('1', $result);
        $this->assertArrayHasKey('2', $result);
        $this->assertEquals('192.168.1.10', $result['1']['ip']);
        $this->assertEquals('192.168.1.11', $result['2']['ip']);
    }
}