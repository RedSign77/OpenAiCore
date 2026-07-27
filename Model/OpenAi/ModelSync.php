<?php

declare(strict_types=1);

namespace WebtechSolutions\AiCore\Model\OpenAi;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use WebtechSolutions\AiCore\Helper\Config as AiCoreConfig;

class ModelSync
{
    private const ENDPOINT_MODELS   = 'https://api.openai.com/v1/models';
    private const CURL_TIMEOUT       = 15;
    private const CONFIG_MODEL       = 'aicore/general/model';
    private const CONFIG_MODELS_LIST = 'aicore/general/available_models';
    private const CONFIG_SYNCED_AT   = 'aicore/general/models_synced_at';

    private const CHAT_MODEL_PATTERNS = ['gpt-4', 'gpt-3.5', 'o1', 'o3', 'o4', 'chatgpt'];

    private const EXCLUDE_PATTERNS = ['instruct', 'vision', 'realtime', 'audio', 'search', 'computer-use'];

    public function __construct(
        private readonly Curl                 $curl,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly WriterInterface      $configWriter,
        private readonly TypeListInterface    $cacheTypeList,
        private readonly SerializerInterface  $serializer,
        private readonly AiCoreConfig         $aiCoreConfig,
        private readonly LoggerInterface      $logger
    ) {
    }

    /**
     * Fetch available models from OpenAI, store them, and handle fallback.
     * @return array<int,array{value:string,label:string}>|null
     */
    public function sync(): ?array
    {
        $apiKey = $this->aiCoreConfig->getApiKey();

        if (empty($apiKey)) {
            $this->logger->warning('AiCore ModelSync: API key not configured, skipping model sync.');
            return null;
        }

        try {
            $this->curl->addHeader('Authorization', 'Bearer ' . $apiKey);
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->setTimeout(self::CURL_TIMEOUT);
            $this->curl->setOption(CURLOPT_TIMEOUT, self::CURL_TIMEOUT);
            $this->curl->get(self::ENDPOINT_MODELS);

            $status = $this->curl->getStatus();
            $body   = $this->curl->getBody();

            if ($status !== 200) {
                $this->logger->error(sprintf('AiCore ModelSync: OpenAI /v1/models returned HTTP %d.', $status));
                return null;
            }

            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['data'])) {
                $this->logger->error('AiCore ModelSync: Invalid response from /v1/models.');
                return null;
            }

            $models = $this->filterAndSortModels($data['data']);

            if (empty($models)) {
                $this->logger->warning('AiCore ModelSync: No usable chat models found in response.');
                return null;
            }

            $this->configWriter->save(self::CONFIG_MODELS_LIST, $this->serializer->serialize($models));
            $this->configWriter->save(self::CONFIG_SYNCED_AT, date('Y-m-d H:i:s'));
            $this->ensureSelectedModelValid($models);
            $this->cacheTypeList->cleanType('config');

            $this->logger->info(sprintf('AiCore ModelSync: Synced %d models successfully.', count($models)));

            return $models;
        } catch (\Exception $e) {
            $this->logger->error('AiCore ModelSync: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array<int,array{value:string,label:string}> $models
     */
    public function ensureSelectedModelValid(array $models): void
    {
        $currentModel = (string)$this->scopeConfig->getValue(self::CONFIG_MODEL);
        $availableIds = array_column($models, 'value');

        if (in_array($currentModel, $availableIds, true)) {
            return;
        }

        $fallback = $this->pickFallbackModel($availableIds);
        $this->logger->warning(sprintf(
            'AiCore ModelSync: Configured model "%s" no longer available. Falling back to "%s".',
            $currentModel,
            $fallback
        ));

        $this->configWriter->save(self::CONFIG_MODEL, $fallback);
        $this->cacheTypeList->cleanType('config');
    }

    /**
     * @return array<int,array{value:string,label:string}>|null
     */
    public function getStoredModels(): ?array
    {
        $raw = $this->scopeConfig->getValue(self::CONFIG_MODELS_LIST);
        if (empty($raw)) {
            return null;
        }

        try {
            $models = $this->serializer->unserialize($raw);
            return is_array($models) ? $models : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param array<int,array<string,mixed>> $rawModels
     * @return array<int,array{value:string,label:string}>
     */
    private function filterAndSortModels(array $rawModels): array
    {
        $filtered = [];
        foreach ($rawModels as $model) {
            $id = strtolower((string)($model['id'] ?? ''));
            if ($this->isChatModel($id)) {
                $filtered[] = $id;
            }
        }

        $filtered = array_unique($filtered);
        usort($filtered, [$this, 'compareModelIds']);

        return array_map(static fn (string $id) => ['value' => $id, 'label' => $id], $filtered);
    }

    private function isChatModel(string $id): bool
    {
        $isChat = false;
        foreach (self::CHAT_MODEL_PATTERNS as $pattern) {
            if (str_contains($id, $pattern)) {
                $isChat = true;
                break;
            }
        }
        if (!$isChat) {
            return false;
        }
        foreach (self::EXCLUDE_PATTERNS as $exclude) {
            if (str_contains($id, $exclude)) {
                return false;
            }
        }
        return true;
    }

    private function compareModelIds(string $a, string $b): int
    {
        $aIsMini = str_contains($a, 'mini') || str_contains($a, 'nano');
        $bIsMini = str_contains($b, 'mini') || str_contains($b, 'nano');
        $aPrefix = preg_replace('/-mini.*|-nano.*/', '', $a);
        $bPrefix = preg_replace('/-mini.*|-nano.*/', '', $b);

        if ($aPrefix === $bPrefix) {
            return $aIsMini <=> $bIsMini;
        }
        return strcmp($b, $a);
    }

    /** @param string[] $availableIds */
    private function pickFallbackModel(array $availableIds): string
    {
        foreach ($availableIds as $id) {
            if (str_contains($id, 'mini')) {
                return $id;
            }
        }
        return $availableIds[0] ?? 'gpt-4o-mini';
    }
}
