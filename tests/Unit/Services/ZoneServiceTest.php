<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\DTOs\Requests\CreateZoneRequest;
use LJPc\ClouDNS\Enums\RowsPerPage;
use LJPc\ClouDNS\Enums\ZoneType;
use LJPc\ClouDNS\Services\ZoneService;
use PHPUnit\Framework\TestCase;

class ZoneServiceTest extends TestCase
{
    private function createService(array $responses): ZoneService
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
        
        return new ZoneService($client);
    }

    public function test_list_zones(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'page' => 1,
                'pages' => 2,
                '0' => [
                    'name' => 'example.com',
                    'type' => 'master',
                    'status' => 'active',
                    'records' => 10
                ],
                '1' => [
                    'name' => 'test.com',
                    'type' => 'slave',
                    'status' => 'active',
                    'records' => 5,
                    'master_ip' => '192.168.1.1'
                ]
            ]))
        ]);

        $result = $service->list(
            page: 1,
            rowsPerPage: RowsPerPage::THIRTY,
            search: 'example',
            groupId: 123
        );

        $this->assertEquals(1, $result['page']);
        $this->assertEquals(2, $result['pages']);
        $this->assertCount(2, $result['zones']);
        $this->assertEquals('example.com', $result['zones'][0]->name);
        $this->assertEquals(ZoneType::MASTER, $result['zones'][0]->type);
    }

    public function test_create_zone_with_dto(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Domain zone example.com was created successfully.'
            ]))
        ]);

        $request = new CreateZoneRequest(
            domainName: 'example.com',
            zoneType: ZoneType::MASTER,
            nameservers: ['ns1.example.com', 'ns2.example.com']
        );

        $result = $service->create($request);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_create_zone_with_array(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Domain zone slave.com was created successfully.'
            ]))
        ]);

        $result = $service->create([
            'domain_name' => 'slave.com',
            'zone_type' => 'slave',
            'master_ip' => '192.168.1.1'
        ]);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_delete_zone(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Domain zone example.com was deleted successfully.'
            ]))
        ]);

        $result = $service->delete('example.com');

        $this->assertEquals('Success', $result['status']);
    }

    public function test_get_zone_info(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'name' => 'example.com',
                'type' => 'master',
                'status' => 'active',
                'ns' => ['ns1.example.com', 'ns2.example.com']
            ]))
        ]);

        $result = $service->getInfo('example.com');

        $this->assertEquals('example.com', $result['name']);
        $this->assertEquals('master', $result['type']);
    }

    public function test_get_statistics(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'total_zones' => 100,
                'master_zones' => 80,
                'slave_zones' => 20
            ]))
        ]);

        $result = $service->getStatistics();

        $this->assertEquals(100, $result['total_zones']);
        $this->assertEquals(80, $result['master_zones']);
    }

    public function test_update_status(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Zone status updated.'
            ]))
        ]);

        $result = $service->updateStatus('example.com', 'inactive');

        $this->assertEquals('Success', $result['status']);
    }

    public function test_get_page_count(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'pages' => 5
            ]))
        ]);

        $result = $service->getPageCount('search', 123);

        $this->assertEquals(5, $result);
    }

    public function test_get_page_count_default(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([]))
        ]);

        $result = $service->getPageCount();

        $this->assertEquals(1, $result);
    }

    public function test_exists_true(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'name' => 'example.com',
                'type' => 'master'
            ]))
        ]);

        $result = $service->exists('example.com');

        $this->assertTrue($result);
    }

    public function test_exists_false(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Failed',
                'statusDescription' => 'Zone not found.'
            ]))
        ]);

        $result = $service->exists('notfound.com');

        $this->assertFalse($result);
    }

    public function test_get_all_zones(): void
    {
        $service = $this->createService([
            // First page
            new Response(200, [], json_encode([
                'page' => 1,
                'pages' => 2,
                '0' => [
                    'name' => 'example1.com',
                    'type' => 'master',
                    'status' => 'active',
                    'records' => 10
                ]
            ])),
            // Second page
            new Response(200, [], json_encode([
                'page' => 2,
                'pages' => 2,
                '0' => [
                    'name' => 'example2.com',
                    'type' => 'master',
                    'status' => 'active',
                    'records' => 5
                ]
            ]))
        ]);

        $result = $service->getAll();

        $this->assertCount(2, $result);
        $this->assertEquals('example1.com', $result[0]->name);
        $this->assertEquals('example2.com', $result[1]->name);
    }
}