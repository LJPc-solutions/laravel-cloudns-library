<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Client;

use LJPc\ClouDNS\Client\ClouDNSClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClouDNSClientRetryTest extends TestCase
{
    public function test_retry_decider_method(): void
    {
        $client = new ClouDNSClient(
            authId: 'test-id',
            authPassword: 'test-pass',
            retryTimes: 3,
            cacheEnabled: false,
            logEnabled: false
        );

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('retryDecider');
        $method->setAccessible(true);
        
        $decider = $method->invoke($client);
        
        // Test with max retries reached
        $this->assertFalse($decider(3, $this->createMock(RequestInterface::class)));
        
        // Test with guzzle exception
        $exception = new \GuzzleHttp\Exception\ConnectException('Test', $this->createMock(RequestInterface::class));
        $this->assertTrue($decider(1, $this->createMock(RequestInterface::class), null, $exception));
        
        // Test with retryable status codes
        $response429 = $this->createMock(ResponseInterface::class);
        $response429->method('getStatusCode')->willReturn(429);
        $this->assertTrue($decider(1, $this->createMock(RequestInterface::class), $response429));
        
        $response500 = $this->createMock(ResponseInterface::class);
        $response500->method('getStatusCode')->willReturn(500);
        $this->assertTrue($decider(1, $this->createMock(RequestInterface::class), $response500));
        
        $response502 = $this->createMock(ResponseInterface::class);
        $response502->method('getStatusCode')->willReturn(502);
        $this->assertTrue($decider(1, $this->createMock(RequestInterface::class), $response502));
        
        $response503 = $this->createMock(ResponseInterface::class);
        $response503->method('getStatusCode')->willReturn(503);
        $this->assertTrue($decider(1, $this->createMock(RequestInterface::class), $response503));
        
        $response504 = $this->createMock(ResponseInterface::class);
        $response504->method('getStatusCode')->willReturn(504);
        $this->assertTrue($decider(1, $this->createMock(RequestInterface::class), $response504));
        
        // Test with non-retryable status code
        $response200 = $this->createMock(ResponseInterface::class);
        $response200->method('getStatusCode')->willReturn(200);
        $this->assertFalse($decider(1, $this->createMock(RequestInterface::class), $response200));
        
        $response400 = $this->createMock(ResponseInterface::class);
        $response400->method('getStatusCode')->willReturn(400);
        $this->assertFalse($decider(1, $this->createMock(RequestInterface::class), $response400));
    }
    
    public function test_retry_delay_method(): void
    {
        $client = new ClouDNSClient(
            authId: 'test-id',
            authPassword: 'test-pass',
            retryDelay: 1000,
            cacheEnabled: false,
            logEnabled: false
        );

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('retryDelay');
        $method->setAccessible(true);
        
        $delayCallback = $method->invoke($client);
        
        // Test exponential backoff
        $this->assertEquals(1000, $delayCallback(1));
        $this->assertEquals(2000, $delayCallback(2));
        $this->assertEquals(3000, $delayCallback(3));
    }
    
    public function test_build_auth_params_method(): void
    {
        // Test main user
        $client = new ClouDNSClient(
            authId: 'main-id',
            authPassword: 'main-pass',
            isSubUser: false,
            cacheEnabled: false,
            logEnabled: false
        );

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('buildAuthParams');
        $method->setAccessible(true);
        
        $params = $method->invoke($client);
        
        $this->assertEquals([
            'auth-password' => 'main-pass',
            'auth-id' => 'main-id'
        ], $params);
        
        // Test sub user with ID
        $subClient = new ClouDNSClient(
            authId: 'sub-id',
            authPassword: 'sub-pass',
            isSubUser: true,
            useSubUsername: false,
            cacheEnabled: false,
            logEnabled: false
        );

        $subParams = $method->invoke($subClient);
        
        $this->assertEquals([
            'auth-password' => 'sub-pass',
            'sub-auth-id' => 'sub-id'
        ], $subParams);
        
        // Test sub user with username
        $subUserClient = new ClouDNSClient(
            authId: 'sub-username',
            authPassword: 'sub-pass',
            isSubUser: true,
            useSubUsername: true,
            cacheEnabled: false,
            logEnabled: false
        );

        $subUserParams = $method->invoke($subUserClient);
        
        $this->assertEquals([
            'auth-password' => 'sub-pass',
            'sub-auth-user' => 'sub-username'
        ], $subUserParams);
    }
}