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

namespace WebtechSolutions\AiCore\Test\Unit\Model\Service;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use WebtechSolutions\AiCore\Helper\Config;
use WebtechSolutions\AiCore\Model\Sanitizer;
use WebtechSolutions\AiCore\Model\Service\OpenAiClient;

class OpenAiClientTest extends TestCase
{
    private CurlFactory&MockObject  $curlFactory;
    private Curl&MockObject         $curl;
    private Config&MockObject       $config;
    private Sanitizer&MockObject    $sanitizer;
    private LoggerInterface&MockObject $logger;
    private OpenAiClient            $client;

    protected function setUp(): void
    {
        $this->curlFactory = $this->createMock(CurlFactory::class);
        $this->curl        = $this->createMock(Curl::class);
        $this->config      = $this->createMock(Config::class);
        $this->sanitizer   = $this->createMock(Sanitizer::class);
        $this->logger      = $this->createMock(LoggerInterface::class);

        $this->curlFactory->method('create')->willReturn($this->curl);

        $this->client = new OpenAiClient(
            $this->curlFactory,
            $this->config,
            $this->sanitizer,
            $this->logger
        );
    }

    private function configureEnabledDefaults(): void
    {
        $this->config->method('isEnabled')->willReturn(true);
        $this->config->method('getApiKey')->willReturn('sk-test');
        $this->config->method('getOrganizationId')->willReturn('');
        $this->config->method('getDefaultModel')->willReturn('gpt-4o-mini');
        $this->config->method('getApiEndpoint')->willReturn('https://api.openai.com/v1/chat/completions');
        $this->config->method('getTimeout')->willReturn(60);
        $this->config->method('isLoggingEnabled')->willReturn(false);
    }

    public function testQueryReturnsParsedResponseOnSuccess(): void
    {
        $this->configureEnabledDefaults();

        $this->sanitizer->method('mask')->willReturnArgument(0);

        $responseBody = json_encode([
            'choices' => [['message' => ['content' => '{"result":"ok"}']]],
            'usage'   => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
        ]);

        $this->curl->method('getStatus')->willReturn(200);
        $this->curl->method('getBody')->willReturn($responseBody);

        $result = $this->client->query('test prompt', ['key' => 'value']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('choices', $result);
    }

    public function testQueryLogsWarningOn429(): void
    {
        $this->configureEnabledDefaults();
        $this->sanitizer->method('mask')->willReturnArgument(0);

        $this->curl->method('getStatus')->willReturn(429);
        $this->curl->method('getBody')->willReturn('rate limited');

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('429'));

        $result = $this->client->query('prompt', []);
        $this->assertSame([], $result);
    }

    public function testQueryLogsErrorOn401(): void
    {
        $this->configureEnabledDefaults();
        $this->sanitizer->method('mask')->willReturnArgument(0);

        $this->curl->method('getStatus')->willReturn(401);
        $this->curl->method('getBody')->willReturn('unauthorized');

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('401'));

        $result = $this->client->query('prompt', []);
        $this->assertSame([], $result);
    }

    public function testQueryReturnsEmptyArrayOnNon200(): void
    {
        $this->configureEnabledDefaults();
        $this->sanitizer->method('mask')->willReturnArgument(0);

        $this->curl->method('getStatus')->willReturn(500);
        $this->curl->method('getBody')->willReturn('internal error');

        $this->logger->expects($this->once())->method('error');

        $result = $this->client->query('prompt', []);
        $this->assertSame([], $result);
    }

    public function testQueryCallsSanitizerBeforeSending(): void
    {
        $this->configureEnabledDefaults();

        $inputData   = ['email' => 'user@example.com'];
        $maskedData  = ['email' => '[EMAIL_MASKED]'];

        $this->sanitizer->expects($this->once())
            ->method('mask')
            ->with($inputData)
            ->willReturn($maskedData);

        $this->curl->method('getStatus')->willReturn(200);
        $this->curl->method('getBody')->willReturn(json_encode([
            'choices' => [['message' => ['content' => '{}']]],
            'usage'   => [],
        ]));

        $this->client->query('prompt', $inputData);
    }

    public function testModelOverrideIsUsed(): void
    {
        $this->configureEnabledDefaults();
        $this->sanitizer->method('mask')->willReturnArgument(0);

        // Capture the payload posted to verify the override model was used
        $capturedPayload = null;
        $this->curl->method('post')->willReturnCallback(
            function (string $url, string $payload) use (&$capturedPayload) {
                $capturedPayload = json_decode($payload, true);
            }
        );

        $this->curl->method('getStatus')->willReturn(200);
        $this->curl->method('getBody')->willReturn(json_encode([
            'choices' => [['message' => ['content' => '{}']]],
            'usage'   => [],
        ]));

        $this->client->query('prompt', [], 'gpt-4o');

        $this->assertSame('gpt-4o', $capturedPayload['model']);
    }

    public function testQueryReturnsEmptyOnInvalidJson(): void
    {
        $this->configureEnabledDefaults();
        $this->sanitizer->method('mask')->willReturnArgument(0);

        $this->curl->method('getStatus')->willReturn(200);
        $this->curl->method('getBody')->willReturn('not valid json {{');

        $this->logger->expects($this->once())->method('error');

        $result = $this->client->query('prompt', []);
        $this->assertSame([], $result);
    }

    public function testQueryReturnsEmptyWhenDisabled(): void
    {
        $this->config->method('isEnabled')->willReturn(false);

        $this->sanitizer->expects($this->never())->method('mask');
        $this->curlFactory->expects($this->never())->method('create');

        $this->logger->expects($this->once())->method('warning');

        $result = $this->client->query('prompt', []);
        $this->assertSame([], $result);
    }
}
