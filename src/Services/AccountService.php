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
     * Note: This endpoint may not be available for all account types.
     * 
     * @return string
     * @throws \LJPc\ClouDNS\Exceptions\ClouDNSException If the endpoint is not available
     */
    public function getCurrentIp(): string
    {
        try {
            $response = $this->client->get('account/get-current-ip');
            return $response['ip'] ?? '';
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Invalid request')) {
                throw new \LJPc\ClouDNS\Exceptions\ClouDNSException(
                    'The get-current-ip endpoint is not available for this account type.',
                    0,
                    $e
                );
            }
            throw $e;
        }
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
     * Note: This endpoint may not be available for all account types.
     * If it returns "Invalid request", your account type may not support this feature.
     * 
     * @return array
     * @throws \LJPc\ClouDNS\Exceptions\ClouDNSException If the endpoint is not available
     */
    public function getInfo(): array
    {
        try {
            return $this->client->get('account/get-info');
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Invalid request')) {
                throw new \LJPc\ClouDNS\Exceptions\ClouDNSException(
                    'The get-info endpoint is not available for this account type. This may be due to account limitations or permissions.',
                    0,
                    $e
                );
            }
            throw $e;
        }
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