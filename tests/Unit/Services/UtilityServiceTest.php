<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\UtilityService;
use PHPUnit\Framework\TestCase;

class UtilityServiceTest extends TestCase
{
    private function createService(array $responses): UtilityService
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
        
        return new UtilityService($client);
    }

    public function test_get_available_ttls(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                60, 300, 900, 1800, 3600, 21600, 43200, 86400, 172800, 259200, 604800, 1209600, 2592000
            ]))
        ]);

        $result = $service->getAvailableTTLs();

        $this->assertIsArray($result);
        $this->assertContains(60, $result);
        $this->assertContains(3600, $result);
        $this->assertContains(86400, $result);
    }

    public function test_get_available_record_types(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SRV', 'WR', 'ALIAS'
            ]))
        ]);

        $result = $service->getAvailableRecordTypes('master');

        $this->assertIsArray($result);
        $this->assertContains('A', $result);
        $this->assertContains('CNAME', $result);
        $this->assertContains('MX', $result);
        $this->assertContains('WR', $result);
    }

    public function test_create_failover_webhook(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Webhook created successfully.'
            ]))
        ]);

        $result = $service->createFailoverWebhook(
            'example.com',
            12345,
            'webhook-down',
            'https://example.com/webhook'
        );

        $this->assertEquals('Success', $result['status']);
    }
}