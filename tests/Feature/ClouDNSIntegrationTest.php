<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Facades\ClouDNS;
use LJPc\ClouDNS\Tests\TestCase;

class ClouDNSIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock handler for testing
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Success login.',
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Replace the HTTP client in the ClouDNSClient
        $clouDNSClient = new ClouDNSClient(
            authId: 'test-auth-id',
            authPassword: 'test-password',
            isSubUser: false,
            useSubUsername: false,
            baseUrl: 'https://api.cloudns.net',
            responseFormat: 'json',
            cacheEnabled: false,
            logEnabled: false
        );

        $this->app->instance(ClouDNSClient::class, $clouDNSClient);
    }

    public function test_facade_is_registered(): void
    {
        $this->assertInstanceOf(
            \LJPc\ClouDNS\Services\AccountService::class,
            ClouDNS::account()
        );

        $this->assertInstanceOf(
            \LJPc\ClouDNS\Services\ZoneService::class,
            ClouDNS::zone()
        );

        $this->assertInstanceOf(
            \LJPc\ClouDNS\Services\RecordService::class,
            ClouDNS::record()
        );
    }

    public function test_service_provider_registers_all_services(): void
    {
        $services = [
            'cloudns.account' => \LJPc\ClouDNS\Services\AccountService::class,
            'cloudns.zone' => \LJPc\ClouDNS\Services\ZoneService::class,
            'cloudns.record' => \LJPc\ClouDNS\Services\RecordService::class,
            'cloudns.dynamicdns' => \LJPc\ClouDNS\Services\DynamicDNSService::class,
            'cloudns.geodns' => \LJPc\ClouDNS\Services\GeoDNSService::class,
            'cloudns.failover' => \LJPc\ClouDNS\Services\FailoverService::class,
            'cloudns.soa' => \LJPc\ClouDNS\Services\SOAService::class,
            'cloudns.mailforwarding' => \LJPc\ClouDNS\Services\MailForwardingService::class,
            'cloudns.slavezone' => \LJPc\ClouDNS\Services\SlaveZoneService::class,
            'cloudns.axfr' => \LJPc\ClouDNS\Services\AXFRService::class,
            'cloudns.monitoring' => \LJPc\ClouDNS\Services\MonitoringService::class,
            'cloudns.utility' => \LJPc\ClouDNS\Services\UtilityService::class,
        ];

        foreach ($services as $key => $class) {
            $this->assertTrue($this->app->bound($key));
            $this->assertInstanceOf($class, $this->app->make($key));
        }
    }

    public function test_configuration_is_loaded(): void
    {
        $config = config('cloudns');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('auth_id', $config);
        $this->assertArrayHasKey('auth_password', $config);
        $this->assertArrayHasKey('is_sub_user', $config);
        $this->assertArrayHasKey('base_url', $config);
        $this->assertArrayHasKey('response_format', $config);
    }

    public function test_client_is_singleton(): void
    {
        $client1 = $this->app->make(ClouDNSClient::class);
        $client2 = $this->app->make(ClouDNSClient::class);

        $this->assertSame($client1, $client2);
    }
}