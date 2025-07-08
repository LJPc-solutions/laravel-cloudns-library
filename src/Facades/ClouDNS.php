<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Facades;

use Illuminate\Support\Facades\Facade;
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
 * ClouDNS Facade
 * 
 * Provides a convenient static interface to all ClouDNS services.
 * This facade allows you to access ClouDNS functionality using
 * static method calls for improved developer experience.
 * 
 * @package LJPc\ClouDNS\Facades
 * @author LJPC
 * @since 1.0.0
 * 
 * @method static AccountService account() Get the account service for managing account operations
 * @method static ZoneService zone() Get the zone service for managing DNS zones
 * @method static RecordService record() Get the record service for managing DNS records
 * @method static DynamicDNSService dynamicDns() Get the dynamic DNS service
 * @method static GeoDNSService geoDns() Get the GeoDNS service for location-based DNS
 * @method static FailoverService failover() Get the failover service
 * @method static SOAService soa() Get the SOA service for managing Start of Authority records
 * @method static MailForwardingService mailForwarding() Get the mail forwarding service
 * @method static SlaveZoneService slaveZone() Get the slave zone service
 * @method static AXFRService axfr() Get the AXFR service for zone transfers
 * @method static MonitoringService monitoring() Get the monitoring service
 * @method static UtilityService utility() Get the utility service for helper functions
 * 
 * @see \LJPc\ClouDNS\ClouDNS
 * @see \LJPc\ClouDNS\ClouDNSServiceProvider
 */
class ClouDNS extends Facade
{
    /**
     * Get the registered name of the component
     * 
     * @return string The container binding name
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cloudns';
    }
}