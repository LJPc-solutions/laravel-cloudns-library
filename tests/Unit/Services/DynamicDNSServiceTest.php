<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Enums\RowsPerPage;
use LJPc\ClouDNS\Services\DynamicDNSService;
use PHPUnit\Framework\TestCase;

class DynamicDNSServiceTest extends TestCase
{
    private function createService(array $responses): DynamicDNSService
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
        
        return new DynamicDNSService($client);
    }

    public function test_get_dynamic_url(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'url' => 'https://ipv4.cloudns.net/api/dynamicURL/?q=unique-string-123'
            ]))
        ]);

        $result = $service->getDynamicUrl('example.com', 12345);

        $this->assertEquals('https://ipv4.cloudns.net/api/dynamicURL/?q=unique-string-123', $result);
    }

    public function test_get_dynamic_url_empty(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([]))
        ]);

        $result = $service->getDynamicUrl('example.com', 12345);

        $this->assertEquals('', $result);
    }

    public function test_disable_dynamic_url(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Dynamic URL disabled.'
            ]))
        ]);

        $result = $service->disableDynamicUrl('example.com', 12345);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_change_dynamic_url(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'url' => 'https://ipv4.cloudns.net/api/dynamicURL/?q=new-unique-string-456'
            ]))
        ]);

        $result = $service->changeDynamicUrl('example.com', 12345);

        $this->assertEquals('https://ipv4.cloudns.net/api/dynamicURL/?q=new-unique-string-456', $result);
    }

    public function test_change_dynamic_url_empty(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([]))
        ]);

        $result = $service->changeDynamicUrl('example.com', 12345);

        $this->assertEquals('', $result);
    }

    public function test_get_history(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'page' => 1,
                'pages' => 2,
                '0' => [
                    'date' => '2023-01-01 10:00:00',
                    'ip' => '192.168.1.100',
                    'user_agent' => 'curl/7.68.0'
                ],
                '1' => [
                    'date' => '2023-01-01 09:00:00',
                    'ip' => '192.168.1.99',
                    'user_agent' => 'wget/1.20.3'
                ]
            ]))
        ]);

        $result = $service->getHistory('example.com', 12345, 1, RowsPerPage::TWENTY);

        $this->assertEquals(1, $result['page']);
        $this->assertEquals(2, $result['pages']);
        $this->assertCount(2, $result['history']);
        $this->assertEquals('2023-01-01 10:00:00', $result['history'][0]['date']);
        $this->assertEquals('192.168.1.100', $result['history'][0]['ip']);
        $this->assertEquals('curl/7.68.0', $result['history'][0]['user_agent']);
    }

    public function test_get_history_with_missing_fields(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'page' => 1,
                'pages' => 1,
                '0' => [
                    'date' => '2023-01-01 10:00:00'
                    // Missing ip and user_agent
                ]
            ]))
        ]);

        $result = $service->getHistory('example.com', 12345);

        $this->assertCount(1, $result['history']);
        $this->assertEquals('2023-01-01 10:00:00', $result['history'][0]['date']);
        $this->assertEquals('', $result['history'][0]['ip']);
        $this->assertEquals('', $result['history'][0]['user_agent']);
    }

    public function test_update_ip_with_specific_ip(): void
    {
        $this->markTestSkipped('This test requires external HTTP call which cannot be mocked easily');
    }

    public function test_update_ip_auto_detect(): void
    {
        $this->markTestSkipped('This test requires external HTTP call which cannot be mocked easily');
    }

    public function test_update_ip_invalid_url(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid dynamic URL format');

        $service = $this->createService([]);
        $service->updateIp('https://invalid.url/without/query');
    }

    public function test_is_enabled_true(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'url' => 'https://ipv4.cloudns.net/api/dynamicURL/?q=some-string'
            ]))
        ]);

        $result = $service->isEnabled('example.com', 12345);

        $this->assertTrue($result);
    }

    public function test_is_enabled_false_empty_url(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'url' => ''
            ]))
        ]);

        $result = $service->isEnabled('example.com', 12345);

        $this->assertFalse($result);
    }

    public function test_is_enabled_false_exception(): void
    {
        $service = $this->createService([
            new Response(500, [], 'Server Error')
        ]);

        $result = $service->isEnabled('example.com', 12345);

        $this->assertFalse($result);
    }
}