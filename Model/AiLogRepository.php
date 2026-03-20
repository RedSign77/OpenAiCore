<?php

/**
 * AiCore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Proprietary License that is bundled
 * with this package in the file COPYING.txt.
 *
 * @category   Vendor
 * @package    WebtechSolutions_AiCore
 * @copyright  Copyright (c) 2026 Webtech Solutions
 * @license    Proprietary
 */

declare(strict_types=1);

namespace WebtechSolutions\AiCore\Model;

use WebtechSolutions\AiCore\Model\ResourceModel\AiLog as AiLogResource;

class AiLogRepository
{
    /**
     * USD cost per 1 000 tokens (input / output) by model prefix.
     * Prices from OpenAI pricing page (2025-Q1).
     */
    private const PRICING = [
        'gpt-4o-mini'          => ['input' => 0.000150, 'output' => 0.000600],
        'gpt-4o'               => ['input' => 0.002500, 'output' => 0.010000],
        'gpt-4-turbo'          => ['input' => 0.010000, 'output' => 0.030000],
        'gpt-4'                => ['input' => 0.030000, 'output' => 0.060000],
        'gpt-3.5-turbo'        => ['input' => 0.000500, 'output' => 0.001500],
        'o1-preview'           => ['input' => 0.015000, 'output' => 0.060000],
        'o1-mini'              => ['input' => 0.003000, 'output' => 0.012000],
    ];

    public function __construct(
        protected readonly AiLogFactory  $aiLogFactory,
        protected readonly AiLogResource $resource
    ) {
    }

    public function save(AiLog $log): void
    {
        $this->resource->save($log);
    }

    /**
     * Compute estimated USD cost for a given model and token counts.
     */
    public function computeCost(string $model, int $promptTokens, int $completionTokens): ?float
    {
        $pricing = null;
        foreach (self::PRICING as $prefix => $rates) {
            if (str_starts_with(strtolower($model), $prefix)) {
                $pricing = $rates;
                break;
            }
        }

        if ($pricing === null) {
            return null;
        }

        // Prices are per 1 000 tokens
        return ($promptTokens / 1000 * $pricing['input'])
             + ($completionTokens / 1000 * $pricing['output']);
    }

    /**
     * Persist an AI query log entry.
     */
    public function log(
        string  $model,
        string  $prompt,
        array   $requestData,
        ?array  $responseData,
        int     $httpStatus,
        string  $status,
        int     $promptTokens = 0,
        int     $completionTokens = 0,
        int     $totalTokens = 0,
        ?string $errorMessage = null,
        ?string $module = null
    ): void {
        $estimatedCost = ($status === 'success' && ($promptTokens + $completionTokens) > 0)
            ? $this->computeCost($model, $promptTokens, $completionTokens)
            : null;

        $entry = $this->aiLogFactory->create();
        $entry->setData([
            'module'            => $module,
            'model'             => $model,
            'prompt'            => $prompt,
            'request_data'      => json_encode($requestData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'response_data'     => $responseData !== null
                ? json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
            'http_status'       => $httpStatus,
            'status'            => $status,
            'prompt_tokens'     => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens'      => $totalTokens,
            'estimated_cost'    => $estimatedCost,
            'error_message'     => $errorMessage,
        ]);
        $this->save($entry);
    }
}
