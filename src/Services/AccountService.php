<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * Account Service
 * 
 * Manages ClouDNS account operations including authentication testing,
 * retrieving account information, balance, and statistics.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class AccountService
{
    /**
     * Create a new account service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Test login credentials
     * 
     * @return array{status: string, statusDescription: string}
     */
    public function testLogin(): array
    {
        return $this->client->post('login/login');
    }

    /**
     * Get current IP address
     * 
     * @return string
     */
    public function getCurrentIp(): string
    {
        $response = $this->client->get('account/get-current-ip');
        return $response['ip'] ?? '';
    }

    /**
     * Get account balance
     * 
     * @return array{balance: string, currency: string}
     */
    public function getBalance(): array
    {
        return $this->client->get('account/get-balance');
    }

    /**
     * Get account information
     * 
     * @return array
     */
    public function getInfo(): array
    {
        return $this->client->get('account/get-info');
    }

    /**
     * Get account statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return $this->client->get('account/get-statistics');
    }

    /**
     * Check if authenticated
     * 
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        try {
            $response = $this->testLogin();
            return isset($response['status']) && $response['status'] === 'Success';
        } catch (\Exception $e) {
            return false;
        }
    }
}