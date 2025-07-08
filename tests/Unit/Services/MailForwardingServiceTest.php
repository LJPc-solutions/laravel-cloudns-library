<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\MailForwardingService;
use PHPUnit\Framework\TestCase;

class MailForwardingServiceTest extends TestCase
{
    private function createService(array $responses): MailForwardingService
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
        
        return new MailForwardingService($client);
    }

    public function test_list_mail_forwards(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                '123' => [
                    'id' => '123',
                    'box' => 'info',
                    'host' => '@',
                    'destination' => 'admin@example.com'
                ],
                '124' => [
                    'id' => '124',
                    'box' => 'support',
                    'host' => '@',
                    'destination' => 'support@example.com'
                ]
            ]))
        ]);

        $result = $service->list('example.com');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('123', $result);
        $this->assertArrayHasKey('124', $result);
        $this->assertEquals('info', $result['123']['box']);
        $this->assertEquals('admin@example.com', $result['123']['destination']);
    }

    public function test_add_mail_forward(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Mail forward added successfully.'
            ]))
        ]);

        $result = $service->add('example.com', 'sales', '@', 'sales@external.com');

        $this->assertEquals('Success', $result['status']);
    }

    public function test_delete_mail_forward(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Mail forward deleted successfully.'
            ]))
        ]);

        $result = $service->delete('example.com', 123);

        $this->assertEquals('Success', $result['status']);
    }
}