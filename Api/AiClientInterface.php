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

namespace WebtechSolutions\AiCore\Api;

/**
 * Shared AI Client Interface
 *
 * Provides a unified entry point for OpenAI API communication.
 * All consumer modules should inject this interface rather than
 * implementing their own HTTP client.
 */
interface AiClientInterface
{
    /**
     * Send a prompt to the AI and receive a structured response.
     *
     * Returns the full decoded OpenAI response envelope as an array
     * (callers can access choices[0], usage, etc.) or an empty array on failure.
     *
     * @param string      $prompt        System/instruction prompt
     * @param array       $data          User data payload (PII will be masked automatically)
     * @param string|null $modelOverride Override the configured default model for this call
     * @return array|string
     */
    public function query(string $prompt, array $data, ?string $modelOverride = null): array|string;
}
