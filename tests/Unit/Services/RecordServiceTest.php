<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\DTOs\Requests\CreateRecordRequest;
use LJPc\ClouDNS\Enums\RecordType;
use LJPc\ClouDNS\Enums\RowsPerPage;
use LJPc\ClouDNS\Services\RecordService;
use PHPUnit\Framework\TestCase;

class RecordServiceTest extends TestCase
{
    private function createService(array $responses): RecordService
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
        
        return new RecordService($client);
    }

    public function test_list_records(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'page' => 1,
                'pages' => 1,
                '12345' => [
                    'id' => '12345',
                    'type' => 'A',
                    'host' => 'www',
                    'record' => '192.168.1.1',
                    'ttl' => '3600'
                ],
                '12346' => [
                    'id' => '12346',
                    'type' => 'MX',
                    'host' => '@',
                    'record' => 'mail.example.com',
                    'ttl' => '3600',
                    'priority' => '10'
                ]
            ]))
        ]);

        $result = $service->list(
            domainName: 'example.com',
            host: 'www',
            type: RecordType::A,
            page: 1,
            rowsPerPage: RowsPerPage::FIFTY
        );

        $this->assertEquals(1, $result['page']);
        $this->assertEquals(1, $result['pages']);
        $this->assertCount(2, $result['records']);
        $this->assertEquals(12345, $result['records'][0]->id);
        $this->assertEquals(RecordType::A, $result['records'][0]->type);
    }

    public function test_list_records_with_string_type(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'page' => 1,
                'pages' => 1
            ]))
        ]);

        $result = $service->list(
            domainName: 'example.com',
            type: 'CNAME'
        );

        $this->assertEquals(1, $result['page']);
        $this->assertEmpty($result['records']);
    }

    public function test_get_record(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'id' => '12345',
                'type' => 'A',
                'host' => 'www',
                'record' => '192.168.1.1',
                'ttl' => '3600'
            ]))
        ]);

        $result = $service->get('example.com', 12345);

        $this->assertEquals(12345, $result->id);
        $this->assertEquals(RecordType::A, $result->type);
        $this->assertEquals('www', $result->host);
    }

    public function test_create_record_with_dto(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Record successfully added with id [24135067]'
            ]))
        ]);

        $request = new CreateRecordRequest(
            domainName: 'example.com',
            recordType: RecordType::A,
            host: 'test',
            record: '192.168.1.100',
            ttl: 3600
        );

        $result = $service->create($request);

        $this->assertEquals(24135067, $result);
    }

    public function test_create_record_with_array(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Record successfully added with id [24135068]'
            ]))
        ]);

        $result = $service->create([
            'domain_name' => 'example.com',
            'record_type' => 'MX',
            'host' => '@',
            'record' => 'mail.example.com',
            'ttl' => 3600,
            'priority' => 10
        ]);

        $this->assertEquals(24135068, $result);
    }

    public function test_create_record_throws_exception_on_invalid_response(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Record added but no ID returned'
            ]))
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to extract record ID from response');

        $service->create([
            'domain_name' => 'example.com',
            'record_type' => 'A',
            'host' => 'test',
            'record' => '192.168.1.1',
            'ttl' => 3600
        ]);
    }

    public function test_update_record(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Record modified successfully.'
            ]))
        ]);

        $result = $service->update('example.com', 12345, [
            'host' => 'updated',
            'record' => '192.168.1.200',
            'ttl' => 7200,
            'priority' => 20,
            'weight' => 10,
            'port' => 80,
            'frame' => '1',
            'frame_title' => 'Title',
            'frame_keywords' => 'keywords',
            'frame_description' => 'description',
            'save_path' => true,
            'redirect_type' => 301,
            'geodns_location' => 'NA'
        ]);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_delete_record(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Record deleted successfully.'
            ]))
        ]);

        $result = $service->delete('example.com', 12345);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_copy_records(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Records copied successfully.'
            ]))
        ]);

        $result = $service->copy('source.com', 'target.com', true);

        $this->assertEquals('Success', $result['status']);
    }

    public function test_import_records(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Records imported successfully.'
            ]))
        ]);

        $bindContent = <<<BIND
        www A 192.168.1.1
        mail MX 10 mail.example.com
        BIND;

        $result = $service->import(
            domainName: 'example.com',
            content: $bindContent,
            format: 'bind',
            deleteExistingRecords: true,
            recordTypes: ['A', 'MX']
        );

        $this->assertEquals('Success', $result['status']);
    }

    public function test_export_records(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'zone' => "; Zone file for example.com\nwww A 192.168.1.1"
            ]))
        ]);

        $result = $service->export('example.com');

        $this->assertStringContainsString('Zone file for example.com', $result);
    }

    public function test_export_records_empty(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([]))
        ]);

        $result = $service->export('example.com');

        $this->assertEquals('', $result);
    }

    public function test_get_all_records(): void
    {
        $service = $this->createService([
            // First page
            new Response(200, [], json_encode([
                'page' => 1,
                'pages' => 2,
                '1' => [
                    'id' => '1',
                    'type' => 'A',
                    'host' => 'www',
                    'record' => '192.168.1.1',
                    'ttl' => '3600'
                ]
            ])),
            // Second page
            new Response(200, [], json_encode([
                'page' => 2,
                'pages' => 2,
                '2' => [
                    'id' => '2',
                    'type' => 'A',
                    'host' => 'test',
                    'record' => '192.168.1.2',
                    'ttl' => '3600'
                ]
            ]))
        ]);

        $result = $service->getAll('example.com', 'www', RecordType::A);

        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals(2, $result[1]->id);
    }

    public function test_delete_multiple_records(): void
    {
        $service = $this->createService([
            new Response(200, [], json_encode([
                'status' => 'Success',
                'statusDescription' => 'Record deleted successfully.'
            ])),
            new Response(200, [], json_encode([
                'status' => 'Failed',
                'statusDescription' => 'Record not found.'
            ]))
        ]);

        $result = $service->deleteMultiple('example.com', [123, 456]);

        $this->assertEquals('Success', $result[123]['status']);
        $this->assertEquals('Failed', $result[456]['status']);
    }

    public function test_delete_multiple_records_with_exception(): void
    {
        $service = $this->createService([
            new Response(500, [], 'Server Error')
        ]);

        $result = $service->deleteMultiple('example.com', [123]);

        $this->assertEquals('Failed', $result[123]['status']);
        $this->assertStringContainsString('API request failed', $result[123]['statusDescription']);
    }
}