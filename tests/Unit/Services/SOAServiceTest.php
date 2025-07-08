<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Services\SOAService;
use PHPUnit\Framework\TestCase;

class SOAServiceTest extends TestCase
{
    private function createService(array $responses): SOAService
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
        
        return new SOAService($client);
    }

    public function test_get_details(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'primaryNS' => 'ns1.example.com',
                'adminMail' => 'admin@example.com',
                'serial' => '2023010101',
                'refresh' => '7200',
                'retry' => '1800',
                'expire' => '1209600',
                'defaultTTL' => '3600'
            ]))
        ]);

        $result = $service->getDetails('example.com');

        $this->assertEquals('ns1.example.com', $result['primaryNS']);
        $this->assertEquals('admin@example.com', $result['adminMail']);
        $this->assertEquals('2023010101', $result['serial']);
        $this->assertEquals('7200', $result['refresh']);
        $this->assertEquals('1800', $result['retry']);
        $this->assertEquals('1209600', $result['expire']);
        $this->assertEquals('3600', $result['defaultTTL']);
    }

    public function test_modify_with_all_fields(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'SOA record modified.'
            ]))
        ]);

        $result = $service->modify('example.com', [
            'primary_ns' => 'ns2.example.com',
            'admin_mail' => 'hostmaster@example.com',
            'default_ttl' => '7200',
            'refresh' => '14400',
            'retry' => '3600',
            'expire' => '2419200'
        ]);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_modify_with_partial_fields(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'SOA record modified.'
            ]))
        ]);

        $result = $service->modify('example.com', [
            'default_ttl' => '14400',
            'refresh' => '28800',
            'unused_field' => 'should_be_ignored'
        ]);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_reset(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'SOA record reset to defaults.'
            ]))
        ]);

        $result = $service->reset('example.com');

        $this->assertEquals('Success', $result['status']);
    }
}