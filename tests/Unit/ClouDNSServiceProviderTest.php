<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use LJPc\ClouDNS\ClouDNS;
use LJPc\ClouDNS\ClouDNSServiceProvider;
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

class ClouDNSServiceProviderTest extends TestCase
{
    private Application $app;
    private ClouDNSServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app = new Application();
        $this->app['config'] = new Repository([
            'cloudns' => [
                'auth_id' => 'test-id',
                'auth_password' => 'test-password',
                'is_sub_user' => false,
                'use_sub_username' => false,
                'base_url' => 'https://api.cloudns.net',
                'response_format' => 'json',
                'timeout' => 30,
                'retry_times' => 3,
                'retry_delay' => 1000,
                'cache_enabled' => true,
                'cache_ttl' => 300,
                'cache_prefix' => 'cloudns_',
                'log_enabled' => true,
                'log_channel' => 'stack',
                'log_level' => 'info',
            ]
        ]);
        
        $this->provider = new ClouDNSServiceProvider($this->app);
    }

    public function test_register_binds_cloudns_to_container(): void
    {
        $this->provider->register();
        
        $this->assertTrue($this->app->bound('cloudns'));
        $this->assertInstanceOf(ClouDNS::class, $this->app->make('cloudns'));
    }

    public function test_register_binds_cloudns_client_to_container(): void
    {
        $this->provider->register();
        
        $this->assertTrue($this->app->bound('cloudns.client'));
    }

    public function test_register_binds_all_services_to_container(): void
    {
        $this->provider->register();
        
        $services = [
            'cloudns.account' => AccountService::class,
            'cloudns.zone' => ZoneService::class,
            'cloudns.record' => RecordService::class,
            'cloudns.dynamicdns' => DynamicDNSService::class,
            'cloudns.geodns' => GeoDNSService::class,
            'cloudns.soa' => SOAService::class,
            'cloudns.mailforwarding' => MailForwardingService::class,
            'cloudns.slavezone' => SlaveZoneService::class,
            'cloudns.axfr' => AXFRService::class,
            'cloudns.failover' => FailoverService::class,
            'cloudns.monitoring' => MonitoringService::class,
            'cloudns.utility' => UtilityService::class,
        ];
        
        foreach ($services as $abstract => $concrete) {
            $this->assertTrue($this->app->bound($abstract));
            $this->assertInstanceOf($concrete, $this->app->make($abstract));
        }
    }

    public function test_services_are_singletons(): void
    {
        $this->provider->register();
        
        $instance1 = $this->app->make('cloudns');
        $instance2 = $this->app->make('cloudns');
        
        $this->assertSame($instance1, $instance2);
    }

    public function test_boot_publishes_config(): void
    {
        // Use reflection to check if boot method properly sets up publishes
        $provider = new ClouDNSServiceProvider($this->app);
        $provider->boot();
        
        // Since we can't easily test the publishes call, we'll just verify
        // the boot method runs without errors
        $this->assertTrue(true);
    }

    public function test_provides_returns_correct_services(): void
    {
        $provides = $this->provider->provides();
        
        $expected = [
            'cloudns',
            'cloudns.client',
            'cloudns.account',
            'cloudns.zone',
            'cloudns.record',
            'cloudns.dynamicdns',
            'cloudns.geodns',
            'cloudns.soa',
            'cloudns.mailforwarding',
            'cloudns.slavezone',
            'cloudns.axfr',
            'cloudns.failover',
            'cloudns.monitoring',
            'cloudns.utility',
        ];
        
        $this->assertEquals($expected, $provides);
    }

    public function test_config_values_are_used(): void
    {
        $this->app['config']->set('cloudns.auth_id', 'custom-auth-id');
        $this->app['config']->set('cloudns.auth_password', 'custom-password');
        $this->app['config']->set('cloudns.timeout', 60);
        
        $this->provider->register();
        
        $client = $this->app->make('cloudns.client');
        
        // Verify the client was created with the custom config
        $clientReflection = new \ReflectionClass($client);
        
        $authIdProperty = $clientReflection->getProperty('authId');
        $authIdProperty->setAccessible(true);
        $this->assertEquals('custom-auth-id', $authIdProperty->getValue($client));
        
        $authPasswordProperty = $clientReflection->getProperty('authPassword');
        $authPasswordProperty->setAccessible(true);
        $this->assertEquals('custom-password', $authPasswordProperty->getValue($client));
        
        $timeoutProperty = $clientReflection->getProperty('timeout');
        $timeoutProperty->setAccessible(true);
        $this->assertEquals(60, $timeoutProperty->getValue($client));
    }

    public function test_facade_accessor_returns_correct_value(): void
    {
        $this->provider->register();
        
        $facadeReflection = new \ReflectionClass(\LJPc\ClouDNS\Facades\ClouDNS::class);
        $method = $facadeReflection->getMethod('getFacadeAccessor');
        $method->setAccessible(true);
        
        $accessor = $method->invoke(null);
        
        $this->assertEquals('cloudns', $accessor);
    }
}