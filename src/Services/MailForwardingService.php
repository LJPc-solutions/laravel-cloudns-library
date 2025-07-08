<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * MailForwardingService
 * 
 * Manages email forwarding operations for domains.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class MailForwardingService
{
    /**
     * Create a new mail forwarding service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * List mail forwards for a domain
     * 
     * @param string $domainName
     * @return array
     */
    public function list(string $domainName): array
    {
        return $this->client->get('dns/mail-forwards', [
            'domain-name' => $domainName,
        ]);
    }

    /**
     * Add a mail forward
     * 
     * @param string $domainName
     * @param string $box
     * @param string $host
     * @param string $destination
     * @return array
     */
    public function add(string $domainName, string $box, string $host, string $destination): array
    {
        return $this->client->post('dns/add-mail-forward', [
            'domain-name' => $domainName,
            'box' => $box,
            'host' => $host,
            'destination' => $destination,
        ]);
    }

    /**
     * Delete a mail forward
     * 
     * @param string $domainName
     * @param int $mailForwardId
     * @return array
     */
    public function delete(string $domainName, int $mailForwardId): array
    {
        return $this->client->post('dns/delete-mail-forward', [
            'domain-name' => $domainName,
            'mail-forward-id' => $mailForwardId,
        ]);
    }
}