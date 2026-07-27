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

use Magento\Framework\Data\OptionSourceInterface;
use WebtechSolutions\AiCore\Model\OpenAi\ModelSync;

class AiModel implements OptionSourceInterface
{
    private const FALLBACK_MODELS = [
        ['value' => 'gpt-4o-mini',  'label' => 'GPT-4o Mini (recommended)'],
        ['value' => 'gpt-4o',       'label' => 'GPT-4o'],
        ['value' => 'gpt-4-turbo',  'label' => 'GPT-4 Turbo'],
        ['value' => 'gpt-3.5-turbo','label' => 'GPT-3.5 Turbo'],
    ];

    public function __construct(
        private readonly ModelSync $modelSync
    ) {
    }

    public function toOptionArray(): array
    {
        $stored = $this->modelSync->getStoredModels();

        if (!empty($stored)) {
            return $stored;
        }

        return self::FALLBACK_MODELS;
    }
}
