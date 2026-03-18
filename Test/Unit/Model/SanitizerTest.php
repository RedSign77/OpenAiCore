<?php


/**
 * AiCore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Proprietary License that is bundled
 * with this package in the file COPYING.txt.
 * It is also available through the world-wide-web at this URL:
 * [A weboldalad URL-je]/license
 *
 * @category   Vendor
 * @package    WebtechSolutions_AiCore
 * @copyright  Copyright (c) 2026 Webtech Solutions
 * @license    Proprietary
 */

declare(strict_types=1);

namespace WebtechSolutions\AiCore\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use WebtechSolutions\AiCore\Model\Sanitizer;

class SanitizerTest extends TestCase
{
    private Sanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new Sanitizer();
    }

    public function testMasksEmail(): void
    {
        $result = $this->sanitizer->mask(['message' => 'Contact user@example.com for details']);
        $this->assertStringContainsString('[EMAIL_MASKED]', $result['message']);
        $this->assertStringNotContainsString('user@example.com', $result['message']);
    }

    public function testMasksIpAddress(): void
    {
        $result = $this->sanitizer->mask(['ip' => 'Request from 192.168.1.100']);
        $this->assertStringContainsString('[IP_MASKED]', $result['ip']);
        $this->assertStringNotContainsString('192.168.1.100', $result['ip']);
    }

    public function testMasksBearerToken(): void
    {
        $result = $this->sanitizer->mask(['auth' => 'Authorization: Bearer abc123xyz']);
        $this->assertStringContainsString('[TOKEN_MASKED]', $result['auth']);
        $this->assertStringNotContainsString('abc123xyz', $result['auth']);
    }

    public function testMasksAccountNumber(): void
    {
        $result = $this->sanitizer->mask(['card' => 'Card: 4111111111111111']);
        $this->assertStringContainsString('[ACCOUNT_MASKED]', $result['card']);
        $this->assertStringNotContainsString('4111111111111111', $result['card']);
    }

    public function testPreservesShortDigitSequences(): void
    {
        $result = $this->sanitizer->mask(['code' => 'Error code: 1234']);
        $this->assertStringContainsString('1234', $result['code']);
        $this->assertStringNotContainsString('[ACCOUNT_MASKED]', $result['code']);
    }

    public function testSanitizesNestedArrayRecursively(): void
    {
        $result = $this->sanitizer->mask([
            'level1' => [
                'level2' => ['email' => 'nested@test.com'],
            ],
        ]);
        $this->assertSame('[EMAIL_MASKED]', $result['level1']['level2']['email']);
    }

    public function testPreservesNonStringTypes(): void
    {
        $result = $this->sanitizer->mask([
            'count' => 42,
            'flag'  => true,
            'cost'  => 1.23,
            'empty' => null,
        ]);
        $this->assertSame(42, $result['count']);
        $this->assertTrue($result['flag']);
        $this->assertSame(1.23, $result['cost']);
        $this->assertNull($result['empty']);
    }

    public function testMaskMultiplePiiInSameString(): void
    {
        $result = $this->sanitizer->mask([
            'log' => 'User test@test.com from 10.0.0.1 used Bearer mytoken123',
        ]);
        $this->assertStringContainsString('[EMAIL_MASKED]', $result['log']);
        $this->assertStringContainsString('[IP_MASKED]', $result['log']);
        $this->assertStringContainsString('[TOKEN_MASKED]', $result['log']);
        $this->assertStringNotContainsString('test@test.com', $result['log']);
        $this->assertStringNotContainsString('10.0.0.1', $result['log']);
        $this->assertStringNotContainsString('mytoken123', $result['log']);
    }

    public function testReturnsUnchangedDataWhenNoPii(): void
    {
        $data   = ['message' => 'No sensitive data here', 'count' => 5];
        $result = $this->sanitizer->mask($data);
        $this->assertSame('No sensitive data here', $result['message']);
        $this->assertSame(5, $result['count']);
    }
}
