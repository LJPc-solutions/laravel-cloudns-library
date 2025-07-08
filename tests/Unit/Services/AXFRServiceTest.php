<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\AXFRService;
use PHPUnit\Framework\TestCase;

class AXFRServiceTest extends TestCase
{
    private function createService(array $responses): AXFRService
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
        
        return new AXFRService($client);
    }

    public function test_add_ip(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'IP added to AXFR whitelist.'
            ]))
        ]);

        $result = $service->addIp('example.com', '192.168.1.100');

        $this->assertEquals('Success', $result['status']);
    }

    public function test_remove_ip(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'IP removed from AXFR whitelist.'
            ]))
        ]);

        $result = $service->removeIp('example.com', 123);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_list_ips(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                '1' => [
                    'id' => '1',
                    'ip' => '192.168.1.100'
                ],
                '2' => [
                    'id' => '2',
                    'ip' => '192.168.1.101'
                ]
            ]))
        ]);

        $result = $service->listIps('example.com');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('1', $result);
        $this->assertArrayHasKey('2', $result);
        $this->assertEquals('192.168.1.100', $result['1']['ip']);
        $this->assertEquals('192.168.1.101', $result['2']['ip']);
    }
}