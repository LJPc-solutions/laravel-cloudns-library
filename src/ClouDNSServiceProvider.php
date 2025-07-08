<?php

declare(strict_types=1);

namespace LJPc\ClouDNS;

use Illuminate\Support\ServiceProvider;
use LJPc\ClouDNS\Client\ClouDNSClient;
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

/**
 * ClouDNS Laravel Service Provider
 * 
 * This service provider registers all ClouDNS services with the Laravel container
 * and publishes the configuration file. It provides:
 * - Singleton instances of all services
 * - Configuration file publishing
 * - Service auto-discovery support
 * 
 * @package LJPc\ClouDNS
 * @author LJPC
 * @since 1.0.0
 */
class ClouDNSServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     * 
     * Registers all ClouDNS services as singletons in the container,
     * merges configuration, and sets up the main ClouDNS facade.
     * 
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cloudns.php',
            'cloudns'
        );

        $this->app->singleton('cloudns.client', function ($app) {
            $config = $app['config']['cloudns'];
            
            return new ClouDNSClient(
                authId: $config['auth_id'],
                authPassword: $config['auth_password'],
                isSubUser: $config['is_sub_user'] ?? false,
                useSubUsername: $config['use_sub_username'] ?? false,
                baseUrl: $config['base_url'] ?? 'https://api.cloudns.net',
                responseFormat: $config['response_format'] ?? 'json',
                timeout: (int) ($config['timeout'] ?? 30),
                retryTimes: (int) ($config['retry_times'] ?? 3),
                retryDelay: (int) ($config['retry_delay'] ?? 1000),
                cacheEnabled: (bool) ($config['cache_enabled'] ?? true),
                cacheTtl: (int) ($config['cache_ttl'] ?? 300),
                cachePrefix: $config['cache_prefix'] ?? 'cloudns_',
                logEnabled: (bool) ($config['log_enabled'] ?? true),
                logChannel: $config['log_channel'] ?? 'stack',
                logLevel: $config['log_level'] ?? 'info'
            );
        });

        $this->app->alias('cloudns.client', ClouDNSClient::class);

        $this->registerServices();

        $this->app->singleton('cloudns', function ($app) {
            return new ClouDNS($app->make('cloudns.client'));
        });
    }

    /**
     * Bootstrap the application services
     * 
     * Publishes the configuration file for customization.
     * 
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/cloudns.php' => config_path('cloudns.php'),
        ], 'config');
    }

    /**
     * Register all ClouDNS services
     * 
     * Registers each service as a singleton in the container,
     * allowing them to be resolved individually if needed.
     * 
     * @return void
     */
    protected function registerServices(): void
    {
        $services = [
            'account' => AccountService::class,
            'zone' => ZoneService::class,
            'record' => RecordService::class,
            'dynamicdns' => DynamicDNSService::class,
            'geodns' => GeoDNSService::class,
            'failover' => FailoverService::class,
            'soa' => SOAService::class,
            'mailforwarding' => MailForwardingService::class,
            'slavezone' => SlaveZoneService::class,
            'axfr' => AXFRService::class,
            'monitoring' => MonitoringService::class,
            'utility' => UtilityService::class,
        ];

        foreach ($services as $name => $class) {
            $this->app->singleton("cloudns.{$name}", function ($app) use ($class) {
                return new $class($app->make('cloudns.client'));
            });
        }
    }

    /**
     * Get the services provided by the provider
     * 
     * This method is used by Laravel's deferred service providers
     * to determine which services this provider offers.
     * 
     * @return array<int, string> List of service names
     */
    public function provides(): array
    {
        return [
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
    }
}