<?php


/**
 * MageSentinel / MageInsight Pro
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

namespace WebtechSolutions\AiCore\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AiModel implements OptionSourceInterface
{
    private const FALLBACK_MODELS = [
        ['value' => 'gpt-4o-mini', 'label' => 'GPT-4o Mini (recommended)'],
        ['value' => 'gpt-4o',      'label' => 'GPT-4o'],
    ];

    private const CONFIG_MODELS_LIST = 'aicore/general/available_models';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function toOptionArray(): array
    {
        $raw = $this->scopeConfig->getValue(self::CONFIG_MODELS_LIST);

        if (!empty($raw)) {
            try {
                $models = $this->serializer->unserialize($raw);
                if (is_array($models) && !empty($models)) {
                    return $models;
                }
            } catch (\Exception $e) {
                // fall through to static list
            }
        }

        return self::FALLBACK_MODELS;
    }
}
