<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\AccountService;
use PHPUnit\Framework\TestCase;

class AccountServiceTest extends TestCase
{
    private function createService(array $responses): AccountService
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
        
        return new AccountService($client);
    }

    public function test_test_login(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Success login.'
            ]))
        ]);

        $result = $service->testLogin();

        $this->assertEquals('Success', $result['status']);
        $this->assertEquals('Success login.', $result['statusDescription']);
    }

    public function test_get_current_ip(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'ip' => '192.168.1.100'
            ]))
        ]);

        $result = $service->getCurrentIp();

        $this->assertEquals('192.168.1.100', $result);
    }

    public function test_get_current_ip_empty(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([]))
        ]);

        $result = $service->getCurrentIp();

        $this->assertEquals('', $result);
    }

    public function test_get_balance(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'balance' => '100.00',
                'currency' => 'USD'
            ]))
        ]);

        $result = $service->getBalance();

        $this->assertEquals('100.00', $result['balance']);
        $this->assertEquals('USD', $result['currency']);
    }

    public function test_get_info(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'id' => '12345',
                'email' => 'test@example.com',
                'status' => 'active'
            ]))
        ]);

        $result = $service->getInfo();

        $this->assertEquals('12345', $result['id']);
        $this->assertEquals('test@example.com', $result['email']);
        $this->assertEquals('active', $result['status']);
    }

    public function test_get_statistics(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'zones' => 10,
                'records' => 150,
                'queries' => 50000
            ]))
        ]);

        $result = $service->getStatistics();

        $this->assertEquals(10, $result['zones']);
        $this->assertEquals(150, $result['records']);
        $this->assertEquals(50000, $result['queries']);
    }

    public function test_is_authenticated_success(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Success login.'
            ]))
        ]);

        $result = $service->isAuthenticated();

        $this->assertTrue($result);
    }

    public function test_is_authenticated_failure(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Failed',
                'statusDescription' => 'Invalid authentication.'
            ]))
        ]);

        $result = $service->isAuthenticated();

        $this->assertFalse($result);
    }

    public function test_is_authenticated_exception(): void
    {
        $service = $this->createService([
            new Response(500, [], 'Server Error')
        ]);

        $result = $service->isAuthenticated();

        $this->assertFalse($result);
    }
}