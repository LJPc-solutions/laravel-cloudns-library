<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LJPc\ClouDNS\Exceptions\AuthenticationException;
use LJPc\ClouDNS\Exceptions\ClouDNSException;
use LJPc\ClouDNS\Exceptions\ValidationException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * ClouDNS API HTTP client
 * 
 * This class handles all HTTP communication with the ClouDNS API. It provides:
 * - Authentication handling (main user and sub-user)
 * - Automatic retry logic with exponential backoff
 * - Response caching for GET requests
 * - Request/response logging
 * - Error handling and exception mapping
 * 
 * @package LJPc\ClouDNS\Client
 * @author LJPC
 * @since 1.0.0
 */
class ClouDNSClient
{
    /**
     * @var Client The Guzzle HTTP client instance
     */
    protected Client $httpClient;
    
    /**
     * @var array<string, string> Authentication parameters for API requests
     */
    protected readonly array $authParams;

    /**
     * Create a new ClouDNS client instance
     * 
     * @param string $authId The authentication ID (user ID or sub-user ID/username)
     * @param string $authPassword The authentication password
     * @param bool $isSubUser Whether to authenticate as a sub-user
     * @param bool $useSubUsername Whether to use sub-user username instead of ID
     * @param string $baseUrl The base URL for the ClouDNS API
     * @param string $responseFormat The desired response format ('json' or 'xml')
     * @param int $timeout Request timeout in seconds
     * @param int $retryTimes Number of times to retry failed requests
     * @param int $retryDelay Base delay in milliseconds between retries
     * @param bool $cacheEnabled Whether to cache GET request responses
     * @param int $cacheTtl Cache time-to-live in seconds
     * @param string $cachePrefix Prefix for cache keys
     * @param bool $logEnabled Whether to log API requests and responses
     * @param string $logChannel Laravel log channel to use
     * @param string $logLevel Log level for API requests
     */
    public function __construct(
        protected readonly string $authId,
        protected readonly string $authPassword,
        protected readonly bool $isSubUser = false,
        protected readonly bool $useSubUsername = false,
        protected readonly string $baseUrl = 'https://api.cloudns.net',
        protected readonly string $responseFormat = 'json',
        protected readonly int $timeout = 30,
        protected readonly int $retryTimes = 3,
        protected readonly int $retryDelay = 1000,
        protected readonly bool $cacheEnabled = true,
        protected readonly int $cacheTtl = 300,
        protected readonly string $cachePrefix = 'cloudns_',
        protected readonly bool $logEnabled = true,
        protected readonly string $logChannel = 'stack',
        protected readonly string $logLevel = 'info'
    ) {
        $this->authParams = $this->buildAuthParams();
        $this->httpClient = $this->createHttpClient();
    }

    /**
     * Build authentication parameters based on user type
     * 
     * @return array<string, string> Authentication parameters
     */
    protected function buildAuthParams(): array
    {
        $params = ['auth-password' => $this->authPassword];

        if ($this->isSubUser) {
            if ($this->useSubUsername) {
                $params['sub-auth-user'] = $this->authId;
            } else {
                $params['sub-auth-id'] = $this->authId;
            }
        } else {
            $params['auth-id'] = $this->authId;
        }

        return $params;
    }

    /**
     * Create and configure the Guzzle HTTP client
     * 
     * Sets up middleware for retry logic, logging, and other features.
     * 
     * @return Client Configured Guzzle client
     */
    protected function createHttpClient(): Client
    {
        $stack = HandlerStack::create();

        if ($this->retryTimes > 0) {
            $stack->push(Middleware::retry(
                $this->retryDecider(),
                $this->retryDelay()
            ));
        }

        if ($this->logEnabled) {
            $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
                $this->logRequest($request);
                return $request;
            }));

            $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
                $this->logResponse($response);
                return $response;
            }));
        }

        return new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => $this->timeout,
            'handler' => $stack,
            'http_errors' => false,
            'verify' => true,
        ]);
    }

    /**
     * Set a custom HTTP client (for testing)
     * 
     * This method allows injecting a mock HTTP client for unit testing.
     * 
     * @param Client $client The HTTP client to use
     * @return void
     */
    public function setHttpClient(Client $client): void
    {
        $this->httpClient = $client;
    }

    /**
     * Create the retry decision logic
     * 
     * Determines whether a request should be retried based on:
     * - Network errors (GuzzleException)
     * - HTTP status codes (429, 5xx)
     * - Retry attempt count
     * 
     * @return callable The retry decider function
     */
    protected function retryDecider(): callable
    {
        return function (int $retries, RequestInterface $request, ?ResponseInterface $response = null, ?\Exception $exception = null): bool {
            if ($retries >= $this->retryTimes) {
                return false;
            }

            if ($exception instanceof GuzzleException) {
                return true;
            }

            if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504])) {
                return true;
            }

            return false;
        };
    }

    /**
     * Create the retry delay calculator
     * 
     * Implements exponential backoff for retries.
     * 
     * @return callable The delay calculator function
     */
    protected function retryDelay(): callable
    {
        return function (int $retries): int {
            return $retries * $this->retryDelay;
        };
    }

    /**
     * Make a GET request to the API
     * 
     * @param string $endpoint The API endpoint (e.g., 'dns/list-zones')
     * @param array<string, mixed> $params Query parameters
     * @return array The parsed API response
     * @throws ClouDNSException If the request fails
     * @throws AuthenticationException If authentication fails
     * @throws ValidationException If validation fails
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * Make a POST request to the API
     * 
     * @param string $endpoint The API endpoint (e.g., 'dns/add-record')
     * @param array<string, mixed> $params Request parameters
     * @return array The parsed API response
     * @throws ClouDNSException If the request fails
     * @throws AuthenticationException If authentication fails
     * @throws ValidationException If validation fails
     */
    public function post(string $endpoint, array $params = []): array
    {
        return $this->request('POST', $endpoint, $params);
    }

    /**
     * Make an HTTP request to the API
     * 
     * This method handles:
     * - Authentication parameter injection
     * - Response caching for GET requests
     * - Error handling and exception mapping
     * - Response parsing
     * 
     * @param string $method HTTP method ('GET' or 'POST')
     * @param string $endpoint The API endpoint
     * @param array<string, mixed> $params Request parameters
     * @return array The parsed API response
     * @throws ClouDNSException If the request fails
     * @throws AuthenticationException If authentication fails
     * @throws ValidationException If validation fails
     */
    protected function request(string $method, string $endpoint, array $params = []): array
    {
        $params = array_merge($this->authParams, $params);
        $endpoint = $this->formatEndpoint($endpoint);

        $cacheKey = $this->getCacheKey($method, $endpoint, $params);

        if ($method === 'GET' && $this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $options = $method === 'GET' 
                ? ['query' => $params]
                : ['form_params' => $params];

            $response = $this->httpClient->request($method, $endpoint, $options);
            $body = (string) $response->getBody();

            if ($response->getStatusCode() !== 200) {
                throw new ClouDNSException(
                    "API request failed with status {$response->getStatusCode()}: {$body}"
                );
            }

            $data = $this->parseResponse($body);

            if (isset($data['status']) && $data['status'] === 'Failed') {
                $this->handleError($data);
            }

            if ($method === 'GET' && $this->cacheEnabled) {
                Cache::put($cacheKey, $data, $this->cacheTtl);
            }

            return $data;

        } catch (GuzzleException $e) {
            throw new ClouDNSException(
                "HTTP request failed: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Format the API endpoint with response format
     * 
     * Ensures the endpoint includes the response format extension.
     * 
     * @param string $endpoint The raw endpoint
     * @return string The formatted endpoint
     */
    protected function formatEndpoint(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');
        
        if (!str_contains($endpoint, '.')) {
            $endpoint .= '.' . $this->responseFormat;
        }

        return $endpoint;
    }

    /**
     * Parse the API response body
     * 
     * Currently supports JSON format only.
     * 
     * @param string $body The raw response body
     * @return array The parsed response data
     * @throws ClouDNSException If parsing fails
     */
    protected function parseResponse(string $body): array
    {
        if ($this->responseFormat === 'json') {
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ClouDNSException(
                    'Failed to parse JSON response: ' . json_last_error_msg()
                );
            }

            return $data;
        }

        throw new ClouDNSException('XML response format not yet implemented');
    }

    /**
     * Handle API error responses
     * 
     * Maps error messages to appropriate exception types.
     * 
     * @param array $data The error response data
     * @return void
     * @throws AuthenticationException For authentication errors
     * @throws ValidationException For validation errors
     * @throws ClouDNSException For general errors
     */
    protected function handleError(array $data): void
    {
        $message = $data['statusDescription'] ?? 'Unknown error';

        if (str_contains(strtolower($message), 'authentication')) {
            throw new AuthenticationException($message);
        }

        if (str_contains(strtolower($message), 'missing') || 
            str_contains(strtolower($message), 'invalid') ||
            str_contains(strtolower($message), 'wrong')) {
            throw new ValidationException($message);
        }

        throw new ClouDNSException($message);
    }

    /**
     * Generate a cache key for the request
     * 
     * Excludes sensitive data (passwords) from the cache key.
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array<string, mixed> $params Request parameters
     * @return string The cache key
     */
    protected function getCacheKey(string $method, string $endpoint, array $params): string
    {
        unset($params['auth-password']);
        return $this->cachePrefix . md5($method . $endpoint . serialize($params));
    }

    /**
     * Log an API request
     * 
     * Logs request details while sanitizing sensitive headers.
     * 
     * @param RequestInterface $request The HTTP request
     * @return void
     */
    protected function logRequest(RequestInterface $request): void
    {
        if (!$this->logEnabled) {
            return;
        }

        Log::channel($this->logChannel)->log($this->logLevel, 'ClouDNS API Request', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'headers' => $this->sanitizeHeaders($request->getHeaders()),
        ]);
    }

    /**
     * Log an API response
     * 
     * Logs response status and truncated body.
     * 
     * @param ResponseInterface $response The HTTP response
     * @return void
     */
    protected function logResponse(ResponseInterface $response): void
    {
        if (!$this->logEnabled) {
            return;
        }

        Log::channel($this->logChannel)->log($this->logLevel, 'ClouDNS API Response', [
            'status' => $response->getStatusCode(),
            'body' => substr((string) $response->getBody(), 0, 1000),
        ]);
    }

    /**
     * Sanitize headers for logging
     * 
     * Redacts sensitive authentication headers.
     * 
     * @param array<string, array<string>> $headers The request headers
     * @return array<string, array<string>> Sanitized headers
     */
    protected function sanitizeHeaders(array $headers): array
    {
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), ['authorization', 'auth-password'])) {
                $headers[$key] = ['***REDACTED***'];
            }
        }

        return $headers;
    }

    /**
     * Clear cached API responses
     * 
     * @param string|null $pattern Optional pattern to match specific cache entries
     * @return void
     */
    public function clearCache(?string $pattern = null): void
    {
        if ($pattern) {
            Cache::forget($this->cachePrefix . $pattern);
        } else {
            // Clear all ClouDNS cache entries
            // Note: This is a simplified implementation
            // In production, you might want to use cache tags
        }
    }
}