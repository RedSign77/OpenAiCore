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

namespace WebtechSolutions\AiCore\Model\Service;

use Psr\Log\LoggerInterface;
use WebtechSolutions\AiCore\Api\AiClientInterface;
use WebtechSolutions\AiCore\Helper\Config;
use WebtechSolutions\AiCore\Model\Sanitizer;
use Magento\Framework\HTTP\Client\CurlFactory;

class OpenAiClient implements AiClientInterface
{
    public function __construct(
        protected readonly CurlFactory     $curlFactory,
        protected readonly Config          $config,
        protected readonly Sanitizer       $sanitizer,
        protected readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function query(string $prompt, array $data, ?string $modelOverride = null): array|string
    {
        if (!$this->config->isEnabled()) {
            $this->logger->warning('AiCore: Module is disabled. Skipping AI query.');
            return [];
        }

        // 1. PII sanitization
        $cleanData = $this->sanitizer->mask($data);

        // 2. Resolve model
        $model = $modelOverride ?? $this->config->getDefaultModel();

        // 3. Build messages
        // When $data is empty the caller has already embedded everything in $prompt
        // (e.g. a pre-built user message). In that case $prompt is sent as the user
        // role only. When $data is provided, $prompt is the system role and the
        // serialised $data array is the user role.
        if (empty($cleanData)) {
            $messages = [
                ['role' => 'user', 'content' => $prompt],
            ];
        } else {
            $messages = [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user',   'content' => json_encode($cleanData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
            ];
        }

        $payload = json_encode([
            'model'    => $model,
            'messages' => $messages,
        ]);

        // 4. HTTP request
        $client = $this->curlFactory->create();
        $client->addHeader('Content-Type', 'application/json');
        $client->addHeader('Authorization', 'Bearer ' . $this->config->getApiKey());

        $orgId = $this->config->getOrganizationId();
        if ($orgId !== '') {
            $client->addHeader('OpenAI-Organization', $orgId);
        }

        $timeout = $this->config->getTimeout();
        $client->setTimeout($timeout);
        $client->setOption(CURLOPT_TIMEOUT, $timeout);

        $client->post($this->config->getApiEndpoint(), $payload);

        $status = $client->getStatus();
        $body   = $client->getBody();

        // 5. HTTP error handling
        if ($status === 429) {
            $this->logger->warning('AiCore: OpenAI rate limit reached (HTTP 429). Retry later.');
            return [];
        }

        if ($status === 401) {
            $this->logger->error('AiCore: OpenAI API key rejected (HTTP 401). Check key validity and expiry.');
            return [];
        }

        if ($status !== 200) {
            $this->logger->error(sprintf(
                'AiCore: OpenAI API returned HTTP %d. Body: %s',
                $status,
                substr($body, 0, 500)
            ));
            return [];
        }

        // 6. JSON decode
        $response = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('AiCore: OpenAI response is not valid JSON: ' . substr($body, 0, 500));
            return [];
        }

        // 7. Log usage
        if ($this->config->isLoggingEnabled()) {
            $usage = $response['usage'] ?? [];
            $this->logger->info(sprintf(
                'AiCore: Query successful. Model: %s | Prompt tokens: %d | Completion tokens: %d | Total: %d',
                $model,
                (int)($usage['prompt_tokens'] ?? 0),
                (int)($usage['completion_tokens'] ?? 0),
                (int)($usage['total_tokens'] ?? 0)
            ));
        }

        return $response;
    }
}
