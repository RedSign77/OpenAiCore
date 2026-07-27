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

namespace WebtechSolutions\AiCore\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;

class Config extends AbstractHelper
{
    private const XML_PATH_ENABLED          = 'aicore/general/enabled';
    private const XML_PATH_API_KEY          = 'aicore/general/api_key';
    private const XML_PATH_ORGANIZATION_ID  = 'aicore/general/organization_id';
    private const XML_PATH_MODEL            = 'aicore/general/model';
    private const XML_PATH_API_ENDPOINT     = 'aicore/general/api_endpoint';
    private const XML_PATH_TIMEOUT          = 'aicore/general/timeout';
    private const XML_PATH_LOGGING_ENABLED  = 'aicore/logging/logging_enabled';

    private const DEFAULT_ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    private const DEFAULT_TIMEOUT  = 60;

    public function __construct(
        Context $context,
        private readonly EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Returns the decrypted OpenAI API key.
     */
    public function getApiKey(): string
    {
        $raw = (string)$this->scopeConfig->getValue(self::XML_PATH_API_KEY);

        return $raw !== '' ? $this->encryptor->decrypt($raw) : '';
    }

    public function getOrganizationId(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_ID);
    }

    public function getDefaultModel(): string
    {
        return (string)($this->scopeConfig->getValue(self::XML_PATH_MODEL) ?? 'gpt-4o-mini');
    }

    public function getApiEndpoint(): string
    {
        $value = (string)$this->scopeConfig->getValue(self::XML_PATH_API_ENDPOINT);

        return $value !== '' ? $value : self::DEFAULT_ENDPOINT;
    }

    public function getTimeout(): int
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_TIMEOUT);

        return $value !== null ? (int)$value : self::DEFAULT_TIMEOUT;
    }

    public function isLoggingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LOGGING_ENABLED);
    }
}
