# Laravel ClouDNS Library

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://www.php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Code Coverage](https://img.shields.io/badge/coverage-97%25-brightgreen.svg)](https://github.com/ljpc/laravel-cloudns)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

<a href="https://www.buymeacoffee.com/Lars-" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-orange.png" alt="Buy Me A Coffee" height="60" style="height: 60px !important;width: 217px !important;" ></a>

A comprehensive Laravel package for interacting with the ClouDNS API. This library provides a fluent, Laravel-friendly interface to manage DNS zones, records, monitoring, failover, GeoDNS, and more.

## Features

✨ **Complete API Coverage**
- 🌐 DNS Zone Management
- 📝 DNS Record Management
- 🔄 Dynamic DNS Support
- 🌍 GeoDNS Functionality
- 🔁 Failover Configuration
- 📊 Monitoring Services
- 📧 Mail Forwarding
- 🔗 AXFR/Slave Zones
- 🔧 SOA Settings
- 📈 Statistics & Reports

🚀 **Laravel Integration**
- Service Provider with auto-discovery
- Facade for easy access
- Configuration file
- Dependency injection support
- Laravel 11.x compatible

🛡️ **Enterprise Ready**
- PHP 8.3+ with full type safety
- Comprehensive error handling
- Retry logic with exponential backoff
- Request/response caching
- Detailed logging
- 97% test coverage

## Requirements

- PHP 8.3 or higher
- Laravel 11.x
- Guzzle HTTP Client 7.8+

## Installation

Install the package via Composer:

```bash
composer require ljpc/laravel-cloudns
```

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="LJPc\ClouDNS\ClouDNSServiceProvider" --tag="config"
```

Configure your ClouDNS credentials in `.env`:

```env
CLOUDNS_AUTH_ID=your-auth-id
CLOUDNS_AUTH_PASSWORD=your-auth-password
CLOUDNS_IS_SUB_USER=false
CLOUDNS_USE_SUB_USERNAME=false

# Optional configuration
CLOUDNS_CACHE_ENABLED=true
CLOUDNS_CACHE_TTL=3600
CLOUDNS_LOG_ENABLED=true
CLOUDNS_LOG_CHANNEL=cloudns
```

## Usage

### Using the Facade

```php
use LJPc\ClouDNS\Facades\ClouDNS;

// Account operations
$balance = ClouDNS::account()->getBalance();
$info = ClouDNS::account()->getInfo();
$stats = ClouDNS::account()->getStatistics();
```

### Using Dependency Injection

```php
use LJPc\ClouDNS\ClouDNS;

class DnsController extends Controller
{
    public function __construct(
        private readonly ClouDNS $cloudns
    ) {}

    public function index()
    {
        $zones = $this->cloudns->zones()->list();
        return view('dns.index', compact('zones'));
    }
}
```

## Available Services

### 1. Account Service

Manage your ClouDNS account:

```php
// Test authentication
$isValid = ClouDNS::account()->testLogin();

// Get current IP address
$ip = ClouDNS::account()->getCurrentIp();

// Get account balance
$balance = ClouDNS::account()->getBalance();

// Get account information
$info = ClouDNS::account()->getInfo();

// Get usage statistics
$stats = ClouDNS::account()->getStatistics();

// Check if authenticated
$authenticated = ClouDNS::account()->isAuthenticated();
```

### 2. Zone Service

Manage DNS zones:

```php
use LJPc\ClouDNS\Enums\ZoneType;
use LJPc\ClouDNS\Enums\RowsPerPage;

// List zones with pagination
$zones = ClouDNS::zones()->list(
    page: 1,
    rowsPerPage: RowsPerPage::FIFTY,
    search: 'example'
);

// Create a new zone
$zone = ClouDNS::zones()->create(
    domainName: 'example.com',
    zoneType: ZoneType::MASTER,
    masterIp: '192.0.2.1' // for slave zones
);

// Get zone information
$info = ClouDNS::zones()->getInfo('example.com');

// Get zone statistics
$stats = ClouDNS::zones()->getStatistics('example.com');

// Delete a zone
ClouDNS::zones()->delete('example.com');

// Update zone status
ClouDNS::zones()->updateStatus('example.com', true);

// Check if zone exists
$exists = ClouDNS::zones()->exists('example.com');

// Get all zones (auto-pagination)
$allZones = ClouDNS::zones()->getAll(search: 'example');
```

### 3. Record Service

Manage DNS records:

```php
use LJPc\ClouDNS\Enums\RecordType;
use LJPc\ClouDNS\Enums\TTL;

// List records
$records = ClouDNS::records()->list('example.com');

// Create a new record
$record = ClouDNS::records()->create(
    domainName: 'example.com',
    recordType: RecordType::A,
    host: 'www',
    record: '192.0.2.1',
    ttl: TTL::FIFTEEN_MINUTES
);

// Update a record
ClouDNS::records()->update(
    domainName: 'example.com',
    recordId: 12345,
    host: 'www',
    record: '192.0.2.2',
    ttl: TTL::ONE_HOUR
);

// Delete a record
ClouDNS::records()->delete('example.com', 12345);

// Copy records between zones
$copiedCount = ClouDNS::records()->copy(
    fromDomain: 'example.com',
    toDomain: 'example.org',
    deleteExisting: true
);

// Import records from file or string
$imported = ClouDNS::records()->import(
    domainName: 'example.com',
    content: $zoneFileContent,
    deleteExisting: false
);

// Export records
$exported = ClouDNS::records()->export('example.com');

// Delete multiple records
$deleted = ClouDNS::records()->deleteMultiple('example.com', [123, 456, 789]);
```

### 4. Dynamic DNS Service

Manage Dynamic DNS:

```php
// Get dynamic URL
$url = ClouDNS::dynamicDns()->getDynamicUrl('example.com', 12345);

// Disable dynamic URL
ClouDNS::dynamicDns()->disableDynamicUrl('example.com', 12345);

// Change dynamic URL
$newUrl = ClouDNS::dynamicDns()->changeDynamicUrl('example.com', 12345);

// Update IP address
ClouDNS::dynamicDns()->updateIp($dynamicUrl, '192.0.2.1');

// Get update history
$history = ClouDNS::dynamicDns()->getHistory(
    domainName: 'example.com',
    recordId: 12345,
    page: 1,
    rowsPerPage: RowsPerPage::TWENTY
);

// Check if dynamic DNS is enabled
$enabled = ClouDNS::dynamicDns()->isEnabled('example.com', 12345);
```

### 5. GeoDNS Service

Manage GeoDNS locations:

```php
// Get available locations
$locations = ClouDNS::geodns()->getLocations('example.com');

// Check if GeoDNS is available
$available = ClouDNS::geodns()->isAvailable('example.com');
```

### 6. Failover Service

Manage failover settings:

```php
// Activate failover
ClouDNS::failover()->activate('example.com', 12345);

// Deactivate failover
ClouDNS::failover()->deactivate('example.com', 12345);
```

### 7. Mail Forwarding Service

Manage email forwarding:

```php
// List mail forwards
$forwards = ClouDNS::mailForwarding()->list('example.com');

// Add mail forward
ClouDNS::mailForwarding()->add(
    domainName: 'example.com',
    email: 'info@example.com',
    destination: 'forward@example.org'
);

// Delete mail forward
ClouDNS::mailForwarding()->delete('example.com', 12345);
```

### 8. Monitoring Service

Manage monitoring checks:

```php
use LJPc\ClouDNS\Enums\MonitoringType;

// Create monitoring check
$monitor = ClouDNS::monitoring()->create(
    name: 'Web Server Check',
    type: MonitoringType::HTTP,
    host: 'www.example.com',
    port: 80,
    path: '/health',
    interval: 300,
    timeout: 10,
    notificationEmails: ['admin@example.com']
);

// List monitoring checks
$monitors = ClouDNS::monitoring()->list();

// Delete monitoring check
ClouDNS::monitoring()->delete(12345);
```

### 9. AXFR Service

Manage AXFR allowed IPs:

```php
// Add allowed IP
ClouDNS::axfr()->addIp('example.com', '192.0.2.0/24');

// Remove allowed IP
ClouDNS::axfr()->removeIp('example.com', '192.0.2.0/24');

// List allowed IPs
$ips = ClouDNS::axfr()->listIps('example.com');
```

### 10. Slave Zone Service

Manage slave zone master servers:

```php
// Add master server
ClouDNS::slaveZones()->addMasterServer('example.com', '192.0.2.1');

// Delete master server
ClouDNS::slaveZones()->deleteMasterServer('example.com', '192.0.2.1');

// List master servers
$servers = ClouDNS::slaveZones()->listMasterServers('example.com');
```

### 11. SOA Service

Manage Start of Authority (SOA) settings:

```php
// Get SOA details
$soa = ClouDNS::soa()->getDetails('example.com');

// Modify SOA settings
ClouDNS::soa()->modify(
    domainName: 'example.com',
    primaryNs: 'ns1.example.com',
    adminMail: 'admin@example.com',
    refresh: 7200,
    retry: 1800,
    expire: 1209600,
    defaultTtl: 3600
);

// Reset SOA to defaults
ClouDNS::soa()->reset('example.com');
```

### 12. Utility Service

Utility functions:

```php
// Get available TTLs
$ttls = ClouDNS::utility()->getAvailableTTLs();

// Get available record types for a zone
$types = ClouDNS::utility()->getAvailableRecordTypes('example.com');

// Create failover webhook
$webhook = ClouDNS::utility()->createFailoverWebhook(
    monitoringId: 12345,
    url: 'https://example.com/webhook'
);
```

## Working with Enums

The package uses PHP 8.3 enums for type safety:

```php
use LJPc\ClouDNS\Enums\RecordType;
use LJPc\ClouDNS\Enums\TTL;
use LJPc\ClouDNS\Enums\ZoneType;
use LJPc\ClouDNS\Enums\MonitoringType;
use LJPc\ClouDNS\Enums\RowsPerPage;

// Record types
RecordType::A;          // IPv4 address
RecordType::AAAA;       // IPv6 address
RecordType::MX;         // Mail exchange
RecordType::CNAME;      // Canonical name
RecordType::TXT;        // Text record
RecordType::NS;         // Name server
RecordType::SRV;        // Service record
RecordType::CAA;        // Certificate authority
// ... and more

// TTL values
TTL::ONE_MINUTE;        // 60 seconds
TTL::FIVE_MINUTES;      // 300 seconds
TTL::FIFTEEN_MINUTES;   // 900 seconds
TTL::THIRTY_MINUTES;    // 1800 seconds
TTL::ONE_HOUR;          // 3600 seconds
TTL::THREE_HOURS;       // 10800 seconds
TTL::SIX_HOURS;         // 21600 seconds
TTL::TWELVE_HOURS;      // 43200 seconds
TTL::ONE_DAY;           // 86400 seconds
TTL::TWO_DAYS;          // 172800 seconds
TTL::THREE_DAYS;        // 259200 seconds
TTL::ONE_WEEK;          // 604800 seconds
TTL::TWO_WEEKS;         // 1209600 seconds
TTL::ONE_MONTH;         // 2592000 seconds

// Zone types
ZoneType::MASTER;       // Master zone
ZoneType::SLAVE;        // Slave zone
ZoneType::PARKED;       // Parked zone
ZoneType::GEODNS;       // GeoDNS zone

// Monitoring types
MonitoringType::DNS;    // DNS monitoring
MonitoringType::TCP;    // TCP port monitoring
MonitoringType::UDP;    // UDP port monitoring
MonitoringType::ICMP;   // Ping monitoring
MonitoringType::SMTP;   // SMTP monitoring
MonitoringType::HTTP;   // HTTP monitoring
MonitoringType::HTTPS;  // HTTPS monitoring
MonitoringType::SSL;    // SSL certificate monitoring

// Pagination options
RowsPerPage::TEN;       // 10 rows
RowsPerPage::TWENTY;    // 20 rows
RowsPerPage::THIRTY;    // 30 rows (default)
RowsPerPage::FIFTY;     // 50 rows
RowsPerPage::HUNDRED;   // 100 rows
```

## Error Handling

The package provides specific exceptions for different error scenarios:

```php
use LJPc\ClouDNS\Exceptions\AuthenticationException;
use LJPc\ClouDNS\Exceptions\ValidationException;
use LJPc\ClouDNS\Exceptions\RateLimitException;
use LJPc\ClouDNS\Exceptions\ResourceNotFoundException;
use LJPc\ClouDNS\Exceptions\ClouDNSException;

try {
    $zone = ClouDNS::zones()->create('example.com', ZoneType::MASTER);
} catch (AuthenticationException $e) {
    // Invalid credentials
} catch (ValidationException $e) {
    // Invalid input data
} catch (RateLimitException $e) {
    // API rate limit exceeded
} catch (ResourceNotFoundException $e) {
    // Resource not found
} catch (ClouDNSException $e) {
    // General API error
}
```

## Advanced Features

### Caching

The package includes built-in caching for API responses:

```php
// Cache is enabled by default for GET requests
// Configure in .env:
CLOUDNS_CACHE_ENABLED=true
CLOUDNS_CACHE_TTL=3600
```

### Logging

All API requests and responses can be logged:

```php
// Enable logging in .env:
CLOUDNS_LOG_ENABLED=true
CLOUDNS_LOG_CHANNEL=cloudns
```

### Retry Logic

The package automatically retries failed requests with exponential backoff:

```php
// Configure in config/cloudns.php:
'retry' => [
    'max_attempts' => 3,
    'delay' => 1000, // milliseconds
    'multiplier' => 2,
    'max_delay' => 10000,
],
```

### Sub-User Authentication

Support for ClouDNS sub-user authentication:

```php
// Configure in .env:
CLOUDNS_USE_SUB_AUTH=true
CLOUDNS_SUB_AUTH_ID=123
CLOUDNS_SUB_AUTH_USER=subuser
```

## Testing

The package includes comprehensive PHPUnit tests with 97% code coverage:

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Run specific test suite
composer test -- --testsuite=Unit
composer test -- --testsuite=Integration
```

### Testing Your Integration

```php
use LJPc\ClouDNS\Testing\ClouDNSFake;
use LJPc\ClouDNS\Facades\ClouDNS;

// In your test
ClouDNS::fake();

// Configure expected responses
ClouDNS::shouldReceive('zones->list')
    ->once()
    ->andReturn(['example.com', 'example.org']);

// Your code that uses ClouDNS
$zones = ClouDNS::zones()->list();

// Assertions
$this->assertCount(2, $zones);
```

## API Rate Limits

ClouDNS API has the following rate limits:
- 20 requests per second
- 600 requests per minute

The package handles rate limiting automatically with retry logic.

## Support

For support, please contact support@ljpc.nl or visit the [ClouDNS API documentation](https://www.cloudns.net/wiki/article/41/).
