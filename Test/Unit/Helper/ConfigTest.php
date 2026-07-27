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

namespace WebtechSolutions\AiCore\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WebtechSolutions\AiCore\Helper\Config;

class ConfigTest extends TestCase
{
    private ScopeConfigInterface&MockObject $scopeConfig;
    private EncryptorInterface&MockObject   $encryptor;
    private Config                          $config;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->encryptor   = $this->createMock(EncryptorInterface::class);

        $context = $this->createMock(Context::class);
        $context->method('getScopeConfig')->willReturn($this->scopeConfig);

        $this->config = new Config($context, $this->encryptor);
    }

    public function testIsEnabledReturnsTrueWhenFlagSet(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with('aicore/general/enabled')
            ->willReturn(true);

        $this->assertTrue($this->config->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenFlagNotSet(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with('aicore/general/enabled')
            ->willReturn(false);

        $this->assertFalse($this->config->isEnabled());
    }

    public function testGetApiKeyCallsDecrypt(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('aicore/general/api_key')
            ->willReturn('encrypted_value');

        $this->encryptor->expects($this->once())
            ->method('decrypt')
            ->with('encrypted_value')
            ->willReturn('sk-plaintext-key');

        $this->assertSame('sk-plaintext-key', $this->config->getApiKey());
    }

    public function testGetApiKeyReturnsEmptyStringWhenNotConfigured(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('aicore/general/api_key')
            ->willReturn('');

        $this->encryptor->expects($this->never())->method('decrypt');

        $this->assertSame('', $this->config->getApiKey());
    }

    public function testGetTimeoutReturnsInt(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('aicore/general/timeout')
            ->willReturn('30');

        $this->assertSame(30, $this->config->getTimeout());
        $this->assertIsInt($this->config->getTimeout());
    }

    public function testGetTimeoutReturnsDefaultWhenNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('aicore/general/timeout')
            ->willReturn(null);

        $this->assertSame(60, $this->config->getTimeout());
    }

    public function testGetDefaultModelReturnsConfiguredValue(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('aicore/general/model')
            ->willReturn('gpt-4o');

        $this->assertSame('gpt-4o', $this->config->getDefaultModel());
    }

    public function testGetApiEndpointFallsBackToDefault(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('aicore/general/api_endpoint')
            ->willReturn('');

        $this->assertSame(
            'https://api.openai.com/v1/chat/completions',
            $this->config->getApiEndpoint()
        );
    }

    public function testIsLoggingEnabledReturnsFlag(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with('aicore/logging/logging_enabled')
            ->willReturn(true);

        $this->assertTrue($this->config->isLoggingEnabled());
    }
}
