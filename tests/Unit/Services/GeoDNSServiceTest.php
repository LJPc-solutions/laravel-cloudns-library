<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\GeoDNSService;
use PHPUnit\Framework\TestCase;

class GeoDNSServiceTest extends TestCase
{
    private function createService(array $responses): GeoDNSService
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
        
        return new GeoDNSService($client);
    }

    public function test_get_locations(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'DEFAULT' => 'Default location',
                'NA' => 'North America',
                'EU' => 'Europe',
                'AS' => 'Asia',
                'AF' => 'Africa',
                'SA' => 'South America',
                'OC' => 'Oceania',
                'US' => 'United States',
                'UK' => 'United Kingdom',
                'CA' => 'Canada',
                'AU' => 'Australia'
            ]))
        ]);

        $result = $service->getLocations();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('DEFAULT', $result);
        $this->assertArrayHasKey('NA', $result);
        $this->assertArrayHasKey('EU', $result);
        $this->assertEquals('Default location', $result['DEFAULT']);
        $this->assertEquals('North America', $result['NA']);
        $this->assertEquals('Europe', $result['EU']);
    }

    public function test_is_available_true(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'available' => true
            ]))
        ]);

        $result = $service->isAvailable('example.com');

        $this->assertTrue($result);
    }

    public function test_is_available_false(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'available' => false
            ]))
        ]);

        $result = $service->isAvailable('example.com');

        $this->assertFalse($result);
    }

    public function test_is_available_missing_field(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([]))
        ]);

        $result = $service->isAvailable('example.com');

        $this->assertFalse($result);
    }
}