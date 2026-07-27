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

namespace WebtechSolutions\AiCore\Model;

/**
 * PII masking for outgoing AI payloads.
 *
 * Masks email addresses, IPv4 addresses, bearer tokens, and long digit
 * sequences (card/account numbers) before data leaves the server.
 */
class Sanitizer
{
    private const PATTERNS = [
        '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i' => '[EMAIL_MASKED]',
        '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/'  => '[IP_MASKED]',
        '/Bearer\s+\S+/'                               => '[TOKEN_MASKED]',
        // 12–19 consecutive digits: covers card numbers, IBANs, account numbers
        '/\b\d{12,19}\b/'                              => '[ACCOUNT_MASKED]',
    ];

    /**
     * Recursively mask PII in all string values of the given array.
     *
     * @param array $data
     * @return array
     */
    public function mask(array $data): array
    {
        return $this->maskRecursive($data);
    }

    private function maskRecursive(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'maskRecursive'], $value);
        }

        if (is_string($value)) {
            foreach (self::PATTERNS as $pattern => $replacement) {
                $value = (string)preg_replace($pattern, $replacement, $value);
            }
            return $value;
        }

        // int, float, bool, null — pass through unchanged
        return $value;
    }
}
