<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Tests\Unit\Enums;

use LJPc\ClouDNS\Enums\RecordType;
use PHPUnit\Framework\TestCase;

class RecordTypeTest extends TestCase
{
    public function test_values_returns_all_record_types(): void
    {
        $values = RecordType::values();
        
        $this->assertIsArray($values);
        $this->assertContains('A', $values);
        $this->assertContains('AAAA', $values);
        $this->assertContains('CNAME', $values);
        $this->assertContains('MX', $values);
        $this->assertContains('TXT', $values);
    }

    public function test_requires_priority(): void
    {
        $this->assertTrue(RecordType::MX->requiresPriority());
        $this->assertTrue(RecordType::SRV->requiresPriority());
        $this->assertFalse(RecordType::A->requiresPriority());
        $this->assertFalse(RecordType::CNAME->requiresPriority());
    }

    public function test_requires_port(): void
    {
        $this->assertTrue(RecordType::SRV->requiresPort());
        $this->assertFalse(RecordType::A->requiresPort());
        $this->assertFalse(RecordType::MX->requiresPort());
    }

    public function test_requires_weight(): void
    {
        $this->assertTrue(RecordType::SRV->requiresWeight());
        $this->assertFalse(RecordType::A->requiresWeight());
        $this->assertFalse(RecordType::MX->requiresWeight());
    }

    public function test_get_description(): void
    {
        $this->assertEquals('IPv4 address', RecordType::A->getDescription());
        $this->assertEquals('IPv6 address', RecordType::AAAA->getDescription());
        $this->assertEquals('Mail exchange', RecordType::MX->getDescription());
        $this->assertEquals('Web Redirect', RecordType::WR->getDescription());
    }

    public function test_from_string(): void
    {
        $recordType = RecordType::from('A');
        $this->assertSame(RecordType::A, $recordType);

        $recordType = RecordType::from('MX');
        $this->assertSame(RecordType::MX, $recordType);
    }

    public function test_try_from_returns_null_for_invalid_type(): void
    {
        $recordType = RecordType::tryFrom('INVALID');
        $this->assertNull($recordType);
    }
}