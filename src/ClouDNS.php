<?php

declare(strict_types=1);

namespace LJPc\ClouDNS;

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
 * Main ClouDNS API client class
 * 
 * This class provides a fluent interface to interact with all ClouDNS API services.
 * It acts as a factory for service instances, ensuring each service has access to
 * the configured HTTP client with proper authentication.
 * 
 * @package LJPc\ClouDNS
 * @author LJPC
 * @since 1.0.0
 */
class ClouDNS
{
    /**
     * Create a new ClouDNS instance
     * 
     * @param ClouDNSClient $client The configured HTTP client for API communication
     */
    public function __construct(
        protected readonly ClouDNSClient $client
    ) {}

    /**
     * Get the account service instance
     * 
     * Provides access to account-related operations such as:
     * - Testing authentication credentials
     * - Retrieving current IP address
     * - Getting account balance and information
     * - Fetching account statistics
     * 
     * @return AccountService
     */
    public function account(): AccountService
    {
        return new AccountService($this->client);
    }

    /**
     * Get the zone service instance
     * 
     * Provides access to DNS zone management operations such as:
     * - Creating, deleting, and listing zones
     * - Retrieving zone information and statistics
     * - Updating zone status
     * - Checking zone existence
     * 
     * @return ZoneService
     */
    public function zone(): ZoneService
    {
        return new ZoneService($this->client);
    }

    /**
     * Get the record service instance
     * 
     * Provides access to DNS record management operations such as:
     * - Creating, updating, and deleting records
     * - Listing and retrieving records
     * - Copying records between zones
     * - Importing and exporting records in BIND format
     * 
     * @return RecordService
     */
    public function record(): RecordService
    {
        return new RecordService($this->client);
    }

    /**
     * Get the dynamic DNS service instance
     * 
     * Provides access to dynamic DNS operations such as:
     * - Managing dynamic DNS URLs
     * - Updating IP addresses dynamically
     * - Retrieving update history
     * - Checking dynamic DNS status
     * 
     * @return DynamicDNSService
     */
    public function dynamicDNS(): DynamicDNSService
    {
        return new DynamicDNSService($this->client);
    }

    /**
     * Get the GeoDNS service instance
     * 
     * Provides access to GeoDNS operations such as:
     * - Retrieving available GeoDNS locations
     * - Checking GeoDNS availability for zones
     * 
     * @return GeoDNSService
     */
    public function geoDNS(): GeoDNSService
    {
        return new GeoDNSService($this->client);
    }

    /**
     * Get the SOA service instance
     * 
     * Provides access to Start of Authority (SOA) operations such as:
     * - Retrieving SOA details
     * - Modifying SOA settings
     * - Resetting SOA to default values
     * 
     * @return SOAService
     */
    public function soa(): SOAService
    {
        return new SOAService($this->client);
    }

    /**
     * Get the mail forwarding service instance
     * 
     * Provides access to email forwarding operations such as:
     * - Listing mail forwards
     * - Adding new mail forwards
     * - Deleting existing mail forwards
     * 
     * @return MailForwardingService
     */
    public function mailForwarding(): MailForwardingService
    {
        return new MailForwardingService($this->client);
    }

    /**
     * Get the slave zone service instance
     * 
     * Provides access to slave zone operations such as:
     * - Managing master servers
     * - Adding and removing master servers
     * - Listing configured master servers
     * 
     * @return SlaveZoneService
     */
    public function slaveZone(): SlaveZoneService
    {
        return new SlaveZoneService($this->client);
    }

    /**
     * Get the AXFR service instance
     * 
     * Provides access to AXFR (zone transfer) operations such as:
     * - Managing allowed IP addresses for zone transfers
     * - Adding and removing IP addresses
     * - Listing allowed IPs
     * 
     * @return AXFRService
     */
    public function axfr(): AXFRService
    {
        return new AXFRService($this->client);
    }

    /**
     * Get the failover service instance
     * 
     * Provides access to failover operations such as:
     * - Activating failover for records
     * - Deactivating failover for records
     * 
     * @return FailoverService
     */
    public function failover(): FailoverService
    {
        return new FailoverService($this->client);
    }

    /**
     * Get the monitoring service instance
     * 
     * Provides access to monitoring operations such as:
     * - Creating monitoring checks
     * - Listing active monitoring checks
     * - Deleting monitoring checks
     * 
     * @return MonitoringService
     */
    public function monitoring(): MonitoringService
    {
        return new MonitoringService($this->client);
    }

    /**
     * Get the utility service instance
     * 
     * Provides access to utility operations such as:
     * - Getting available TTL values
     * - Getting available record types for zones
     * - Creating failover webhooks
     * 
     * @return UtilityService
     */
    public function utility(): UtilityService
    {
        return new UtilityService($this->client);
    }

    /**
     * Get the underlying HTTP client instance
     * 
     * This method provides direct access to the configured ClouDNSClient
     * for advanced use cases that may require custom API calls.
     * 
     * @return ClouDNSClient The configured HTTP client
     */
    public function getClient(): ClouDNSClient
    {
        return $this->client;
    }
}