<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Exceptions;

use LJPc\ClouDNS\Exceptions\AuthenticationException;
use LJPc\ClouDNS\Exceptions\ClouDNSException;
use LJPc\ClouDNS\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    public function test_cloudns_exception_with_context(): void
    {
        $context = ['key' => 'value', 'foo' => 'bar'];
        $exception = new ClouDNSException('Test message', 500, null, $context);

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertEquals($context, $exception->getContext());
    }

    public function test_cloudns_exception_set_context(): void
    {
        $exception = new ClouDNSException('Test message');
        $context = ['new' => 'context'];
        
        $result = $exception->setContext($context);

        $this->assertSame($exception, $result);
        $this->assertEquals($context, $exception->getContext());
    }

    public function test_authentication_exception_defaults(): void
    {
        $exception = new AuthenticationException();

        $this->assertEquals('Authentication failed', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
    }

    public function test_authentication_exception_custom(): void
    {
        $previous = new \Exception('Previous exception');
        $context = ['user' => 'test'];
        $exception = new AuthenticationException('Custom auth error', 403, $previous, $context);

        $this->assertEquals('Custom auth error', $exception->getMessage());
        $this->assertEquals(403, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals($context, $exception->getContext());
    }

    public function test_validation_exception_defaults(): void
    {
        $exception = new ValidationException();

        $this->assertEquals('Validation failed', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
        $this->assertEquals([], $exception->getErrors());
    }

    public function test_validation_exception_with_errors(): void
    {
        $errors = [
            'field1' => ['Required field'],
            'field2' => ['Invalid format', 'Too long']
        ];
        $exception = new ValidationException('Custom validation error', 400, null, $errors);

        $this->assertEquals('Custom validation error', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals($errors, $exception->getErrors());
    }

    public function test_validation_exception_set_errors(): void
    {
        $exception = new ValidationException();
        $errors = ['field' => ['error']];
        
        $result = $exception->setErrors($errors);

        $this->assertSame($exception, $result);
        $this->assertEquals($errors, $exception->getErrors());
    }
}