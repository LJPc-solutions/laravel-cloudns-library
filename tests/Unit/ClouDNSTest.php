<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit;

use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\ClouDNS;
use LJPc\ClouDNS\Services\AccountService;
use LJPc\ClouDNS\Services\AXFRService;
use LJPc\ClouDNS\Services\DynamicDNSService;
use LJPc\ClouDNS\Services\FailoverService;
use LJPc\ClouDNS\Services\GeoDNSService;
use LJPc\ClouDNS\Services\MailForwardingService;
use LJPc\ClouDNS\Services\MonitoringService;
use LJPc\ClouDNS\Services\RecordService;
use LJPc\ClouDNS\Services\SlaveZoneService;
use LJPc\ClouDNS\Services\SOAService;
use LJPc\ClouDNS\Services\UtilityService;
use LJPc\ClouDNS\Services\ZoneService;
use PHPUnit\Framework\TestCase;

class ClouDNSTest extends TestCase
{
    private ClouDNS $cloudns;
    private ClouDNSClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = new ClouDNSClient(
            authId: 'test-id',
            authPassword: 'test-pass',
            cacheEnabled: false,
            logEnabled: false
        );
        
        $this->cloudns = new ClouDNS($this->client);
    }

    public function test_account_returns_account_service(): void
    {
        $service = $this->cloudns->account();
        
        $this->assertInstanceOf(AccountService::class, $service);
    }

    public function test_zone_returns_zone_service(): void
    {
        $service = $this->cloudns->zone();
        
        $this->assertInstanceOf(ZoneService::class, $service);
    }

    public function test_record_returns_record_service(): void
    {
        $service = $this->cloudns->record();
        
        $this->assertInstanceOf(RecordService::class, $service);
    }

    public function test_dynamicDNS_returns_dynamic_dns_service(): void
    {
        $service = $this->cloudns->dynamicDNS();
        
        $this->assertInstanceOf(DynamicDNSService::class, $service);
    }

    public function test_geoDNS_returns_geo_dns_service(): void
    {
        $service = $this->cloudns->geoDNS();
        
        $this->assertInstanceOf(GeoDNSService::class, $service);
    }

    public function test_soa_returns_soa_service(): void
    {
        $service = $this->cloudns->soa();
        
        $this->assertInstanceOf(SOAService::class, $service);
    }

    public function test_mailForwarding_returns_mail_forwarding_service(): void
    {
        $service = $this->cloudns->mailForwarding();
        
        $this->assertInstanceOf(MailForwardingService::class, $service);
    }

    public function test_slaveZone_returns_slave_zone_service(): void
    {
        $service = $this->cloudns->slaveZone();
        
        $this->assertInstanceOf(SlaveZoneService::class, $service);
    }

    public function test_axfr_returns_axfr_service(): void
    {
        $service = $this->cloudns->axfr();
        
        $this->assertInstanceOf(AXFRService::class, $service);
    }

    public function test_failover_returns_failover_service(): void
    {
        $service = $this->cloudns->failover();
        
        $this->assertInstanceOf(FailoverService::class, $service);
    }

    public function test_monitoring_returns_monitoring_service(): void
    {
        $service = $this->cloudns->monitoring();
        
        $this->assertInstanceOf(MonitoringService::class, $service);
    }

    public function test_utility_returns_utility_service(): void
    {
        $service = $this->cloudns->utility();
        
        $this->assertInstanceOf(UtilityService::class, $service);
    }

    public function test_get_client_returns_cloudns_client(): void
    {
        $client = $this->cloudns->getClient();
        
        $this->assertInstanceOf(ClouDNSClient::class, $client);
        $this->assertSame($this->client, $client);
    }
}